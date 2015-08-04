#!/bin/sh

rm -f overpass.json
curl -o overpass.json --data-urlencode data@overpass.ql http://overpass-api.de/api/interpreter 2>overpass.stderr
cat overpass.json | ./json-geojson.php > overpass.geojson
cat overpass.json | ./json-geojson-kml.php > overpass-kml.geojson
ogr2ogr -F KML overpass.kml overpass-kml.geojson
