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
			$rncs = explode('; ', @$tags['umts:RNC']);
			$eNBs = explode('; ', @$tags['lte:eNB']);
			$lacs = explode('; ', @$tags[$net . ':LAC']);

			if (count($ops) == count($mncs)) {
				foreach ($mncs as $i => $mnc) {
					$mnc = sprintf('%02d', trim($mnc));
					$cidlist = $ops[$i];
					$cids = explode(';', $cidlist);
					foreach (explode(';', $eNBs[$i]) as $eNB) {
						foreach ($cids as $cid) {
							$cid = trim($cid);
							if ($cid === '') continue;
							if (!is_numeric($cid)) continue;

							$site = (int) floor($cid/10);

							if ($net == 'umts') {
								$rnc = $rncs[$i];
								if (!is_numeric($rnc)) continue;
								$cid += $rnc*65536;
							}

							if ($net == 'lte') {
								$site = $eNB;
								if (!is_numeric($eNB)) continue;
								$cid += $eNB*256;
							}

							$cells[] = sprintf('%d:%d:%d',
								$cid, $mcc, $mnc);

							if ($net == 'lte' &&
								in_array($mnc, array('01', '30')) &&
								hasSharedLac($mnc, $lacs[$i])) {
								$cells[] = sprintf('%d:%d:%d',
									$cid, $mcc, $mnc == '01' ? '30' : '01');
							}
						}
					}
				}
			}
		}
	}

	if (count($cells)) {
		echo $name . '|' . implode('|', $cells), "\n";
	}

}

function hasSharedLac($mnc, $lac) {
	$lacs = explode(';', $lac);
	foreach ($lacs as $lac)
		if ($mnc == '01' && $lac >= 20000 && $lac <= 29999) return true;
		if ($mnc == '30' && $lac >= 30000 && $lac <= 39999) return true;
	return false;
}
