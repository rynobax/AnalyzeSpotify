<?php ini_set('max_execution_time', 0); ?>
<html>
	<?php
	if(isset($_ENV["SERVER_NAME"])){
		$base = $_ENV["SERVER_NAME"];
	}else{
		$base = base_url();
	}
	echo "base is ".$base;
	?>
    <script type="text/javascript" src="<?php echo $base.'assets/js/Chart.bundle.js'; ?>"></script>
    <script type="text/javascript" src="<?php echo $base.'assets/js/jQuery-1.12.4.min.js'; ?>"></script>
    <script type="text/javascript" src="<?php echo $base.'assets/js/bootstrap.js'; ?>"></script>
    <link rel="stylesheet" href="<?php echo $base.'assets/css/bootstrap.css' ?>" />
    <head>
        <title>Spotify Playlist Analyzer</title>
    </head>
    <body>

        <?php session_start(); ?>