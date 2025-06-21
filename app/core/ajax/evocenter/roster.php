<?php
  require_once '../../required/session.php';

  if ( isset($_SESSION['EvoChroniclesRPG']) )
  {
    for ( $i = 0; $i <= 5; $i++ )
    {
      if ( isset($User_Data['Roster'][$i]['ID']) )
      {
        $Roster_Slot[$i] = GetPokemonData($User_Data['Roster'][$i]['ID']);

        echo "
          <td style='width: calc(100% / 6);' onclick='Display_Evos({$Roster_Slot[$i]['ID']});'>
            <img class='spricon' src='{$Roster_Slot[$i]['Icon']}' ?><br />
            <b>{$Roster_Slot[$i]['Display_Name']}</b><br />
          </td>
        ";
      }
      else
      {
        $Roster_Slot[$i]['Icon'] = DOMAIN_SPRITES . '/Pokemon/Sprites/0_mini.png';
        $Roster_Slot[$i]['Display_Name'] = 'Empty';

        echo "
          <td style='width: calc(100% / 6);'>
            <img class='spricon' src='{$Roster_Slot[$i]['Icon']}' ?><br />
            <b>{$Roster_Slot[$i]['Display_Name']}</b>
          </td>
        ";
      }
    }
  }
  else
  {
    echo "Your Evo-Chronicles RPG login session is invalid.";
  }
