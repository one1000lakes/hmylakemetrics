<?php
//Database definition
define('DB_SERVER', '127.0.0.1');
define('DB_USERNAME', 'exampleuser');
define('DB_PASSWORD', 'password123');
define('DB_NAME', 'hmylakemetrics');

//Api key definition
define('API_KEY', 'xxxyyyzzz');

//Definition of how many days old historical measurement data is kept in history-table. Default value is '14'.
define('HISTORICAL_DAYS', '14');

//Ip addresses allowed to update data
//Uncomment this and also uncomment lines on update.values.php to allow values only be updated from specific ip addresses
//define('ALLOWED_IP1', '192.168.110');
//define('ALLOWED_IP2', '192.168.111');

//Open mysql connection
$con=mysqli_connect(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);
// Check connection
if (mysqli_connect_errno())
  {
  echo "Failed to connect to MySQL: " . mysqli_connect_error();
  }

?> 
