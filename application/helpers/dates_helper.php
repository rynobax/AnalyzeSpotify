<?php
function is_first_older_than_second($first, $second){
	if(compare_dates($first, $second) == -1) return true;
	return false;
}

function is_first_younger_than_second($first, $second){
	if(compare_dates($first, $second) == 1) return true;
	return false;
}

// Returns 1 if date1 is more recent, 0 if equal, -1 if date1 older
function compare_dates($date1, $date2){
	if($date1["year"] > $date2["year"]) return 1;
	if($date1["year"] < $date2["year"]) return -1;

	if($date1["month"] > $date2["month"]) return 1;
	if($date1["month"] < $date2["month"]) return -1;

	if($date1["day"] > $date2["day"]) return 1;
	if($date1["day"] < $date2["day"]) return -1;

	if($date1["hour"] > $date2["hour"]) return 1;
	if($date1["hour"] < $date2["hour"]) return -1;

	if($date1["minute"] > $date2["minute"]) return 1;
	if($date1["minute"] < $date2["minute"]) return -1;

	if($date1["second"] > $date2["second"]) return 1;
	if($date1["second"] < $date2["second"]) return -1;

	return 0;
}
?>