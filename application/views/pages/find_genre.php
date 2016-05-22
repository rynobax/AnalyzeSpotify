<?php
// http://ws.audioscrobbler.com/2.0/?method=artist.gettoptags&artist=walk%20the%20moon&api_key=6a23add5eb8a989f4d8c897d76ffc21e&format=json

//http://ws.audioscrobbler.com/2.0/?method=tag.gettopartists&tag=seen%20live&api_key=6a23add5eb8a989f4d8c897d76ffc21e&format=json

$this->load->helper('find_genre');

?>
	<form>
	  Artist: 
	  <input type="text" name="artist"><br>
	  <input type="submit" value="Get Genre">
	</form>
<?php

	$artist = $_GET['artist'];
	$genre = get_genre_from_last_fm($artist, 0);
	echo "<br><br>Genre: ".$genre;
?>