#!/usr/local/bin/php
<?php

if ($argc < 3) {
    die("Syntax: distance.php [zip] [miles])\n");
}

$startZip = $argv[1];
$maxDistance = (int) $argv[2];

define('MILES_PER_KM', 0.6213712);
define('ZIP', 0);
define('LAT', 1);
define('LON', 2);
define('CITY', 3);
define('STATE', 4);
define('POPULATION', 8);
define('DIST', 18);

$zips = readZips();
addDist($startZip, $zips);

// Sort the array by distance
uasort($zips, 'distanceSort');

// Loop through them
foreach($zips as $zip) {
    echo "{$zip[ZIP]}\t{$zip[DIST]}\t{$zip[CITY]}, {$zip[STATE]}\t{$zip[POPULATION]}\n";
    if ($zip[DIST] > $maxDistance) break;
}

// var_dump($zips['84067']);

/**
 * Add distance from $start to all of the zip codes.
 */
function addDist($start, &$zips) {
    foreach($zips as $key => $zip) {
        $dist = haversineDistance(
            (float) $zip[LAT],
            (float) $zip[LON],
            (float) $zips[$start][LAT], 
            (float) $zips[$start][LON]);
        $zips[$key][] = round($dist * MILES_PER_KM, 2);
    }
}

function distanceSort($a, $b) {
    return ( $a[DIST] > $b[DIST] ? 1 : -1 );
}

/**
 * Read the zip codes into memory.
 */
function readZips() {
    $row = 1;
    $zips = [];
    if (($handle = fopen("uszips.csv", "r")) !== FALSE) {
        fgets($handle); // Read one line to skip the header
        while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
            $zips[$data[ZIP]] = $data;
        }
        fclose($handle);
    }
    return $zips;
}

/**
 * Calculates the great-circle distance between two points, with
 * the Haversine formula.
 * 
 * Source: https://stackoverflow.com/questions/14750275/haversine-formula-with-php
 * 
 * @param float $latitudeFrom Latitude of start point in [deg decimal]
 * @param float $longitudeFrom Longitude of start point in [deg decimal]
 * @param float $latitudeTo Latitude of target point in [deg decimal]
 * @param float $longitudeTo Longitude of target point in [deg decimal]
 * @param float $earthRadius Mean earth radius in [m]
 * @return float Distance between points in [m] (same as earthRadius)
 */
function haversineDistance($latitudeFrom, $longitudeFrom, $latitudeTo, $longitudeTo, $earthRadius = 6371) {
    // convert from degrees to radians
    $latFrom = deg2rad($latitudeFrom);
    $lonFrom = deg2rad($longitudeFrom);
    $latTo = deg2rad($latitudeTo);
    $lonTo = deg2rad($longitudeTo);
  
    $latDelta = $latTo - $latFrom;
    $lonDelta = $lonTo - $lonFrom;
  
    $angle = 2 * asin(sqrt(pow(sin($latDelta / 2), 2) + cos($latFrom) * cos($latTo) * pow(sin($lonDelta / 2), 2)));
    return $angle * $earthRadius;
}
