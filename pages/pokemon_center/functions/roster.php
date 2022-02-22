<?php
  /**
   * Return the user's roster as a JSON object.
   */
  function GetRosterJSON()
  {
    global $PDO, $User_Data, $Poke_Class;

    try
    {
      $Get_Roster_Pokemon = $PDO->prepare("
        SELECT `ID`, `Pokedex_ID`, `Name`, `Forme`, `Type`, `Experience`, `Slot`, `Move_1`, `Move_2`, `Move_3`, `Move_4`, `Nickname`
        FROM `pokemon`
        WHERE `Owner_Current` = ? AND `Location` = 'Roster'
        ORDER BY `Slot` ASC
        LIMIT 6
      ");
      $Get_Roster_Pokemon->execute([
        $User_Data['ID']
      ]);
      $Get_Roster_Pokemon->setFetchMode(PDO::FETCH_ASSOC);
      $Roster_Pokemon = $Get_Roster_Pokemon->fetchAll();
    }
    catch ( PDOException $e )
    {
      HandleError($e);
    }

    foreach ( $Roster_Pokemon as $Slot => $Pokemon )
    {
      $Pokemon_Info = $Poke_Class->FetchPokemonData($Pokemon['ID']);
      $Moves = [
        '1' => $Poke_Class->FetchMoveData($Pokemon_Info['Move_1']),
        '2' => $Poke_Class->FetchMoveData($Pokemon_Info['Move_2']),
        '3' => $Poke_Class->FetchMoveData($Pokemon_Info['Move_3']),
        '4' => $Poke_Class->FetchMoveData($Pokemon_Info['Move_4']),
      ];

      $Roster_Pokemon[$Slot]['Move_Data'] = $Moves;
    }

    return $Roster_Pokemon;
  }

  /**
   * Return the user's boxed Pokemon as a string.
   *
   * @param $Page
   */
  function GetBoxedPokemon
  (
    $Page
  )
  {
    global $PDO, $User_Data;

    $Page = (int) Purify($Page);

    $Limit_Start = ($Page - 1) * 48;
    if ( $Limit_Start < 1 )
      $Limit_Start = 1;

    try
    {
      $Get_Boxed_Pokemon = $PDO->prepare("
        SELECT `ID`, `Pokedex_ID`, `Forme`, `Type`
        FROM `pokemon`
        WHERE `Owner_Current` = ? AND `Location` = 'Box'
        ORDER BY `ID` ASC, `Pokedex_ID` ASC, `Alt_ID` ASC
        LIMIT ?,48
      ");
      $Get_Boxed_Pokemon->execute([
        $User_Data['ID'],
        $Limit_Start
      ]);
      $Get_Boxed_Pokemon->setFetchMode(PDO::FETCH_ASSOC);
      $Boxed_Pokemon = $Get_Boxed_Pokemon->fetchAll();
    }
    catch ( PDOException $e )
    {
      HandleError($e);
    }

    $Pagination = Pagination(
      str_replace(
        'SELECT `ID`, `Pokedex_ID`, `Forme`, `Type`',
        'SELECT COUNT(*)',
        'SELECT `ID`, `Pokedex_ID`, `Forme`, `Type` FROM `pokemon` WHERE `Owner_Current` = ? AND `Location` = "Box" ORDER BY `ID` ASC, `Pokedex_ID` ASC, `Alt_ID` ASC'
      ),
      [ $User_Data['ID'] ],
      $User_Data['ID'],
      $Page,
      48,
      3,
      'onclick="GetBoxedPokemon([PAGE]); return false;"',
      true
    );

    return [
      'Pagination' => $Pagination,
      'Boxed_Pokemon' => $Boxed_Pokemon,
      'Page' => $Page
    ];
  }

  /**
   * Return information about the specified Pokemon.
   *
   * @param $Pokemon_ID
   */
  function GetPokemonPreview
  (
    $Pokemon_ID
  )
  {
    global $PDO, $Poke_Class, $User_Data;

    if ( empty($Pokemon_ID) )
    {
      return [
        'Pokemon_Data' => 'Select a valid Pok&eacute;mon to preview.'
      ];
    }

    try
    {
      $Get_Pokemon_Data = $PDO->prepare("
        SELECT `ID`
        FROM `pokemon`
        WHERE `ID` = ? AND `Owner_Current` = ?
        LIMIT 1
      ");
      $Get_Pokemon_Data->execute([
        $Pokemon_ID,
        $User_Data['ID']
      ]);
      $Get_Pokemon_Data->setFetchMode(PDO::FETCH_ASSOC);
      $Pokemon_Data = $Get_Pokemon_Data->fetch();
    }
    catch ( PDOException $e )
    {
      HandleError($e);
    }

    if ( empty($Pokemon_Data) )
    {
      return [
        'Pokemon_Data' => 'This Pok&eacute;mon does not exist.'
      ];
    }

    $Pokemon_Info = $Poke_Class->FetchPokemonData($Pokemon_Data['ID']);
    $Pokemon_Level = number_format(FetchLevel($Pokemon_Info['Experience_Raw'], 'Pokemon'));

    $Item_Icon = '';
    if ( $Pokemon_Info['Item_ID'] != null )
    {
      $Item_Icon = "
        <div class='border-gradient' style='height: 28px; width: 28px;'>
          <div>
            <img src='{$Pokemon_Info['Item_Icon']}' />
          </div>
        </div>
      ";
    }

    $Roster_Slots = '';
    for ( $i = 0; $i <= 5; $i++ )
    {
      if ( isset($User_Data['Roster'][$i]['ID'])  )
      {
        $Roster_Slot[$i] = $Poke_Class->FetchPokemonData($User_Data['Roster'][$i]['ID']);

        $Roster_Slots .= "
          <div class='border-gradient hover' style='height: 32px; width: 42px;'>
            <div style='padding: 2px;'>
              <img src='{$Roster_Slot[$i]['Icon']}' onclick=\"MovePokemon({$Pokemon_Info['ID']}, " . ($i + 1) . ");\" />
            </div>
          </div>
        ";
      }
      else
      {
        $Roster_Slots .= "
          <div class='border-gradient hover' style='height: 32px; width: 42px;'>
            <div style='padding: 2px;'>
              <img src='" . DOMAIN_SPRITES . "/Pokemon/Sprites/0_mini.png' style='height: 30px; width: 40px;' onclick=\"MovePokemon({$Pokemon_Info['ID']}, " . ($i + 1) . ");\" />
            </div>
          </div>
        ";
      }
    }

    return [
        'Pokemon_Data' => "
          <div class='flex' style='flex-basis: 100%; gap: 6px;'>
            <div class='flex' style='align-items: center; flex-basis: 175px; flex-wrap: wrap; justify-content: center;'>
              <div class='flex' style='align-items: center; gap: 10px; justify-content: center;'>
                <div class='border-gradient hover hw-96px padding-0px'>
                  <div>
                    <img class='popup' src='" . $Pokemon_Info['Sprite'] . "' data-src='" . DOMAIN_ROOT . "/core/ajax/pokemon.php?id=" . $Pokemon_Info['ID'] . "' />
                  </div>
                </div>

                <div class='flex' style='flex-basis: 30px; flex-wrap: wrap; gap: 35px 0px;'>
                  <div class='border-gradient hw-30px' style='height: 28px; width: 28px;'>
                    <div>
                      <img src='" . $Pokemon_Info['Gender_Icon'] . "' style='height: 24px; width: 24px;' />
                    </div>
                  </div>

                  {$Item_Icon}
                </div>
              </div>

              <div style='flex-basis: 100%;'>
                <b>Level</b><br />
                " . $Pokemon_Level . "<br />
                <i style='font-size: 12px;'>(" . $Pokemon_Info['Experience'] . " Exp)</i>
              </div>
            </div>

            <div class='flex' style='align-items: center; flex-basis: 120px; flex-wrap: wrap; gap: 10px; justify-content: flex-start;'>
              <b>Add To Roster</b><br />
              " . $Roster_Slots . "
            </div>

            <div style='flex-basis: 40%;'>
              <table class='border-gradient' style='width: 100%;'>
                <thead>
                  <tr>
                    <th style='width: 25%;'>Stat</th>
                    <th style='width: 25%;'>Base</th>
                    <th style='width: 25%;'>IV</th>
                    <th style='width: 25%;'>EV</th>
                  </tr>
                </thead>
                <tbody>
                  <tr>
                    <td style='padding: 3px;'><b>HP</b></td>
                    <td>" . number_format($Pokemon_Info['Stats'][0]) . "</td>
                    <td>" . number_format($Pokemon_Info['IVs'][0]) . "</td>
                    <td>" . number_format($Pokemon_Info['EVs'][0]) . "</td>
                  </tr>
                  <tr>
                    <td style='padding: 3px;'><b>Attack</b></td>
                    <td>" . number_format($Pokemon_Info['Stats'][1]) . "</td>
                    <td>" . number_format($Pokemon_Info['IVs'][1]) . "</td>
                    <td>" . number_format($Pokemon_Info['EVs'][1]) . "</td>
                  </tr>
                  <tr>
                    <td style='padding: 3px;'><b>Defense</b></td>
                    <td>" . number_format($Pokemon_Info['Stats'][2]) . "</td>
                    <td>" . number_format($Pokemon_Info['IVs'][2]) . "</td>
                    <td>" . number_format($Pokemon_Info['EVs'][2]) . "</td>
                  </tr>
                  <tr>
                    <td style='padding: 3px;'><b>Sp. Att</b></td>
                    <td>" . number_format($Pokemon_Info['Stats'][3]) . "</td>
                    <td>" . number_format($Pokemon_Info['IVs'][3]) . "</td>
                    <td>" . number_format($Pokemon_Info['EVs'][3]) . "</td>
                  </tr>
                  <tr>
                    <td style='padding: 3px;'><b>Sp. Def</b></td>
                    <td>" . number_format($Pokemon_Info['Stats'][4]) . "</td>
                    <td>" . number_format($Pokemon_Info['IVs'][4]) . "</td>
                    <td>" . number_format($Pokemon_Info['EVs'][4]) . "</td>
                  </tr>
                  <tr>
                    <td style='padding: 3px;'><b>Speed</b></td>
                    <td>" . number_format($Pokemon_Info['Stats'][5]) . "</td>
                    <td>" . number_format($Pokemon_Info['IVs'][5]) . "</td>
                    <td>" . number_format($Pokemon_Info['EVs'][5]) . "</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        "
      ];
  }
