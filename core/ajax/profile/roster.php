<?php
  require '../../required/session.php';

  if ( isset($_GET['id']) )
  {
    try
    {
      $Fetch_Pokemon = $PDO->prepare("SELECT `ID` FROM `pokemon` WHERE `Owner_Current` = ? AND `Location` = 'Roster' ORDER BY `Slot` ASC LIMIT 6");
      $Fetch_Pokemon->execute([$_GET['id']]);
      $Fetch_Pokemon->setFetchMode(PDO::FETCH_ASSOC);
      $Fetch_Roster = $Fetch_Pokemon->fetchAll();
    }
    catch ( PDOException $e )
    {
      HandleError( $e->getMessage() );
    }

    echo "
      <div class='panel'>
        <div class='head'>Roster</div>
        <div class='body'>";

    for ( $i = 0; $i <= 5; $i++ )
    {
      if ( isset($Fetch_Roster[$i]['ID']) )
      {
        $Roster_Slot[$i] = $Poke_Class->FetchPokemonData($Fetch_Roster[$i]['ID']);

        $Popup = "popup cboxElement' href='" . DOMAIN_ROOT . "/core/ajax/pokemon.php?id={$Roster_Slot[$i]['ID']}'";
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

        $Popup = "";
      }

      $Gender = '';
      $Item = '';

      if ( $Roster_Slot[$i]['Item'] != null )
      {
        $Item = "<img src='" . DOMAIN_SPRITES . "{$Roster_Slot[$i]['Item_Icon']}' style='margin-top: 48px;' />";
      }

      if ( $Roster_Slot[$i]['Gender_Icon'] != null )
      {
        $Gender = "<img src='" . DOMAIN_SPRITES . "{$Roster_Slot[$i]['Gender_Icon']}' style='height: 20px; width: 20px;' /><br />";
      }

      echo "
        <div class='roster_slot full' style='padding: 0px;'>
          <div style='float: left; padding-top: 3px; text-align: center; width: 30px;'>
            $Gender
            $Item
          </div>

          <div style='float: left; margin-left: -30px; padding: 3px;'>
            <img class='spricon {$Popup}' src='{$Roster_Slot[$i]['Sprite']}' />
          </div>

          <div class='info_cont' style='float: right; width: 189px;'>
            <div style='font-weight: bold; padding: 2px;'>
              {$Roster_Slot[$i]['Display_Name']}
            </div>
            <div class='info'>Level</div>
            <div>{$Roster_Slot[$i]['Level']}</div>
            <div class='info'>Experience</div>
            <div>" . number_format($Roster_Slot[$i]['Experience']) . "</div>
          </div>
        </div>
      ";
    }

    echo "
        </div>
      </div>
    ";
  }