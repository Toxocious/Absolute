<?php
  require_once $_SERVER['DOCUMENT_ROOT'] . '/core/required/layout_top.php';

  require_once $_SERVER['DOCUMENT_ROOT'] . '/pages/evolution_center/functions/display_pokemon.php';
?>

<div class='panel content'>
	<div class='head'>Evolution Center</div>
	<div class='body padding-5px'>
    <div class='flex row center' id='Evolution_Page_Roster'>
        <div class='loading-element'></div>
    </div>
    <br />

		<table class='border-gradient' id='Evo_Data' style='width: 750px;'>
			<thead>
				<tr>
					<th colspan='7'>
						Evolutions
					</th>
				</tr>
			</thead>

			<tbody>
				<tr>
					<td colspan='7' style='padding: 5px;'>
						Please select the Pok&eacute;mon that you wish to evolve.
					</td>
				</tr>
			</tbody>
		</table>
	</div>
</div>

<script src='<?= DOMAIN_ROOT; ?>/pages/_shared/js/ajax_functions.js'></script>

<script src='<?= DOMAIN_ROOT; ?>/pages/evolution_center/js/ajax_functions.js'></script>

<script>
    (function() {
        UpdateRoster();
    })();
</script>

<?php
	require_once $_SERVER['DOCUMENT_ROOT'] . '/core/required/layout_bottom.php';
