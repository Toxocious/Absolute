<?php
  use BattleHandler\Battle;

  class Roster extends Battle
  {
    /**
     * Create a roster from Pokemon existing within the database.
     */
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
        $Pokemon_Handler[$Index] = new PokemonHandler($Pokemon['ID'], $Side, $Pokemon['Slot']);
      }

      return $Pokemon_Handler;
    }

    /**
     * Create a roster from nonexistent Pokemon.
     *
     * @param {array} $Pokemon
     * @param {string} $Side
     */
    public function CreateFakeRoster
    (
      array $Roster_Pokemon,
      string $Side
    )
    {
      if ( empty($Roster_Pokemon) )
        return false;

      if ( count($Roster_Pokemon) === 6 )
      {
        $Pokemon_Handler[0] = new PokemonHandler(
          null,
          $Side,
          1,
          $Roster_Pokemon['Pokedex_Data']['Pokedex_ID'],
          $Roster_Pokemon['Pokedex_Data']['Alt_ID'],
          $Roster_Pokemon['Level'],
          $Roster_Pokemon['Type'],
          $Roster_Pokemon['Gender']
        );
      }
      else
      {
        foreach ( $Roster_Pokemon as $Slot => $Pokemon )
        {
          $Pokemon_Handler[$Slot] = new PokemonHandler(
            null,
            $Side,
            $Slot,
            $Pokemon['Pokedex_ID'],
            $Pokemon['Alt_ID'],
            $Pokemon['Level'],
            $Pokemon['Type'],
            $Pokemon['Gender']
          );
        }
      }


      return $Pokemon_Handler;
    }
  }
