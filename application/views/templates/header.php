<?php ini_set('max_execution_time', 0); ?>
<html>
	<?php
	if(isset($_ENV["SERVER_NAME"])){
		$base = "/";
	}else{
		$base = base_url();
	}
	?>
    <script type="text/javascript" src="<?php echo $base.'assets/js/Chart.bundle.js'; ?>"></script>
    <script type="text/javascript" src="<?php echo $base.'assets/js/jquery-1.12.4.min.js'; ?>"></script>
    <script type="text/javascript" src="<?php echo $base.'assets/js/bootstrap.js'; ?>"></script>
    <style>   
    .container {
        max-width: 800px;
    }
    </style>
    <link rel="stylesheet" href="<?php echo $base.'assets/css/bootstrap.css' ?>" />
    <head>
        <title>Spotify Playlist Analyzer</title>
    </head>
    <body>

        <?php session_start(); ?>