<?php
  require_once $_SERVER['DOCUMENT_ROOT'] . '/core/required/session.php';
  require_once $_SERVER['DOCUMENT_ROOT'] . '/core/required/user_session.php';

  /**
   * Display the user's roster.
   */
  function DisplayRoster()
  {
    global $User_Data;

    $Roster_Text = '';

    foreach ( $User_Data['Roster'] as $Roster_Pokemon )
    {
      $Roster_Pokemon = GetPokemonData($Roster_Pokemon['ID']);

      $Roster_Text .= "
        <div style='width: calc(100% / 6);' onclick='DisplayPossibleEvolutions({$Roster_Pokemon['ID']});'>
          <img class='spricon' src='{$Roster_Pokemon['Sprite']}' ?><br />
          <b>{$Roster_Pokemon['Display_Name']}</b><br />
        </div>
      ";
    }

    return $Roster_Text;
  }

  /**
   * Display the evolutions for the selected Pokemon.
   */
  function DisplayEvolutions($Pokemon_ID)
  {
    global $PDO,
           $Time;

    if ( empty($Pokemon_ID) ) {
      return '';
    }

    $Pokemon = GetPokemonData($Pokemon_ID);

    if ( empty($Pokemon) || !$Pokemon )
    {
      return "
        <thead>
          <tr>
            <th colspan='7'>
              Selected Pok&eacute;mon
            </th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <td colspan='7' style='padding: 5px;'>
              The Pok&eacute;mon that you selected does not exist.
            </td>
          </tr>
        </tbody>
      ";

      return;
    }

    /**
     * Fetch and render the evolutions.
     */
    try
    {
      $Fetch_Evolutions = $PDO->prepare("SELECT * FROM `evolution_data` WHERE `poke_id` = ? AND `alt_id` = ?");
      $Fetch_Evolutions->execute([ $Pokemon['Pokedex_ID'], $Pokemon['Alt_ID'] ]);
      $Fetch_Evolutions->setFetchMode(PDO::FETCH_ASSOC);

      $Num_Of_Evos = $Fetch_Evolutions->rowCount();
    }
    catch ( PDOException $e )
    {
      HandleError($e);
    }

    $Time_Of_Day = (date('G') > 7 && date('G') < 19) ? 'Day' : 'Night';

    if ( $Num_Of_Evos === 0 )
    {
      $Evolution_Text = "
        <tr>
          <td colspan='7' style='padding: 5px;'>
            This Pok&eacute;mon may not evolve further.
          </td>
        </tr>
      ";
    }
    else
    {
      $Evolution_Text = '';

      while ( $Evolution = $Fetch_Evolutions->fetch() )
      {
        $Evolution_Data = GetPokedexData($Evolution['to_poke_id'], $Evolution['to_alt_id'], $Pokemon['Type']);

        /**
         * Check to if the Pokemon meets all of the requirements needed to evolve.
         */
        $Error = false;
        if ( $Evolution['level'] && $Evolution['level'] != 0 && ( $Pokemon['Level'] < $Evolution['level']) )
        {
          $Error = true;
        }

        if ( $Evolution['min_happy'] && $Evolution['min_happy'] > $Pokemon['Happiness'] )
        {
          $Error = true;
        }

        if ( $Evolution['item'] && $Evolution['item'] != $Pokemon['Item'] )
        {
          $Error = true;
        }

        if ( $Evolution['held_item'] && $Evolution['held_item'] != $Pokemon['Item'] )
        {
          $Error = true;
        }

        if ( $Evolution['gender'] && ucfirst($Evolution['gender']) != $Pokemon['Gender'] )
        {
          $Error = true;
        }

        if ( $Evolution['time'] && $Evolution['time'] !== $Time )
        {
          $Error = true;
        }

        /**
         * Display the appropriate evolution button.
         */
        if ( $Error )
        {
          $Evolve_Button = "
            <button class='disabled'>
              Requirements Not Met
            </div>
          ";
        }
        else
        {
          $Evolve_Button = "
            <button onclick='EvolvePokemon({$Pokemon['ID']}, {$Evolution_Data['Pokedex_ID']}, {$Evolution_Data['Alt_ID']});'>
              Evolve into {$Evolution_Data['Display_Name']}
            </div>
          ";
        }

        $Evolution_Text .= "
          <tr>
            <td style='width: 150px;'>
              <img src='{$Evolution_Data['Icon']}' />
            </td>
            <td style='width: 100px;'>
              <b>Level</b>
            </td>
            <td style='width: 100px;'>
              <b>Gender</b>
            </td>
            <td style='width: 100px;'>
              <b>Held Item</b>
            </td>
            <td style='width: 100px;'>
              <b>Use Item</b>
            </td>
            <td style='width: 100px;'>
              <b>Time of Day</b>
            </td>
            <td style='width: 100px;'>
              <b>Happiness</b>
            </td>
          </tr>
          <tr>
            <td>
              <b>{$Evolution_Data['Display_Name']}</b>
            </td>
            <td>
              " . ($Evolution['level'] > 0 ? $Evolution['level'] : 'N/A') . "
            </td>
            <td>
              " . ($Evolution['gender'] ? ucfirst($Evolution['gender']) : 'N/A') . "
            </td>
            <td>
              " . ($Evolution['held_item'] ? $Evolution['held_item'] : 'N/A') . "
            </td>
            <td>
              " . ($Evolution['item'] ? $Evolution['item'] : 'N/A') . "
            </td>
            <td>
              " . ($Evolution['time'] ? $Evolution['time'] : 'N/A') . "
            </td>
            <td>
              " . ($Evolution['min_happy'] ? $Evolution['min_happy'] : 'N/A') . "
            </td>
          </tr>
          <tr>
            <td colspan='7'>
              {$Evolve_Button}
            </td>
          </tr>
        ";
      }
    }

    return "
      <thead>
        <tr>
          <th colspan='7'>
            Selected Pok&eacute;mon
          </th>
        </tr>
      </thead>
      <tbody>
        <tr>
          <td colspan='1' style='width: 150px;'>
            <img src='{$Pokemon['Icon']}' />
          </td>
          <td colspan='1' style='width: 100px;'>
            <b>Level</b>
          </td>
          <td colspan='1' style='width: 100px;'>
            <b>Gender</b>
          </td>
          <td colspan='1' style='width: 100px;'>
            <b>Held Item</b>
          </td>
          <td colspan='1' style='width: 100px;'>
            <b>Use Item</b>
          </td>
          <td colspan='1' style='width: 100px;'>
            <b>Time of Day</b>
          </td>
          <td colspan='1' style='width: 100px;'>
            <b>Happiness</b>
          </td>
        </tr>
        <tr>
          <td>
            <b>{$Pokemon['Display_Name']}</b>
          </td>
          <td>
            {$Pokemon['Level']}
          </td>
          <td>
            {$Pokemon['Gender']}
          </td>
          <td>
            " . ($Pokemon['Item'] ? $Pokemon['Item'] : 'No Item') . "
          </td>
          <td>
            N/A
          </td>
          <td>
            {$Time_Of_Day}
          </td>
          <td>
            {$Pokemon['Happiness']}
          </td>
        </tr>
      </tbody>

      <thead>
        <tr>
          <th colspan='7'>
            Available Evolutions
          </th>
        </tr>
      </thead>
      <tbody>
        {$Evolution_Text}
      </tbody>
    ";
  }
