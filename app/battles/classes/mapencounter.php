<?php
  use BattleHandler\Battle;

  class MapEncounter extends Battle
  {
    public $ID = -1;
    public $Username = null;

    public $Side = null;

    public $Active = null;

    public $Roster = null;
    public $Roster_Hash = null;

    public function __construct
    (
      string $Side
    )
    {
      $this->Side = $Side;
    }

    /**
     * Initialize the user and their respective roster.
     */
    public function Initialize()
    {
      $Roster = new Roster();
      $Roster = $Roster->CreateFakeRoster($_SESSION['EvoChroniclesRPG']['Maps']['Encounter'], 'Foe');

      if ( !$Roster )
        return false;

      $this->Username = $_SESSION['EvoChroniclesRPG']['Maps']['Encounter']['Obtained_Text'];
      $this->Active = $Roster[0];
      $this->Roster = $Roster;

      return $this;
    }

    /**
     * Finds and returns the index of the next non-fainted Pokemon in the roster.
     * Returns false if all are fainted.
     */
    public function NextPokemon()
    {
      foreach ($this->Roster as $Key => $Pokemon)
      {
        if ( $Pokemon->HP > 0 )
          return $Key;
      }

      return false;
    }
  }
