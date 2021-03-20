<?php
	require_once 'core/required/layout_top.php';
?>

<div class='panel content'>
	<div class='head'>Pok&eacute;mon Masteries</div>
	<div class='body'>
		<div class='description' style='margin-bottom: 5px;'>
			Mastery Points are obtained from reaching certain milestones. These points are then able to be allocated to perks within the mastery tree, which in turn will grant you buffs across areas of the RPG.
		</div>

		Total MP &nbsp;&bull;&nbsp; Used MP<br />
		<?= number_format($User_Data['Mastery_Points_Total']); ?> &nbsp;&bull;&nbsp; <?= number_format($User_Data['Mastery_Points_Used']); ?>

		<div class='row'>
			<div class='panel' style='float: left; margin-right: 1%; width: 49.5%;'>
				<div class='head'>Mastery Tree</div>
				<div class='body' style='padding: 10px 0px;'>
					<?php
						$Mastery_Class->FetchMasteries();
					?>
				</div>
			</div>

			<div class='panel' style='float: left; margin-bottom: 5px; width: 49.5%;'>
				<div class='head'>Active Buffs</div>
				<div class='body'>
					<div style='padding: 5px;'>
						Active Buffs
					</div>
				</div>
			</div>

			<div class='panel' style='float: left; width: 49.5%;'>
				<div class='head'>Mastery Info</div>
				<div class='body'>
					<div style='padding: 5px;'>
						Click on a mastery to see more information.
					</div>
				</div>
			</div>

		</div>
	</div>
</div>

<?php
  require_once 'core/required/layout_bottom.php';