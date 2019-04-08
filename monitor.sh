#!/bin/bash
source /home/pi/bot/keys
URL="https://api.telegram.org/bot$TOKEN/sendMessage"
/usr/bin/inotifywait -e create,delete,modify,move,attrib -mrq /home/pi/bot | while read line; do
	curl -s -X POST $URL -d chat_id=$CHANNEL -d text="$line" >/dev/null 2>&1
done
