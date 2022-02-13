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

	require_once $_SERVER['DOCUMENT_ROOT'] . '/staff/functions/edit_pokedex.php';
?>

<div style='display: flex; flex-direction: column; flex-wrap: wrap; gap: 10px; justify-content: center;'>
  <div style='flex-basis: 100%; width: 100%;'>
    <h3>Edit Pok&eacute;dex</h3>
  </div>

  <div class='description'>
    Select a Pok&eacute;mon below to edit its pokedex entry.
  </div>

  <div id='Edit_Pokedex_AJAX'></div>

  <table class='border-gradient' style='width: 300px;'>
    <tbody>
      <tr>
        <td>
          <?php
            echo ShowPokedexDropdown();
          ?>
        </td>
      </tr>
    </tbody>
  </table>

  <div id='Edit_Pokedex_Table'></div>
</div>
