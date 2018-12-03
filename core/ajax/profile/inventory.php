<?php
  require '../../required/db.php';
  require '../../functions/global_functions.php';

  if ( isset($_GET['id']) )
  {
		echo "
			<div class='panel'>
				<div class='panel-heading'>Inventory</div>
				<div class='panel-body'>
					Inventory
				</div>
			</div>
		";
	}

	exit();
?>