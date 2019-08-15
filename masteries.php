<?php
	require 'core/required/layout_top.php';
?>

<div class='content'>
	<div class='head'>Pok&eacute;mon Masteries</div>
	<div class='box'>
		<div class='description' style='margin-bottom: 5px;'>
			Mastery Points are obtained from reaching certain milestones. These points are then able to be allocated to perks within the mastery tree, which in turn will grant you buffs across areas of the RPG.<br /><br />

			Total MP &nbsp;&bull;&nbsp; Used MP<br />
			<?= number_format($User_Data['Mastery_Points_Total']); ?> &nbsp;&bull;&nbsp; <?= number_format($User_Data['Mastery_Points_Used']); ?>
		</div>

		<div class='row'>
			<div class='panel' style='float: left; margin-right: 1%; width: 49.5%;'>
				<div class='panel-heading'>Mastery Tree</div>
				<div class='panel-body' style='padding: 10px 0px;'>
					Mastery Tree
				</div>
			</div>

			<div class='panel' style='float: left; margin-bottom: 5px; width: 49.5%;'>
				<div class='panel-heading'>Active Buffs</div>
				<div class='panel-body'>
					<div style='padding: 5px;'>
						Active Buffs
					</div>
				</div>
			</div>

			<div class='panel' style='float: left; width: 49.5%;'>
				<div class='panel-heading'>Mastery Info</div>
				<div class='panel-body'>
					<div style='padding: 5px;'>
						Click on a mastery to see more information.
					</div>
				</div>
			</div>

		</div>
	</div>
</div>

<?php
  require 'core/required/layout_bottom.php';