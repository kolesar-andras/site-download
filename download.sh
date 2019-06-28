#!/bin/sh

OUTPUT=${OUTPUT:-output}
LOG=${LOG:-log}

mkdir -p $OUTPUT
mkdir -p $LOG

rm -f $OUTPUT/overpass.new.json
curl -o $OUTPUT/overpass.new.json --data-urlencode data@overpass.ql http://overpass-api.de/api/interpreter 2>$LOG/overpass.stderr
if [ $(wc -c < $OUTPUT/overpass.new.json) -ge 10000 ]; then
    rm -f $OUTPUT/overpass.json
    mv $OUTPUT/overpass.new.json $OUTPUT/overpass.json
    cat $OUTPUT/overpass.json | ./json-geojson.php > $OUTPUT/overpass.geojson
    cat $OUTPUT/overpass.json | ./json-geojson-kml.php > $OUTPUT/overpass-kml.geojson
    cat $OUTPUT/overpass.json | ./json-llama.php > $OUTPUT/Llama_Areas.txt
    cat $OUTPUT/overpass.json | ./json-clf.php > $OUTPUT/overpass.clf
    cat $OUTPUT/overpass.json | ./json-cells.php | psql kolesar > $LOG/psql.log 2>&1
    ogr2ogr -F KML $OUTPUT/overpass.kml $OUTPUT/overpass-kml.geojson
fi
