<?php

function parse_tags_result($result) {
    $valid_tags = array("Alternative", "Blues", "Classical", "Country", "Electronic", "Experimental", "Folk", "Hiphop", "Indie Rock", "Jazz", "Metal", "Pop", "Punk", "Rap", "Rock", "Soul", "World");
    $valid_tags_few = array("Classical", "Country", "Electronic", "Folk", "Jazz", "Metal", "Pop", "Rap", "Rock");
    $valid_tags = array_map('strtolower', $valid_tags);
    $valid_tags_few = array_map('strtolower', $valid_tags_few);

    if ($result == null) {
        return "NO_TAGS";
    }
    if (isset($result->error)) {
        return "NO_TAGS";
    }
    $max = count($result->toptags->tag);
    if ($max == 0) {
        return "NO_TAGS";
    }
    $tags = array();
    $tag_percent_limit = 33;
    for ($i = 0; $i < $max; $i++) {
        if ($result->toptags->tag[$i]->count < $tag_percent_limit)
            break;
        array_push($tags, $result->toptags->tag[$i]->name);
    }
    if (count($tags) == 0) {
        return "NO_TAGS";
    }
    foreach ($tags as $tag) {
        if (in_array($tag, $valid_tags_few))
            return $tag;
    }
    return "NO_TAGS";
}

function sum_tags_results($result){
    $ignore_tags = "seen live";
    if ($result == null) {
        return "NO_TAGS";
    }
    if (isset($result->error)) {
        return "NO_TAGS";
    }
    $max = count($result->toptags->tag);
    if ($max == 0) {
        return "NO_TAGS";
    }
    $tags = array();
    $tag_percent_limit = 33;
    for ($i = 0; $i < $max; $i++) {
        if ($result->toptags->tag[$i]->count < $tag_percent_limit){
            break;
        }
        $tag = $result->toptags->tag[$i]->name;
        if(in_array(strtolower($tag), $ignore_tags)){
            break;
        }
        $tags[$tag] = $result->toptags->tag[$i]->count;
    }
    return $tags;
}

function get_multiple_genres($artists, $depth) {
    /* echo "<br><br>Finding genres for: ";
      foreach ($artists as $artist => $searchfor) {
      echo $artist."->".$searchfor.", ";
      } */
    $mh = curl_multi_init();
    $chs = array();
    foreach ($artists as $artist => $searchfor) {
        $ch = curl_init();
        $url = "http://ws.audioscrobbler.com/2.0/?method=artist.gettoptags&artist=" . urlencode($searchfor) . "&api_key=6a23add5eb8a989f4d8c897d76ffc21e&format=json";
        curl_setopt($ch, CURLOPT_URL, "$url");
        //curl_setopt($ch, CURLOPT_TIMEOUT_MS, 5000);
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
    $artists_without_tags = array();
    foreach ($artist_genres as $artist => $genre) {
        if ($genre == "NO_TAGS") {
            $artists_without_tags[$artist] = "NO_TAGS";
        }else{
            $artists[$artist] = $genre;
        }
    }
    $searchfors = get_multiple_related_artists($artists_without_tags, $depth);
    $zero = 0;
    recurse_for_untagged($artists, $artists_without_tags, $searchfors, $depth, $zero);
    return $artists;
}

function recurse_for_untagged(&$artists, $untagged_artists, $searchfors, $max_depth, &$iteration) {
    $mh = curl_multi_init();
    $chs = array();
    foreach ($untagged_artists as $artist => $genre) {
        if(!isset($searchfors[$artist])){
            $artists[$artist] = "NO_TAGS";
            unset($untagged_artists[$artist]);
        }else{
            $search_artist = $searchfors[$artist][$iteration];
            $ch = curl_init();
            $url = "http://ws.audioscrobbler.com/2.0/?method=artist.gettoptags&artist=" . urlencode($search_artist) . "&api_key=6a23add5eb8a989f4d8c897d76ffc21e&format=json";
            curl_setopt($ch, CURLOPT_URL, "$url");
            //curl_setopt($ch, CURLOPT_TIMEOUT_MS, 5000);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
            $chs[$artist] = $ch;
            curl_multi_add_handle($mh, $ch);
        }
    }
    $running = null;
    do {
        curl_multi_exec($mh, $running);
    } while ($running);
    foreach ($chs as $artist => $ch) {
        curl_multi_remove_handle($mh, $ch);
    }
    curl_multi_close($mh);

    foreach ($chs as $artist => $ch) {
        $response = curl_multi_getcontent($ch);
        $result = json_decode($response);
        $genre = parse_tags_result($result);
        if ($genre != "NO_TAGS") {
            $artists[$artist] = $genre;
            unset($untagged_artists[$artist]);
        }
    }
    
    $iteration = $iteration + 1;
    if ($iteration < $max_depth AND ! empty($untagged_artists)) {
        recurse_for_untagged($artists, $untagged_artists, $searchfors, $max_depth, $iteration);
        return;
    } else {
        foreach($untagged_artists as $artist => $genre){
            //echo "<br>no tag for ".$artist;
            $artists[$artist] = "NO_TAGS";
        }
        return;
    }
}

function get_multiple_related_artists($artists, $max) {
    $mh = curl_multi_init();
    $chs = array();
    foreach ($artists as $artist => $searchfor) {
        $ch = curl_init();
        $url = "http://ws.audioscrobbler.com/2.0/?method=artist.getsimilar&artist=" . urlencode($artist) . "&api_key=6a23add5eb8a989f4d8c897d76ffc21e&format=json";
        curl_setopt($ch, CURLOPT_URL, "$url");
        //curl_setopt($ch, CURLOPT_TIMEOUT_MS, 5000);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        $chs[$artist] = $ch;
        curl_multi_add_handle($mh, $ch);
    }
    $running = null;
    do {
        curl_multi_exec($mh, $running);
    } while ($running);
    curl_multi_close($mh);

    $relatedartists = array();
    foreach ($chs as $artist => $ch) {
        $response = curl_multi_getcontent($ch);
        $result = json_decode($response);
            for ($i = 0; $i < $max; $i++) {
                if (!isset($result->similarartists->artist[$i])) {
                    break;
                }
                $similarartist = $result->similarartists->artist[$i]->name;
                $relatedartists[$artist][$i] = $similarartist;
            }
    }
    return $relatedartists;
}

function generate_genre_count($artist_genres, $artists) {
    $genre_count = array();
    $total = 0;
    foreach ($artist_genres as $artist_name => $genre) {
        $artist = $artists[$artist_name];
        $artist->genre = $genre;
        if (!isset($genre_count[$genre])) {
            $genre_count[$genre] = $artist->count;
        } else {
            $genre_count[$genre] += $artist->count;
        }
        $total += $genre_count[$genre];
    }

    return $genre_count;
}

function my_ucwords($word){
    if($word == "NO_TAGS") return "No Genre Found";
    return ucwords($word);
}

?>