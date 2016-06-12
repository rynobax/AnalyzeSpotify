<?php
	/* This page should call display_data and show waiting while it loads */
	if(!isset($_POST["userid"])){
		?>

		<div class="container">
		    <div class="jumbotron" style="text-align: center">
		<script type="text/javascript">
		document.write("An error occured.  Redirecting in 5 seconds.");
    	window.setTimeout(function(){
	        // Move to a new location or you can do something else
	        window.location.href = "pick_playlist";
    	}, 5000);
    	</script>
    	</div></div>
    	<?php
	}else{
	$userid = $_POST["userid"];
	$playlistid = $_POST["playlistid"];
	$playlistname = $_POST["playlistname"];
	/* fail out if these arent set */
?>
<style>
.img-responsive {
    margin: 0 auto;
}
</style>
<div class="container">
    <div class="jumbotron" style="text-align: center">
	Statistics are being calculated, please wait.<br>
	<img src="<?php echo base_url("assets/images/loading-cropped.gif"); ?>" class="img-responsive" alt="Loading">
	</div>
</div>
<body>
 <!-- Include jQuery here! Also have the loading animation here. -->
<script type="text/javascript">
$(function(){

    var response = $.get('<?php echo "display_data?userid=". $userid ."&playlistid=". $playlistid . "&playlistname=".$playlistname ?>', null, function(resp) {
  		document.write( resp );
	})
  	.fail(function() {
    	document.write("An error occured.  Redirecting in 5 seconds.");
    	window.setTimeout(function(){
	        // Move to a new location or you can do something else
	        window.location.href = "pick_playlist";
    	}, 5000);
  	});

});
</script>
<?php
}
?>