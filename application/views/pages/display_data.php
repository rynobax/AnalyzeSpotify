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

    echo "<h1><b>".$playlistname."</b></h1>";
    echo "<hr size=>";
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

    /* Print table of genres */
    arsort($genre_count);
    ?>
    <table class="table table-striped table-hover">
        <thead>
            <tr>
                <th>Genre</th><th>Count</th><th>Percentage</th>
            </tr>
        </thead>
        <tbody>
        <?php
        foreach ($genre_count as $genre => $count) {
            echo "<tr class=\"active\"><td>" . my_ucwords($genre) . "</td><td>" . $count . "</td><td>" . round($count / count($playlist),2) . "</td></tr>";
        }?>
        </tbody>
    </table>
    <?php
    /* Calculate the number of songs for each genre added to the playlist each month */
    $month_genre_stats = generate_month_genre_stats($playlist, $artists);

    /* Decides which genres should be shown on the graph */
    $genre_list = generate_genre_list($month_genre_stats, round(count($playlist) * 0.1), $genre_count);

    /* Build and draw the graphs */
    $data1 = build_percentage_data($month_genre_stats, $genre_list);
    $data2 = build_cumulative_data($month_genre_stats, $genre_list);
    $data3 = build_line_data($month_genre_stats, $genre_list);
    ?>
    <hr>
    <h2><b>HistoricalGraphs</b></h2>
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
            <?php generate_solid_line_graph_html(1, $data1, $genre_list); ?>
        </div>
        <div class="tab-pane fade in" id="graph2">
            <?php generate_solid_line_graph_html(2, $data2, $genre_list); ?>
        </div>
        <div class="tab-pane fade in" id="graph3">
            <?php generate_line_graph_html(3, $data3, $genre_list); ?>
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