#!/usr/bin/env php
<?php

/**
 * overpass json alapján ismert site-ok listája
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

echo "BEGIN;\n";
echo "TRUNCATE TABLE known.cells;\n";

foreach ($json['elements'] as $element) {

	$tags = $element['tags'];
	$cells = [];

	foreach ($nets as $net) {
		$key = $net . ':cellid';
		if (isset($tags[$key]) &&
			isset($tags['MCC']) &&
			isset($tags['MNC'])) {

			$ops = explode(' ', $tags[$key]);
			$mcc = $tags['MCC'];
			$mncs = explode(';', $tags['MNC']);
			$rncs = explode('; ', @$tags['umts:RNC']);
			$eNBs = explode('; ', @$tags['lte:eNB']);
			$lacs = explode('; ', @$tags[$net . ':LAC']);

			if (count($ops) == count($mncs)) {
				foreach ($mncs as $i => $mnc) {
					$mnc = sprintf('%02d', trim($mnc));
					$cidlist = $ops[$i];
					$cids = explode(';', $cidlist);
					foreach (explode(';', $eNBs[$i]) as $eNB) {
						$sites = array();
						foreach ($cids as $cid) {
							$cid = trim($cid);
							if ($cid === '') continue;
							if (!is_numeric($cid)) continue;

							$site = (int) floor($cid/10);

							if ($net == 'umts') {
								$rnc = $rncs[$i];
								if (!is_numeric($rnc)) continue;
							}

							if ($net == 'lte') {
								$site = $eNB;
								if (!is_numeric($eNB)) continue;
							}

							if ($mnc == 30 && in_array($net, array('gsm', 'umts')))
								$site = -$cid;

							echo sprintf(
								"INSERT INTO known.cells (mcc, mnc, site, net, cellid, osmid, g) VALUES (%d, %d, %d, '%s', %d, %s, ST_SetSRID('POINT(%f %f)'::geometry, 4326));",
								$mcc, $mnc, $site, $net, $cid, $element['id'], $element['lon'], $element['lat']
								), "\n";

						}
					}
				}
			}
		}
	}

}

echo "COMMIT;\n";
