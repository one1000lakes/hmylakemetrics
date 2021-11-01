<?php
function getMedian($numbers){
//Returns median value of value array

sort($numbers);
$count = sizeof($numbers);   // cache the count
$index = floor($count/2);  // cache the index
if (!$count) {
    echo "no values";
	return -1;
} elseif ($count & 1) {    // count is odd
    return $numbers[$index];
} else {                   // count is even
    return ($numbers[$index-1] + $numbers[$index]) / 2;
}
}

function getAverage_wo_minmax($numbers){
//Counts average of middle numbers leaving out 1 biggest and 1 smallest. Minimum count of 3 numbers

sort($numbers);

$limit = sizeof($numbers) - 1;
$counted = 0;
$sum = 0.0;

//If not enough numbers then return 0.0;
if (sizeof($numbers) < 3) {
	return 0.0;
}

for ($i = 1; $i < $limit; ++$i) {
    $sum = $sum + $numbers[$i];
	$counted = $counted + 1;
}

return $sum / $counted;
}

function getAverage_wo_2min2max($numbers){
//Counts average of middle numbers leaving out 2 biggest and 2 smallest. Minimum count of 5 numbers

sort($numbers);

$limit = sizeof($numbers) - 2;
$counted = 0;
$sum = 0.0;

//If not enough numbers then return 0.0;
if (sizeof($numbers) < 5) {
	return 0.0;
}

for ($i = 2; $i < $limit; ++$i) {
    $sum = $sum + $numbers[$i];
	$counted = $counted + 1;
}

return $sum / $counted;
}


?> 