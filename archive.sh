#!/bin/sh

gzip -c overpass.json > archive/`date "+%Y%m%d"`.json.gz
