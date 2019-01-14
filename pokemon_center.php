<?php
	require 'core/required/layout_top.php';

	try
	{
		$Fetch_Pokemon = $PDO->prepare("SELECT `ID` FROM `pokemon` WHERE `Owner_Current` = ? AND `Location` = 'Roster' ORDER BY `Slot` ASC LIMIT 6");
		$Fetch_Pokemon->execute([$User_Data['id']]);
		$Fetch_Pokemon->setFetchMode(PDO::FETCH_ASSOC);
		$Fetch_Roster = $Fetch_Pokemon->fetchAll();
	}
	catch ( PDOException $e )
	{
		HandleError( $e->getMessage() );
	}
?>

<style>
	.active { background: #253047 !important; color: #fff !important; }
</style>

<div class='content'>
	<div class='head'>Pokemon Center</div>
	<div class='box pokecenter'>
		<div class='nav'>
			<div onclick="showTab('roster');" class='active'>Roster</div>
			<div onclick="showTab('moves');">Moves</div>
			<div onclick="showTab('inventory');">Inventory</div>
			<div onclick="showTab('nickname');">Nickname</div>
			<!--<div onclick="showTab('release');">Release</div>-->
		</div>
    
    <div class='row' id='pokemon_center'>
			<div class='panel' style='margin-bottom: 5px; width: 100%;'>
        <div class='panel-heading'>Roster</div>
        <div class='panel-body'>
					<?php
						for ( $i = 0; $i <= 5; $i++ )
						{
							if ( isset($Fetch_Roster[$i]['ID']) )
							{
								$Roster_Slot[$i] = $PokeClass->FetchPokemonData($Fetch_Roster[$i]['ID']);
			
								if ( $Roster_Slot[$i]['Item'] != null )
								{
									$Item = "<img src='{$Roster_Slot[$i]['Item_Icon']}' style='margin: 5px 0px 0px -10px; position: absolute;' />";
								}
								else
								{
									$Item = "";
								}
								
								echo "
									<div class='roster_slot full' style='/*width: calc(100% / 3);*/'>
										<div style='float: left;' class='slots left'>
								";
			
								for ($x = 1; $x <= 3; ++$x) {
									if ( $x == $i + 1 || $x > count($Fetch_Roster) )
									{
										echo "<div><span style='color: #000; display: block; padding: 13px;'>$x</span></div>";
									}
									else
									{
										echo "<div><a href='javascript:void(0);' onclick=\"handlePokemon('Move', {$Roster_Slot[$i]['ID']}, $x);\" style='display: block; padding: 13px;'>$x</a></div>";
									}
								}
			
								echo "
									</div>
									<img src='{$Roster_Slot[$i]['Gender_Icon']}' style='height: 20px; margin: 10px 0px 0px -20px; position: absolute; width: 20px;' />
									<img class='spricon popup cboxElement' src='{$Roster_Slot[$i]['Sprite']}' href='core/ajax/pokemon.php?id={$Roster_Slot[$i]['ID']}' />
									$Item
									<div style='float: right;' class='slots right'>
								";
			
								for ($x = 4; $x <= 6; ++$x) {
									if ( $x == $i + 1 || $x > count($Fetch_Roster) )
									{
										echo "<div><span style='color: #000; display: block; padding: 13px;'>$x</span></div>";
									}
									else
									{
										echo "<div><a href='javascript:void(0);' onclick=\"handlePokemon('Move', {$Roster_Slot[$i]['ID']}, $x);\" style='display: block; padding: 13px;'>$x</a></div>";
									}
								}
			
								echo "
										</div>
										<div><b>{$Roster_Slot[$i]['Display_Name']}</b></div>
										<div class='info'>
											<div>Level</div>
											<div>{$Roster_Slot[$i]['Level']}</div>
										</div>
										<div class='info'>
											<div>Experience</div>
											<div>{$Roster_Slot[$i]['Experience']}</div>
										</div>
									</div>
								";
							}
							else
							{
								$Roster_Slot[$i]['Sprite'] = Domain(3) . 'images/pokemon/0.png';
								$Roster_Slot[$i]['Display_Name'] = 'Empty';
								$Roster_Slot[$i]['Level'] = '0';
								$Roster_Slot[$i]['Experience'] = '0';
			
								echo "
									<div class='roster_slot full' style='/*width: calc(100% / 3);*/'>
										<img src='{$Roster_Slot[$i]['Sprite']}' />
										<div><b>{$Roster_Slot[$i]['Display_Name']}</b></div>
										<div class='info'>
											<div>Level</div>
											<div>{$Roster_Slot[$i]['Level']}</div>
										</div>
										<div class='info'>
											<div>Experience</div>
											<div>{$Roster_Slot[$i]['Experience']}</div>
										</div>
									</div>
								";
							}
						}
					?>
        </div>
      </div>

      <div class='panel' style='float: left; width: calc(100% / 2 - 2.5px);'>
        <div class='panel-heading'>Box</div>
        <div class='panel-body' style='padding: 3px;'>
          <?php
						try
						{
							$Box_Query = $PDO->prepare("SELECT * FROM `pokemon` WHERE `Owner_Current` = ? AND `Slot` = 7 ORDER BY `Pokedex_ID` ASC LIMIT 50");
							$Box_Query->execute([$User_Data['id']]);
							$Box_Query->setFetchMode(PDO::FETCH_ASSOC);
							$Box_Pokemon = $Box_Query->fetchAll();
						}
						catch (PDOException $e)
						{
							echo $e->getMessage();
						}
			
						foreach ( $Box_Pokemon as $Index => $Pokemon )
						{
							$Pokemon = $PokeClass->FetchPokemonData($Pokemon['ID']);
							echo "<img class='spricon' src='{$Pokemon['Icon']}' onclick='displayPokeData({$Pokemon['ID']});'/>";
						}
			
						if ( count($Box_Pokemon) == 0 )
						{
							echo "No Pokemon were found in your box.";
						}
          ?>
        </div>
      </div>

      <div class='panel' style='float: right; width: calc(100% / 2 - 2.5px);'>
        <div class='panel-heading'>Selected Pokemon</div>
        <div class='panel-body' style='padding: 3px;' id='pokeData'>
          <div style='padding: 5px;'>Please select a Pokemon to view it's statistics.</div>
        </div>
      </div>
    </div>
  </div>
</div>

<script type='text/javascript'>
	$("img.popup.cboxElement").colorbox({ iframe: true, innerWidth: 680, innerHeight: 491 });

	/**
	 * Neato navigation styling.
	 */
	let navDivs = $('.pokecenter .nav div');
	for ( let i = 0; i < navDivs.length; i++ )
	{
		navDivs[i].addEventListener("click", function()
		{
			let current = document.getElementsByClassName("active");
			current[0].className = current[0].className.replace("active", "");
			this.className += "active";
		});
	}
	 
	 /**
	 	* Handle AJAX requests pertaining to moving Pokemon around, as well as displaying their stats if necessary.
		* Also updates both the Userbar roster, as well as the Pokemon Center roster.
		*/
	function handlePokemon(Request, PokeID = null, Slot = null)
	{
		$.ajax({
			type: 'POST',
			url: 'core/ajax/functions/manage_pokemon.php',
			data: { Request: Request, PokeID: PokeID, Slot: Slot },
			success: function(data)
			{
				$('#pokemon_center').html(data);
				updateRoster('pokecenter');
				updateRoster('userbar');
				$("img.popup.cboxElement").colorbox({ iframe: true, innerWidth: 680, innerHeight: 491 });
			},
			error: function(data)
			{
				$('#pokemon_center').html(data);
			}
		});
	}

	/**
	 * Update the Userbar and Pokemon Center rosters.
	 */
	function updateRoster(Location)
	{
		$.ajax({
			type: 'POST',
			url: 'core/ajax/functions/manage_pokemon.php',
			data: { Request: 'Roster', Location: Location },
			success: function(data)
			{
				if ( $("#"+Location+"_roster").length > -1 )
				{
					$("#"+Location+"_roster").html(data);
				}
			},
			error: function(data)
			{
				$("#"+Location+"_roster").html(data);
			}
		});
	}

	/**
	 * Display a Pokemon's data to the user.
	 */
	function displayPokeData(PokeID)
	{
		$.ajax({
			type: 'POST',
			url: 'core/ajax/pokecenter/pokemon.php',
			data: { PokeID: PokeID },
			success: function(data)
			{
				$("#pokeData").html(data);
				$("img.popup.cboxElement").colorbox({ iframe: true, innerWidth: 680, innerHeight: 491 });
			},
			error: function(data)
			{
				$("#pokeData").html(data);
			}
		});
	}

	/**
	 * Handle swapping between inventory tabs, as well as displaying item data.
	 */
	function itemHandler(request, category = null, id = null, pokeid = null)
	{
		$.ajax({
			type: 'POST',
			url: 'core/ajax/pokecenter/inventory.php',
			data: { request: request, category: category, id: id, pokeid: pokeid },
			success: function(data)
			{
				if ( request == 'item_data' )
				{
					$('#itemData').html(data);
				}
				else if ( request == 'item_tab' )
				{
					$('#activeTab').html(data);
				}
				else if ( request == 'attach' || request == 'detach' || request == 'detachall' )
				{
					$('#pokemon_center').html(data);
				}
			},
			error: function(data)
			{
				$('#pokemon_center').html(data);
			}
		});
	}

	/**
	 * Handle nicknaming Pokemon.
	 */
	function Nickname(PokeID)
	{
		$.ajax({
			type: 'POST',
			url: 'core/ajax/pokecenter/nickname.php',
			data: { PokeID: PokeID, Nickname: $("[name='" + PokeID + "_nick']").val() },
			success: function(data)
			{
				$('#pokemon_center').html(data);
			},
			error: function(data)
			{
				$('#pokemon_center').html(data);
			}
		});
	}

	/**
	 * Swap between tab content.
	 */
	function showTab(tab)
	{
		$.get('core/ajax/pokecenter/' + tab + '.php', function(data)
		{
			$('#pokemon_center').html(data);
			$("img.popup.cboxElement").colorbox({ iframe: true, innerWidth: 680, innerHeight: 491 });
		});
	}
</script>

<?php
	require 'core/required/layout_bottom.php';