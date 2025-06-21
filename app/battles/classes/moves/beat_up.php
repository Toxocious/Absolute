<?php
  class Beat_Up extends Move
  {
    public function __construct
    (
      int $Move,
      int $Slot
    )
    {
      parent::__construct($Move, $Slot);
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
          $Attacker = $_SESSION['EvoChroniclesRPG']['Battle']['Ally'];
          $Defender = $_SESSION['EvoChroniclesRPG']['Battle']['Foe'];
          break;
        case 'Foe':
          $Attacker = $_SESSION['EvoChroniclesRPG']['Battle']['Foe'];
          $Defender = $_SESSION['EvoChroniclesRPG']['Battle']['Ally'];
          break;
      }

      $Damage = 0;
      foreach ( $Defender->Roster as $Pokemon )
      {
        if ( $Pokemon->HP <= 0 )
          continue;

        $this->Power = $Pokemon->Stats['Attack']->Current_Value / 10 + 5;
        $Damage += $this->CalculateDamage($Side, $STAB, $Does_Move_Crit, $Move_Effectiveness['Mult']);
      }

      return [
        'Text' => "{$Attacker->Active->Display_Name} used {$this->Name} and dealt <b>" . number_format($Damage) . "</b> damage to {$Defender->Active->Display_Name}." .
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
    public function CalculateDamage
    (
      string $Side,
      int $STAB,
      bool $Crit,
      int $Move_Effectiveness
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
