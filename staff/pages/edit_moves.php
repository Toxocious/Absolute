<?php
  require_once $_SERVER['DOCUMENT_ROOT'] . '/core/required/session.php';
  require_once $_SERVER['DOCUMENT_ROOT'] . '/staff/functions/auth.php';

  if ( !AuthorizeUser() )
  {
    echo "
      <div style='padding: 5px;'>
        You aren't authorized to be here.
      </div>
    ";

    exit;
  }

  require_once $_SERVER['DOCUMENT_ROOT'] . '/staff/functions/edit_moves.php';
?>

<div style='display: flex; flex-direction: column; flex-wrap: wrap; gap: 10px; justify-content: center;'>
  <div style='flex-basis: 100%; width: 100%;'>
    <h3>Edit Moves</h3>
  </div>

  <div class='description'>
    Select a move below to edit its properties.
  </div>

  <div id='Edit_Move_AJAX'></div>

  <table class='border-gradient' style='width: 300px;'>
    <tbody>
      <tr>
        <td>
          <?php
            echo ShowMovesDropdown();
          ?>
        </td>
      </tr>
    </tbody>
  </table>

  <div id='Edit_Move_Table'></div>
</div>
