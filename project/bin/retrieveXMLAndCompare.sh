#!/bin/bash

. $(dirname $0)/config.inc
 curl -s $path | while read url ; do
	OUT=$(php5 symfony drm:storeXMLRetour $url $*)
	RET=$?
	DRM=$(echo $OUT | sed 's/ .*//')
	echo $OUT
	if test $RET -eq 0 ; then
		php5 symfony $SYMFONY_ENV drm:compareXMLs $DRM $*
	fi
done
