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
?>

<div style='display: flex; flex-wrap: wrap; flex-direction: column; gap: 10px; justify-content: center;'>
  <div style='flex-basis: 100%; width: 100%;'>
    <h3>Transfer Pok&eacute;mon</h3>
  </div>

  <table class='border-gradient' style='width: 300px;'>
    <tbody>
      <tr>
        <td>
          <input
            type='number'
            name='Pokemon_Search'
            placeholder='Pok&eacute;mon ID'
            onkeydown='return event.keyCode !== 69'
          />
        </td>
        <td>
          <button style='width: 130px;' onclick='ShowPokemon("transfer_pokemon");'>Select Pok&eacute;mon</button>
        </td>
      </tr>
    </tbody>
  </table>

  <div id='Modification_AJAX'></div>
  <div id='Modification_Table'></div>
</div>
