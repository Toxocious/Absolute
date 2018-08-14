<?php
	require 'layout_top.php';
?>

<style>
  .tooltip > .tooltip-arrow
  {
    display: none;
  }

  .tooltip > .tooltip-inner
  {
    background: none;
    min-width: 276px !important;
    max-width: 276px !important;
    overflow-x: auto !important;
    overflow-y: auto !important;
  }
</style>

<div class='content'>
	<div class='head'>Testing</div>
	<div class='box testing'>
		<div class='row'>
			<button class='popup cboxElement' style='border-color: #4A618F; width: 49.75%;' href='ajax/equip_list.php'>Spawn An Item</button>
			<button class='popup cboxElement' style='border-color: #4A618F; width: 49.75%;' href='ajax/equip_owned.php'>Dispose Of An Item</button>
		</div>

		<div class='row' style='margin-bottom: 5px;'>
			<div class='panel' style='float: left; width: 33%'>
				<div class='panel-heading'>Active Pokemon</div>
				<div class='panel-body'>
					<div>
						<?php
							for ($i = 1 ; $i <= 6 ; $i++) {
								$Query = mysqli_query($con, "SELECT * FROM pokemon WHERE Owner_Current = '" . $User_Data['id'] . "' AND Slot = $i");
								$Slot[$i] = mysqli_fetch_assoc($Query);
								
								if ( $Slot[$i] ) {
									$Pokedex_Data = mysqli_fetch_assoc(mysqli_query($con, "SELECT `Name` FROM `pokedex` WHERE `ID` = '" . $Slot[$i]["Pokedex_ID"] . "'"));
									$Item_Data = mysqli_fetch_assoc(mysqli_query($con, "SELECT Item_Name FROM items_owned WHERE Equipped_To = '" . $Slot[$i]['ID'] . "'"));
									$Slot[$i]["Name"] = $Pokedex_Data["Name"];
								} else {
									$Slot[$i] = "Empty";
								}
		
								if ( $Slot[$i] != "Empty" ) {
									if ( $Slot[$i]['Item'] != '0' ) {
										$Equipped_Item = "<img class='item' src='images/Items/" . $Item_Data['Item_Name'] . ".png' />";
									} else {
										$Equipped_Item = null;
									}
		
									echo "
										<div class='roster_slot'>
											<div class='roster_mini'>
									";
		
									if ( strpos($Pokedex_Data['Name'], ' (Mega)') )
									{
										echo "<img class='popup cboxElement' src='images/Icons/Normal/{$Slot[$i]['Pokedex_ID']}.png' href='ajax/ajax_pokemon.php?id={$Slot[$i]['ID']}' />";
									}
									else
									{
										echo "<img class='popup cboxElement' src='images/Icons/{$Slot[$i]['Type']}/{$Slot[$i]['Pokedex_ID']}.png' href='ajax/ajax_pokemon.php?id={$Slot[$i]['ID']}' />";
									}
																			
									echo "</div></div>";
								} else {
									echo "
										<div class='roster_slot'>
											<div class='roster_mini' style='padding: 0px 5px;'>
												<img src='images/Assets/pokeball.png' style='width: 30px; height: 30px;' />
											</div>
										</div>
									";
								}
							}

							$Active_Pokemon = mysqli_fetch_assoc(mysqli_query($con, "SELECT * FROM `pokemon` WHERE `Owner_Current` = '" . $User_Data['id'] . "' AND `Slot` = '1'"));
							$Active_Pokedex = mysqli_fetch_assoc(mysqli_query($con, "SELECT * FROM `pokedex` WHERE `ID` = '" . $Active_Pokemon['Pokedex_ID'] . "'"));

							echo "<img src='images/Pokemon/{$Active_Pokemon['Type']}/{$Active_Pokemon['Pokedex_ID']}.png' /><br />";

							if ( $Active_Pokemon['Type'] !== "Normal" )
							{
								echo "<b>" . $Active_Pokemon['Type'] . $Active_Pokedex['Name'] . "</b><br />";
							}
							else
							{
								echo "<b>" . $Active_Pokedex['Name'] . "</b><br />";
							}
						?>
					</div>
				</div>
			</div>

			<div class='panel' style='float: left; margin-left: 0.5%; margin-right: 0.5%; width: 33%'>
				<div class='panel-heading'>Equipped Items</div>
				<div class='panel-body equippedItem' style='padding: 3px;'>
						<img style='height: 40px; width: 40px;' onmouseover='showEquip(1);' onmouseout='hideEquip(1);' />
						<div class='equipTooltip' id='equipTooltip1' style='display: none;'>
							hi i am tooltip :)
						</div>
						<img style='height: 40px; width: 40px;' />
						<img style='height: 40px; width: 40px;' />
						<img style='height: 40px; width: 40px;' />
						<img style='height: 40px; width: 40px;' />
						<img style='height: 40px; width: 40px;' />
						<br /><br />
						<img style='height: 40px; width: 40px;' />
						<img style='height: 40px; width: 40px;' />
						<img style='height: 40px; width: 40px;' />
						<img style='height: 40px; width: 40px;' />
				</div>
			</div>

			<div class='panel' style='float: left; width: 33%'>
				<div class='panel-heading'>Equipped Stats</div>
				<div class='panel-body'>
					<div style='padding: 5px;'>You aren't getting any additional stats from your equipment.</div>
				</div>
			</div>
		</div>

		<div class='row'>
			<div class='panel' style='float: left; margin-right: 0.5%; width: 49.75%;'>
				<div class='panel-heading'>Equipment Inventory</div>
				<div class='panel-body' style='padding: 5px;'>
					<?php
						$equip_inventory = mysqli_query($con, "SELECT * FROM equips WHERE Owner_ID = {$User_Data['id']}");
						while ( $equip_data = mysqli_fetch_assoc($equip_inventory) )
						{
							echo "<img class='get_tooltip' id='{$equip_data['Auto']}' src='images/Equipment/Rings/0{$equip_data['ID']}.png' />";
						}
					?>
				</div>
			</div>

			<div class='panel' style='float: left; width: 49.75%;'>
				<div class='panel-heading'>Equip Stats</div>
				<div class='panel-body' id='equipStats'>
					Click on a piece of equipment that you own to check it's stats.
				</div>
			</div>
		</div>
	</div>
