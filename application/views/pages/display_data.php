<?php
	include_once './vendor/autoload.php';

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
					array_push($track->artists, $artist->name);
				}
				$track->popularity = $item->track->popularity;
				array_push($playlist, $track);
			}
			$song_count += $song_limit;
		}

		//

		function cmp_pop($a, $b) {
		    if ($a->popularity == $b->popularity) {
		        return 0;
		    }
		    return ($a->popularity > $b->popularity) ? -1 : 1;
		}

		uasort($playlist, "cmp_pop");
		//echo "<pre>"; print_r($playlist); echo "</pre>";
		foreach ($playlist as $track) {
			echo $track->popularity." - ".$track->name."<br>";
		}
	}else{
		echo "wrong params yo";
	}
?>
