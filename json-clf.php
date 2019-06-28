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

$operators = array(
	'01' => 'Telenor',
	'30' => 'Telekom',
	'70' => 'Vodafone',
);

$nets = array(
	2 => 'gsm',
	3 => 'umts',
	4 => 'lte',
);

foreach ($json['elements'] as $element) {

	$tags = $element['tags'];
	$cells = [];
	
	$name = $element['id'];
	if (@$tags['location'] == 'roof') $name = 'háztetőn';
	if (@$tags['location'] == 'pole') $name = 'oszlopon';
	if (@$tags['location'] == 'church') $name = 'templomban';
	if (@$tags['location'] == 'chimney') $name = 'kéményen';
	if (@$tags['man_made'] == 'tower') $name = 'tornyon';
	if (@$tags['man_made'] == 'water_tower') $name = 'víztornyon';
	if (@$tags['man_made'] == 'mast') $name = 'póznán';
	if (@$tags['man_made'] == 'chimney') $name = 'kéményen';
	if (@$tags['description'] != '') $name = $tags['description'];
	if (@$tags['ref'] != '') $name = $tags['ref'];

	foreach ($nets as $net) {
		$key = $net . ':cellid';
		if (isset($tags[$key]) &&
			isset($tags['MCC']) &&
			isset($tags['MNC'])) {

			$ops = explode(' ', $tags[$key]);
			$mcc = $tags['MCC'];
			$mncs = explode(';', $tags['MNC']);
			$lacs = explode(';', $tags[$net . ':LAC']);
			if (count($ops) == count($mncs)) {
				foreach ($mncs as $i => $mnc) {
					$mnc = sprintf('%02d', trim($mnc));
					$lac = trim($lacs[$i]);
					$cidlist = $ops[$i];
					$cids = explode(';', $cidlist);
					if ($net != 'gsm') {
						$rnckey = $key = $net . ':RNC';
						if (!isset($tags[$rnckey])) continue;
						$rncops = explode(' ', $tags[$rnckey]);
						if (!isset($rncops[$i])) continue;
						$rnc = $rncops[$i];
					}
					foreach ($cids as $cid) {
						$cid = trim($cid);
						if ($cid == '') continue;
						// if ($net != 'gsm') $cid += 65536*$rnc;
						echo sprintf('%03d%02d;%05d;%05d;%05d;%1.6f;%1.6f;%d;%s;%d',
							$mcc, $mnc,
							$cid,
							$lac,
							$rnc,
							$element['lat'],
							$element['lon'],
							0,
							$name,
							0
						), "\n";
					}
				}
			}
		}
	}

}

