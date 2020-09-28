<?php
	require '../core/required/session.php';
	require '../core/functions/staff.php';
?>

<div class='head'>Pokemon Spawner</div>
<div class='body'>

	<div class='description' style='margin-bottom: 5px;'>
		Here, you may spawn any Pokemon, and give it to any user.
	</div>

	<div id='AJAX'></div>

	<div class='row'>
		<div class='panel' style='float: left; width: 512px;'>
			<div class='head'>Pokemon Selector</div>
			<div class='body' style='height: 563px; overflow: auto; padding-top: 3px;'>
				<?php
					try
					{
						$Fetch_Pokedex = $PDO->prepare("SELECT `id`, `Pokedex_ID`, `Alt_ID` FROM `pokedex`");
						$Fetch_Pokedex->execute();
						$Fetch_Pokedex->setFetchMode(PDO::FETCH_ASSOC);
						$Pokedex = $Fetch_Pokedex->fetchAll();
					}
					catch ( PDOException $e )
					{
						HandleError( $e->getMessage() );
					}

					foreach ( $Pokedex as $Key => $Pokemon )
					{
						$Poke_Data = $Poke_Class->FetchPokedexData( $Pokemon['Pokedex_ID'], $Pokemon['Alt_ID'] );

						echo "
							<img class='iconSelect' src='{$Poke_Data['Icon']}' onclick='SelectPokemon({$Pokemon['id']});' />
						";
					}
				?>
			</div>
		</div>

		<div style='float: left;'>
			<form id='SpawnPokeForm' onsubmit='LoadContent("ajax/spawn_pokemon.php", "AJAX", $("#SpawnPokeForm").serialize()); return false;'>
				<input type='hidden' name='Action' value='Create' />
				<table class='standard' style='width: 362px;'>
					<thead>
						<tr>
							<th colspan='4'>Configured Pokemon</th>
						</tr>
					</thead>
					<tbody>
						<tr>
							<td colspan='4' id='Sel_Pokemon' style='height: 100px;'>
								<div style='padding: 38px;'>Please Select A Pokemon.</div>
							</td>
						</tr>
						<tr>
							<td colspan='4' id='Sel_Name'>
								<b>Unknown</b>
							</td>
						</tr>
						<tr>
							<td colspan='2' style='padding: 3px;'>
								<b>Type</b>
							</td>
							<td colspan='2'>
								<select name='Type' id='Type' onchange='UpdateSprite(this);' style='padding: 5px;'>
									<option value='Normal'>Normal</option>
									<option value='Shiny'>Shiny</option>
									<option value='Sunset'>Sunset</option>
								</select>
							</td>
						</tr>
						<tr>
							<td colspan='2' style='padding: 3px;'>
								<b>Level</b>
							</td>
							<td colspan='2'>
								<input type='text' name='Level' value='5' />
							</td>
						</tr>
						<tr>
							<td colspan='2'>
								<input type='button' value='Randomize IVs' onclick='Randomize("IV");' style='border: none; border-radius: 0px; width: 100%;' />
							</td>
							<td colspan='2'>
								<input type='button' value='Randomize EVs' onclick='Randomize("EV");' style='border: none; border-radius: 0px; width: 100%;' />
							</td>
						</tr>
						<tr>
							<td style='padding: 3px; width: 80px;'></td>
							<td style='padding: 3px; width: 80px;'><b>Base</b></td>
							<td style='padding: 3px; width: 80px;'><b>IVs</b></td>
							<td style='padding: 3px; width: 80px;'><b>EVs</b></td>
						</tr>
						<tr>
							<td style='width: 80px;'><b>HP</b></td>
							<td style='width: 80px;'><input type='text' name="Base[Base_HP]" value='0' disabled /></td>
							<td style='width: 80px;'><input type='text' id='IV_HP' name="IV[IV_HP]" maxlength='2' value='0' onkeyup="UpdateStat('IV', this)" /></td>
							<td style='width: 80px;'><input type='text' id='EV_HP' name="EV[EV_HP]" maxlength='3' value='0' onkeyup="UpdateStat('EV', this)" /></td>
						</tr>
						<tr>
							<td style='width: 80px;'><b>Attack</b></td>
							<td style='width: 80px;'><input type='text' name="Base[Base_Att]" value='0' disabled /></td>
							<td style='width: 80px;'><input type='text' id='IV_Att' name="IV[IV_Att]" maxlength='2' value='0' onkeyup="UpdateStat('IV', this)" /></td>
							<td style='width: 80px;'><input type='text' id='EV_Att' name="EV[EV_Att]" maxlength='3' value='0' onkeyup="UpdateStat('EV', this)" /></td>
						</tr>
						<tr>
							<td style='width: 80px;'><b>Defense</b></td>
							<td style='width: 80px;'><input type='text' name="Base[Base_Def]" value='0' disabled /></td>
							<td style='width: 80px;'><input type='text' id='IV_Def' name="IV[IV_Def]" maxlength='2' value='0' onkeyup="UpdateStat('IV', this)" /></td>
							<td style='width: 80px;'><input type='text' id='EV_Def' name="EV[EV_Def]" maxlength='3' value='0' onkeyup="UpdateStat('EV', this)" /></td>
						</tr>
						<tr>
							<td style='width: 80px;'><b>Sp. Att</b></td>
							<td style='width: 80px;'><input type='text' name="Base[Base_SpAtt]" value='0' disabled /></td>
							<td style='width: 80px;'><input type='text' id='IV_SpAtt' name="IV[IV_SpAtt]" maxlength='2' value='0' onkeyup="UpdateStat('IV', this)" /></td>
							<td style='width: 80px;'><input type='text' id='EV_SpAtt' name="EV[EV_SpAtt]" maxlength='3' value='0' onkeyup="UpdateStat('EV', this)" /></td>
						</tr>
						<tr>
							<td style='width: 80px;'><b>Sp. Def</b></td>
							<td style='width: 80px;'><input type='text' name="Base[Base_SpDef]" value='0' disabled /></td>
							<td style='width: 80px;'><input type='text' id='IV_SpDef' name="IV[IV_SpDef]" maxlength='2' value='0' onkeyup="UpdateStat('IV', this)" /></td>
							<td style='width: 80px;'><input type='text' id='EV_SpDef' name="EV[EV_SpDef]" maxlength='3' value='0' onkeyup="UpdateStat('EV', this)" /></td>
						</tr>
						<tr>
							<td style='width: 80px;'><b>Speed</b></td>
							<td style='width: 80px;'><input type='text' name="Base[Base_Speed]" value='0' disabled /></td>
							<td style='width: 80px;'><input type='text' id='IV_Speed' name="IV[IV_Speed]" maxlength='2' value='0' onkeyup="UpdateStat('IV', this)" /></td>
							<td style='width: 80px;'><input type='text' id='EV_Speed' name="EV[EV_Speed]" maxlength='3' value='0' onkeyup="UpdateStat('EV', this)" /></td>
						</tr>
						<tr>
							<td><b>Total</b></td>
							<td><input type='text' id='Total_Base' value='0' disabled /></td>
							<td><input type='text' id='Total_IV' value='0' disabled /></td>
							<td><input type='text' id='Total_EV' value='0' disabled /></td>
						</tr>
						<tr>
							<td colspan='2' style='padding: 3px;'><b>Send To User</b></td>
							<td colspan='2' style='padding: 3px;'><b>Reason</b></td>
						</tr>
						<tr>
							<td colspan='2'><input type='text' name='SendTo' placeholder='User ID' /></td>
							<td colspan='2'><input type='text' name='Location' placeholder='Location' /></td>
						</tr>
						<tr>
							<td colspan='4'>
								<input type='submit' value='Spawn Pokemon' style='border: none; border-radius: 0px; width: 100%;' />
							</td>
						</tr>
					</tbody>
				</table>
			</form>
		</div>
	</div>

