<?php ini_set('max_execution_time', 0); ?>
<html>
    <script type="text/javascript" src="<?php echo base_url("assets/js/jQuery-1.12.4.min.js"); ?>"></script>
    <script type="text/javascript" src="<?php echo base_url("assets/js/bootstrap.js"); ?>"></script>
    <link rel="stylesheet" href="<?php echo base_url("assets/css/bootstrap.css"); ?>" />
        <head>
                <title>Spotify Stuff</title>
        </head>
        <body>

                <h1><?php echo $title; ?></h1>

                <?php session_start(); ?>