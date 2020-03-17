#!/bin/sh

# The list of hosts can contain comments on lines starting with '#'
list() {
    cat /data/services.txt | sed -e '/^[[:space:]]*$/d' -e '/^#/d'
}

if [ "$USE_SERVICE" -eq 0 ]; then
    NLINES=`list | wc -l`
    USE_SERVICE=$(( 1 + $RANDOM % $NLINES ))
fi

TIMESTAMP=`date +%s`
SERVICE=https://`list | sed ${USE_SERVICE}!d`
echo "[`date --rfc-2822 --date=@$TIMESTAMP`] Checking public IP with server $SERVICE" >&2

# Since only IPv4 addresses are parsed, the request is made over IPv4
IP=`curl -4 --silent --show-error --max-time $UPDATE_TIMEOUT ${SERVICE}`
if [ -z "$IP" ]; then
    IP=0.0.0.0
else
    # If the server produced some error curl would still succeed, check the format
    if ! echo "$IP" | grep -qE '^(\d{1,3}\.){3}\d{1,3}$' ; then
        IP=0.0.0.0
    fi
fi
echo $TIMESTAMP $IP >> /data/ip-history.txt