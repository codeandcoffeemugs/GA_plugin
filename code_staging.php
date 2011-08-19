<?php

function get_dates_for_intervals($begin = null, $end = null, $interval = '1 day') {
	// convert args to timestamps
	$start = strtotime($begin); // some date
	$end = strtotime($end); // some other date, or the same date
	$intervals = array();
	// build interval list
	$intervals[] = $next = $start;
	do {
	  $intervals[] = $next = strtotime($interval, $next); 
	} while ($next < $end);
	// convert all intervals to GData-formatted dates, and remove dupes
	$intervals = array_unique( array_map( create_function('$t', 'return date('Y-m-d', $t);'), $intervals));
	return $intervals;
}

get_dates_for_intervals('last week', 'today');
print_r($intervals);