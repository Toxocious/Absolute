<?php
  use BattleHandler\Battle;

  class Rewards extends Battle
  {
    public $Money = 0;
    public $Abso_Coins = 0;

    /**
     * Calculate how much money the user is awarded.
     */
    public function CalcMoneyYield()
    {
      $Money = $this->Money;

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
      $Abso_Coins = $this->Abso_Coins;

      $Abso_Coins += count($_SESSION['Battle']['Foe']->Roster);

      $Clan_Bonus = $_SESSION['Battle']['Ally']->Clan->HasUpgrade(5);
      if ( $Clan_Bonus )
        $Abso_Coins += $Clan_Bonus['Current_Level'];

      return $Abso_Coins;
    }
  }
