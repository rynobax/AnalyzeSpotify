<?php
function parse_tags_result($result){
	$valid_tags = array("Alternative", "Blues", "Classical", "Country", "Electronic", "Experimental", "Folk", "Hiphop", "Indie Rock", "Jazz", "Metal", "Pop", "Punk", "Rap", "Rock", "Soul", "World");
	$valid_tags_few = array("Classical", "Country", "Electronic", "Folk", "Jazz", "Metal", "Pop", "Rap", "Rock");
	$valid_tags = array_map('strtolower', $valid_tags);
	$valid_tags_few = array_map('strtolower', $valid_tags_few);

	if($result == null){
		return "NO_TAGS";
	}
	if(isset($result->error)){
		return "NO_TAGS";
	} 
	$max = count($result->toptags->tag);
	if($max == 0){
		return "NO_TAGS";
	}
	$tags = array();
	$tag_percent_limit = 33;
	for ($i=0; $i < $max; $i++) { 
		if($result->toptags->tag[$i]->count < $tag_percent_limit) break;
			array_push($tags, $result->toptags->tag[$i]->name);
	}
	if(count($tags) == 0){
		return "NO_TAGS";
	}
	foreach ($tags as $tag) {
		if(in_array($tag, $valid_tags_few)) return $tag;
	}
	return "NO_TAGS";
}

function get_multiple_genres($artists, $depth, $iteration){
	/*echo "<br><br>Finding genres for: ";
	foreach ($artists as $artist => $searchfor) {
		echo $artist."->".$searchfor.", ";
	}*/
	$mh = curl_multi_init();
	$chs = array();
	foreach ($artists as $artist => $searchfor) {
		$ch = curl_init();
		$url = "http://ws.audioscrobbler.com/2.0/?method=artist.gettoptags&artist=".urlencode($searchfor)."&api_key=6a23add5eb8a989f4d8c897d76ffc21e&format=json";
		curl_setopt($ch, CURLOPT_URL, "$url");
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
		$chs[$artist] = $ch;
		curl_multi_add_handle($mh, $ch);
	}
	$running = null;
	do {
	    curl_multi_exec($mh, $running);
	} while ($running);
	foreach ($chs as $artist => $ch) {
		curl_multi_remove_handle($mh, $ch);
	}
	curl_multi_close($mh);

	$artist_genres = array();
	foreach ($chs as $artist => $ch) {
		$response = curl_multi_getcontent($ch);
		$result = json_decode($response);
		$artist_genres[$artist] = parse_tags_result($result);
	}

	if($depth > 0){
		$artists_without_tags = array();
		foreach ($artist_genres as $artist => $genre) {
			if($genre == "NO_TAGS"){
				$artists_without_tags[$artist] = $artist;
			}
		}
		$new_searchfors = get_multiple_related_artists($artists_without_tags, $iteration);
		$more_artists = array();
		foreach($new_searchfors as $artist => $searchfor){
			if($searchfor == "NO_SIMILAR_ARTIST_FOUND"){
				// Do nothing
			}else{
				$more_artists[$artist] = $searchfor;
			}
		}
		$more_genres = get_multiple_genres($more_artists, $depth-1, $iteration+1);
		foreach ($more_genres as $artist => $genre) {
			$artist_genres[$artist] = $genre;
		}
	}
	return $artist_genres;
}

function get_multiple_related_artists($artists, $iteration){
	/*foreach ($artists as $artist => $searchfor) {
		echo "<br><br>Getting related for ".$artist."->".$searchfor.", ";
	}*/
	$mh = curl_multi_init();
	$chs = array();
	foreach ($artists as $artist => $searchfor) {
		$ch = curl_init();
		$url = "http://ws.audioscrobbler.com/2.0/?method=artist.getsimilar&artist=".urlencode($artist)."&api_key=6a23add5eb8a989f4d8c897d76ffc21e&format=json";
		curl_setopt($ch, CURLOPT_URL, "$url");
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
		$chs[$artist] = $ch;
		curl_multi_add_handle($mh, $ch);
	}
	$running = null;
	do {
	    curl_multi_exec($mh, $running);
	} while ($running);
	curl_multi_close($mh);

	$artist_genres = array();
	foreach ($chs as $artist => $ch) {
		$response = curl_multi_getcontent($ch);
		$result = json_decode($response);
		if(count($result->similarartists->artist) == 0 OR count($result->similarartists->artist) <= $iteration){
			$artists[$artist] = "NO_SIMILAR_ARTIST_FOUND";
		}else{	
			$similarartist = $result->similarartists->artist[$iteration]->name;
			$artists[$artist] = $similarartist;
		}
	}
	return $artists;
}
?>