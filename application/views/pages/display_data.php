<script type="text/javascript" src="/spotifyapp/assets/js/Chart.bundle.js" ></script>
<script type="text/javascript" src="assets/js/Chart.bundle.js" ></script>
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
    $api = new SpotifyWebAPI\SpotifyWebAPI();
    $api->setAccessToken($_SESSION["token"]);
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
    echo('<br>It took ' . timer_calc() . ' seconds to retrieve the get a list of all the playlist songs');

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
    $popularity_average = 100 - round($popularity_average);
    echo "<br>Hipster rating: $popularity_average <br><br>";

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
    print('<br>It took ' . timer_calc() . ' seconds to retrieve the get a list of all the playlist artists');

    /* Get artist genres */
    timer_starts();
    $artist_names = array();
    foreach ($artists as $artist) {
        $artist_names[$artist->name] = $artist->name;
    }
    $depth = 15;
    $artist_genres = get_multiple_genres($artist_names, $depth);
    timer_ends();
    print('<br>It took ' . timer_calc() . ' seconds to retrieve all the genres');

    timer_starts();
    $genre_count = generate_genre_count($artist_genres, $artists);
    $month_genre_stats = generate_month_genre_stats($playlist, $artists);
    $genre_list = generate_genre_list($month_genre_stats, round(count($artists) * 0.1), $genre_count);
    $data1 = build_percentage_data($month_genre_stats, $genre_list);
    $data2 = build_cumulative_data($month_genre_stats, $genre_list);
    $data3 = build_line_data($month_genre_stats, $genre_list);
    generate_solid_line_graph_html(1, $data1, $genre_list);
    generate_solid_line_graph_html(2, $data2, $genre_list);
    generate_line_graph_html(3, $data3, $genre_list);
}

/* First Executed Code */
if (isset($_GET['userid']) && isset($_GET['playlistid'])) {
    print_data();
} else {
    echo "wrong params yo";
}
?>