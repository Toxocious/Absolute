<?php
  require '../../required/db.php';
  require '../../functions/global_functions.php';

  if ( isset($_GET['id']) )
  {
		echo "
			<div class='panel'>
				<div class='head'>Inventory</div>
				<div class='body'>
					Inventory
				</div>
			</div>
		";
	}

	exit();
?>