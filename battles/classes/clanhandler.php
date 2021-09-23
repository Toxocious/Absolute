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
      $this->Level = FetchLevel($Clan['Experience_Raw'], 'Clan');
      $this->Exp = $Clan['Experience_Raw'];
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

    /**
     * Increase the clan's exp.
     * @param int $Clan_Exp
     */
    public function IncreaseExp
    (
      int $Clan_Exp
    )
    {
      global $PDO;

      if ( !isset($Clan_Exp) )
        return false;

      if ( $Clan_Exp < 0 )
        return false;

      try
      {
        $PDO->beginTransaction();

        $Update_Clan_Exp = $PDO->prepare("
          UPDATE `clans`
          SET `Experience` = `Experience` + ?
          WHERE `ID` = ?
          LIMIT 1
        ");
        $Update_Clan_Exp->execute([ $Clan_Exp, $this->ID ]);

        $PDO->commit();
      }
      catch ( PDOException $e )
      {
        $PDO->rollback();
        HandleError($e);
      }

      $this->Exp += $Clan_Exp;

      return true;
    }

    /**
     * Increase the clan's Clan Points.
     * @param int $Clan_Points
     */
    public function IncreaseClanPoints
    (
      int $Clan_Points
    )
    {
      global $PDO;

      try
      {
        $PDO->beginTransaction();

        $Update_Clan_Exp = $PDO->prepare("
          UPDATE `clans`
          SET `Clan_Points` = `Clan_Points` + ?
          WHERE `ID` = ?
          LIMIT 1
        ");
        $Update_Clan_Exp->execute([ $Clan_Points, $this->ID ]);

        $PDO->commit();
      }
      catch ( PDOException $e )
      {
        $PDO->rollback();
        HandleError($e);
      }

      return true;
    }

    /**
     * Determine if the user has a given clan upgrade.
     * @param int $Upgrade_ID
     */
    public function HasUpgrade
    (
      int $Upgrade_ID
    )
    {
      global $Clan_Class;

      if ( !isset($Upgrade_ID) )
        return false;

      $Upgrade_Check = $Clan_Class->FetchPurchasedUpgrade($this->ID, $Upgrade_ID);
      if ( !$Upgrade_Check )
        return false;

      return $Upgrade_Check;
    }
  }
