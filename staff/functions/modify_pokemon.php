<?php
  /**
   * Display a table that allows modification of a given Pokemon.
   *
   * @param $Pokemon_ID
   */
  function ShowPokemonModTable
  (
    $Pokemon_ID
  )
  {
    global $Poke_Class;

    $Pokemon_Info = $Poke_Class->FetchPokemonData($Pokemon_ID);

    $Frozen_Status = false;
    $Frozen_Text = '';
    if ( $Pokemon_Info['Frozen'] )
    {
      $Frozen_Status = true;
      $Frozen_Text = '<div><i>This Pok&eacute;mon is <b>frozen</b> and bound to its owner\'s account.</i></div>';
    }

    return "
      <input type='hidden' name='Pokemon_ID_To_Update' value='{$Pokemon_ID}}' />
      <input type='hidden' name='Pokemon_Freeze_Status' value='{$Frozen_Status}' />

      <table class='border-gradient' style='width: 500px;'>
        <thead>
          <tr>
            <th colspan='4'>
              Modifying Pok&eacute;mon #{$Pokemon_Info['ID']}
            </th>
          </tr>
        </thead>

        <tbody>
          <tr>
            <td colspan='4' style='width: 100%;'>
              <img src='{$Pokemon_Info['Sprite']}' />
              <br />
              <b>{$Pokemon_Info['Display_Name']}</b>
              {$Frozen_Text}
            </td>
          </tr>

          <tr>
            <td colspan='2' style='width: 50%;'>
              <h3>Level</h3>
            </td>
            <td colspan='2' style='width: 50%;'>
              <input type='text' name='Level' value='{$Pokemon_Info['Level_Raw']}' />
            </td>
          </tr>

          <tr>
            <td colspan='2' style='width: 50%;'>
              <h3>Gender</h3>
            </td>
            <td colspan='2' style='width: 50%;'>
              <select name='Gender' style='padding: 4px; text-align: center; width: 180px;'>
                <option value='Ungendered'>(?)</option>
                <option value='Genderless'>Genderless</option>
                <option value='Female'>Female</option>
                <option value='Male'>Male</option>
              </select>
            </td>
          </tr>

          <tr>
            <td colspan='2' style='width: 50%;'>
              <h3>Nature</h3>
            </td>
            <td colspan='2' style='width: 50%;'>
              <select name='Nature' style='padding: 4px; text-align: center; width: 180px;'>
                <option value='Ungendered'>(?)</option>
                <option value='Genderless'>Genderless</option>
                <option value='Female'>Female</option>
                <option value='Male'>Male</option>
              </select>
            </td>
          </tr>

          <tr>
            <td colspan='2' style='width: 50%;'>
              <h3>Ability</h3>
            </td>
            <td colspan='2' style='width: 50%;'>
              <select name='Ability' style='padding: 4px; text-align: center; width: 180px;'>
                <option value='Ungendered'>(?)</option>
                <option value='Genderless'>Genderless</option>
                <option value='Female'>Female</option>
                <option value='Male'>Male</option>
              </select>
            </td>
          </tr>
        </tbody>

        <tbody>
          <tr>
            <td colspan='4' style='width: 50%;'>
              <h3>Moves</h3>
            </td>
          </tr>
          <tr>
            <td colspan='2' style='width: 50%;'>
              <select name='Move_1' style='padding: 4px; text-align: center; width: 180px;'>
                <option value='Ungendered'>(?)</option>
                <option value='Genderless'>Genderless</option>
                <option value='Female'>Female</option>
                <option value='Male'>Male</option>
              </select>
            </td>
            <td colspan='2' style='width: 50%;'>
              <select name='Move_2' style='padding: 4px; text-align: center; width: 180px;'>
                <option value='Ungendered'>(?)</option>
                <option value='Genderless'>Genderless</option>
                <option value='Female'>Female</option>
                <option value='Male'>Male</option>
              </select>
            </td>
          </tr>
          <tr>
            <td colspan='2' style='width: 50%;'>
              <select name='Move_3' style='padding: 4px; text-align: center; width: 180px;'>
                <option value='Ungendered'>(?)</option>
                <option value='Genderless'>Genderless</option>
                <option value='Female'>Female</option>
                <option value='Male'>Male</option>
              </select>
            </td>
            <td colspan='2' style='width: 50%;'>
              <select name='Move_4' style='padding: 4px; text-align: center; width: 180px;'>
                <option value='Ungendered'>(?)</option>
                <option value='Genderless'>Genderless</option>
                <option value='Female'>Female</option>
                <option value='Male'>Male</option>
              </select>
            </td>
          </tr>
        </tbody>

        <tbody>
          <tr>
            <td colspan='4' style='padding: 10px; width: 100%;'>
              <button>
                Update Pok&eacute;mon
              </button>
            </td>
          </tr>
        </tbody>
      </table>

      <br />

      <table style='width: 600px;'>
        <tbody>
          <tr>
            <td colspan='1' style='padding: 10px; width: 50%;'>
              <button>
                Delete Pok&eacute;mon
              </button>

              <br /><br />

              <i>
                This effectively releases the Pok&eacute;mon from the owner's account.
              </i>
            </td>

            <td colspan='1' style='padding: 10px; width: 50%;'>
              <button onclick='TogglePokemonFreeze();'>
                Freeze Pok&eacute;mon
              </button>

              <br /><br />

              <i>
                This prevents the Pok&eacute;mon from leaving the owner's account.
              </i>
            </td>
          </tr>
        </tbody>
      </table>
    ";
  }
