<?php
  require_once $_SERVER['DOCUMENT_ROOT'] . '/core/required/session.php';
?>

<div class='description'>
  Any Pok&eacute;mon that you no longer want to keep are able to be released here.
  <br />
  You will have multiple chances to deny the releasing of your Pok&eacute;mon before they are released.
</div>

<div id='Pokemon_Center_Release_AJAX'></div>

<div id='Release_Page_1'>
  <table class='border-gradient' style='width: 400px;'>
    <tbody>
      <tr>
        <td style='width: 50%;'>
          <h3>
            Total Pok&eacute;mon
          </h3>
        </td>
        <td>
          <h3 id='Total_Releasable_Pokemon'>
            0
          </h3>
        </td>
      </tr>
    </tbody>

    <tbody>
      <tr>
        <td colspan='2'>
          <select
            id='Releasable_Pokemon'
            name='Release[]'
            multiple='multiple'
            onchange='UpdateSelectedPokemonCounter();'
            style='height: 400px; width: 100%;'
          >
          </select>
        </td>
      </tr>
    </tbody>

    <tbody>
      <tr>
        <td style='width: 50%;'>
          <h3>
            Selected Pok&eacute;mon
          </h3>
        </td>
        <td>
          <h3 id='Total_Selected_Pokemon'>
            0
          </h3>
        </td>
      </tr>
    </tbody>

    <tbody>
      <tr>
        <td colspan='2'>
          <button id='Release_Button' disabled>
            Release Pok&eacute;mon
          </button>
        </td>
      </tr>
    </tbody>
  </table>
</div>

<div id='Release_Page_2'></div>
