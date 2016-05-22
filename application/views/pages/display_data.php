<script type="text/javascript" src="/spotifyapp/js/Chart.bundle.js" ></script>
<script type="text/javascript" src="/js/Chart.bundle.js" ></script>
<?php
	include_once './vendor/autoload.php';
	$this->load->helper('find_genre');
	$this->load->helper('dates');
	$this->load->helper('graph');
	ini_set('max_execution_time', 0); 
	/* Timer stuff */
	$tstart = 0;
	$tend = 0;

	function timer_starts()
	{
	global $tstart;

	$tstart=microtime(true); ;

	}

	function timer_ends()
	{
	global $tend;

	$tend=microtime(true); ;

	}

	function timer_calc()
	{
	global $tstart,$tend;

	return (round($tend - $tstart,2));
	}

	/* Comparison functions */
	function cmp_pop($a, $b) {
		if ($a->popularity == $b->popularity) {
		    return 0;
		}
		return ($a->popularity > $b->popularity) ? -1 : 1;
	}

	function cmp_cnt($a, $b) {
		if ($a->count == $b->count) {
		    return 0;
		}
		return ($a->count > $b->count) ? -1 : 1;
	}

	function cmp_flw($a, $b) {
		if ($a->followers == $b->followers) {
		    return 0;
		}
		return ($a->followers > $b->followers) ? -1 : 1;
	}

	/* Code */
	if (isset($_GET['userid']) && isset($_GET['playlistid'])){
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
		while($song_count < $playlist_size){
			$options = array('limit' => $song_limit, 'offset' => $song_count);
			$playlist_portion = $api->getUserPlaylistTracks($userid, $playlistid, $options);
			foreach($playlist_portion->items as $item){
				$track = new stdClass();
				$track->added_at = $item->added_at;
				$track->name = $item->track->name;
				$track->album = $item->track->album->name;
				$track->artists = array();
				foreach ($item->track->artists as $artist) {
					$artist_object = new stdClass();
					$artist_object->name = $artist->name;
					$artist_object->id = $artist->id;
					if($artist->id == NULL) continue;
					array_push($track->artists, $artist_object);
				}
				if(count($track->artists) == 0) continue;
				$track->popularity = $item->track->popularity;
				array_push($playlist, $track);
			}
			$song_count += $song_limit;
		}
		timer_ends();
		echo('<br>It took '.timer_calc().' seconds to retrieve the get a list of all the playlist songs');

		//echo "<pre>"; print_r($playlist); echo "</pre>";
		/*uasort($playlist, "cmp_pop");
		foreach ($playlist as $track) {
			echo $track->popularity." - ".$track->name."<br>";
		}*/

		/* Count of each artist */
		$popularity_average = 0;
		$artistsnames = array();
		foreach ($playlist as $track) {
			$popularity_average += $track->popularity;
			if(!isset($artistsnames[$track->artists[0]->name])){
				$artistsnames[$track->artists[0]->name] = new stdClass();
				$artistsnames[$track->artists[0]->name]->count = 1;
				$artistsnames[$track->artists[0]->name]->id = $track->artists[0]->id;
			}else{
				$artistsnames[$track->artists[0]->name]->count++;
			}
		}
		$popularity_average = $popularity_average / count($playlist);
		$popularity_average = 100 - round($popularity_average);
		echo "<br>Hipster rating: $popularity_average <br><br>";

		/* Gets playlist artists */
		timer_starts();
		$artist_queue = new SplQueue();
		foreach($artistsnames as $name => $stats){
			$artist_queue->push($stats->id);
		}
		$artists = array();
		$total = 0;
		$query_limit = 50;
		while(!$artist_queue->isEmpty()){
			$count = 0;
			$ids = array();
			while($count < $query_limit AND !$artist_queue->isEmpty()){
				array_push($ids, $artist_queue->pop());
				$count++;
				$total++;
			}
			$artistdata = $api->getArtists($ids);
			foreach($artistdata->artists as $artist){
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
		print('<br>It took '.timer_calc().' seconds to retrieve the get a list of all the playlist artists');

		/* Get artist genres */
		timer_starts();
		$artist_names = array();
		foreach ($artists as $artist) {
			$artist_names[$artist->name] = $artist->name;
		}
		$depth = 10;
		//echo "<br>Depth is $depth";
		$artist_genres = get_multiple_genres($artist_names, $depth, 0);
		//var_dump($artist_genres);
		$genre_count = array();
		foreach($artist_genres as $artist_name => $genre) {
			if($genre == "") echo "$artist_name is the culprit";
			$artist = $artists[$artist_name];
			$artist->genre = $genre;
			if(!isset($genre_count[$genre])){
				$genre_count[$genre] = $artist->count;
			}else{
				$genre_count[$genre] += $artist->count;
			}
		}
		timer_ends();
		print('<br>It took '.timer_calc().' seconds to retrieve all the genres');

		arsort($genre_count);
		echo "<table>";
		echo "<tr>";
		echo "<th>Genre</th><th>Count</th>";
		echo "</tr>";
		foreach ($genre_count as $genre => $count) {
			echo "<tr><td>".ucwords($genre)."</td><td>".$count."</td></tr>";
		}
		echo "</table>";

		$oldest_track = $playlist[0];
		$youngest_track = $playlist[0];
		//var_dump($oldest_track);
		foreach ($playlist as $track) {
			if(is_first_older_than_second(date_parse($track->added_at), date_parse($oldest_track->added_at))) {
				$oldest_track = $track;
			}
			if(is_first_younger_than_second(date_parse($track->added_at), date_parse($youngest_track->added_at))) {
				$youngest_track = $track;
			}
		}
		echo "<br>Oldest added song is ".$oldest_track->name;
		echo "<br>Youngest added song is ".$youngest_track->name;

		timer_starts();
		$month_genre_stats = array();
		foreach ($playlist as $track) {
			$date = date_parse($track->added_at);
			$genre = $artists[$track->artists[0]->name]->genre;
			//echo "genre: ".$genre;
			if(!isset($month_genre_stats[$date["month"]."-".$date["year"]]))
				$month_genre_stats[$date["month"]."-".$date["year"]] = array();
			if(!isset($month_genre_stats[$date["month"]."-".$date["year"]][$genre])){
				$month_genre_stats[$date["month"]."-".$date["year"]][$genre] = 1;
			}
			else{
				$month_genre_stats[$date["month"]."-".$date["year"]][$genre]++;
			}
		}

		$genre_list = array();
		$count_cutoff = round(count($playlist) * 0.1); 
		foreach ($month_genre_stats as $date => $stats) {
			foreach ($stats as $genre => $count) {
				if($genre != "NO_TAGS" AND $genre_count[$genre] > $count_cutoff)
					$genre_list[$genre] = true;
			}
		}
		timer_ends();
		print('<br>It took '.timer_calc().' seconds to analyze the months');
		echo "<br>count: ".count($genre_list);
		$colors = generate_n_distinct_colors(count($genre_list));
?>

<canvas id="myChart" width="400" height="400"></canvas>
<script>
var ctx = document.getElementById("myChart");
var data = {
	labels : [<?php
	foreach ($month_genre_stats as $date => $stats) {
		echo '"'.$date.'", ';
	}
	?>],
	datasets : [
		<?php
		foreach ($genre_list as $genre => $ignore) {
			echo "{\n";
				echo "\t\tlabel: "."'$genre',\n";
				$color = array_pop($colors);
				echo "\t\tbackgroundColor : \"rgba(".$color['r'].",".$color['g'].",".$color['b'].",0.4)\",\n";
				//echo "\t\tstrokeColor : '#ACC26D',\n";
				//echo "\t\tpointColor : '#fff',\n";
				//echo "\t\tpointStrokeColor : '#9DB86D',\n";
				echo "\t\tdata :[";
				foreach ($month_genre_stats as $date => $stats) {
					if(isset($stats[$genre])){
						echo $stats[$genre].", ";
					}else{
						echo "0, ";
					}
				}
				echo "]\n";
			echo "\t},";
		}
		?>
	]
}

var myLineChart = new Chart(ctx, {
    type: 'line',
    data: data,
    options: {
        scales: {
            yAxes: [{
                ticks: {
                    beginAtZero:true
                }
            }]
        }
    }
});
</script>
<?php
	}else{
		echo "wrong params yo";
	}
?>