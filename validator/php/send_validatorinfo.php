<?php
//Load config array
$configs = include('config.php');

//Read configs
$server_post_url = $configs['server_post_url'];
$sender_node = $configs['sender_node'];

//Read file
$path= $configs['datasourcepath'] . "/validator_info.txt";
$contents = file_get_contents($path);

$lines = preg_split('/\r\n|\r|\n/', $contents);

//Parse and post validator info
foreach ($lines as &$line) {
			
			if (strpos($line, 'current-epoch-signed') !== false) {
			$line_value_array = explode(' ', trim($line));
			$currentepochsigned = str_replace(",", "", $line_value_array[1]);
			
			$tagname = "current-epoch-signed";
			$url = $server_post_url . 'sender_node=' . $sender_node . '&';
			$url = $url . 'tagname=' . $tagname . '&';
			$url = $url . 'valuetype=int&value=' . $currentepochsigned;
			$response = file_get_contents($url);
			}
			
			if (strpos($line, 'current-epoch-signing-percentage') !== false) {
			$line_value_array = explode(' ', trim($line));
			$currentepochsigningpercentage = str_replace(",", "", $line_value_array[1]);
			$currentepochsigningpercentage = str_replace('"', '', $currentepochsigningpercentage);
			$currentepochsigningpercentage_string = number_format((float)$currentepochsigningpercentage * 100.0, 2, '.', '');
			
			$tagname = "current-epoch-signing-percentage";
			$url = $server_post_url . 'sender_node=' . $sender_node . '&';
			$url = $url . 'tagname=' . $tagname . '&';
			$url = $url . 'valuetype=float&value=' . $currentepochsigningpercentage_string;
			$response = file_get_contents($url);
			}
			
			if (strpos($line, 'current-epoch-to-sign') !== false) {
			$line_value_array = explode(' ', trim($line));
			$currentepochtosign = str_replace(",", "", $line_value_array[1]);
			
			$tagname = "current-epoch-to-sign";
			$url = $server_post_url . 'sender_node=' . $sender_node . '&';
			$url = $url . 'tagname=' . $tagname . '&';
			$url = $url . 'valuetype=int&value=' . $currentepochtosign;
			$response = file_get_contents($url);
			
			$currentepochmissed = intval($currentepochtosign) - intval($currentepochsigned);
			
			$tagname = "current-epoch-missed";
			$url = $server_post_url . 'sender_node=' . $sender_node . '&';
			$url = $url . 'tagname=' . $tagname . '&';
			$url = $url . 'valuetype=int&value=' . $currentepochmissed;
			$response = file_get_contents($url);

			
			exit();
			}
			
			}


?> 
