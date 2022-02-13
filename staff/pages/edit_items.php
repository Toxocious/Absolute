<?php
	require_once $_SERVER['DOCUMENT_ROOT'] . '/core/required/session.php';
	require_once $_SERVER['DOCUMENT_ROOT'] . '/staff/functions/auth.php';

  if ( !AuthorizeUser() )
  {
    echo "
      <div class='panel content'>
        <div class='head'>Staff Panel</div>
        <div class='body' style='padding: 5px'>
          You aren't authorized to be here.
        </div>
      </div>
    ";

    exit;
  }

	require_once $_SERVER['DOCUMENT_ROOT'] . '/staff/functions/edit_item.php';
?>

<div style='display: flex; flex-direction: column; flex-wrap: wrap; gap: 10px; justify-content: center;'>
  <div style='flex-basis: 100%; width: 100%;'>
    <h3>Edit Items</h3>
  </div>

  <div class='description'>
    Select an item below to edit its item entry.
  </div>

  <div id='Edit_item_AJAX'></div>

  <table class='border-gradient' style='width: 300px;'>
    <tbody>
      <tr>
        <td>
          <?php
            echo ShowitemDropdown();
          ?>
        </td>
      </tr>
    </tbody>
  </table>

  <div id='Edit_item_Table'></div>
</div>
