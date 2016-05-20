<?php
require 'vendor/autoload.php';

$host  = $_SERVER['HTTP_HOST'];
$uri  = rtrim(dirname($_SERVER['PHP_SELF']), '/\\');
$extra = 'spotify_login';  // page to redirect to
$redirecturl = "http://$host$uri/$extra";

$session = new SpotifyWebAPI\Session(
	'b0b2f940fc9d48a6864eff64a6f73dbe', 
	'c662a156c62e40ffb90b3c6bb90a8b89', 
	$redirecturl
	);
$api = new SpotifyWebAPI\SpotifyWebAPI();

if (isset($_GET['code'])) {
    $session->requestAccessToken($_GET['code']);
    $api->setAccessToken($session->getAccessToken());

    $_SESSION['token'] = $session->getAccessToken();

    $api = new SpotifyWebAPI\SpotifyWebAPI();

    $host  = $_SERVER['HTTP_HOST'];
	$uri  = rtrim(dirname($_SERVER['PHP_SELF']), '/\\');
	$extra = 'pick_playlist';  // page to redirect to

	header("Location: http://$host$uri/$extra");
} else {
    header('Location: ' . $session->getAuthorizeUrl(array(
        'scope' => array(
            'playlist-read-private',
        )
    )));
    die();
}
?>