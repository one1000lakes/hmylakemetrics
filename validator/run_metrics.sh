#!/bin/bash

#This should be called from metrics_wrapper.sh. See comments in metrics_wrapper.sh
#This script runs php files depending on time interval flag. Some are called more frequently than others
#There is delay (sleep) between consecutive calls to prevent spikes on server load, although load effect is pretty minimal

while getopts t:p:e:s:r:h: flag
do
    case "${flag}" in
        t) interval=${OPTARG};;
	p) path=${OPTARG};;
	e) hmypath=${OPTARG};;
	s) shard=${OPTARG};;
	r) remoteaddress=${OPTARG};;
	h) nodehash=${OPTARG};;
    esac
done

#Ping address 1, local ping address, preferably some server in same country your node is located to measure if your own network is working normally
#Set empty (ping1address="") to disable ping
ping1address="fi.hma.rocks"

#Ping measurement 2, remote ping address, preferably some server in same country where main node is located (USA) to measure if marine cables and international networks are working normally
#Set empty (ping2address="") to disable ping
ping2address="ca.us.hma.rocks"

if [ -z ${interval+x} ]
then
  echo "Time interval flag missing! Set with -t flag. Example: /data/hmymetrics/metrics_wrapper.sh -t 1 -p /data/hmymetrics -e /home/user/hmy -s 1 -r example.remotenod3.com -h one123456789abcdefghijklmnopqrstuvwxyz"
  exit 1
fi

if [ -z ${path+x} ]
then
  echo "Path flag missing! Set with -p flag. Example: /data/hmymetrics/metrics_wrapper.sh -t 1 -p /data/hmymetrics -e /home/user/hmy -s 1 -r example.remotenod3.com -h one123456789abcdefghijklmnopqrstuvwxyz"
  exit 1
fi

if [ -z ${shard+x} ]
then
  echo "Shard to be signed flag missing! Set with -s flag. Example: /data/hmymetrics/metrics_wrapper.sh -t 1 -p /data/hmymetrics -e /home/user/hmy -s 1 -r example.remotenod3.com -h one123456789abcdefghijklmnopqrstuvwxyz"
  exit 1
fi

if [ -z ${remoteaddress+x} ]
then
  echo "Remote node address flag missing! Set with -r flag. Example: /data/hmymetrics/metrics_wrapper.sh -t 1 -p /data/hmymetrics -e /home/user/hmy -s 1 -r example.remotenod3.com -h one123456789abcdefghijklmnopqrstuvwxyz"
  exit 1
fi

if [ -z ${nodehash+x} ]
then
  echo "Node hash flag missing! Set with -h flag. Example: /data/hmymetrics/metrics_wrapper.sh -t 1 -p /data/hmymetrics -e /home/user/hmy -s 1 -r example.remotenod3.com -h one123456789abcdefghijklmnopqrstuvwxyz"
  exit 1
fi

if [ -z ${hmypath+x} ]
then
  echo "Hmy executable path flag missing! Set with -e flag. Example: /data/hmymetrics/metrics_wrapper.sh -t 1 -p /data/hmymetrics -e /home/user/hmy -s 1 -r example.remotenod3.com -h one123456789abcdefghijklmnopqrstuvwxyz"
  exit 1
fi

if [ $interval == 1 ]
then

  ""${hmypath} blockchain latest-headers | egrep 'shardID|viewID' > ${path}/datasource/headers_localnode.txt""
  ""${hmypath} --node=""https://api.s${shard}.t.hmny.io"" blockchain latest-headers | egrep 'shardID|viewID' > ${path}/datasource/headers_main.txt""
  sleep 1
  ""${hmypath} --node=""${remoteaddress}"" blockchain latest-headers | egrep 'shardID|viewID' > ${path}/datasource/headers_remotebackup.txt""
  sleep 1
  ""/usr/bin/curl localhost:9900/metrics | grep ^hmy > ${path}/datasource/metrics_local.txt""
  sleep 1
  ""/usr/bin/php ${path}/php/send_headers.php""
  sleep 1
  ""/usr/bin/php ${path}/php/send_localmetrics.php""

elif [ $interval == 5 ]
then

  ""${hmypath} blockchain latest-headers | egrep 'shardID|viewID' > ${path}/datasource/headers_localnode.txt""
  ""${hmypath} --node=""https://api.s${shard}.t.hmny.io"" blockchain latest-headers | egrep 'shardID|viewID' > ${path}/datasource/headers_main.txt""
  sleep 1
  ""${hmypath} --node=""${remoteaddress}"" blockchain latest-headers | egrep 'shardID|viewID' > ${path}/datasource/headers_remotebackup.txt""
  sleep 1
  ""/usr/bin/curl localhost:9900/metrics | grep ^hmy > ${path}/datasource/metrics_local.txt""
  sleep 1
  ""/usr/bin/php ${path}/php/send_headers.php""
  sleep 1
  ""/usr/bin/php ${path}/php/send_localmetrics.php""
  sleep 1
  ""${hmypath} blockchain validator information ${nodehash} --node="https://api.s0.t.hmny.io" > ${path}/datasource/validator_info.txt""
  sleep 2
  ""/usr/bin/php ${path}/php/send_validatorinfo.php""
  sleep 1
  ""/usr/bin/df > ${path}/datasource/diskfree.txt""
  sleep 1
  ""/usr/bin/echo ${shard} > ${path}/datasource/shards_to_sign.txt""
  ""/usr/bin/php ${path}/php/send_shards.php""
  sleep 1
  ""/usr/bin/php ${path}/php/send_dbsize.php""
  sleep 1
  #Ping measurement 1 if address set
  if [ ! -z "$ping1address" ]
  then
  ""/usr/bin/ping -c 3 -q -W 1 ${ping1address} > ${path}/datasource/ping1.txt""
  sleep 1
  ""/usr/bin/php ${path}/php/send_ping1.php""
  sleep 1
  fi
  #Ping measurement 2 if address set
  if [ ! -z "$ping2address" ]
  then
  ""/usr/bin/ping -c 3 -q -W 1 ${ping2address} > ${path}/datasource/ping2.txt""
  sleep 1
  ""/usr/bin/php ${path}/php/send_ping2.php""
  sleep 1
  fi

fi
