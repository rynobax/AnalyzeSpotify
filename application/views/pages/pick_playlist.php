<h2>Pick a Playlist:</h2>
<?php
	include_once './vendor/autoload.php';

	$api = new SpotifyWebAPI\SpotifyWebAPI();
	$api->setAccessToken($_SESSION["token"]);
	$playlists = $api->getMyPlaylists();

	foreach ($playlists->items as $playlist) {
    echo '<a href="display_data?userid='. $playlist->owner->id .'&playlistid='. $playlist->id .'">' . $playlist->name . '</a> <br>';
	}
	//echo "<pre>"; print_r($playlists); echo "</pre>";
?>