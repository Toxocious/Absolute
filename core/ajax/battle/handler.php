<?php
	$Battle_Version = '1';
	$FULL_DEBUG = false;

	require_once '../../required/session.php';
	require_once '../../classes/battle.php';

	/**
	 * Quick checks to see if the battle should continue.
	 */
	if ( !isset($_SESSION['Battle']) ) 
	{
		die("<div class='error'>There is no battle in session.<br />Please start a battle.</div>");
	}
	else if ( $User_Data['id'] == $_SESSION['Battle']['Battle_Foe'] )
	{
		die("<div class='error'>You may not battle yourself.</div>");
	}

	$Battle = new Battle();

	/**
	 * Verify that the user's roster hasn't changed.
	 */
	$Roster_Check = $Battle->VerifyRoster( $User_Data['Roster'] );
	if ( $Roster_Check )
	{
		unset($_SESSION['Battle']);
		die("<div class='error'>Your roster has changed.<br />Please restart the battle.</div>");
	}

	/**
	 * Fetch the rosters of both users.
	 */
	try
	{
		$Attackers_Roster_Query = $PDO->prepare("SELECT `ID` FROM `pokemon` WHERE `Owner_Current` = ? AND `Slot` <= 6 AND `Location` = 'Roster' ORDER BY `Slot` ASC LIMIT 6");
		$Attackers_Roster_Query->execute([ $User_Data['id'] ]);
		$Attackers_Roster_Query->setFetchMode(PDO::FETCH_ASSOC);
		$Attackers_Roster = $Attackers_Roster_Query->fetchAll();

		$Defenders_Roster_Query = $PDO->prepare("SELECT `ID` FROM `pokemon` WHERE `Owner_Current` = ? AND `Slot` <= 6 AND `Location` = 'Roster' ORDER BY `Slot` ASC LIMIT 6");
		$Defenders_Roster_Query->execute([ $_SESSION['Battle']['Battle_Foe'] ]);
		$Defenders_Roster_Query->setFetchMode(PDO::FETCH_ASSOC);
		$Defenders_Roster = $Defenders_Roster_Query->fetchAll();
	}
	catch( PDOException $e )
	{
		HandleError( $e->getMessage() );
	}

	/**
	 * Handle processing turns.
	 */
	if ( isset($_POST['Element']) && isset($_POST['x']) && isset($_POST['y']) && isset($_POST['Clicks']) )
	{
		/**
		 * Attacking the foe.
		 */
		if ( isset($_POST['Move']) )
		{
			$Move = $Purify->Cleanse($_POST['Move']);
			$Clicks = $Purify->Cleanse($_POST['Clicks']);
			$Element = [
				'PostCode'	=> $Purify->Cleanse($_POST['Element']['PostCode']),
				'Pos_Top'		=> $Purify->Cleanse(round($_POST['Element']['Position']['top'])),
				'Pos_Left'	=> $Purify->Cleanse(round($_POST['Element']['Position']['left'])),
				'Height'		=> $Purify->Cleanse($_POST['Element']['Height']),
				'Width'			=> $Purify->Cleanse($_POST['Element']['Width']),
			];
			$Coords = [
				'x' => $Purify->Cleanse($_POST['x']),
				'y' => $Purify->Cleanse($_POST['y']),
			];

			/**
			 * Calculate the accepted coordinate range of the clicked element.
			 */
			$Valid_Coords =
			[
				'X' =>
				[
					'Min'	=> $Element['Pos_Left'],
					'Max'	=> $Element['Pos_Left'] + $Element['Width'],
				],
				'Y' =>
				[
					'Min'	=> $Element['Pos_Top'],
					'Max'	=> $Element['Pos_Top'] + $Element['Height'],
				],
			];

			/**
			 * Check to see if the user clicked within the accepted coordinate range.
			 */
			$Check_Coords = $Battle->CheckCoords( $Coords['x'], $Valid_Coords['X']['Min'], $Valid_Coords['X']['Max'], $Coords['y'], $Valid_Coords['Y']['Min'], $Valid_Coords['Y']['Max'] );
			if ( $Check_Coords )
			{
				$Message = "Coords are within the accepted range.<br />";
			}
			else
			{
				$Message = "Coords are outside the accepted range.<br />";
			}

			/**
			 * Check to see if the submitted post code matches the session post code.
			 */
			if ( $Element['PostCode'] == $_SESSION['Battle']['PostCode_M' . $Move] )
			{
				$Message .= "The submitted postcode matches the session postcode.<br />";
			}
			else
			{
				$Message .= "The submitted postcode doesn't match the session postcode.<br />";
			}

			/**
			 * A high amount of clicks usually indicates that someone is using a basic autoclicker.
			 */
			if ( $Clicks > 7 )
			{
				$Message .= "A high amount of clicks in between input submits has been detected.";
			}

			/**
			 * Update the session's battle text.
			 */
			$_SESSION['Battle']['Text'] = "You attacked the foe!";
			if ( $FULL_DEBUG )
			{
				$_SESSION['Battle']['Text'] .= "
					<br /><br />
					Postcode Data (Received/Expected):<br />
					{$Element['PostCode']} / {$_SESSION['Battle']['PostCode_M' . $Move]}<br /><br />

					Valid Coord Area:<br />
					x => {$Valid_Coords['X']['Min']} to {$Valid_Coords['X']['Max']}<br />
					y => {$Valid_Coords['Y']['Min']} to {$Valid_Coords['Y']['Max']}
					<br /><br />

					Clicked Coordinates (x,y):<br />
					{$Coords['x']} , {$Coords['y']}
					<br /><br />

					Total Clicks:<br />
					{$Clicks}
					<br /><br />

					Message:<br />
					{$Message}
				";
			}
		}

		/**
		 * Continuing the battle.
		 */
		if ( isset($_POST['Continue']) )
		{

		}

		/**
		 * Restarting the battle.
		 */
		if ( isset($_POST['Restart']) )
		{

		}
	}
