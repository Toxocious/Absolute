<?php
  use BattleHandler\Battle;

  class Sleep_Talk extends Battle
  {
    public $Name = null;
    public $Slot = null;
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
      $this->Slot = $Move_Data->Slot;

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

      if ( !$Attacker->HasStatus('Sleep') )
      {
        $Effect_Text = 'But it failed!';
      }
      else
      {
        $Random_Move = $this->FetchRandomMove($Attacker);

        if ( class_exists($Random_Move->Class_Name) )
        {
          $Move_Class = new $Random_Move->Class_Name($Random_Move);

          if ( isset($Move_Class) )
            $Handle_Move = $Move_Class->ProcessMove($Side, $STAB, $Does_Move_Crit, $Move_Effectiveness);
          else
            $Handle_Move = $Defender->Moves[$Defender->Last_Move['Slot']]->HandleMove($Side, $STAB, $Does_Move_Crit, $Move_Effectiveness);
        }
      }

      return [
        'Text' => "{$Attacker->Display_Name} used {$this->Name}." .
                  (isset($Handle_Move['Text']) && $Handle_Move['Text'] != '' ? "<br />{$Handle_Move['Text']}" : '') .
                  (isset($Handle_Move['Effect_Text']) && $Handle_Move['Effect_Text'] != '' ? "<br />{$Handle_Move['Effect_Text']}" : ''),
        'Effect_Text' => (isset($Effect_Text) ? $Effect_Text : ''),
        'Damage' => (isset($Handle_Move['Damage']) && $Handle_Move['Damage'] > 0 ? $Handle_Move['Damage'] : 0),
        'Heal' => (isset($Handle_Move['Healing']) && $Handle_Move['Healing'] > 0 ? $Handle_Move['Healing'] : 0),
      ];
    }

    /**
     * Fetch a random move of the user.
     */
    public function FetchRandomMove
    (
      object $Attacker
    )
    {
      $Move_Pool = [0, 1, 2, 3];
      unset($Move_Pool[$this->Slot]);

      $Random_Slot = mt_rand(0, count($Move_Pool));

      return $Attacker->Moves[$Random_Slot];
    }
  }
