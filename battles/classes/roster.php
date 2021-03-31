<?php
  use BattleHandler\Battle;

  class Roster extends Battle
  {
    public function CreateRoster
    (
      $Side_ID,
      $Side
    )
    {
      global $PDO;

      try
      {
        $Fetch_Roster = $PDO->prepare("
          SELECT `ID`, `Slot`
          FROM `pokemon`
          WHERE `Location` = 'Roster' AND `Slot` <= 6 AND `Owner_Current` = ?
          ORDER BY `Slot` ASC
          LIMIT 6
        ");
        $Fetch_Roster->execute([ $Side_ID ]);
        $Fetch_Roster->setFetchMode(PDO::FETCH_ASSOC);
        $Roster = $Fetch_Roster->fetchAll();
      }
      catch ( PDOException $e )
      {
        HandleError($e);
      }

      if ( !$Roster )
        return false;

      foreach ( $Roster as $Index => $Pokemon )
      {
        $PokemonHandler[$Index] = new PokemonHandler($Pokemon['ID'], $Side, $Pokemon['Slot']);
      }

      return $PokemonHandler;
    }

    public function GetRosterHash($Side_ID)
    {
      return 0;
    }

    public function SetRosterHash($Side_ID)
    {

    }
  }
