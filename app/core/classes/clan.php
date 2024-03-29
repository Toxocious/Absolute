<?php
  class Clan
  {
    public $PDO;

		public function __construct
    ()
		{
			global $PDO;
			$this->PDO = $PDO;
    }
    
    /**
     * Fetch database information for a Clan, given a Clan ID.
     * @param int $Clan_ID
     */
    public function FetchClanData
    (
      int $Clan_ID
    )
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
    public function FetchMembers
    (
      int $Clan_ID
    )
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
        $Fetch_Members = $PDO->prepare("SELECT `id` FROM `users` WHERE `Clan` = ? ORDER BY `Clan_Rank` ASC, `Clan_Exp` DESC");
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
     * Set a clan member's clan rank.
     * @param int $Clan_ID
     * @param int $User_ID
     * @param int $Clan_Rank
     */
    public function UpdateRank
    (
      int $Clan_ID,
      int $User_ID,
      string $Clan_Rank
    )
    {
      global $PDO, $User_Class;

      if ( !$Clan_ID || !$User_ID || !$Clan_Rank )
        return false;

      if ( !in_array($Clan_Rank, ['Member', 'Moderator', 'Administrator']) )
        return false;

      $Clan_Data = $this->FetchClanData($Clan_ID);
      if ( !$Clan_Data )
        return false;

      $Member_Data = $User_Class->FetchUserData($User_ID);
      if ( $Member_Data['Clan'] != $Clan_Data['ID'] )
        return false;

      try
      {
        $Update_Rank = $PDO->prepare("UPDATE `users` SET `Clan_Rank` = ? WHERE `id` = ? AND `Clan` = ? LIMIT 1");
        $Update_Rank->execute([ $Clan_Rank, $User_ID, $Clan_ID ]);
      }
      catch ( PDOException $e )
      {
        HandleError($e);
      }

      return true;
    }

    /**
     * Kick a member from their clan.
     * @param int $Clan_ID
     * @param int $User_ID
     */
    public function KickMember
    (
      int $Clan_ID,
      int $User_ID
    )
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
    public function UpdateTitle
    (
      int $Clan_ID,
      int $User_ID,
      string $Title
    )
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
    public function UpdateSignature
    (
      int $Clan_ID,
      string $Signature
    )
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
     * Disband a clan.
     * @param int $Clan_ID
     */
    public function DisbandClan
    (
      int $Clan_ID
    )
    {
      global $PDO;

      if ( !$Clan_ID )
        return false;

      $Clan_Members = $this->FetchMembers($Clan_ID);
      if ( !$Clan_Members )
        return false;

      foreach ( $Clan_Members as $Member )
        $this->LeaveClan($Member['id']);

      try
      {
        $Disband_Clan = $PDO->prepare("DELETE FROM `clans` WHERE `ID` = ? LIMIT 1");
        $Disband_Clan->execute([ $Clan_ID ]);

        $Remove_Donations = $PDO->prepare("DELETE FROM `clan_donations` WHERE `Clan_ID` = ?");
        $Remove_Donations->execute([ $Clan_ID ]);
        
        $Remove_Upgrades = $PDO->prepare("DELETE FROM `clan_upgrades_purchased` WHERE `Clan_ID` = ?");
        $Remove_Upgrades->execute([ $Clan_ID ]);
      }
      catch ( PDOException $e )
      {
        HandleError($e);
      }

      return true;
    }

    /**
     * Add a user to the clan.
     * @param int $Clan_ID
     * @param int $User_ID
     */
    public function JoinClan
    (
      int $Clan_ID,
      int $User_ID
    )
    {
      global $PDO, $User_Class;

      if ( !$Clan_ID || !$User_ID )
        return false;

      $Member_Data = $User_Class->FetchUserData($User_ID);
      if ( $Member_Data['Clan'] )
        return false;

      $Clan_Data = $this->FetchClanData($Clan_ID);
      if ( !$Clan_Data )
        return false;

      try
      {
        $Apply_Membership = $PDO->prepare("
          UPDATE `users`
          SET `Clan` = ?
          WHERE `ID` = ?
          LIMIT 1
        ");
        $Apply_Membership->execute([ $Clan_ID, $User_ID ]);
      }
      catch ( PDOException $e )
      {
        HandleError($e);
      }

      $Direct_Message = new DirectMessage();
      $Clan_DM = $Direct_Message->FetchGroup(null, $Clan_ID);
      if ( !$Clan_DM )
        return false;

      try
      {
        $Apply_Participation = $PDO->prepare("
          INSERT INTO `direct_message_groups`
          (`Group_ID`, `Group_Name`, `Clan_ID`, `User_ID`, `Unread_Messages`, `Last_Message`)
          VALUES (?, ?, ?, ?, ?, ?)
        ");
        $Apply_Participation->execute([
          $Clan_DM['Group_ID'],
          "{$Clan_Data['Name']}: Clan Announcement",
          $Clan_ID,
          $User_ID,
          1,
          time()
        ]);
      }
      catch ( PDOException $e )
      {
        HandleError($e);
      }

      $Create_Message = $Direct_Message->CreateMessage(
        $Clan_DM['Group_ID'],
        "{$Member_Data['Username']} has joined {$Clan_Data['Name']}!",
        3,
        $Member_Data['Clan']
      );

      if ( !$Create_Message )
        return false;

      return true;
    }

    /**
     * Remove a user from a clan.
     * @param int $User_ID
     */
    public function LeaveClan
    (
      int $User_ID
    )
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
     * Update the currencies of a given clan.
     * @param int $Clan_ID
     * @param array $Currencies
     */
    public function UpdateCurrencies
    (
      int $Clan_ID,
      array $Currencies
    )
    {
      global $PDO;

      foreach ( $Currencies as $Currency => $Quantity )
      {
        try
        {
          $Update_Currency = $PDO->prepare("
            UPDATE `clans`
            SET `{$Currency}` = ?
            WHERE `ID` = ?
            LIMIT 1
          ");
          $Update_Currency->execute([ $Quantity, $Clan_ID ]);
        }
        catch ( PDOException $e )
        {
          HandleError($e);
        }
      }
    }

    /**
     * Donate a given currency to a clan.
     * @param int $User_ID - ID of the User donating the currency.
     * @param int $Clan_ID - ID of the Clan that the user is donating to.
     * @param string $Currency - Value of the Currency that is being donated.
     * @param int $Quantity - Amount of currency being donated.
     */
    public function DonateCurrency
    (
      int $User_ID,
      int $Clan_ID,
      string $Currency,
      int $Quantity
    )
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
    public function FetchAllClanUpgrades
    ()
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
                'Quantity' => $Upgrade['Clan_Points_Cost'] + $Upgrade_Data['Current_Level'],
              ],
              'Money' => [
                'Name' => 'Money',
                'Quantity' => $Upgrade['Money_Cost'] * ($Upgrade_Data['Current_Level'] + 1),
              ],
              'Abso_Coins' => [
                'Name' => 'Absolute Coins',
                'Quantity' => $Upgrade['Abso_Coins_Cost'] * ($Upgrade_Data['Current_Level'] + 1),
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

    /**
     * Fetch the data of a given clan upgrade.
     * @param int $Upgrade_ID
     */
    public function PurchaseUpgrade
    (
      int $Clan_ID,
      int $Upgrade_ID
    )
    {
      global $PDO;

      if ( !$Clan_ID || !$Upgrade_ID )
        return false;

      $Clan_Data = $this->FetchClanData($Clan_ID);
      if ( !$Clan_Data )
        return false;

      $Upgrade_Data = $this->FetchUpgradeData($Upgrade_ID);
      if ( !$Upgrade_Data )
        return false;

      $Purchased_Upgrade = $this->FetchPurchasedUpgrade($Clan_Data['ID'], $Upgrade_Data['ID']);
      if ( $Purchased_Upgrade )
      {
        $New_Level = $Purchased_Upgrade['Current_Level'] + 1;

        try
        {
          $Purchase_Upgrade = $PDO->prepare("
            UPDATE `clan_upgrades_purchased`
            SET `Current_Level` = ?
            WHERE `Clan_ID` = ? AND `Upgrade_ID` = ?
          ");
          $Purchase_Upgrade->execute([ $New_Level, $Clan_Data['ID'], $Upgrade_Data['ID'] ]);
        }
        catch ( PDOException $e )
        {
          HandleError($e);
        }
      }
      else
      {
        try
        {
          $Purchase_Upgrade = $PDO->prepare("
            INSERT INTO `clan_upgrades_purchased`
            (`Clan_ID`, `Upgrade_ID`)
            VALUES (?, ?)
          ");
          $Purchase_Upgrade->execute([ $Clan_Data['ID'], $Upgrade_Data['ID'] ]);
        }
        catch ( PDOException $e )
        {
          HandleError($e);
        }
      }

      return $this->FetchPurchasedUpgrade($Clan_Data['ID'], $Upgrade_Data['ID']);
    }
  }
