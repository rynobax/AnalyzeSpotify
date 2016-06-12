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
	if(isset($_ENV["SERVER_NAME"])){
		$base = "/";
	}else{
		$base = base_url();
	}
?>
<style>
.img-responsive {
    margin: 0 auto;
}
</style>
<div class="container">
    <div class="jumbotron" style="text-align: center">
	Statistics are being calculated, please wait.<br>
	<img src="<?php echo $base."assets/images/loading-cropped.gif"; ?>" class="img-responsive" alt="Loading">
	</div>
</div>
<body>
<script type="text/javascript">
$(function(){
    var response = $.get('<?php echo "display_data?userid=". $userid ."&playlistid=". $playlistid . "&playlistname=".$playlistname ?>', null, function(resp) {
  		document.write( resp );
  		console.log(resp);

        var h1 = $('#graph1').height();
        var h2 = $('#graph2').height();
        var h3 = $('#graph3').height();
        var height = Math.max(h1, h2, h3);

        for (var i = 1; i <= 3; i++) {
            $('#graph'+i).height(height);
        }
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
<script type="text/javascript">
    $('.nav-tabs li a').click( function(e) {
    history.pushState( null, null, $(this).attr('href') );
    });
</script>
<?php
}
?>