</div>

<style>
	#StaffAJAX input,
	#StaffAJAX select
	{ border: none; border-radius: 0px; margin-bottom: 0px; text-align: center; width: 100%; }
</style>

<script type='text/javascript'>
	function UpdateSprite(Type)
	{
		Type = Type.value;
		
		let src = $('#Sel_Pokemon img').attr('src');
		let Type_Current = $('#Sel_Pokemon img').attr('src').split('/')[4];
		let updated = src.replace(Type_Current, Type);

		$('#Sel_Pokemon img').attr('src', updated);
	}

	function SelectPokemon(ID)
	{
		$('#Sel_Pokemon').html("<div style='padding: 33px;'><div class='spinner' style='left: 45%; position: relative;'></div></div>");

		$.ajax({
			type: 'get',
			url: 'ajax/spawn_pokemon.php',
			data: { ID: ID },
			success: function(data)
			{
				UpdateSelected(data);
			},
			error: function(data)
			{
				alert(data);
				alert("An error occurred while attempting to retrieve data on the wanted Pokemon.");
			}
		});
	}

	function UpdateSelected(Data)
	{
		$('#Sel_Pokemon').html(`
			<img src='` + Data['Sprite'] + `' />
			<input type='hidden' name='db_id' value='` + Data['ID'] + `' />
		`);
		$('#Sel_Name').html( "<b>" + Data['Name'] + "</b>" );

		$("input[id='Total_Base']").val( Data['Base_Total'] );
		$("input[name='Base[Base_HP]']").val( Data['Base_HP'] );
		$("input[name='Base[Base_Att]']").val( Data['Base_Attack'] );
		$("input[name='Base[Base_Def]']").val( Data['Base_Defense'] );
		$("input[name='Base[Base_SpAtt]']").val( Data['Base_SpAttack'] );
		$("input[name='Base[Base_SpDef]']").val( Data['Base_SpDefense'] );
		$("input[name='Base[Base_Speed]']").val( Data['Base_Speed'] );

		document.getElementById("Type").options.selectedIndex = 0;
	}

	const EV_Sum = (accumulator, currentValue) => accumulator + currentValue;
	let Total_IV = [0, 0, 0, 0, 0, 0];
	let Total_EV = [0, 0, 0, 0, 0, 0];

	function Randomize(Stat)
	{
		var input_array = [ 'HP', 'Att', 'Def', 'SpAtt', 'SpDef', 'Speed' ];

		if ( Stat == 'IV' )
		{
			Total_IV = [0, 0, 0, 0, 0, 0];

			for ( let iv = 0; iv < 6; iv++ )
			{
				if ( Total_IV[0] + Total_IV[1] + Total_IV[2] + Total_IV[3] + Total_IV[4] + Total_IV[5]  > 186 )
				{
					Total_IV[iv] = 0;
				}
				else
				{
					Total_IV[iv] = Math.floor(Math.random() * 32);
				}
			}

			let inc = 0;
			$.each(input_array, function(Key, Ele)
			{
				$('#' + Stat + '_' + Ele).val( Total_IV[inc] );

				inc++;
			});
		}
		else
		{
			Total_EV = [0, 0, 0, 0, 0, 0];

			for ( let i = 0; i <= 510; i++ )
			{
				let stat = Math.floor(Math.random() * 6);

				if ( Total_EV[stat] >= 252 )
				{
					Total_EV[stat] += 0;
				}
				else
				{
					Total_EV[stat]++;
				}
			}

			//for ( let ev = 0; ev < 6; ev++ )
			//{
			//	if ( Total_EV[0] + Total_EV[1] + Total_EV[2] + Total_EV[3] + Total_EV[4] + Total_EV[5]  > 510 )
			//	{
			//		Total_EV[ev] = 0;
			//	}
			//	else
			//	{
			//		Total_EV[ev] = Math.floor(Math.random() * 252);
			//	}
			//}
			//while ( Total <= 510 )
			//{
			//	let stat = Math.floor(Math.random() * 6) + 1;
			//
			//	Total_EV[stat]++;
			//}

			let inc = 0;
			$.each(input_array, function(Key, Ele)
			{
				$('#' + Stat + '_' + Ele).val( Total_EV[inc] );

				inc++;
			});
		}
		
		$("input[id='Total_IV']").val( FetchArraySum(Total_IV, Stat) );
		$("input[id='Total_EV']").val( FetchArraySum(Total_EV, Stat) );
	}

	function FetchArraySum(Array, Stat)
	{
		let Sum = 0;

		for ( let i = 0; i < Array.length; i++ )
		{
			if ( Number.isNaN(Array[i]) )
			{
				Array[i] = 0;
			}

			if ( Stat == 'IV' && Array[i] > 31 )
			{
				Array[i] = 31;
			}

			if ( Stat == 'EV' && Array[i] > 252 )
			{
				Array[i] = 252;
			}

			Sum = Sum + Array[i];
		}

		if ( Stat == 'IV' )
		{
			if ( Sum > 186 )
			{
				return 186;
			}
		}

		if ( Stat == 'EV' )
		{
			if ( Sum > 510 )
			{
				return 510;
			}
		}

		return Sum;
	}

	function UpdateStat(Stat, Value)
	{
		/**
		 * Handle updating IV's.
		 */
		$("input[id^='IV_']").each(function(Key, Ele)
		{
			Total_IV[Key] = parseInt( $(Ele).val() );

			if ( Total_IV[Key] > 31 )
			{
				Total_IV[Key] = 31;
				$(this).val(Total_IV[Key]);
			}
		});
		$("input[id='Total_IV']").val( FetchArraySum(Total_IV, 'IV') );

		/**
		 * Handle updating EV's.
		 */
		$("input[id^='EV_']").each(function(Key, Ele)
		{
			Total_EV[Key] = parseInt( $(Ele).val() );

			if ( Total_EV[0] + Total_EV[1] + Total_EV[2] + Total_EV[3] + Total_EV[4] + Total_EV[5] > 510 )
			{
				$(this).val('0');
			}

			if ( Total_EV[Key] > 252 )
			{
				Total_EV[Key] = 252;
				$(this).val(Total_EV[Key]);
			}
		});
		$("input[id='Total_EV']").val( FetchArraySum(Total_EV, 'EV') );
	}
</script>