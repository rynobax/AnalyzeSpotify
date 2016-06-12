<style>
.jumbotron {
	padding-top: 12px;
}
.button-text {
	font-size: 150%;
}
</style>
<div class="container">
  <div class="jumbotron" style="text-align: center">
	<h1><b>Pick a Playlist:</b></h1>
	<?php
		include_once './vendor/autoload.php';

		if(isset($_SESSION["token"])){
		$api = new SpotifyWebAPI\SpotifyWebAPI();
		$api->setAccessToken($_SESSION["token"]);
		try {
			$playlists = $api->getMyPlaylists();
		} catch (Exception $e) {
			$host  = $_SERVER['HTTP_HOST'];
			$uri  = rtrim(dirname($_SERVER['PHP_SELF']), '/\\');
			$extra = 'spotify_login';
			$redirecturl = "http://$host$uri/$extra";
			$session = new SpotifyWebAPI\Session(
				'b0b2f940fc9d48a6864eff64a6f73dbe', 
				'c662a156c62e40ffb90b3c6bb90a8b89', 
				$redirecturl
			);
			$session->refreshAccessToken($_SESSION["refresh-token"]);
			$accessToken = $session->getAccessToken();
			$api->setAccessToken($accessToken);
			$_SESSION["token"] = $accessToken;
		}


		$i = 0;
		$bs_column_start = '<div class="col-sm-6">';
		$bs_row_start = '<div class="row">';
		$bs_div_end = "</div>";
		foreach ($playlists->items as $playlist) {?>
			<?php if($i%2 == 0) {
				echo $bs_row_start;
    			echo $bs_column_start;
			}else{ 
    			echo $bs_column_start;
			} ?>
			<form action="analyze" method="post" id="form<?php echo $i; ?>">
				<input type="hidden" name="userid" value="<?php echo $playlist->owner->id; ?>">
				<input type="hidden" name="playlistid" value="<?php echo $playlist->id; ?>">
				<input type="hidden" name="playlistname" value="<?php echo $playlist->name; ?>">
			</form>
			<button class="btn btn-success btn-lg btn-block" type="submit" form="form<?php echo $i; ?>">
				<div class="button-text">
					<?php echo $playlist->name; ?>
				</div>
			</button>
			<?php if($i%2 == 0) {
				echo $bs_div_end;
			}else{
				echo $bs_div_end;
				echo $bs_div_end;
			} ?>

			<?php $i++; ?>
		<?php }
		}else{
			$host  = $_SERVER['HTTP_HOST'];
			$uri  = rtrim(dirname($_SERVER['PHP_SELF']), '/\\');
			$extra = '';  // page to redirect to
			$redirecturl = "http://$host$uri/$extra";
			header("Location: http://$host$uri/$extra");
		}
	?>
	</div>
</div>