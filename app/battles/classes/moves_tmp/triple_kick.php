<?php
  use BattleHandler\Battle;

  class Triple_Kick extends Battle
  {
    public $Name = null;
    public $Accuracy = null;
    public $Power = null;
    public $Priority = null;
    public $Max_PP = null;
    public $Current_PP = null;
    public $Damage_Type = null;
    public $Move_Type = null;

    public $Flinch_Chance = null;
    public $Crit_Chance = null;
    public $Effect_Chance = null;
    public $Effect_Short = null;
    public $Ailment = null;
    public $Ailment_Chance = null;
    public $Drain = null;
    public $Healing = null;
    public $Max_Hits = null;
    public $Max_Turns = null;
    public $Min_Hits = null;
    public $Min_Turns = null;
    public $Stat_Chance = null;

    public $HP_Boost = null;
    public $Attack_Boost = null;
    public $Defense_Boost = null;
    public $Sp_Attack_Boost = null;
    public $Sp_Defense_Boost = null;
    public $Speed_Boost = null;
    public $Accuracy_Boost = null;
    public $Evasion_Boost = null;

    public $Class_Name = null;

    public function __construct
    (
      Move $Move_Data
    )
    {
      $this->Name = $Move_Data->Name;

      $this->Accuracy = $Move_Data->Accuracy;
      $this->Power = $Move_Data->Power;
      $this->Priority = $Move_Data->Priority;
      $this->Max_PP = $Move_Data->Max_PP;
      $this->Current_PP = $Move_Data->Current_PP;
      $this->Damage_Type = $Move_Data->Damage_Type;
      $this->Move_Type = $Move_Data->Move_Type;

      $this->Flinch_Chance = $Move_Data->Flinch_Chance;
      $this->Crit_Chance = $Move_Data->Crit_Chance;
      $this->Effect_Chance = $Move_Data->Effect_Chance;
      $this->Effect_Short = $Move_Data->Effect_Short;
      $this->Ailment = $Move_Data->Ailment;
      $this->Ailment_Chance = $Move_Data->Ailment_Chance;
      $this->Drain = $Move_Data->Drain;
      $this->Healing = $Move_Data->Healing;
      $this->Max_Hits = $Move_Data->Max_Hits;
      $this->Max_Turns = $Move_Data->Max_Turns;
      $this->Min_Hits = $Move_Data->Min_Hits;
      $this->Min_Turns = $Move_Data->Min_Turns;
      $this->Stat_Chance = $Move_Data->Stat_Chance;

      $this->HP_Boost = $Move_Data->HP_Boost;
      $this->Attack_Boost = $Move_Data->Attack_Boost;
      $this->Defense_Boost = $Move_Data->Defense_Boost;
      $this->Sp_Attack_Boost = $Move_Data->Sp_Attack_Boost;
      $this->Sp_Defense_Boost = $Move_Data->Sp_Defense_Boost;
      $this->Speed_Boost = $Move_Data->Speed_Boost;
      $this->Accuracy_Boost = $Move_Data->Accuracy_Boost;
      $this->Evasion_Boost = $Move_Data->Evasion_Boost;

      $this->Class_Name = $Move_Data->Class_Name;
    }

    public function ProcessMove
    (
      string $Side,
      int $STAB,
      bool $Does_Move_Crit,
      array $Move_Effectiveness
    )
    {
      switch ( $Side )
      {
        case 'Ally':
          $Attacker = $_SESSION['Absolute']['Battle']['Ally']->Active;
          $Defender = $_SESSION['Absolute']['Battle']['Foe']->Active;
          break;
        case 'Foe':
          $Attacker = $_SESSION['Absolute']['Battle']['Foe']->Active;
          $Defender = $_SESSION['Absolute']['Battle']['Ally']->Active;
          break;
      }

      if ( in_array($Attacker->Item->Name, ["King's Rock", "Razor Fang"]) )
      {
        if ( isset($_SESSION['Absolute']['Battle'][$this->Turn_ID]['First_Attacker']) )
          $Turn_First_Attacker = $_SESSION['Absolute']['Battle'][$this->Turn_ID]['First_Attacker'];
        else
          $Turn_First_Attacker = $Side;

        if ( $Turn_First_Attacker == $Side )
          if ( mt_rand(1, 100) <= $this->Effect_Chance )
            $Defender->SetStatus('Flinch');
      }

      $Damage = 0;
      for ( $Hits = 0; $Hits < $this->Max_Hits; $Hits++ )
      {
        if ( $this->DoesMoveHit($Side) )
        {
          $this->Power += $Hits * 10;
          $Damage += $this->CalcDamage($Side, $STAB, $Does_Move_Crit, $Move_Effectiveness['Mult']);
        }
      }

      return [
        'Text' => "{$Attacker->Display_Name} used {$this->Name} and dealt <b>" . number_format($Damage) . "</b> damage to {$Defender->Display_Name}." .
                  ($Move_Effectiveness['Text'] != '' ? "<br />{$Move_Effectiveness['Text']}" : '') .
                  ($Does_Move_Crit ? '<br />It critically hit!' : ''),
        'Damage' => $Damage,
        'Healing' => 0,
      ];
    }

    /**
     * Calculates how much damage the move will do.
     */
    public function CalcDamage
    (
      $Side,
      $STAB,
      $Crit,
      $Move_Effectiveness
    )
    {
      if ( !isset($STAB) || !isset($Crit) || !isset($Move_Effectiveness) )
        return -1;

      switch ( $Side )
      {
        case 'Ally':
          $Attacker = $_SESSION['Absolute']['Battle']['Ally']->Active;
          $Defender = $_SESSION['Absolute']['Battle']['Foe']->Active;
          break;
        case 'Foe':
          $Attacker = $_SESSION['Absolute']['Battle']['Foe']->Active;
          $Defender = $_SESSION['Absolute']['Battle']['Ally']->Active;
          break;
      }

      $Crit_Mult = 1;
      if ( $Crit )
        if ( $Attacker->Ability == 'Sniper' )
          $Crit_Mult = 2.25;
        else
          $Crit_Mult = 1.5;

      $Weather_Mult = 1;
      switch ( $this->Weather )
      {
        case 'Rain':
          if ( $this->Move_Type == 'Water' )
            $Weather_Mult = 1.5;
          else if ( $this->Move_Type == 'Fire' )
            $Weather_Mult = 0.5;
          break;

        case 'Harsh Sunlight':
          if ( $this->Move_Type == 'Fire' )
            $Weather_Mult = 1.5;
          else if ( $this->Move_Type == 'Water' )
            $Weather_Mult = 0.5;
          break;
      }

      $Status_Mult = 1;
      if ( $Attacker->Ability == 'Guts' )
        if ( $Attacker->HasStatusFromArray(['Burn', 'Freeze', 'Paralyze', 'Poison', 'Sleep']) )
          $Status_Mult = 1.5;
      else
        if ( $Attacker->HasStatus('Burn') )
          $Status_Mult = 0.5;


      switch ($this->Damage_Type)
      {
        case 'Physical':
          $Damage = floor(((2 * $Attacker->Level / 5 + 2) * $this->Power * $Attacker->Stats['Attack']->Current_Value / $Defender->Stats['Defense']->Current_Value / 50 + 2) * 1 * $Weather_Mult * $Crit_Mult * (mt_rand(185, 200) / 200) * $STAB * $Move_Effectiveness * $Status_Mult * 1);
          break;

        case 'Special':
          $Damage = $Damage = floor(((2 * $Attacker->Level / 5 + 2) * $this->Power * $Attacker->Stats['Sp_Attack']->Current_Value / $Defender->Stats['Sp_Defense']->Current_Value / 50 + 2) * 1 * $Weather_Mult * $Crit_Mult * (mt_rand(185, 200) / 200) * $STAB * $Move_Effectiveness * $Status_Mult * 1);
          break;

        default:
          $Damage = 0;
      }

      if ( $Damage < 0 )
        $Damage = 0;

      return $Damage;
    }

    /**
     * Determine if the move will hit.
     * @param string $Side
     */
    public function DoesMoveHit
    (
      string $Side
    )
    {
      if ( $this->Accuracy === 0 )
        return false;

      if ( $this->Accuracy === 'None' )
        return true;

      switch ( $Side )
      {
        case 'Ally':
          $Attacker = $_SESSION['Absolute']['Battle']['Ally']->Active;
          $Defender = $_SESSION['Absolute']['Battle']['Foe']->Active;
          break;
        case 'Foe':
          $Attacker = $_SESSION['Absolute']['Battle']['Foe']->Active;
          $Defender = $_SESSION['Absolute']['Battle']['Ally']->Active;
          break;
      }

      if ( $this->Effect_Short == 'Causes a one-hit KO.' )
        if ( $Attacker->Level < $Defender->Level )
          return false;
        else if ( $Attacker->Stats['Speed']->Current_Value < $Defender->Stats['Speed']->Current_Value )
          return false;
        else if ( $Attacker->HasStatusFromArray(['No Guard', 'Lock-On']) )
          return true;
        else if ( $Defender->HasStatus('Semi-Invulnerable') )
          return false;
        else if ( $Attacker->Level > $Defender->Level + 70 )
          return true;
        else
        {
          $Level_Diff = 30;
          if ( $this->Name == 'Sheer Cold' )
            if ( $Attacker->Primary_Type != 'Ice' || $Attacker->Secondary_Type != 'Ice' )
              $Level_Diff = 20;

          return mt_rand(1, ($Attacker->Level - $Defender->Level + $Level_Diff)) === 1;
        }

      switch ($this->Name)
      {
        case 'Flying Press':
          if ( $Defender->HasStatus('Minimize') )
            return true;
          break;

        case 'Thunder':
        case 'Hurricane':
          if ( $this->Weather == 'Rain' )
            return true;

          if ( $this->Weather == 'Sunlight' )
            $this->Accuracy = 50;
          break;

        case 'Blizzard':
          if ( $this->Weather == 'Hail' )
            return true;
          break;

        case 'Dream Eater':
          if ( $Defender->HasStatus('Sleep') )
            return true;
          else
            return false;
          break;

        case 'Stomp':
          if ( $Defender->Evasion > 1  && !$Defender->HasStatus('Semi-Invulnerable') )
            return true;
      }

      if ( $Defender->HasStatus('Bounce') )
        if ( !in_array($this->Name, ['Gust', 'Twister', 'Thunder', 'Sky Uppercut']) )
          return false;

      if ( $Defender->HasStatus('Dig') )
        if ( !in_array($this->Name, ['Earthquake', 'Magnitude', 'Fissure']) )
          return false;

      if ( $Defender->HasStatus('Dive') )
        if ( !in_array($this->Name, ['Surf', 'Whirlpool', 'Low Kick']) )
          return false;

      if ( $Defender->HasStatus('Fly') )
        if ( !in_array($this->Name, ['Gust', 'Twister', 'Thunder', 'Sky Uppercut', 'Smack Down']) )
          return false;

      if ( $Defender->HasStatus('Sky Drop') )
        if ( !in_array($this->Name, ['Gust', 'Thunder', 'Twister', 'Sky Uppercut', 'Hurricane', 'Smack Down']) )
          return false;

      $Accuracy_Mod = $Attacker->Stats['Accuracy']->Current_Value / $Defender->Stats['Evasion']->Current_Value;

      if ( mt_rand(1, 100) < $this->Accuracy * $Accuracy_Mod )
        return true;

      return false;
    }
  }
