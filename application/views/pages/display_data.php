<style>
.jumbotron {
    padding-top: 12px;
}
</style>
<script type="text/javascript">
    function resize_graphs(){
        var h1 = $('#graph1').height();
        var h2 = $('#graph2').height();
        var h3 = $('#graph3').height();
        var height = Math.max(h1, h2, h3);

        for (var i = 1; i <= 3; i++) {
            $('#graph'+i).height(height);
        }
    }
    $(window).resize(resize_graphs);
</script>
<div class="container">
    <div class="jumbotron" style="text-align: center">
<?php
include_once './vendor/autoload.php';
$this->load->helper('find_genre');
$this->load->helper('dates');
$this->load->helper('graph');
$this->load->helper('data_builder');
$this->load->helper('timer');
$this->load->helper('comparison');

ini_set('max_execution_time', 0);

function print_data(){
    $userid = $_GET['userid'];
    $playlistid = $_GET['playlistid'];
    $playlistname = $_GET['playlistname'];
    $api = new SpotifyWebAPI\SpotifyWebAPI();
    $api->setAccessToken($_SESSION["token"]);

    if(isset($_ENV["SERVER_NAME"])){
        $base = "/";
    }else{
        $base = base_url();
    } 
    ?>
    <div class="row">
        <div class="col-sm-1">

            <a href="<?php echo $base.'index.php/pages/view/pick_playlist' ?>" class="btn btn-success">
              <span class="glyphicon glyphicon-new-window"></span>
            </a>
        </div>
        <div class="col-sm-10">
            <h1><b><?php echo $playlistname ?></b></h1>
        </div>
        <div class="col-sm-1">
        </div>
    </div>
    <hr>
    <?php
    timer_starts();
    /* Gets playlist songs */
    $limit_fields = array('total,limit');
    $limit_options = array('fields' => $limit_fields);
    $limit_checker = $api->getUserPlaylistTracks($userid, $playlistid, $limit_options);
    $song_limit = $limit_checker->limit;
    $playlist_size = $limit_checker->total;

    $playlist = array();
    $song_count = 0;
    while ($song_count < $playlist_size) {
        $options = array('limit' => $song_limit, 'offset' => $song_count);
        $playlist_portion = $api->getUserPlaylistTracks($userid, $playlistid, $options);
        foreach ($playlist_portion->items as $item) {
            $track = new stdClass();
            $date = date_parse($item->added_at);
            $track->added_at_year = $date["year"];
            $track->added_at_month = $date["month"];
            $track->added_at_day = $date["day"];
            if (!$track->added_at_year OR ! $track->added_at_month OR ! $track->added_at_day) {
                continue;
            }
            $track->name = $item->track->name;
            $track->album = $item->track->album->name;
            $track->artists = array();
            foreach ($item->track->artists as $artist) {
                $artist_object = new stdClass();
                $artist_object->name = $artist->name;
                $artist_object->id = $artist->id;
                if ($artist->id == NULL) {
                    continue;
                }
                array_push($track->artists, $artist_object);
            }
            if (count($track->artists) == 0) {
                continue;
            }
            $track->popularity = $item->track->popularity;
            array_push($playlist, $track);
        }
        $song_count += $song_limit;
    }
    timer_ends();

    /* Count of each artist */
    $popularity_average = 0;
    $artistsnames = array();
    foreach ($playlist as $track) {
        $popularity_average += $track->popularity;
        if (!isset($artistsnames[$track->artists[0]->name])) {
            $artistsnames[$track->artists[0]->name] = new stdClass();
            $artistsnames[$track->artists[0]->name]->count = 1;
            $artistsnames[$track->artists[0]->name]->id = $track->artists[0]->id;
        } else {
            $artistsnames[$track->artists[0]->name]->count++;
        }
    }
    $popularity_average = $popularity_average / count($playlist);
    $hipster_rating = 100 - round($popularity_average);
    echo "<h4><b>Hipster rating: $hipster_rating / 100</b></h4>";
    echo "The hipster rating is based on the popularity of the playlist's tracks on spotify.";
    echo "<hr>";
    //echo('<br>It took ' . timer_calc() . ' seconds to retrieve the get a list of all the playlist songs');

    /* Gets playlist artists */
    timer_starts();
    $artist_queue = new SplQueue();
    foreach ($artistsnames as $name => $stats) {
        $artist_queue->push($stats->id);
    }
    $artists = array();
    $total = 0;
    $query_limit = 50;
    while (!$artist_queue->isEmpty()) {
        $count = 0;
        $ids = array();
        while ($count < $query_limit AND ! $artist_queue->isEmpty()) {
            array_push($ids, $artist_queue->pop());
            $count++;
            $total++;
        }
        $artistdata = $api->getArtists($ids);
        foreach ($artistdata->artists as $artist) {
            $a = new stdClass();
            $a->id = $artist->id;
            $a->name = $artist->name;
            $a->popularity = $artist->popularity;
            $a->followers = $artist->followers->total;
            $a->count = $artistsnames[$a->name]->count;
            $artists[$a->name] = $a;
        }
        unset($ids);
    }
    timer_ends();
    //print('<br>It took ' . timer_calc() . ' seconds to retrieve the get a list of all the playlist artists');

    /* Get artist genres */
    timer_starts();
    $artist_names = array();
    foreach ($artists as $artist) {
        $artist_names[$artist->name] = $artist->name;
    }
    $depth = 15;
    $artist_genres = get_multiple_genres($artist_names, $depth);
    timer_ends();
    //print('<br>It took ' . timer_calc() . ' seconds to retrieve all the genres');

    timer_starts();

    /* This function counts how many songs in the playlist are of each genre */
    $genre_count = generate_genre_count($artist_genres, $artists);

    /* Calculate the number of songs for each genre added to the playlist each month */
    $month_genre_stats = generate_month_genre_stats($playlist, $artists);

    /* Decides which genres should be shown on the historic graphs */
    $historic_genre_list = generate_genre_list($month_genre_stats, round(count($playlist) * 0.1), $genre_count);

    $data_pie = build_pie_data($genre_count, round(count($playlist) * 0.1));

    arsort($genre_count);
    $color_count = 64;  // Number of colors we generate
    $colors = generate_n_distinct_colors($color_count);
    $initial_offset = 0;
    $jump_size = round($color_count / count($historic_genre_list), 0); // How much should be jumped to get a new color each time
    $rotate_size = round($jump_size / round((count($genre_count) + 1) / count($historic_genre_list)), 0); // When we reach the end, reset, jump by this much, then jump by jump_size repeatedly
    $GLOBALS["colors"] = array();
    $n = $initial_offset;
    foreach ($historic_genre_list as $genre => $ignore) {
        $GLOBALS["colors"][$genre] = $colors[$n];
        $n += $jump_size;
    }
    $n -= $color_count;
    $n += $rotate_size;
    foreach ($genre_count as $genre => $count) {
        if(!isset($GLOBALS["colors"][$genre])){
            $GLOBALS["colors"][$genre] = $colors[$n];
            $n += $jump_size;
            if($n >= $color_count){
                $n -= $color_count;
                $n += $rotate_size;
            }
        }
    }


    /* Print table of genres */
    if(isset($genre_count["NO_TAGS"])){
        $no_tags_count = $genre_count["NO_TAGS"];
        unset($genre_count["NO_TAGS"]);
    }else{
        $no_tags_count = 0;
    }?>
    <div class="row">
        <div class="col-xs-6">
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Genre</th>
                            <th>Song Count</th>
                        </tr>
                    </thead>
                <?php foreach ($genre_count as $genre => $count) {?>
                    <tr bgcolor="<?php color_print($GLOBALS["colors"][$genre]); ?>">
                        <td>
                            <font color="black"><b><?php echo ucwords($genre); ?></b></font>
                        </td>
                        <td>
                            <font color="black"><?php echo $count ?></font>
                        </td>
                    </tr>
                <?php }?>
                </table>
            </div>
        </div>
        <div class="col-xs-6">
            <?php generate_pie_graph_html($data_pie); ?>
        </div>
    </div>
    <?php
    /* Build and draw the graphs */
    $data_percentage = build_percentage_data($month_genre_stats, $historic_genre_list);
    $data_cumulative = build_cumulative_data($month_genre_stats, $historic_genre_list);
    $data_line = build_line_data($month_genre_stats, $historic_genre_list);
    ?>
    <hr>
    <h2><b>Historical Graphs</b></h2>
    These graphs show the distribution of what genre of songs you have been adding to the playlist each month.
    <ul class="nav nav-tabs">
        <li class="active">
            <a href="#graph1" data-toggle="tab" aria-expanded="true">Percentage Area Graph</a>
        </li>
        <li class="">
            <a href="#graph2" data-toggle="tab" aria-expanded="true">Total Area Graph</a>
        </li>
        <li class="">
            <a href="#graph3" data-toggle="tab" aria-expanded="true">Line Graph</a>
        </li>
    </ul>
    <div id="graphTabs" class="tab-content">
        <div class="tab-pane fade active in" id="graph1">
            <?php generate_solid_line_graph_html($data_percentage, $historic_genre_list); ?>
        </div>
        <div class="tab-pane fade in" id="graph2">
            <?php generate_solid_line_graph_html($data_cumulative, $historic_genre_list); ?>
        </div>
        <div class="tab-pane fade in" id="graph3">
            <?php generate_line_graph_html($data_line, $historic_genre_list); ?>
        </div>
    </div>
    
    
    
<?php
}

/* First Executed Code */
if (isset($_GET['userid']) && isset($_GET['playlistid'])) {
    print_data();
} else {
    echo "wrong params yo";
}
?>
    </div>
</div>