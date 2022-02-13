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

<div style='display: flex; flex-direction: column; flex-wrap: wrap; gap: 10px; justify-content: center;'>
  <div style='flex-basis: 100%; width: 100%;'>
    <h3>Set Pok&eacute;mon</h3>
  </div>

  <div class='description'>
    All currently obtainable Pok&eacute;mon are found here, and more may be added if desired.
  </div>

  <table class='border-gradient' style='width: 400px;'>
    <thead>
      <tr>
        <th colspan='2'>
          Pok&eacute;mon Locations
        </th>
      </tr>
    </thead>
    <tbody>
      <tr>
        <td colspan='1' style='width: 50%;'>
          <h3>
            <a href='javascript:void(0);' onclick='ShowObtainablePokemonByTable("map_encounters");'>
              Map Encounters
            </a>
          </h3>
        </td>
        <td colspan='1' style='width: 50%;'>
          <h3>
            <a href='javascript:void(0);' onclick='ShowObtainablePokemonByTable("shop_pokemon");'>
              Shop Pok&eacute;mon
            </a>
          </h3>
        </td>
      </tr>
    </tbody>
  </table>

  <div id='Set_Pokemon_AJAX'></div>
  <div style='display: flex; flex-wrap: wrap; gap: 10px;' id='Set_Pokemon_Table'></div>
</div>
