<?php
	require_once $_SERVER['DOCUMENT_ROOT'] . '/core/required/layout_top.php';

  require_once $_SERVER['DOCUMENT_ROOT'] . '/pages/online_list/functions/online_list.php';
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

<script src='<?= DOMAIN_ROOT; ?>/pages/online_list/js/ajax_functions.js'></script>

<script>
  setInterval(() =>
  {
    RefreshOnlineList();
  }, 2000);
  </script>

<?php
	require_once $_SERVER['DOCUMENT_ROOT'] . '/core/required/layout_bottom.php';
