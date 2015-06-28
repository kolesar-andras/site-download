<?php
ini_set('display_errors', 1);
passthru('./download.sh');
echo filesize('overpass.geojson');


