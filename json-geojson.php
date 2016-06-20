#!/usr/bin/env php
<?php

/**
 * overpass json átalakítása geojson-ra
 *
 * @author Kolesár András <kolesar@kolesar.hu>
 * @since 2015.03.06
 *
 */

$json = json_decode(file_get_contents('php://stdin'), true);
$features = array();
$nodes = array();

foreach ($json['elements'] as $element) {
	if ($element['type'] == 'node') {

		$features[] = array(
			'type' => 'Feature',
			'geometry' => array(
				'type' => 'Point',
				'coordinates' => array($element['lon'], $element['lat']),
			),
			'properties' => $element,
		);
		$nodes[$element['id']] = array($element['lon'], $element['lat']);

	} else if ($element['type'] == 'relation' &&
		is_array($element['members']) &&
		count($element['members']) >= 1 &&
		$element['members'][0]['type'] == 'node' &&
		isset($nodes[$element['members'][0]['ref']])) {

		$start = $nodes[$element['members'][0]['ref']];

		if (count($element['members']) == 1 &&
			is_numeric($element['tags']['direction'])) {
			$destination = destination(
				$start, // coordinates
				$element['tags']['direction'], // bearing
				diameterToDistance(@$element['tags']['diameter']) // distance
			);

		} else if ($element['members'][1]['type'] == 'node' &&
				isset($nodes[$element['members'][1]['ref']])) {

			$destination = $nodes[$element['members'][1]['ref']];

			if (@$element['tags']['survey'] != 'both') {
				$middle = [
					($start[0]+$destination[0])/2,
					($start[1]+$destination[1])/2,
				];

				$element['note'] = 'not-surveyed-half';
				$features[] = array(
					'type' => 'Feature',
					'geometry' => array(
						'type' => 'LineString',
						'coordinates' => array(
							$middle,
							$destination
						),
					),
					'properties' => $element,
				);
				$element['note'] = 'surveyed-half';
				$destination = $middle;

			}
		} else {
			$destination = null;
		}

		if ($destination !== null) $features[] = array(
			'type' => 'Feature',
			'geometry' => array(
				'type' => 'LineString',
				'coordinates' => array(
					$start,
					$destination
				),
			),
			'properties' => $element,
		);

	}
}

echo json_encode(array(
	'type' => 'FeatureCollection',
	'features' => $features,
    ),
    JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE
);

function destination($coordinates, $bearing, $distance) {
	$lat = deg2rad($coordinates[1]);
	$lng = deg2rad($coordinates[0]);
	$bearing = deg2rad($bearing);
	$a = 6378137;

	$endLat = asin(sin($lat) * cos($distance / $a) + cos($lat) *
		sin($distance / $a) * cos($bearing));
	$endLon = $lng + atan2(sin($bearing) * sin($distance / $a) * cos($lat),
		cos($distance / $a) - sin($lat) * sin($endLat));

	return [rad2deg($endLon), rad2deg($endLat)];
}

function diameterToDistance($diameter) {
	if (!is_numeric($diameter)) return 3000;
	return $diameter * 20000; // more or less
}
