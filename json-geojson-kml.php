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

	$name = null;
	if ($p['man_made'] == 'water_tower') $name = 'víztorony';
	if ($p['man_made'] == 'mast') $name = 'pózna';
	if ($p['man_made'] == 'chimney') $name = 'kémény';
	if ($p['tower:type'] == 'communication') $name = 'rádiótorony';
	if (isset($p['communication:mobile_phone'])) $name = 'bázisállomás';
	if (isset($p['operator'])) $name = $p['operator'];
	if ($name !== null) $p['name'] = $name;

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
