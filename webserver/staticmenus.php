<?php

//Site title
define('SITE_TITLE', 'My validator / Node data');

//Site name on navbar
define('NAVBAR_TXT', 'My validator / Node data');

//Navbar link to validator home page
define('NAVBAR_LINK', 'https://www.myvalidatorhomepage.example');

function staticmenus_drawnav() {
	echo '
	<nav class="navbar navbar-expand-md navbar-dark bg-dark fixed-top">
	<button class="navbar-toggler navbar-toggler-right" type="button" data-toggle="collapse" data-target="#navbars" aria-controls="navbars" aria-expanded="false" aria-label="Toggle navigation">
    <span class="navbar-toggler-icon"></span>
	</button>
	
	<a class="navbar-brand" href="' . NAVBAR_LINK . '">' . NAVBAR_TXT . '</a>

	<div class="collapse navbar-collapse" id="navbars">
        <div class="navbar-nav">
            <a class="nav-item nav-link" href="index.php">[Status]</a>
			<a class="nav-item nav-link" href="graph_6h.php">[Graph 6h]</a>
			<a class="nav-item nav-link" href="graph_10min.php">[Graph 10m/24h]</a>
			<a class="nav-item nav-link" href="graph_60min.php">[Graph 60m/24h]</a>
			<a class="nav-item nav-link" href="graph_7d_60min.php">[Graph 60m/7d]</a>
			<a class="nav-item nav-link" href="graph_ping.php">[Graph ping]</a>
			<a class="nav-item nav-link" href="graph_missed.php">[Graph missed]</a>
			<a class="nav-item nav-link" href="blockids.php">[Block IDs]</a>
        </div>
    </div>

	</nav>
	';
	
    return null;
}



?>