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
   * Primary code below.
   */
  echo "
		<div class='panel'>
			<div class='head'>Roster</div>
			<div class='body'>
  ";
  
	for ( $i = 0; $i <= 5; $i++ )
  {
    if ( isset($Roster[$i]['ID']) )
    {
      $Roster_Slot[$i] = $Poke_Class->FetchPokemonData(Purify($Roster[$i]['ID']));

      $Nickname = $Roster_Slot[$i]['Nickname'] ? "(<i>" . $Roster_Slot[$i]['Nickname'] . "</i>)" : "";

      echo "
        <div class='roster_slot full' style='padding: 5px;'>
          <div style='float: left;'>
            <img class='spricon popup cboxElement' src='{$Roster_Slot[$i]['Sprite']}' href='" . Domain(1) . "/core/ajax/pokemon.php?id={$Roster_Slot[$i]['ID']}' />
          </div>
          <div style='float: left; width: calc(100% - 96px);'>
            <b>{$Roster_Slot[$i]['Display_Name']}</b><br />
            {$Nickname}<br />
          </div>

          <input type='text' name='{$Roster_Slot[$i]['ID']}_nick' style='border-bottom-left-radius: 0px; border-bottom-right-radius: 0px; margin: 0; text-align: center; width: 50%;' />
          <button onclick='Nickname({$Roster_Slot[$i]['ID']});' style='border-top-left-radius: 0px; border-top-right-radius: 0px; border-top: none; width: 50%;'>Update Nickname</button>
        </div>
      ";
    }
    else
    {
      $Roster_Slot[$i]['Sprite'] = Domain(3) . 'images/pokemon/0.png';
      $Roster_Slot[$i]['Icon'] = Domain(3) . 'images/pokemon/0_mini.png';
      $Roster_Slot[$i]['Display_Name'] = 'Empty';
      $Roster_Slot[$i]['Level'] = '0';
      $Roster_Slot[$i]['Experience'] = '0';

      echo "
        <div class='roster_slot full' style='height: 107px; padding-top: 10px;'>
          <div style='float: left;'>
            <img class='spricon' src='{$Roster_Slot[$i]['Sprite']}' />
          </div>
          <div style='float: left; padding-left: 50px; padding-top: 35px;'>
            <b>{$Roster_Slot[$i]['Display_Name']}</b>
          </div>
        </div>
      ";
    }
  }
  
  echo "
			</div>
    </div>
	";
?>