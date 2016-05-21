<?php
	include_once './vendor/autoload.php';

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
					array_push($track->artists, $artist_object);
				}
				$track->popularity = $item->track->popularity;
				array_push($playlist, $track);
			}
			$song_count += $song_limit;
		}

		//echo "<pre>"; print_r($playlist); echo "</pre>";
		/*uasort($playlist, "cmp_pop");
		foreach ($playlist as $track) {
			echo $track->popularity." - ".$track->name."<br>";
		}*/
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
		echo "Hipster rating: $popularity_average <br><br>";

		$artist_queue = new SplQueue();
		foreach($artistsnames as $name => $stats){
			$artist_queue->push($stats->id);
		}
		$artists = array();
		$total = 0;
		$limit = 50;
		while(!$artist_queue->isEmpty()){
			$count = 0;
			$ids = array();
			while($count < $limit AND !$artist_queue->isEmpty()){
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
				$a->genres = $artist->genres;
				$a->followers = $artist->followers->total;
				$a->count = $artistsnames[$a->name]->count;
				array_push($artists, $a);
			}
			unset($ids); 
		}

		uasort($artists, "cmp_cnt");
		echo "<table>";
		echo "<tr>";
		echo "<th>Song Count</th><th>Artist Name</th><th>Popularity</th>";
		echo "</tr>";
		foreach ($artists as $artist) {
			echo "<tr><td>".$artist->count."</td><td>".$artist->name."</td><td>".$artist->popularity."</td></tr>";
		}
		echo "</table>";


	}else{
		echo "wrong params yo";
	}
?>
