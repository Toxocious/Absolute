<?php
	require '../core/required/session.php';
	require '../core/functions/staff.php';

	try
	{
		$Query_Pokedex = $PDO->prepare("SELECT * FROM `pokedex` ORDER BY `Pokedex_ID` ASC, `Alt_ID` ASC");
		$Query_Pokedex->execute();
		$Query_Pokedex->setFetchMode(PDO::FETCH_ASSOC);
		$Pokedex = $Query_Pokedex->fetchAll();
	}
	catch ( PDOException $e )
	{
		HandleError( $e->getMessage() );
	}
?>

<div class='head'>Edit Pokedex</div>
<div class='body'>
	<div class='description' style='margin-bottom: 5px;'>
		Click on a Pokemon to edit it's pokedex information.
	</div>

	<div class='row'>
		<div class='panel' style='float: left; margin-right: 5px; width: calc(100% / 2 - 2.5px);'>
			<div class='head'>Pokedex List</div>
			<div class='body' style='height: 563px; overflow: auto; padding-top: 3px;'>
				<?php
					foreach ( $Pokedex as $Key => $Value )
					{
						$Pokemon = $Poke_Class->FetchPokedexData(null, null, "Normal", $Value['id']);

						echo "
							<img class='iconSelect' src='{$Pokemon['Icon']}' onclick='FetchPokemon({$Value['id']});' />
						";
					}
				?>
			</div>
		</div>

		<div class='panel' style='float: left; width: calc(100% / 2 - 2.5px);'>
			<div class='head'>Selected Pokemon</div>
			<div class='body' id='AJAX' style='padding: 5px;'>
					Please select a Pokemon from the Pokedex.
			</div>
		</div>
	</div>
</div>

<script type='text/javascript'>
	function FetchPokemon(ID)
	{
		$.ajax({
			type: 'get',
			url: 'ajax/edit_pokedex.php',
			data: { Pokemon: ID },
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