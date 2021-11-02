<?php

//Server related variables (Set these values according your settings, see documentation, especially CHANGE API KEY to some randomized password!!)

$server_post_url = 'http://my.own.webserver.example/hmylakemetrics/update_values.php';   //Your web server address where update_values.php is hosted
$api_key = 'xXx123YYYzzz';							     //Key for sending data to web-server. This must match to key set to web server
$sender_node = 1;							     		 //This node's id (1 or 2)
$basepath = '/data/hmymetrics';						     //Path where your /datasource folder is located where *.txt files are outputted (don't put / at the end. Example: '/data/hmymetrics' (NOT '/data/hmymetrics/'))
$devname_or_mntname = '/data'; 						     //Disk where harmony database is located as shown on shell with 'df' command. Example: '/dev/vdb' or '/data'

//Formatting values (you don't have to change these)
$server_post_url_formatted = $server_post_url . '?api_key=' . $api_key . '&';
$datasourcepath = $basepath . '/datasource';

//Return configs as array
return array(
    'server_post_url' => $server_post_url_formatted,
    'sender_node' => $sender_node,
    'devname_or_mntname' => $devname_or_mntname,
    'datasourcepath' => $datasourcepath,
);


?> 
