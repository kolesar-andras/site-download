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
	$p = [];
	foreach ($element as $k => $v) {
		if ($k == 'tags') {
			$p = array_merge($p, $v);
		} else {
			$p['['.$k.']'] = $v;
		}
	}
	$p['name'] = $p['operator'];
	unset($p['[lat]']);
	unset($p['[lon]']);
	unset($p['[uid]']);
	unset($p['[type]']);
	$features[] = array(
		'type' => 'Feature',
		'geometry' => array(
			'type' => 'Point',
			'coordinates' => array($element['lon'], $element['lat']),
		),
		'properties' => $p,
	);
}

echo json_encode(array(
	'type' => 'FeatureCollection',
	'features' => $features,
    ),
    JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE
);
