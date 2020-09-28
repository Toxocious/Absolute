<?php
  require '../../required/session.php';

  if ( isset($_SESSION['abso_user']) )
  {
    for ( $i = 0; $i <= 5; $i++ )
    {
      if ( isset($Roster[$i]['ID']) )
      {
        $Roster_Slot[$i] = $Poke_Class->FetchPokemonData($Roster[$i]['ID']);
  
        echo "
          <td style='width: calc(100% / 6);' onclick='displayEvos({$Roster_Slot[$i]['ID']});'>
            <img class='spricon' src='{$Roster_Slot[$i]['Icon']}' ?><br />
            <b>{$Roster_Slot[$i]['Display_Name']}</b><br />
          </td>
        ";
      }
      else
      {
        $Roster_Slot[$i]['Icon'] = Domain(3) . 'images/pokemon/0_mini.png';
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
    echo "Your Absolute login session is invalid.";
  }