<?php
  require '../../required/session.php';

  if ( isset($_GET['id']) )
  {
    try
    {
      $Fetch_Profile_Pokemon = $PDO->prepare("SELECT `ID` FROM `pokemon` WHERE `Owner_Current` = ? AND `Location` = 'Roster' ORDER BY `Slot` ASC LIMIT 6");
      $Fetch_Profile_Pokemon->execute([$_GET['id']]);
      $Fetch_Profile_Pokemon->setFetchMode(PDO::FETCH_ASSOC);
      $Fetch_Roster = $Fetch_Profile_Pokemon->fetchAll();
    }
    catch ( PDOException $e )
    {
      echo $e->getMessage();
    }

    echo "
      <div class='panel'>
        <div class='panel-heading'>Roster</div>
        <div class='panel-body'>";

    for ( $i = 0; $i <= 5; $i++ )
    {
      if ( isset($Fetch_Roster[$i]['ID']) )
      {
        $Roster_Slot[$i] = $PokeClass->FetchPokemonData($Fetch_Roster[$i]['ID']);
      }
      else
      {
        $Roster_Slot[$i]['Sprite'] = Domain(3) . 'images/pokemon/0.png';
        $Roster_Slot[$i]['Display_Name'] = 'Empty';
        $Roster_Slot[$i]['Level'] = '0';
        $Roster_Slot[$i]['Experience'] = '0';
      }

      echo "
        <div class='roster_slot'>
          <img src='{$Roster_Slot[$i]['Sprite']}' ?><br />
          <b>{$Roster_Slot[$i]['Display_Name']}</b>
          <div class='info'>
            <div>Level</div>
            <div>{$Roster_Slot[$i]['Level']}</div>
          </div>
          <div class='info'>
            <div>Experience</div>
            <div>{$Roster_Slot[$i]['Experience']}</div>
          </div>
        </div>
      ";
    }

    echo "
        </div>
      </div>
    ";
  }