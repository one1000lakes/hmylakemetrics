<!DOCTYPE html>
<?php
require 'update/database.php';
require 'staticmenus.php';
?>

<html lang="en">
<head>
  <title><?php echo SITE_TITLE; ?></title>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="css/bootstrap.min.css">
  <link rel="stylesheet" href="css/hmylakemetrics-template.css">
  <style>
  .fakeimg {
    height: 200px;
    background: #aaa;
  }
  
  #dynamic_data{
            border: 1px solid gray;
            border-radius: 10px;
            padding: 10px;
            text-decoration:none;
            float:left;
            margin:4px;
            text-align:center;
            display: block;
            color: green;
        }
  </style>
</head>
<body>

<?php
//draw navigation menu
staticmenus_drawnav();

//Load shard number that nodes are signing
$result = mysqli_query($con, "SELECT valueint FROM metrics_now WHERE tagname = 'shard-to-sign' LIMIT 1;");
	while($row = mysqli_fetch_array($result))
	{
		$shard_to_sign = $row['valueint'];
	}
?>

<main role="main" class="container">

  <div class="hmylakemetrics-template">
    
	<div class="lead d-flex justify-content-center mb-4">
      <div>Latest block IDs</div>
	</div>
	
	<div class="row justify-content-center">
      <div class="col-xs-6 mr-4 ml-4 mb-4">
        <p class="lead">Node 1 / Shard 0</p>
	    <div id="load_node1blockidshard0"><h2>Loading...</h2></div>
        <br>
	    <p class="lead">Node 1 / Shard <?php echo $shard_to_sign; ?></p>
	    <div id="load_node1blockidshardx"><h2>Loading...</h2></div>
	    <br>
      </div>
      <div class="col-xs-6 mr-4 ml-4 mb-4">
        <p class="lead">Node 2 / Shard 0</p>
	    <div id="load_node2blockidshard0"><h2>Loading...</h2></div>
        <br>
	    <p class="lead">Node 2 / Shard <?php echo $shard_to_sign; ?></p>
	    <div id="load_node2blockidshardx"><h2>Loading...</h2></div>
	    <br>
      </div>
    </div>
	
	<p class="lead">Main node / Shard 0</p>
	<div id="load_mainnodeblockidshard0"><h2>Loading...</h2></div>
    <br>
	<p class="lead">Main node / Shard <?php echo $shard_to_sign; ?></p>
	<div id="load_mainnodeblockidshardx"><h2>Loading...</h2></div>
  </div>

</main>

<script src="js/jquery-3.0.0.js"></script>
<script src="js/popper.min.js"></script>
<script src="js/feather.min.js"></script>
<script src="js/bootstrap.min.js"></script>
<script type="text/javascript">
var auto_refresh = setInterval(
function ()
{
$('#load_node1blockidshard0').load('<?php echo 'loaddata/tagname.php?api_key=' . API_KEY . '&sender_node=1&tagname=Localnode_Shard0_BlockID'; ?>').fadeIn("slow");
}, 5000); // refresh every 5000 milliseconds
</script>
<script type="text/javascript">
var auto_refresh = setInterval(
function ()
{
$('#load_node1blockidshardx').load('<?php echo 'loaddata/tagname.php?api_key=' . API_KEY . '&sender_node=1&tagname=Localnode_Shard' . $shard_to_sign . '_BlockID'; ?>').fadeIn("slow");
}, 5000); // refresh every 5000 milliseconds
</script>
<script type="text/javascript">
var auto_refresh = setInterval(
function ()
{
$('#load_node2blockidshard0').load('<?php echo 'loaddata/tagname.php?api_key=' . API_KEY . '&sender_node=2&tagname=Localnode_Shard0_BlockID'; ?>').fadeIn("slow");
}, 5000); // refresh every 5000 milliseconds
</script>
<script type="text/javascript">
var auto_refresh = setInterval(
function ()
{
$('#load_node2blockidshardx').load('<?php echo 'loaddata/tagname.php?api_key=' . API_KEY . '&sender_node=2&tagname=Localnode_Shard' . $shard_to_sign . '_BlockID'; ?>').fadeIn("slow");
}, 5000); // refresh every 5000 milliseconds
</script>
<script type="text/javascript">
var auto_refresh = setInterval(
function ()
{
$('#load_mainnodeblockidshard0').load('<?php echo 'loaddata/tagname.php?api_key=' . API_KEY . '&tagname=Mainnode_Shard0_BlockID'; ?>').fadeIn("slow");
}, 5000); // refresh every 5000 milliseconds
</script>
<script type="text/javascript">
var auto_refresh = setInterval(
function ()
{
$('#load_mainnodeblockidshardx').load('<?php echo 'loaddata/tagname.php?api_key=' . API_KEY . '&tagname=Mainnode_Shard' . $shard_to_sign . '_BlockID'; ?>').fadeIn("slow");
}, 5000); // refresh every 5000 milliseconds
</script>
</body>
</html>

<?php
mysqli_close($con);
?>