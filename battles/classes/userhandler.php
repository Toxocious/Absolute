<?php
  class UserHandler
  {
    public $ID = null;
    public $Username = null;

    public $Side = null;

    public $Active = null;

    public $Roster = null;
    public $Roster_Hash = null;

    public $Clan = null;

    public $Money = null;
    public $Abso_Coins = null;

    public function __construct
    (
      int $User_ID,
      string $Side
    )
    {
      $this->ID = $User_ID;
      $this->Side = $Side;
    }

    /**
     * Initialize the user and their respective roster.
     */
    public function Initialize()
    {
      global $User_Class;

      $User = $User_Class->FetchUserData($this->ID);
      if ( !$User )
        return false;

      $Roster = new Roster();
      $Roster = $Roster->CreateRoster($this->ID, $this->Side);

      $this->ID = $User['ID'];
      $this->Username = $User['Username'];
      $this->Trainer_Level = FetchLevel($User['Trainer_Exp_Raw'], 'Trainer');
      $this->Trainer_Exp = $User['Trainer_Exp_Raw'];
      $this->Roster_Hash = $User['Roster_Hash'];
      $this->Active = $Roster[0];
      $this->Roster = $Roster;
      $this->Money = $User['Money'];
      $this->Abso_Coins = $User['Abso_Coins'];

      $Clan = new ClanHandler($User['Clan']);
      $Clan = $Clan->Initialize();
      if ( $Clan )
        $this->Clan = $Clan;

      return $this;
    }

    /**
     * Increase the amount of Trainer Exp the user has.
     * @param int $Trainer_Exp
     */
    public function IncreaseTrainerExp
    (
      int $Trainer_Exp
    )
    {
      global $PDO;

      if ( !isset($Trainer_Exp) )
        return false;

      if ( $Trainer_Exp < 0 )
        return false;

      try
      {
        $PDO->beginTransaction();

        $Update_Trainer_Exp = $PDO->prepare("
          UPDATE `users`
          SET `TrainerExp` = `TrainerExp` + ?
          WHERE `ID` = ?
          LIMIT 1
        ");
        $Update_Trainer_Exp->execute([ $Trainer_Exp, $this->ID ]);

        $PDO->commit();
      }
      catch ( PDOException $e )
      {
        $PDO->rollback();
        HandleError($e);
      }

      $this->Trainer_Exp += $Trainer_Exp;

      return true;
    }

    /**
     * Increases the amount of Money the user has.
     * @param int $Money_Gained
     */
    public function IncreaseMoney
    (
      int $Money_Gained
    )
    {
      global $PDO;

      if ( !isset($Money_Gained) )
        return false;

      if ( $Money_Gained < 0 )
        return false;

      try
      {
        $PDO->beginTransaction();

        $Update_Money = $PDO->prepare("
          UPDATE `user_currency`
          SET `Money` = `Money` + ?
          WHERE `ID` = ?
          LIMIT 1
        ");
        $Update_Money->execute([ $Money_Gained, $this->ID ]);

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
     * Increases the amount of Abso_Coins the user has.
     * @param int $Abso_Coins_Gained
     */
    public function IncreaseAbsoCoins
    (
      int $Abso_Coins_Gained
    )
    {
      global $PDO;

      if ( !isset($Abso_Coins_Gained) )
        return false;

      if ( $Abso_Coins_Gained < 0 )
        return false;

      try
      {
        $PDO->beginTransaction();

        $Update_Abso_Coins = $PDO->prepare("
          UPDATE `user_currency`
          SET `Abso_Coins` = `Abso_Coins` + ?
          WHERE `ID` = ?
          LIMIT 1
        ");
        $Update_Abso_Coins->execute([ $Abso_Coins_Gained, $this->ID ]);

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
     * Increases the amount of Clan_Exp the user has earned.
     * @param int $Clan_Exp_Earned
     */
    public function IncreaseClanExp
    (
      int $Clan_Exp_Earned
    )
    {
      global $PDO;

      if ( !isset($Clan_Exp_Earned) )
        return false;

      if ( $Clan_Exp_Earned < 0 )
        return false;

      try
      {
        $PDO->beginTransaction();

        $Update_Clan_Exp = $PDO->prepare("
          UPDATE `users`
          SET `Clan_Exp` = `Clan_Exp` + ?
          WHERE `ID` = ?
          LIMIT 1
        ");
        $Update_Clan_Exp->execute([ $Clan_Exp_Earned, $this->ID ]);

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

    /**
     * Fetches the user's roster hash.
     * Used to see if the user's roster changes mid battle.
     */
    public function GetRosterHash()
    {
      return $this->Roster_Hash;
    }

    /**
     * Sets the user's roster hash.
     */
    public function SetRosterHash()
    {

    }
  }
