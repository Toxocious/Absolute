<?php
	require_once 'core/required/session.php';
	echo "<style>* { background: #222; color: #fff; margin: 0px; padding: 0px; }</style>";	

	for ( $i = 0; $i < 100; $i++ )
	{
		$Fetch_Gender = $Poke_Class->GenerateGender(359, 0);
		echo $Fetch_Gender . "<br />";
	}


	echo "<br />";
	echo "<hr />";

	echo strtotime("Aug 16th, 2017 8:00:35 PM") . "<br />";
	echo strtotime("May 18th, 2018 4:28:00 PM") . "<br />";
	echo strtotime("Jul 23rd, 2018 5:26:51 PM") . "<br />";
	echo "<br /><br />";

	echo "<hr />";

	$User_Class->DisplayUserRank(1);
  $User_Class->DisplayUserRank(2);
  $User_Class->DisplayUserRank(3);

	echo "<hr />";

	echo $User_Class->DisplayUserName(1) . "<br />";
  echo $User_Class->DisplayUserName(2) . "<br />";
  echo $User_Class->DisplayUserName(3) . "<br />";

	echo "<hr />";

	function censor($string)
	{
			if ($string)
			{
					$Censor_List = ("nigger,Nigger,piss,Piss,cunt,Cunt");
					$Censor_List = explode(",", $Censor_List);
					$replacewith = array();
					$index = 0;
					foreach ($Censor_List as $value) {
							$lengthOfStars = strlen($Censor_List[$index]) - 2;
							$replacewith[$index] = substr($Censor_List[$index], 0, 1).str_repeat("*", $lengthOfStars).substr($Censor_List[$index], -1);
							$index++;
					}
					$newstring = str_ireplace($Censor_List, $replacewith, $string);
					return $newstring;
			}
	}

	echo censor("You are the BIGGEST Nigger that I've ever seen. Pissoff, cunt.");

	echo "<br /><hr /><br />";

	$Pokemon = $Poke_Class->FetchPokemonData(4);
	$User = $User_Class->FetchUserData(1);

	echo "
		<div style='float: left; height: 100%; padding: 5px;  width: 25%'>
			<b>User ID</b>: ".$User['ID']."<br />
			<b>Username</b>: ".$User['Username']."<br />
			<b>Trainer Level</b>: ".$User['Trainer_Level']."<br />
			<b>Trainer Exp</b>: ".$User['Trainer_Exp']."<br />
		</div>
	";

	echo "
		<div style='border-left: 2px solid #666; border-right: 2px solid #666; float: left; height: 100%; padding: 5px; width: 25%;'>
			<img src='".$Pokemon['Sprite']."' /><img src='".$Pokemon['Icon']."' /><br />
			<b>Level:</b><br />" . $Pokemon['Level'] . "<br />
			<b>Experience:</b><br />" . $Pokemon['Experience'] . "<br />
			<b>Base Stats:</b><br />
	";
		for ( $i = 0; $i <= 5; $i++ )
		{
			echo $Pokemon['BaseStats'][$i];
			if ( $i !== 5 )
				echo ", ";
		}
	echo "
		<br /><b>Stats:</b><br />
	";
		for ( $i = 0; $i <= 5; $i++ )
		{
			echo $Pokemon['Stats'][$i];
			if ( $i !== 5 )
				echo ", ";
		}
		echo "
			<br /><b>IV's:</b><br />
		";
		for ( $i = 0; $i <= 5; $i++ )
		{
			echo $Pokemon['IVs'][$i];
			if ( $i !== 5 )
				echo ", ";
		}
	echo "
		<br /><b>EV's:</b><br />
	";
		for ( $i = 0; $i <= 5; $i++ )
		{
			echo $Pokemon['EVs'][$i];
			if ( $i !== 5 )
				echo ", ";
		}
	echo "</div>";

	echo "
		<div style='float: left; padding: 5px; width: 48%;'>
			<pre>"; var_dump($Pokemon); echo"</pre>
		</div>
	";
?>

<script src="https://code.jquery.com/jquery-2.2.4.min.js"></script>