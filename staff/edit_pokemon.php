<?php
	require '../core/required/session.php';
	require '../core/functions/staff.php';
?>

<div class='head'>Edit Pokemon</div>
<div class='box'>
	<div class='description' style='margin-bottom: 5px;'>
		Enter the Pokemon's database ID, and you'll be able to edit it's properties.
	</div>

	<div class='panel' style='margin: 0 auto; width: 60%;'>
		<div class='panel-heading'>Find A Pokemon</div>
		<div class='panel-body' style='padding: 5px;'>
			<input type='text' placeholder='Pokemon ID' id='Poke_ID' style='text-align: center;' /><br />
			<button onclick='FindPokemon();' style='width: 180px;'>Search Pokemon</button>
		</div>
	</div>

	<div class='panel' style='margin: 5px auto 0px; width: 60%;'>
		<div class='panel-heading'>Selected Pokemon</div>
		<div class='panel-body' id='AJAX' style='padding: 5px;'>
			Please search for a Pokemon.
		</div>
	</div>
</div>

<script type='text/javascript'>
	function FindPokemon()
	{
		let Poke_ID = $('input#Poke_ID').val();

		if ( Poke_ID == '' )
		{
			alert("The input field may not be empty.");
			return;
		}

		$.ajax({
			type: 'get',
			url: 'ajax/edit_pokemon.php',
			data: { Pokemon: Poke_ID },
			success: function(data)
			{
				$('#AJAX').html(data);
			},
			error: function(data)
			{
				$('#AJAX').html(data);
			}
		});
	}
</script>