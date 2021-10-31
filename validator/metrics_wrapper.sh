#!/bin/bash

#Wrapper calls pkill to kill run_metrics.sh process if left to stuck so that there won't be multiple running. After that it calls run_metrics.sh
#Wrapper should be called like this: /data/hmymetrics/metrics_wrapper.sh -t MINUTEINTERVAL -p BASEPATH -s SHARDTHATNODESIGNS -r REMOTENODEADDRESS -h NODEHASH
#Example: /data/hmymetrics/metrics_wrapper.sh -t 1 -p /data/hmymetrics -e /home/user/hmy -s 1 -r example.remotenod3.com -h one123456789abcdefghijklmnopqrstuvwxyz
#Example indicates it should run tasks marked to run 1 minute interval and basepath for .sh files and BASEPATH/php directory is /data/hmymetrics, location for hmy executable is /home/user/hmy, shard that this node signs is 1, this node's remote node (backup/main) is example.remotenod3.com and validators address is one123456789abcdefghijklmnopqrstuvwxyz
#Wrapper should be called from crontab at 1, 5, 10 and 15 minutes interval

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


pkill -f 'run_metrics.sh'
exec bash ""${path}/run_metrics.sh -t ${interval} -p ${path} -e ${hmypath} -s ${shard} -r ${remoteaddress} -h ${nodehash}""
