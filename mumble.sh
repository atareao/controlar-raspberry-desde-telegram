#!/bin/bash
MPORT=64738
ufwstatus=`ufw status | grep -m 1 $MPORT`
mumblestatus=`systemctl status mumble-server | grep running`
case "$1" in
	start)
		if [ -z "$ufwstatus" ]
		then
			ufw allow $MPORT  > /dev/null 2>&1
		fi
		if [ -z "$mumblestatus" ]
		then
			systemctl start mumble-server > /dev/null 2>&1
		fi
		;;

	stop)
		if [ ! -z "$mumblestatus" ]
		then
			systemctl stop mumble-server > /dev/null 2>&1
		fi
		if [ ! -z "$ufwstatus" ]
		then
			ufw delete allow $MPORT > /dev/null 2>&1
		fi
		;;

	info)
		if [ ! -z "$mumblestatus" ] && [ ! -z "$ufwstatus" ]
		then
			echo "Mumble Server **started**"
		else
			echo "Mumble server **stopped**"
		fi
		;;
	status)
		if [ ! -z "$mumblestatus" ] && [ ! -z "$ufwstatus" ]

		then
			echo "ON"
		else
			echo "OFF"
		fi
		;;
esac
