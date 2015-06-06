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

foreach ($json['elements'] as $element) {
	$element['tags']['id'] = $element['id'];
	$features[] = array(
		'type' => 'Feature',
		'geometry' => array(
			'type' => 'Point',
			'coordinates' => array($element['lon'], $element['lat']),
		),
		'properties' => $element['tags'],
	);
}

echo json_encode(array(
	'type' => 'FeatureCollection',
	'features' => $features)
);
