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
        $_SESSION['EvoChroniclesRPG']['Battle']['Ally']->IncreaseTrainerExp($Trainer_Exp);

        $Dialogue .= 'You have gained +<b>' . number_format($Trainer_Exp) . '</b> Trainer Exp.<br />';

        $Check_Level = FetchLevel($_SESSION['EvoChroniclesRPG']['Battle']['Ally']->Trainer_Exp, 'Trainer');
        if ( $_SESSION['EvoChroniclesRPG']['Battle']['Ally']->Trainer_Level != $Check_Level )
        {
          $_SESSION['EvoChroniclesRPG']['Battle']['Ally']->Trainer_Level = $Check_Level;
          $Dialogue .= "You have reached Trainer Level <b>" . number_format($Check_Level) . "</b>!<br />";
        }
      }

      if ( $this->Earn_Clan_Exp )
      {
        if ( isset($_SESSION['EvoChroniclesRPG']['Battle']['Ally']->Clan) )
        {
          $Clan_Exp = $this->CalcClanExpYield();
          $_SESSION['EvoChroniclesRPG']['Battle']['Ally']->Clan->IncreaseExp($Clan_Exp);
          $_SESSION['EvoChroniclesRPG']['Battle']['Ally']->IncreaseClanExp($Clan_Exp);

          $Dialogue .= 'Your clan has gained +<b>' . number_format($Clan_Exp) . '</b> Exp.<br />';

          $Check_Level = FetchLevel($_SESSION['EvoChroniclesRPG']['Battle']['Ally']->Clan->Exp, 'Clan');
          if ( $_SESSION['EvoChroniclesRPG']['Battle']['Ally']->Clan->Level != $Check_Level )
          {
            $Level_Diff = $Check_Level - $_SESSION['EvoChroniclesRPG']['Battle']['Ally']->Clan->Level;

            $_SESSION['EvoChroniclesRPG']['Battle']['Ally']->Clan->Level = $Check_Level;
            $Dialogue .= "{$_SESSION['EvoChroniclesRPG']['Battle']['Ally']->Clan->Name} has reached Clan Level <b>" . number_format($Check_Level) . "</b>!<br />";

            $_SESSION['EvoChroniclesRPG']['Battle']['Ally']->Clan->IncreaseClanPoints($Level_Diff);
          }
        }
      }

      if ( $this->Earn_Money )
      {
        $Money_Gain = $this->CalcMoneyYield();
        $_SESSION['EvoChroniclesRPG']['Battle']['Ally']->IncreaseMoney($Money_Gain);

        $Dialogue .= "
          <div style='display: inline-block; font-weight: bold; margin-top: 5px; width: 50px;'>
            <img src='" . DOMAIN_SPRITES . "/Assets/Money.png' style='vertical-align: middle;' />
            <br />
            +" . number_format($Money_Gain) . "
          </div>
        ";
      }

      if ( $this->Earn_Abso_Coins )
      {
        $Abso_Coins_Gain = $this->CalcAbsoCoinYield();
        $_SESSION['EvoChroniclesRPG']['Battle']['Ally']->IncreaseAbsoCoins($Abso_Coins_Gain);

        $Dialogue .= "
          <div style='display: inline-block; font-weight: bold; margin-top: 5px; width: 50px;'>
            <img src='" . DOMAIN_SPRITES . "/Assets/Abso_Coins.png' style='vertical-align: middle;' />
            <br />
            +" . number_format($Abso_Coins_Gain) . "
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

      foreach ( $_SESSION['EvoChroniclesRPG']['Battle']['Foe']->Roster as $Pokemon )
        $Money += $Pokemon->Level;

      if ( isset($_SESSION['EvoChroniclesRPG']['Battle']['Pay_Day']) )
        $Money += $_SESSION['EvoChroniclesRPG']['Battle']['Pay_Day'];

      if ( $_SESSION['EvoChroniclesRPG']['Battle']['Ally']->Active->HasItem(['Amulet Coin', 'Luck Incense']) )
        $Money *= 2;

      if ( isset($_SESSION['EvoChroniclesRPG']['Battle']['Ally']->Clan) )
      {
        $Clan_Bonus = $_SESSION['EvoChroniclesRPG']['Battle']['Ally']->Clan->HasUpgrade(4);
        if ( isset($Clan_Bonus) )
          $Money += round($Clan_Bonus['Current_Level'] / 100, 2) * $Money;
      }

      return $Money;
    }

    /**
     * Calculate how much Abso Coins the user is awarded.
     */
    public function CalcAbsoCoinYield()
    {
      $Abso_Coins = 0;

      $Abso_Coins += count($_SESSION['EvoChroniclesRPG']['Battle']['Foe']->Roster);

      if ( isset($_SESSION['EvoChroniclesRPG']['Battle']['Ally']->Clan) )
      {
        $Clan_Bonus = $_SESSION['EvoChroniclesRPG']['Battle']['Ally']->Clan->HasUpgrade(5);
        if ( $Clan_Bonus )
          $Abso_Coins += $Clan_Bonus['Current_Level'];
      }

      return $Abso_Coins;
    }

    /**
     * Calculate how much Trainer Exp the user has earned.
     */
    public function CalcTrainerExpYield()
    {
      $Trainer_Exp = 0;

      foreach ( $_SESSION['EvoChroniclesRPG']['Battle']['Foe']->Roster as $Pokemon )
        $Trainer_Exp += $Pokemon->Level;

      if ( isset($_SESSION['EvoChroniclesRPG']['Battle']['Ally']->Clan) )
      {
        $Clan_Bonus = $_SESSION['EvoChroniclesRPG']['Battle']['Ally']->Clan->HasUpgrade(3);
        if ( $Clan_Bonus )
          $Trainer_Exp += $Clan_Bonus['Current_Level'];
      }

      return $Trainer_Exp;
    }

    /**
     * Calculate how much Clan Exp the user has earned.
     */
    public function CalcClanExpYield()
    {
      if ( !isset($_SESSION['EvoChroniclesRPG']['Battle']['Ally']->Clan) )
        return 0;

      $Clan_Exp = 0;

      $Clan_Exp += count($_SESSION['EvoChroniclesRPG']['Battle']['Foe']->Roster);

      $Clan_Bonus = $_SESSION['EvoChroniclesRPG']['Battle']['Ally']->Clan->HasUpgrade(1);
      if ( $Clan_Bonus )
        $Clan_Exp += $Clan_Bonus['Current_Level'];

      return $Clan_Exp;
    }
  }
