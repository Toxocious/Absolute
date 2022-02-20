<?php
  require_once $_SERVER['DOCUMENT_ROOT'] . '/core/required/session.php';

  $Category = 'Pokemon';
  if ( isset($_GET['Category']) && in_array($_GET['Category'], ['Pokemon', 'Items']) )
  {
    $Category = Purify($_GET['Category']);
  }

  $Sub_Category = 1;
  if ( isset($_GET['Sub_Category']) )
  {
    $Sub_Category = Purify($_GET['Sub_Category']);
  }

  $Sprite_Dialogue = '';

  switch ( $Category )
  {
    case 'Pokemon':
      switch ( $Sub_Category )
      {
        case 1:
          $Range = [
            'Start' => 0,
            'End' => 151
          ];
          break;

        case 2:
          $Range = [
            'Start' => 152,
            'End' => 251
          ];
          break;

        case 3:
          $Range = [
            'Start' => 252,
            'End' => 386
          ];
          break;

        case 4:
          $Range = [
            'Start' => 387,
            'End' => 493
          ];
          break;

        case 5:
          $Range = [
            'Start' => 494,
            'End' => 649
          ];
          break;

        case 6:
          $Range = [
            'Start' => 650,
            'End' => 721
          ];
          break;

        case 7:
          $Range = [
            'Start' => 722,
            'End' => 999
          ];
          break;

        default:
          $Range = [
            'Start' => 0,
            'End' => 151
          ];
          break;
      }

      $Sub_Category = "Gen {$Sub_Category}";

      try
      {
        $Fetch_Pokedex = $PDO->prepare("
          SELECT * FROM `pokedex`
          WHERE `Pokedex_ID` >= ? AND `Pokedex_ID` <= ? AND `Forme` != '(Totem)'
          ORDER BY `Pokedex_ID` ASC, `Alt_ID` ASC
        ");
        $Fetch_Pokedex->execute([ $Range['Start'], $Range['End'] ]);
        $Fetch_Pokedex->setFetchMode(PDO::FETCH_ASSOC);
        $Pokedex = $Fetch_Pokedex->fetchAll();
      }
      catch ( PDOException $e )
      {
        HandleError($e);
      }

      $Sprite_Dialogue .= "
        <tr>
          <td>
            <b>Species & Icons</b>
          </td>
          <td>
            <b>Normal Sprite</b>
          </td>
          <td>
            <b>Shiny Sprite</b>
          </td>
        </tr>
      ";

      foreach ( $Pokedex as $Index => $Poke_Data )
      {
        $Pokemon = $Poke_Class->FetchPokedexData($Poke_Data['Pokedex_ID'], $Poke_Data['Alt_ID']);

        $Normal_Sprites = $Poke_Class->FetchImages($Poke_Data['Pokedex_ID'], $Poke_Data['Alt_ID']);
        $Shiny_Sprites = $Poke_Class->FetchImages($Poke_Data['Pokedex_ID'], $Poke_Data['Alt_ID'], 'Shiny');

        $Sprite_Dialogue .= "
          <tr>
            <td>
              <b>
                {$Pokemon['Name']} {$Pokemon['Forme']}
              </b>
              <br />

              <img src='{$Normal_Sprites['Icon']}' />
              <img src='{$Shiny_Sprites['Icon']}' />
            </td>

            <td style='text-align: center;'>
              <img src='{$Normal_Sprites['Sprite']}' />
            </td>

            <td style='text-align: center;'>
              <img src='{$Shiny_Sprites['Sprite']}' />
            </td>
          </tr>
        ";
      }
      break;

    case 'Items':
      try
      {
        $Fetch_Itemdex = $PDO->prepare("
          SELECT * FROM `item_dex`
          WHERE `Item_Type` = ?
          ORDER BY `Item_Name` ASC
        ");
        $Fetch_Itemdex->execute([ $Sub_Category ]);
        $Fetch_Itemdex->setFetchMode(PDO::FETCH_ASSOC);
        $Itemdex = $Fetch_Itemdex->fetchAll();
      }
      catch ( PDOException $e )
      {
        HandleError($e);
      }

      $Sprite_Dialogue .= "
        <tr>
          <td colspan='2' style='width: 50%;'>
            <b>Name</b>
          </td>
          <td style='width: 50%;'>
            <b>Icon</b>
          </td>
        </tr>
      ";

      foreach ( $Itemdex as $Index => $Item_Data )
      {
        $Item_Data = $Item_Class->FetchItemData($Item_Data['Item_ID']);

        $Sprite_Dialogue .= "
          <tr>
            <td colspan='2'>
              <b>{$Item_Data['Name']}</b>
            </td>

            <td style='text-align: center;'>
              <img src='{$Item_Data['Icon']}' />
            </td>
          </tr>
        ";
      }
      break;

    default:
      $Sprite_Dialogue = "
        <tr>
          <td colspan='3' style='padding: 10px;'>
            The selected category does not exist.
          </td>
        </tr>
      ";
  }

  echo "
    <table class='border-gradient' style='margin-top: 10px; width: 400px;'>
      <thead>
        <tr>
          <th colspan='3'>
            <b>{$Category} &mdash; {$Sub_Category}</b>
          </th>
        </tr>
      </thead>
      <tbody>
        {$Sprite_Dialogue}
      </tbody>
    </table>
  ";
