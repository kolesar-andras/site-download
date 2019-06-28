#!/bin/sh

mkdir -p archive
gzip -c overpass.json > archive/`date "+%Y%m%d"`.json.gz
