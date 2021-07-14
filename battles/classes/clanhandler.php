<?php
  class ClanHandler
  {
    public $ID = null;
    public $Name = null;

    public $Bonuses = null;

    public function __construct
    (
      int $Clan_ID
    )
    {
      $this->ID = $Clan_ID;
    }

    /**
     * Initialize the user and their respective roster.
     */
    public function Initialize()
    {
      global $Clan_Class;

      $Clan = $Clan_Class->FetchClanData($this->ID);
      if ( !$Clan )
        return false;

      $this->ID = $Clan['ID'];
      $this->Name = $Clan['Name'];
      $this->Bonuses = null;

      $Fetch_Bonuses = $Clan_Class->FetchUpgrades($Clan['ID']);
      if ( $Fetch_Bonuses )
      {
        foreach ( $Fetch_Bonuses as $Key => $Bonus )
        {
          $this->Bonuses[] = [
            'Name' => $Bonus['Name'],
            'Level' => $Bonus['Current_Level'],
          ];
        }
      }

      return $this;
    }
  }
