<?php
//Database definition
define('DB_SERVER', '192.168.1.100');
define('DB_USERNAME', 'user');
define('DB_PASSWORD', 'password123');
define('DB_NAME', 'hmylakemetrics');

//Api key definition
define('API_KEY', 'xxxyyyzzz');

//Server protocol (url prefix). 'http://' or 'https://'
define('HTTP_PREFIX', 'http://');

//Definition of how many days old historical measurement data is kept in history-table. Default value is '14'.
define('HISTORICAL_DAYS', '14');

//Open mysql connection
$con=mysqli_connect(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);
// Check connection
if (mysqli_connect_errno())
  {
  echo "Failed to connect to MySQL: " . mysqli_connect_error();
  }

?> 
