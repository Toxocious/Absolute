<?php
  use BattleHandler\Battle;

  class Ancient_Power extends Battle
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
          $Attacker = $_SESSION['EvoChroniclesRPG']['Battle']['Ally']->Active;
          $Defender = $_SESSION['EvoChroniclesRPG']['Battle']['Foe']->Active;
          break;
        case 'Foe':
          $Attacker = $_SESSION['EvoChroniclesRPG']['Battle']['Foe']->Active;
          $Defender = $_SESSION['EvoChroniclesRPG']['Battle']['Ally']->Active;
          break;
      }


      if ( mt_rand(1, 100) <= 100 )
      {
        $Effect_Text = '';

        foreach (['Attack', 'Defense', 'Sp_Attack', 'Sp_Defense', 'Speed'] as $Index => $Stat)
        {
          if ( $Attacker->Stats[$Stat]->Stage < 6)
          {
            $Stat_Boost = $Stat . '_Boost';

            $Stages = 0;
            if ( $Attacker->Ability == 'Simple' )
              $Stages = $this->$Stat_Boost * 2;
            else
              $Stages = $this->$Stat_Boost;

            $Attacker->Stats[$Stat]->SetValue($Stages);

            $Stat_Name = str_replace('_', 'ecial ', $Stat);
            $Effect_Text .= "{$Attacker->Display_Name}'s {$Stat_Name} rose sharply!";

            if ( $Index != 4 )
              $Effect_Text .= '<br />';
          }
        }
      }

      $Damage = $this->CalcDamage($Side, $STAB, $Does_Move_Crit, $Move_Effectiveness['Mult']);

      return [
        'Text' => "{$Attacker->Display_Name} used {$this->Name} and dealt <b>" . number_format($Damage) . "</b> damage to {$Defender->Display_Name}." .
                  ($Move_Effectiveness['Text'] != '' ? "<br />{$Move_Effectiveness['Text']}" : '') .
                  ($Does_Move_Crit ? '<br />It critically hit!' : ''),
        'Effect_Text' => (isset($Effect_Text) ? $Effect_Text : ''),
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
          $Attacker = $_SESSION['EvoChroniclesRPG']['Battle']['Ally']->Active;
          $Defender = $_SESSION['EvoChroniclesRPG']['Battle']['Foe']->Active;
          break;
        case 'Foe':
          $Attacker = $_SESSION['EvoChroniclesRPG']['Battle']['Foe']->Active;
          $Defender = $_SESSION['EvoChroniclesRPG']['Battle']['Ally']->Active;
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
  }
