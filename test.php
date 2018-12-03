<?php
	require_once 'core/required/session.php';
	/*
	function setImgDownload($imagePath) {
		$image = imagecreatefrompng($imagePath);
		header('Content-Type: image/png');
		imagepng($image);
	}

	echo "<img src='".setImgDownload('images/Pokemon/Normal/001.png')."' />";
	*/

	$Pokemon = $PokeClass->FetchPokemonData(4);

	echo "<hr />";

	echo "
		<img src='".$Pokemon['Sprite']."' /><img src='".$Pokemon['Icon']."' /><br />
		<b>Level:</b><br />" . $Pokemon['Level'] . "<br />
		<b>Experience:</b><br />" . $Pokemon['Experience'] . "<br />
		<b>Base Stats:</b><br />";
		for ( $i = 0; $i <= 5; $i++ )
		{
			echo $Pokemon['BaseStats'][$i];
			if ( $i !== 5 )
				echo ", ";
		}
	echo "
		<br /><b>Stats:</b><br />";
		for ( $i = 0; $i <= 5; $i++ )
		{
			echo $Pokemon['Stats'][$i];
			if ( $i !== 5 )
				echo ", ";
		}
		echo "
		<br /><b>IV's:</b><br />";
		for ( $i = 0; $i <= 5; $i++ )
		{
			echo $Pokemon['IVs'][$i];
			if ( $i !== 5 )
				echo ", ";
		}
	echo "
		<br /><b>EV's:</b><br />";
		for ( $i = 0; $i <= 5; $i++ )
		{
			echo $Pokemon['EVs'][$i];
			if ( $i !== 5 )
				echo ", ";
		}
?>

<script src="https://code.jquery.com/jquery-2.2.4.min.js"></script>