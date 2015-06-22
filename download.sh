#!/bin/sh

rm -f overpass.new.json
curl -o overpass.new.json --data-urlencode data@overpass.ql http://overpass-api.de/api/interpreter 2>overpass.stderr
if [ $(wc -c < overpass.new.json) -ge 100 ]; then
    rm -f overpass.json
    mv overpass.new.json overpass.json
    cat overpass.json | ./json-geojson.php > overpass.geojson
    cat overpass.json | ./json-geojson-kml.php > overpass-kml.geojson
    cat overpass.json | ./json-llama.php > Llama_Areas.txt
    ogr2ogr -F KML overpass.kml overpass-kml.geojson
fi
