<!DOCTYPE html>
<?php
require 'update/database.php';
require 'staticmenus.php';

$headline = 'Graph: Signed percentage last 24h';
$json_path = 'chartjson/json_values_signpercentage_60min.php';
$chart_title = 'Sign percentage (60 min moving average)';
$chart_yaxis_title = 'Signed %';
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
?>

<main role="main" class="container">
  <div class="hmylakemetrics-template">
	<div class="lead d-flex justify-content-center mb-4">
      <div><?php echo $headline; ?></div>
	</div>
	<div class="row justify-content-center">
	  <div id="container_highcharts" style="width: 90%;min-width: 310px; height: 500px; margin: 0 auto"></div>
	</div>
  </div>
</main>

<script src="js/jquery-3.0.0.js"></script>
<script src="js/popper.min.js"></script>
<script src="js/feather.min.js"></script>
<script src="js/bootstrap.min.js"></script>
<script>feather.replace()</script>
<script src="highcharts/highcharts.js"></script>
<script src="highcharts/exporting.js"></script>
<script>
		$(function () {
            (function getAjaxData(){

                //use getJSON to get the dynamic data via AJAX call
                $.getJSON('<?php echo $json_path; ?>', function(chartData) {

                    $('#container_highcharts').highcharts({
                        chart: {
							<?php
							echo "type: 'spline'";
							?>,
							borderWidth: 1,
							zoomType: 'xy'
                        },
						credits: {
							enabled: false
						},
                        title: {
                            text: '<?php echo $chart_title; ?>'
                        },
                        xAxis: {
		                <?php
					    echo "type: 'datetime'";
					    ?>,
				        gridLineWidth: 1
			            },
			            yAxis: {
                            title: {
                                text: '<?php echo $chart_yaxis_title; ?>'
                            }
                            },
                            series: chartData,
					        exporting: {
                            enabled: true
                           }
					});
                });
            })();
        });
		
		
    </script>
</body>
</html>

<?php
mysqli_close($con);
?>