</div>

<script type='text/javascript'>
	/* ================================================= */
	$(document).ready(function()
  {
    $('.get_tooltip').tooltip(
    {
      delay: 1,
      placement: 'right',
      title: fetch_stats,
      html: true
    });
  });

  function fetch_stats()
  {
    var equip_id = this.id
    var tooltipText = "";
    $.ajax({
      url: 'ajax/equip_tooltip.php',
      type: 'post',
      async: false,
      data: { equip_id: equip_id },
      success: function(response)
      {
        tooltipText = response;
      }
    });
    return tooltipText;
  }	
	
	/* ================================================= */
	function equipStats(item_id)
	{
		$.ajax({
			type: 'post',
			url: 'ajax/fetch_equip.php',
			data: { equip_id: item_id },
			success: function(data)
			{
				$('#equipStats').html(data);
			},
			error: function(data)
			{
				$('#equipStats').html(data);
			}
		});
	}

	function displayStats()
	{
		var item_id = $('#equip_list').val();

		$.ajax({
			type: 'post',
			url: 'ajax/fetch_equip.php',
			data: { equip_id: item_id },
			success: function(data)
			{
				$('#showEquipStats').html(data);
			},
			error: function(data)
			{
				$('#showEquipStats').html(data);
			}
		});
	}

	function showEquip(id)
	{
		var tooltip = $('#equipTooltip' + id);
		tooltip.css({ "display":"block", "position":"absolute", "z-index":"1000000" });
		document.addEventListener('mousemove', fn, false);

		function fn(e)
		{
				for ( i = tooltip.length; i--; )
				{
						tooltip[i].style.left = e.pageX + 'px';
						tooltip[i].style.top = e.pageY + 'px';
				}
		}
	}

	function hideEquip(id)
	{
		var tooltip = $('#equipTooltip' + id);
		tooltip.css({ "display":"none" });
	}
</script>

<?php
  require 'layout_bottom.php';
?>