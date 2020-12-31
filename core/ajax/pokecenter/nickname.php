<?php
  require '../../required/session.php';
  
  /**
   * Update the nickname change.
   */
  if ( isset($_POST['PokeID']) && isset($_POST['Nickname']) )
  {
    $Poke_ID = Purify($_POST['PokeID']);
    $Nickname = Purify($_POST['Nickname']);

    $Pokemon = $Poke_Class->FetchPokemonData($Poke_ID);

    if ( $Nickname != '' && $Nickname != null )
    {
      try
      {
        $Update_Pokemon = $PDO->prepare("UPDATE `pokemon` SET `Nickname` = ? WHERE `ID` = ?");
        $Update_Pokemon->execute([ $Nickname, $Pokemon['ID'] ]);
      }
      catch ( PDOException $e )
      {
        HandleError( $e->getMessage() );
      }

      echo "
        <div class='success' style='margin-bottom: 5px;'>
          <b>{$Pokemon['Display_Name']}</b>'s new nickname is <b>{$Nickname}</b>.
        </div>
      ";
    }
    else
    {
      try
      {
        $Update_Pokemon = $PDO->prepare("UPDATE `pokemon` SET `Nickname` = null WHERE `ID` = ?");
        $Update_Pokemon->execute([ $Pokemon['ID'] ]);
      }
      catch ( PDOException $e )
      {
        HandleError( $e->getMessage() );
      }

      echo "
        <div class='success' style='margin-bottom: 5px;'>
          <b>{$Pokemon['Display_Name']}</b>'s nickname has been removed.
        </div>
      ";
    }
  }

  /**
   * Fetch the user's current roster.
   */
  $User_Roster = '';
  $Roster_Nick_Row = '';
  for ( $i = 0; $i <= 5; $i++ )
  {
    if ( isset($Roster[$i]['ID']) )
    {
      $Roster_Slot[$i] = $Poke_Class->FetchPokemonData(Purify($Roster[$i]['ID']));

      $Nickname = $Roster_Slot[$i]['Nickname'] ? "(<i>" . $Roster_Slot[$i]['Nickname'] . "</i>)" : "";

      $User_Roster .= "
        <td colspan='1' style='width: calc(100% / 6);'>
          <img
            class='spricon popup cboxElement'
            src='{$Roster_Slot[$i]['Sprite']}'
            href='" . DOMAIN_ROOT . "/core/ajax/pokemon.php?id={$Roster_Slot[$i]['ID']}'
          />
          <br />
          <b>{$Roster_Slot[$i]['Display_Name']}</b>
          <br />
          {$Nickname}
        </td>
      ";

      $Roster_Nick_Row .= "
        <td colspan='1' style='width: calc(100% / 6);'>
          <input
            type='text'
            name='{$Roster_Slot[$i]['ID']}_nick'
            style='margin: 0; max-width: 90%; text-align: center; width: 90%;'
          />
          <br />
          <button
            onclick='Nickname({$Roster_Slot[$i]['ID']});'
            style='width: 100%;'
          >
            Update Nickname
          </button>
        </td>
      ";
    }
    else
    {
      $Roster_Slot[$i]['Sprite'] = DOMAIN_SPRITES . '/Pokemon/Sprites/0.png';
      $Roster_Slot[$i]['Display_Name'] = 'Empty';

      $User_Roster .= "
        <td colspan='1'>
          <img
            src='{$Roster_Slot[$i]['Sprite']}'
          />
          <br />
          <b>{$Roster_Slot[$i]['Display_Name']}</b>
        </td>
      ";

      $Roster_Nick_Row .= "
        <td colspan='1'>
          <i>Not Available</i>
        </td>
      ";
    }
  }

  /**
   * Display the user's roster.
   */
  echo "
    <table class='border-gradient' style='flex-basis: 870px;'>
      <thead>
        <tr>
          <th colspan='6'>
            Roster
          </th>
        </tr>
      </thead>

      <tbody>
        <tr>
          {$User_Roster}
        </tr>
        <tr>
          {$Roster_Nick_Row}
        </tr>
      </tbody>
    </table>
	";
?>