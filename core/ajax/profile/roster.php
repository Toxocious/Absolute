<?php
  require_once '../../required/session.php';

  if ( isset($_GET['User_ID']) )
  {
    $User_ID = Purify($_GET['User_ID']);
    $User_Info = $User_Class->FetchUserData($User_ID);

    if ( $User_Info )
    {
      $Fetch_Roster = $User_Class->FetchRoster($User_ID);
      if ( !$Fetch_Roster )
      {
        echo "
          <tr>
            <td>
              This user currently doesn't have any Pok&eacute;mon in their roster.
            </td>
          </tr>
        ";

        return;
      }

      $Roster_Text = "<div class='flex wrap' style='font-size: 12px;'>";
      for ( $i = 0; $i <= 5; $i++ )
      {
        if ( isset($Fetch_Roster[$i]['ID']) )
        {
          $Roster_Slot[$i] = $Poke_Class->FetchPokemonData($Fetch_Roster[$i]['ID']);

          $Popup = "popup' data-src='" . DOMAIN_ROOT . "/core/ajax/pokemon.php?id={$Roster_Slot[$i]['ID']}'";
        }
        else
        {
          $Roster_Slot[$i]['ID'] = null;
          $Roster_Slot[$i]['Sprite'] = DOMAIN_SPRITES . '/Pokemon/Sprites/0.png';
          $Roster_Slot[$i]['Display_Name'] = 'Empty';
          $Roster_Slot[$i]['Level'] = '0';
          $Roster_Slot[$i]['Experience'] = '0';
          $Roster_Slot[$i]['Item'] = null;
          $Roster_Slot[$i]['Gender_Icon'] = null;

          $Popup = '';
        }

        if ( $Roster_Slot[$i]['Item'] != null )
        {
          $Item = "
            <div class='border-gradient' style='position: absolute; right: 0; height: 30px; width: 30px; z-index: 1;'>
              <div style='padding: 0;'>
                <img src='{$Roster_Slot[$i]['Item_Icon']}' />
              </div>
            </div>
          ";
        }
        else
        {
          $Item = '';
        }

        if ( $Roster_Slot[$i]['Gender_Icon'] != null )
        {
          $Gender = "
            <div class='border-gradient' style='position: absolute; left: 0; height: 30px; width: 30px; z-index: 1;'>
              <div>
                <img src='{$Roster_Slot[$i]['Gender_Icon']}' style='height: 20px; width: 20px;' />
              </div>
            </div>
          ";
        }
        else
        {
          $Gender = '';
        }

        $Roster_Text .= "
          <div style='flex-basis: 165px; margin-left: 2px; position: relative;'>
            {$Gender}
            {$Item}

            <div class='border-gradient hover' style='margin-bottom: 5px;'>
              <div>
                <img class='{$Popup}' src='{$Roster_Slot[$i]['Sprite']}' />
              </div>
            </div>

            <div class='border-gradient' style='margin-bottom: 5px;'>
              <div>
                <b>{$Roster_Slot[$i]['Display_Name']}</b>
              </div>
            </div>

            <div class='border-gradient'>
              <div>
                <b>Lv.</b> {$Roster_Slot[$i]['Level']}
              </div>
            </div>
          </div>
        ";

        if ( $i == 2 )
          $Roster_Text .= "<div style='flex-basis: 100%; height: 15px;'></div>";
      }
      $Roster_Text .= '</div>';

      echo $Roster_Text;
    }
    else
    {
      echo "
        <tbody>
          <tr>
            <td>
              An invalid user has been selected.
            </td>
          </tr>
        </tbody>
      ";
    }
  }
  else
  {
    echo "
      <tbody>
        <tr>
          <td>
            An invalid user has been selected.
          </td>
        </tr>
      </tbody>
    ";
  }
