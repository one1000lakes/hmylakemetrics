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

if [ -z ${interval+x} ]
then
  echo "Time interval flag missing! Set with -t flag. Example: /data/hmylakemetrics/metrics_wrapper.sh -t 1 -p /data/hmylakemetrics -e /home/user/hmy -s 1 -r example.remotenod3.com -h one123456789abcdefghijklmnopqrstuvwxyz"
  exit 1
fi

if [ -z ${path+x} ]
then
  echo "Path flag missing! Set with -p flag. Example: /data/hmylakemetrics/metrics_wrapper.sh -t 1 -p /data/hmylakemetrics -e /home/user/hmy -s 1 -r example.remotenod3.com -h one123456789abcdefghijklmnopqrstuvwxyz"
  exit 1
fi

if [ -z ${shard+x} ]
then
  echo "Shard to be signed flag missing! Set with -s flag. Example: /data/hmylakemetrics/metrics_wrapper.sh -t 1 -p /data/hmylakemetrics -e /home/user/hmy -s 1 -r example.remotenod3.com -h one123456789abcdefghijklmnopqrstuvwxyz"
  exit 1
fi

if [ -z ${remoteaddress+x} ]
then
  echo "Remote node address flag missing! Set with -r flag. Example: /data/hmylakemetrics/metrics_wrapper.sh -t 1 -p /data/hmylakemetrics -e /home/user/hmy -s 1 -r example.remotenod3.com -h one123456789abcdefghijklmnopqrstuvwxyz"
  exit 1
fi

if [ -z ${nodehash+x} ]
then
  echo "Node hash flag missing! Set with -h flag. Example: /data/hmylakemetrics/metrics_wrapper.sh -t 1 -p /data/hmylakemetrics -e /home/user/hmy -s 1 -r example.remotenod3.com -h one123456789abcdefghijklmnopqrstuvwxyz"
  exit 1
fi

if [ -z ${hmypath+x} ]
then
  echo "Hmy executable path flag missing! Set with -e flag. Example: /data/hmylakemetrics/metrics_wrapper.sh -t 1 -p /data/hmylakemetrics -e /home/user/hmy -s 1 -r example.remotenod3.com -h one123456789abcdefghijklmnopqrstuvwxyz"
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
  #Ping measurement 1, local ping, preferably some server in same country your node is located to measure if your own network is working normally
  #Comment these out to disable ping measurement
  sleep 1
  ""/usr/bin/ping -c 3 -q -W 1 fi.hma.rocks > ${path}/datasource/ping1.txt""
  sleep 1
  ""/usr/bin/php ${path}/php/send_ping1.php""
  #Ping measurement 2, remote ping, preferably some server is same country where main node is located (USA) to measure if marine cables and international networks are working normally
  #Comment these out to disable ping measurement
  sleep 1
  ""/usr/bin/ping -c 3 -q -W 1 ca.us.hma.rocks > ${path}/datasource/ping2.txt""
  sleep 1
  ""/usr/bin/php ${path}/php/send_ping2.php""

fi
