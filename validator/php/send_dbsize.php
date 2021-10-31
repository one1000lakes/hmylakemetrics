<?php
//Load config array
$configs = include('config.php');

//Read configs
$server_post_url = $configs['server_post_url'];
$sender_node = $configs['sender_node'];
$devname_or_mntname = $configs['devname_or_mntname'];

//Read file
$path= $configs['datasourcepath'] . "/diskfree.txt";
$contents = file_get_contents($path);

//DEBUG: Comment out to print configs
//echo 'Server post url: ' . $server_post_url . PHP_EOL;
//echo 'Sender node: ' . $sender_node . PHP_EOL;
//echo 'Devname or mntname: ' . $devname_or_mntname . PHP_EOL;
//echo 'Data source path: ' . $path . PHP_EOL;

$lines = preg_split('/\r\n|\r|\n/', $contents);

//Search line which contains mntname or devname and parse size and used percentage
foreach ($lines as &$line) {
			
			if (strpos($line, $devname_or_mntname) !== false) {
			$line_spaces_removed = preg_replace('!\s+!', ' ', $line); //Replace multiple spaces
			$line_value_array = explode(' ', trim($line_spaces_removed));
			$disksize_gb = intval($line_value_array[1]) / 1000000.0;
			$disksize_gb_string = number_format((float)$disksize_gb, 2, '.', '');
			$used_gb = intval($line_value_array[2]) / 1000000.0;
			$used_gb_string = number_format((float)$used_gb, 2, '.', '');
			$used_percent = str_replace("%", "", $line_value_array[4]);
			
			$tagname = "Disksize_GB";
			$url = $server_post_url . 'sender_node=' . $sender_node . '&';
			$url = $url . 'tagname=' . $tagname . '&';
			$url = $url . 'valuetype=float&value=' . $disksize_gb_string;
			$response = file_get_contents($url);
			
			$tagname = "Useddisk_GB";
			$url = $server_post_url . 'sender_node=' . $sender_node . '&';
			$url = $url . 'tagname=' . $tagname . '&';
			$url = $url . 'valuetype=float&value=' . $used_gb_string;
			$response = file_get_contents($url);
			
			$tagname = "Useddisk_percent";
			$url = $server_post_url . 'sender_node=' . $sender_node . '&';
			$url = $url . 'tagname=' . $tagname . '&';
			$url = $url . 'valuetype=int&value=' . $used_percent;
			$response = file_get_contents($url);
			
			exit();
			}
			
			}



?> 
