<?php
  class Clan
  {
    public $PDO;

		public function __construct()
		{
			global $PDO;
			$this->PDO = $PDO;
    }
    
    /**
     * Fetch database information for a Clan, given a Clan ID.
     * @param int $Clan_ID
     */
    public function FetchClanData(int $Clan_ID)
    {
      global $PDO;

      if ( !$Clan_ID || $Clan_ID === 0 )
        return false;

      try
      {
        $Fetch_Clan = $PDO->prepare("SELECT * FROM `clans` WHERE `ID` = ? LIMIT 1");
        $Fetch_Clan->execute([ $Clan_ID ]);
        $Fetch_Clan->setFetchMode(PDO::FETCH_ASSOC);
        $Clan = $Fetch_Clan->fetch();
      }
      catch ( PDOException $e )
      {
        HandleError($e);
      }

      if ( !$Clan )
        return false;

      return [
        'ID' => $Clan['ID'],
        'Name' => $Clan['Name'],
        'Experience' => number_format($Clan['Experience']),
        'Experience_Raw' => $Clan['Experience'],
        'Money' => number_format($Clan['Money']),
        'Money_Raw' => $Clan['Money'],
        'Avatar' => ($Clan['Avatar'] ? DOMAIN_SPRITES . "/" . $Clan['Avatar'] : null),
        'Signature' => $Clan['Signature'],
      ];
    }

    /**
     * Remove a user from a clan.
     * @param int $User_ID
     */
    public function LeaveClan(int $User_ID)
    {
      global $PDO;

      if ( !$User_ID || $User_ID < 0 )
        return false;

      try
      {
        $Select_Query = $PDO->prepare("UPDATE `users` SET `Clan` = 0 WHERE `id` = ?");
        $Select_Query->execute([ $User_ID ]);
      }
      catch ( PDOException $e )
      {
        HandleError($e);
      }

      return true;
    }
  }
