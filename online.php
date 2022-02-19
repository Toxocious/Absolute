<?php
	require_once 'core/required/layout_top.php';
  require_once 'core/ajax/online/online.php';
?>

<div class='panel content'>
	<div class='head'>Online List</div>
	<div class='body' style='padding: 5px;'>
		<div class='description'>
			All trainers that have been online in the past fifteen minutes are displayed below.
		</div>

		<div class='row' style='display: flex; flex-direction: row; flex-wrap: wrap; justify-content: center;' id='Online_List_Container'>
			<?php
        echo GetOnlineUsersTable();
      ?>
		</div>
	</div>
</div>

<?php
	require_once 'core/required/layout_bottom.php';
