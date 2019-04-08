#!/bin/sh

/usr/bin/inotifywait -e create,delete,modify,move,attrib -mrq /home/pi/bot | while read line; do
	path=`echo $line | /usr/bin/awk '{print $1}'`
	echo $line 
	echo "[inotify info] $path"
done

