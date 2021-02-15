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
        'Clan_Points' => number_format($Clan['Clan_Points']),
        'Clan_Points_Raw' => $Clan['Clan_Points'],
        'Avatar' => ($Clan['Avatar'] ? DOMAIN_SPRITES . "/" . $Clan['Avatar'] : null),
        'Signature' => $Clan['Signature'],
      ];
    }

    /**
     * Fetch all given users that are in a clan.
     * @param int $Clan_ID
     */
    public function FetchMembers(int $Clan_ID)
    {
      global $PDO;

      if ( !$Clan_ID || $Clan_ID === 0 )
        return false;

      try
      {
        $Fetch_Clan = $PDO->prepare("SELECT `ID` FROM `clans` WHERE `ID` = ? LIMIT 1");
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

      try
      {
        $Fetch_Members = $PDO->prepare("SELECT `id` FROM `users` WHERE `Clan` = ?");
        $Fetch_Members->execute([ $Clan_ID ]);
        $Fetch_Members->setFetchMode(PDO::FETCH_ASSOC);
        $Members = $Fetch_Members->fetchAll();
      }
      catch ( PDOException $e )
      {
        HandleError($e);
      }

      if ( !$Members )
        return false;

      return $Members;
    }

    /**
     * Kick a member from their clan.
     * @param int $Clan_ID
     * @param int $User_ID
     */
    public function KickMember(int $Clan_ID, int $User_ID)
    {
      global $PDO, $User_Class;

      if ( !$Clan_ID || !$User_ID )
        return false;

      $Clan_Data = $this->FetchClanData($Clan_ID);
      if ( !$Clan_Data )
        return false;

      $Member_Data = $User_Class->FetchUserData($User_ID);
      if ( $Member_Data['Clan'] != $Clan_Data['ID'] )
        return false;

      $Direct_Message = new DirectMessage();
      $Participating_DM_Groups = $Direct_Message->FetchMessageList($Member_Data['ID']);
      foreach ( $Participating_DM_Groups as $DM_Group )
      {
        if ( $DM_Group['Clan_ID'] == $Member_Data['Clan'] )
          $Direct_Message->RemoveUserFromGroup($DM_Group['Group_ID'], $Member_Data['ID']);
      }

      try
      {
        $Kick_Member = $PDO->prepare("UPDATE `users` SET `Clan` = 0 WHERE `id` = ? LIMIT 1");
        $Kick_Member->execute([ $User_ID ]);
      }
      catch ( PDOException $e )
      {
        HandleError($e);
      }

      return true;
    }

    /**
     * Update a member's clan title.
     * @param int $Clan_ID
     * @param int $User_ID
     * @param string $Title
     */
    public function UpdateTitle(int $Clan_ID, int $User_ID, string $Title)
    {
      global $PDO, $User_Class;

      if ( !$Clan_ID || !$User_ID || !$Title )
        return false;

      $Clan_Data = $this->FetchClanData($Clan_ID);
      if ( !$Clan_Data )
        return false;

      $Member_Data = $User_Class->FetchUserData($User_ID);
      if ( $Member_Data['Clan'] != $Clan_Data['ID'] )
        return false;

      try
      {
        $Kick_Member = $PDO->prepare("UPDATE `users` SET `Clan_Title` = ? WHERE `id` = ? LIMIT 1");
        $Kick_Member->execute([ $Title, $User_ID ]);
      }
      catch ( PDOException $e )
      {
        HandleError($e);
      }

      return true;
    }

    /**
     * Update a clan's signature.
     * @param int $Clan_ID
     * @param string $Signature
     */
    public function UpdateSignature(int $Clan_ID, string $Signature)
    {
      global $PDO, $User_Class;

      if ( !$Clan_ID || !$Signature )
        return false;

      $Clan_Data = $this->FetchClanData($Clan_ID);
      if ( !$Clan_Data )
        return false;

      try
      {
        $Kick_Member = $PDO->prepare("UPDATE `clans` SET `Signature` = ? WHERE `id` = ? LIMIT 1");
        $Kick_Member->execute([ $Signature, $Clan_ID ]);
      }
      catch ( PDOException $e )
      {
        HandleError($e);
      }

      return true;
    }

    /**
     * Remove a user from a clan.
     * @param int $User_ID
     */
    public function LeaveClan(int $User_ID)
    {
      global $PDO, $User_Class;

      if ( !$User_ID || $User_ID < 0 )
        return false;

      $Member_Data = $User_Class->FetchUserData($User_ID);
      if ( !$Member_Data['Clan'] )
        return false;

      $Direct_Message = new DirectMessage();
      $Participating_DM_Groups = $Direct_Message->FetchMessageList($Member_Data['ID']);
      foreach ( $Participating_DM_Groups as $DM_Group_K => $DM_Group )
      {
        if ( $DM_Group['Clan_ID'] == $Member_Data['Clan'] )
          $Direct_Message->RemoveUserFromGroup($DM_Group['Group_ID'], $Member_Data['ID']);
      }

      try
      {
        $Select_Query = $PDO->prepare("UPDATE `users` SET `Clan` = 0, `Clan_Exp` = 0, `Clan_Rank` = 'Member', `Clan_Title` = null WHERE `id` = ? LIMIT 1");
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

      if ( !$Clan_Data )
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

    /**
     * Fetch the data of a given clan upgrade.
     * @param int $Upgrade_ID
     */
    public function FetchUpgradeData
    (
      int $Upgrade_ID
    )
    {
      global $PDO;

      if ( !$Upgrade_ID )
        return false;

      try
      {
        $Fetch_Upgrade = $PDO->prepare("SELECT * FROM `clan_upgrades_data` WHERE `ID` = ? LIMIT 1");
        $Fetch_Upgrade->execute([ $Upgrade_ID ]);
        $Fetch_Upgrade->setFetchMode(PDO::FETCH_ASSOC);
        $Upgrade_Data = $Fetch_Upgrade->fetch();
      }
      catch ( PDOException $e )
      {
        HandleError($e);
      }

      if ( !$Upgrade_Data )
        return false;

      return $Upgrade_Data;
    }

    /**
     * Fetch all possible clan upgrades.
     */
    public function FetchAllClanUpgrades()
    {
      global $PDO;

      try
      {
        $Fetch_Upgrades = $PDO->prepare("SELECT * FROM `clan_upgrades_data`");
        $Fetch_Upgrades->execute([ ]);
        $Fetch_Upgrades->setFetchMode(PDO::FETCH_ASSOC);
        $Upgrades = $Fetch_Upgrades->fetchAll();
      }
      catch ( PDOException $e )
      {
        HandleError($e);
      }

      if ( !$Upgrades )
        return false;

      return $Upgrades;
    }

    /**
     * Fetch all upgrades that are available to a given clan.
     * @param int $Clan_ID
     */
    public function FetchUpgrades
    (
      int $Clan_ID
    )
    {
      if ( !$Clan_ID )
        return false;

      $Upgrades = $this->FetchAllClanUpgrades();
      if ( !$Upgrades )
        return false;

      foreach ( $Upgrades as $Key => $Upgrade )
      {
        $Upgrade['ID'] = intval($Upgrade['ID']);
        $Upgrade_Data = $this->FetchPurchasedUpgrade($Clan_ID, $Upgrade['ID']);

        if ( !$Upgrade_Data )
        {
          $Upgrades[$Key] = [
            'Purchase_ID' => -1,
            'Clan_ID' => $Clan_ID,
            'ID' => $Upgrade['ID'],
            'Name' => $Upgrade['Name'],
            'Description' => $Upgrade['Description'],
            'Current_Level' => 0,
            'Suffix' => $Upgrade['Suffix'],
            'Cost' => [
              'Clan_Points' => [
                'Name' => 'Clan Points',
                'Quantity' => $Upgrade['Clan_Point_Cost'],
              ],
              'Money' => [
                'Name' => 'Money',
                'Quantity' => $Upgrade['Money_Cost'],
              ],
              'Abso_Coin' => [
                'Name' => 'Absolute Coins',
                'Quantity' => $Upgrade['Abso_Coin_Cost'],
              ],
            ],
          ];
        }
        else
        {
          $Upgrades[$Key] = [
            'Purchase_ID' => $Upgrade_Data['ID'],
            'Clan_ID' => $Upgrade_Data['Clan_ID'],
            'ID' => $Upgrade_Data['ID'],
            'Name' => $Upgrade['Name'],
            'Description' => $Upgrade['Description'],
            'Current_Level' => $Upgrade_Data['Current_Level'],
            'Suffix' => $Upgrade['Suffix'],
            'Cost' => [
              'Clan_Points' => [
                'Name' => 'Clan Points',
                'Quantity' => $Upgrade['Clan_Point_Cost'] + $Upgrade_Data['Current_Level'],
              ],
              'Money' => [
                'Name' => 'Money',
                'Quantity' => $Upgrade['Money_Cost'] * ($Upgrade_Data['Current_Level'] + 1),
              ],
              'Abso_Coin' => [
                'Name' => 'Absolute Coins',
                'Quantity' => $Upgrade['Abso_Coin_Cost'] * ($Upgrade_Data['Current_Level'] + 1),
              ],
            ],
          ];
        }
      }

      return $Upgrades;
    }

    /**
     * Fetch the current upgrade level of a given boost, given a Clan ID and Upgrade ID.
     * @param int $Clan_ID
     * @param int $Upgrade_ID
     */
    public function FetchPurchasedUpgrade
    (
      int $Clan_ID,
      int $Upgrade_ID
    )
    {
      global $PDO;

      if ( !$Clan_ID || !$Upgrade_ID )
        return false;
      
      try
      {
        $Fetch_Upgrade = $PDO->prepare("SELECT * FROM `clan_upgrades_purchased` WHERE `Clan_ID` = ? AND `Upgrade_ID` = ? LIMIT 1");
        $Fetch_Upgrade->execute([ $Clan_ID, $Upgrade_ID ]);
        $Fetch_Upgrade->setFetchMode(PDO::FETCH_ASSOC);
        $Upgrade = $Fetch_Upgrade->fetch();
      }
      catch ( PDOException $e )
      {
        HandleError($e);
      }

      if ( !$Upgrade )
        return false;

      return $Upgrade;
    }
  }
