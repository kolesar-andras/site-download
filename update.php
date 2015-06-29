<?php
ini_set('display_errors', 1);
$file = 'overpass.geojson';
$data = [];
$data['old'] = fileinfo($file);
shell_exec('./download.sh');
clearstatcache();
$data['new'] = fileinfo($file);

header('Content-type: text/plain; charset=utf-8');
echo json_encode($data, JSON_PRETTY_PRINT);

function fileinfo ($file) {
    return [
        'size' => filesize($file),
        'time' => date('Y-m-d H:i:s', filemtime($file)),
    ];
}
