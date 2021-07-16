<?php
  use BattleHandler\Battle;

  class Rewards extends Battle
  {

    /**
     * Calculate how much money the user is awarded.
     */
    public function CalcMoneyYield()
    {
      $Money = 0;

      foreach ( $_SESSION['Battle']['Foe']->Roster as $Pokemon )
        $Money += $Pokemon->Level;

      if ( isset($_SESSION['Battle']['Pay_Day']) )
        $Money += $_SESSION['Battle']['Pay_Day'];

      $Clan_Bonus = $_SESSION['Battle']['Ally']->Clan->HasUpgrade(4);
      if ( $Clan_Bonus )
        $Money *= floor(100 / $Clan_Bonus['Current_Level']);

      return $Money;
    }

    /**
     * Calculate how much Abso Coins the user is awarded.
     */
    public function CalcAbsoCoinYield()
    {
      $Abso_Coins = 0;

      $Abso_Coins += count($_SESSION['Battle']['Foe']->Roster);

      $Clan_Bonus = $_SESSION['Battle']['Ally']->Clan->HasUpgrade(5);
      if ( $Clan_Bonus )
        $Abso_Coins += $Clan_Bonus['Current_Level'];

      return $Abso_Coins;
    }

    /**
     * Calculate how much Trainer Exp the user has earned.
     */
    public function CalcTrainerExpYield()
    {
      $Trainer_Exp = 0;

      foreach ( $_SESSION['Battle']['Foe']->Roster as $Pokemon )
        $Trainer_Exp += $Pokemon->Level;

      $Clan_Bonus = $_SESSION['Battle']['Ally']->Clan->HasUpgrade(3);
      if ( $Clan_Bonus )
        $Trainer_Exp += $Clan_Bonus['Current_Level'];

      return $Trainer_Exp;
    }

    /**
     * Calculate how much Clan Exp the user has earned.
     */
    public function CalcClanExpYield()
    {
      $Clan_Exp = 0;

      $Clan_Exp += count($_SESSION['Battle']['Foe']->Roster);

      $Clan_Bonus = $_SESSION['Battle']['Ally']->Clan->HasUpgrade(1);
      if ( $Clan_Bonus )
        $Clan_Exp += $Clan_Bonus['Current_Level'];

      return $Clan_Exp;
    }
  }