?>

<div class='row'>
	<div style='float: left; margin-right: 5px; width: calc(100% / 2 - 2.5px);'>
		<div class='roster'>
			<?php
				for ( $i = 0; $i <= 5; $i++ )
				{
					if ( isset($Attackers_Roster[$i]['ID']) )
					{
						$Attacker_Active = $PokeClass->FetchPokemonData($Attackers_Roster[0]['ID']);
						$Attacker_Poke[$i] = $PokeClass->FetchPokemonData($Attackers_Roster[$i]['ID']);


						echo "
							<div class='battle_slot'>
								<img src='{$Attacker_Poke[$i]['Icon']}' />
							</div>
						";
					}
				}
			?>
		</div>

		<div class='active_pokemon'>
			<img src='<?= $Attacker_Active['Sprite']; ?>' />
			<div style='float: right; margin: 5px;'>
				HP
				<div class='hp_bar'>
					<span style='border-radius: 4px; padding: 2px;'></span>
				</div>
			</div>
		</div>
	</div>

	<div style='float: left; width: calc(100% / 2 - 2.5px);'>
		<div class='roster'>
		<?php
				for ( $i = 0; $i <= 5; $i++ )
				{
					if ( isset($Defenders_Roster[$i]['ID']) )
					{
						$Defender_Active = $PokeClass->FetchPokemonData($Defenders_Roster[0]['ID']);
						$Defender_Poke[$i] = $PokeClass->FetchPokemonData($Defenders_Roster[$i]['ID']);

						echo "
							<div class='battle_slot'>
								<img src='{$Defender_Poke[$i]['Icon']}' />
							</div>
						";
					}
				}
			?>
		</div>

		<div class='active_pokemon'>
			<img src='<?= $Defender_Active['Sprite']; ?>' />
			<div style='float: left; margin: 5px;'>
				HP
				<div class='hp_bar'>
					<span style='border-radius: 4px; padding: 2px;'></span>
				</div>
			</div>
		</div>
	</div>
</div>

<div class='panel' style='margin: 5px 0px; padding: 5px;'>
	<div class='panel-body' id='Moves'>
		<?php
			$Battle->RenderMoves();
		?>
	</div>
</div>

<div class='panel'>
	<div class='panel-heading'>Battle Dialogue</div>
	<div class='panel-body' style='padding: 5px;' id='BattleDialogue'>
		<?= $_SESSION['Battle']['Text']; ?>
	</div>
</div>

<script type='text/javascript'>
	let Clicks = 0;
	$(document).click(function()
	{
		Clicks++;
	});

	Attack = function(Move, ID, event)
	{
		$('#BattleDialogue').html("<div style='padding: 10px;'><div class='spinner' style='left: 48.5%; position: relative;'></div></div>");

		let Element = {
			'PostCode': $(event.target).attr('PostCode'),
			'Position': $(event.target).offset(),
			'Width': 		parseInt( $(event.target).css('width') ),
			'Height': 	parseInt( $(event.target).css('height' ))
		};

		$.ajax({
			type: 'POST',
			url: 'core/ajax/battle/handler.php',
			data: { Element: Element, Move: Move, ID: ID, x: event.pageX, y: event.pageY, Clicks: Clicks },
			success: function(data)
			{
				$('#BattleWindow').html(data);
				Clicks = 0;
			},
			error: function(data)
			{
				$('#BattleWindow').html(data);
				Clicks = 0;
			}
		});
	}
</script>