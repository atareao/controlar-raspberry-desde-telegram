#!/bin/bash
nginxstatus=`systemctl status nginx | grep running`
case "$1" in
	start)
		if [ -z "$nginxstatus" ]
		then
			systemctl start nginx > /dev/null 2>&1
		fi
		;;

	stop)
		if [ ! -z "$nginxstatus" ]
		then
			systemctl stop nginx > /dev/null 2>&1
		fi
		;;

	info)
		if [ ! -z "$nginxstatus" ]
		then
			echo "Nginx Server **started**"
		else
			echo "Nginx server **stopped**"
		fi
		;;
	status)
		if [ ! -z "$nginxstatus" ]
		then
			echo "ON"
		else
			echo "OFF"
		fi
		;;
esac
