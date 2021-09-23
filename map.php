<?php
	require_once 'core/required/layout_top.php';
?>

<div class='panel content'>
	<div class='head'>Maps</div>
	<div class='body'>
		<div class='description'>
			You may use WASD to move.
			Hold C to run.
		</div>

		<div class='row' style='padding: 5px;'>

			<div id='screen'></div>

			<div class='panel' style='border-width: 2px; float: left; height: 240px; width: 240px;'>
				<div class='head'>Map Events</div>
				<div class='body' id='map_text' style='padding: 5px;'>
					What'll you find today?
				</div>
			</div>

		</div>

		<!--
		<div class='row'>
			<div class='info'>
				manual map controls
			</div>

			<div class='info'>
				<b>Map Level</b>: 0 omegalul<br />
			</div>
		</div>
		-->
	</div>
</div>

<!-- melonJS Library -->
<script type="text/javascript" src="maps/Libs/melonjs.js"></script>
<script type="text/javascript" src="maps/Libs/debugPanel.js"></script>

<!-- Game Scripts -->
<script type="text/javascript" src="maps/JS/game.js"></script>
<script type="text/javascript" src="maps/JS/map.js"></script>
<script type="text/javascript" src="maps/JS/tileinfo.js"></script>
<script type="text/javascript" src="maps/JS/movement.js"></script>
<script type="text/javascript" src="maps/JS/network.js"></script>
<script type="text/javascript" src="maps/JS/encounter.js"></script>
<script type="text/javascript" src="maps/JS/fishing.js"></script>
<script type="text/javascript" src="maps/JS/actions.js"></script>
<script type="text/javascript" src="maps/resources.js"></script>

<!-- Entities -->
<script type="text/javascript" src="maps/JS/entities/entities.js"></script>
<script type="text/javascript" src="maps/JS/entities/static.js"></script>
<script type="text/javascript" src="maps/JS/entities/transport.js"></script>
<script type="text/javascript" src="maps/JS/entities/HUD.js"></script>

<!-- Screens -->
<script type="text/javascript" src="maps/JS/screens/error.js"></script>
<script type="text/javascript" src="maps/JS/screens/title.js"></script>
<script type="text/javascript" src="maps/JS/screens/play.js"></script>
<script type="text/javascript" src="maps/JS/screens/loading.js"></script>
<script type="text/javascript" src="maps/JS/screens/crash.js"></script>


<!-- Load the canvas when the DOM has finished loading. -->
<script type="text/javascript">
	me.device.onReady(function onReady() {
		game.onload();
	});
</script>

<!-- Temporary styling? -->
<style>
	#screen canvas
	{
		border: 2px solid #4A618F;
		border-radius: 4px;

		display: block;
		float: left;

		margin: 0px 10px;

		height: 240px;
		width: 240px;
	}

	#MapControls
	{
		border: 2px solid #4A618F;
		border-radius: 4px;

		margin-right: 10px;

		width: 240px;
	}

	#MapText
	{
		border: 2px solid #4A618F;
		border-radius: 4px;

		display: block;
		float: left; 

		height: 240px;
		width: 240px;
	}

	.row
	{
		margin: 0 auto !important;

		padding: 5px;

		width: 510px;
	}

	.row .info
	{
		border: 2px solid #4A618F;
		border-radius: 4px;
		float: left;
		margin: 0px 0px 0px 10px;
		padding: 5px 0px;
		width: 240px;
	}
</style>

<?php
	require_once 'core/required/layout_bottom.php';