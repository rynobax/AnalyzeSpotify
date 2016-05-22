<?php
function generate_month_genre_stats($playlist, $artists){
	$month_genre_stats = array();
	$earliest_month = 999999;
	$earliest_year = 99999999;
	$latest_month = -1000;
	$latest_year = -10000;

	foreach ($playlist as $track) {
		$date = date_parse($track->added_at);
		if($date["year"] < $earliest_year){
			$earliest_year = $date["year"];
			$earliest_month = $date["month"];
		}
		if($date["year"] == $earliest_year){
			if($date["month"] < $earliest_month){
				$earliest_year = $date["year"];
				$earliest_month = $date["month"];
			}
		}
		if($date["year"] > $latest_year){
			$latest_year = $date["year"];
			$latest_month = $date["month"];
		}
		if($date["year"] == $latest_year){
			if($date["month"] > $latest_month){
				$latest_year = $date["year"];
				$latest_month = $date["month"];
			}
		}
	}


	$current_month = $earliest_month;
	$current_year = $earliest_year;
	$i = 0;
	while(true){
		$month_genre_stats[$current_year."-".$current_month] = array();
		$month_genre_stats[$current_year."-".$current_month]["TOTAL"] = 0;
		$current_month++;
		if($current_month == 13){
			$current_month = 1;
			$current_year++;
		}
		if(($current_month > $latest_month) AND ($current_year >= $latest_year)) break;
	}

	foreach ($playlist as $track) {
		$date = date_parse($track->added_at);
		$genre = $artists[$track->artists[0]->name]->genre;
		if(!isset($month_genre_stats[$date["year"]."-".$date["month"]][$genre])){
			$month_genre_stats[$date["year"]."-".$date["month"]][$genre] = 1;
			$month_genre_stats[$date["year"]."-".$date["month"]]["TOTAL"]++;
		}else{
			$month_genre_stats[$date["year"]."-".$date["month"]][$genre]++;
			$month_genre_stats[$date["year"]."-".$date["month"]]["TOTAL"]++;
		}
	}
	return $month_genre_stats;
}

function generate_genre_list(&$month_genre_stats, $count_cutoff, $genre_count){
	$genre_list = array();
	foreach ($month_genre_stats as $date => &$stats) {
		foreach ($stats as $genre => $count) {
			if($genre == "Other") continue;
			if($genre != "NO_TAGS" AND $genre != "TOTAL" AND $genre_count[$genre] >= $count_cutoff){
				$genre_list[$genre] = true;
			}else if($genre != "TOTAL"){
				$genre_list["Other"] = true;
				if(!isset($stats["Other"])) $stats["Other"] = 0;
				$stats["Other"] += $count;
			}
		}
	}
	return $genre_list;
}

function generate_genre_count($artist_genres, $artists){
	$genre_count = array();
	foreach($artist_genres as $artist_name => $genre) {
		if($genre == "") echo "$artist_name is the culprit";
		$artist = $artists[$artist_name];
		$artist->genre = $genre;
		if(!isset($genre_count[$genre])){
			$genre_count[$genre] = $artist->count;
		}else{
			$genre_count[$genre] += $artist->count;
		}
	}

	arsort($genre_count);
	echo "<table>";
	echo "<tr>";
	echo "<th>Genre</th><th>Count</th>";
	echo "</tr>";
	foreach ($genre_count as $genre => $count) {
		echo "<tr><td>".ucwords($genre)."</td><td>".$count."</td></tr>";
	}
	echo "</table>";

	return $genre_count;
}

function build_percentage_data($month_genre_stats, $genre_list){
	$data = array();
	$genre_totals = array();
	foreach ($genre_list as $genre => $ignore) {
		$data[$genre] = array();
	}
	foreach ($month_genre_stats as $date => $count) { // For each date
		$date_sum = 0;
		foreach ($genre_list as $genre => $ignore) { // For each genre
			$current_count = 0;
			if(isset($count[$genre])){
				$current_count = $count[$genre];
			}
			if($count["TOTAL"] != 0){
				$data[$genre][$date] = ($current_count / $count["TOTAL"]) + $date_sum;
			}else{
				$data[$genre][$date] = $date_sum;
			}
			$date_sum = $data[$genre][$date];
		}
	}
	return $data;
}

function build_cumulative_data($month_genre_stats, $genre_list){
	$data = array();
	$genre_totals = array();
	foreach ($genre_list as $genre => $ignore) {
		$data[$genre] = array();
		$genre_totals[$genre] = 0;
	}
	foreach ($month_genre_stats as $date => $count) { // For each date
		$date_sum = 0;
		foreach ($genre_list as $genre => $ignore) { // For each genre
			$current_count = 0;
			if(isset($count[$genre])){
				$current_count = $count[$genre];
			}
			$date_sum += $current_count;
			$data[$genre][$date] = $genre_totals[$genre] + $date_sum;
			$genre_totals[$genre] = $data[$genre][$date];
		}
	}
	return $data;
}
?>