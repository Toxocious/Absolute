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
        'Abso_Coins' => number_format($Clan['Abso_Coins']),
        'Abso_Coins_Raw' => $Clan['Abso_Coins'],
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

    /**
     * Donate a given currency to a clan.
     * @param int $User_ID - ID of the User donating the currency.
     * @param int $Clan_ID - ID of the Clan that the user is donating to.
     * @param string $Currency - Value of the Currency that is being donated.
     * @param int $Quantity - Amount of currency being donated.
     */
    public function DonateCurrency(int $User_ID, int $Clan_ID, string $Currency, int $Quantity)
    {
      global $PDO, $User_Class;

      if ( !$User_ID || !$Clan_ID || !$Currency || !$Quantity )
        return false;

      $Clan_Data = $this->FetchClanData($Clan_ID);

      if ( !$Clan_ID )
        return false;

      $User_Class->RemoveCurrency($User_ID, $Currency, $Quantity);

      try
      {
        $Donate_Currency = $PDO->prepare("INSERT INTO `clan_donations` ( `Clan_ID`, `Donator_ID`, `Currency`, `Quantity`, `Timestamp` ) VALUES ( ?, ?, ?, ?, ? )");
        $Donate_Currency->execute([ $Clan_ID, $User_ID, $Currency, $Quantity, time() ]);

        $Add_Currency = $PDO->prepare("UPDATE `clans` SET `$Currency` = `$Currency` + ? LIMIT 1");
        $Add_Currency->execute([ $Quantity ]);
      }
      catch (PDOException $e)
      {
        HandleError($e);
      }

      return true;
    }
  }
