<?php
  use BattleHandler\Battle;

  class Rewards extends Battle
  {
    /**
     * Process the necessary rewards for the user.
     */
    public function ProcessRewards()
    {
      $Dialogue = '<br />';

      if ( $this->Earn_Trainer_Exp )
      {
        $Trainer_Exp = $this->CalcTrainerExpYield();
        $_SESSION['Battle']['Ally']->IncreaseTrainerExp($Trainer_Exp);

        $Dialogue .= 'You have gained +<b>' . number_format($Trainer_Exp) . '</b> Trainer Exp.<br />';
      }

      if ( $this->Earn_Clan_Exp )
      {
        $Clan_Exp = $this->CalcClanExpYield();
        $_SESSION['Battle']['Ally']->Clan->IncreaseExp($Clan_Exp);

        $Dialogue .= 'Your clan has gained +<b>' . number_format($Clan_Exp) . '</b> Exp.<br />';
      }

      if ( $this->Earn_Money )
      {
        $Money_Gain = $this->CalcMoneyYield();
        $_SESSION['Battle']['Ally']->IncreaseMoney($Money_Gain);

        $Dialogue .= "
          <div style='display: inline-block; font-weight: bold; margin-top: 5px; width: 50px;'>
            +" . number_format($Money_Gain) . "
            <img src='" . DOMAIN_SPRITES . "/Assets/Money.png' style='vertical-align: middle;' />
          </div>
        ";
      }

      if ( $this->Earn_Abso_Coins )
      {
        $Abso_Coins_Gain = $this->CalcAbsoCoinYield();
        $_SESSION['Battle']['Ally']->IncreaseAbsoCoins($Abso_Coins_Gain);

        $Dialogue .= "
          <div style='display: inline-block; font-weight: bold; margin-top: 5px; width: 50px;'>
            +" . number_format($Abso_Coins_Gain) . "
            <img src='" . DOMAIN_SPRITES . "/Assets/Abso_Coins.png' style='vertical-align: middle;' />
          </div>
        ";
      }

      return [
        'Text' => $Dialogue,
      ];
    }

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
