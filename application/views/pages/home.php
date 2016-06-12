<?php

if(isset($_ENV["SERVER_NAME"])){
	$base = "/";
}else{
	$base = base_url();
}

$action_spotify = $base."index.php/pages/view/spotify_login";
$action_dummy = $base."index.php/pages/view/dummy";

if(isset($_SESSION["token"])){
	$url = $action_spotify;
	echo $url;
	header("Location: $url");
}
?>

<style>
.button-text {
	font-size: 150%;
}
.jumbotron {
	padding-top: 12px;
	padding-bottom: 88px;
}
</style>

<FORM id="spotify_login" METHOD="LINK" ACTION="<?php echo $action_spotify; ?>"></FORM>
<FORM id="dummy_login" METHOD="LINK" ACTION="<?php echo $action_dummy; ?>"></FORM>

<div class="container">
  <div class="jumbotron" style="text-align: center">
    <h1><b>Spotify Playlist Analyzer</b></h1>
    <h3>Login to view statistics about one of your spotify playlists!</h3>
    <br>
	<button type="submit" form="spotify_login" value="Submit" class="btn btn-success btn-lg">
		<div class="button-text">Login with Spotify</div>
	</button>
	<br>
	<br>
	<button type="submit" form="dummy_login" value="Submit" class="btn btn-default btn-lg">
		<div class="button-text">Use Dummy Playlist</div>
	</button>
	</div>
</div>