<?php
echo $this->input->ip_address();
$uri  = rtrim(dirname($_SERVER['PHP_SELF']), '/\\');
$action = "spotify_login";
if($uri != "/spotifyapp/index.php/pages"){
	$action = "/spotifyapp/index.php/pages/view/spotify_login";
}
?>
<FORM METHOD="LINK" ACTION="<?php echo $action; ?>">
	<INPUT TYPE="submit" VALUE="Do Spotify Stuff">
</FORM>