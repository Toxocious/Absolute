<?php
  use BattleHandler\Battle;

  class Charm extends Battle
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
          $Attacker = $_SESSION['Absolute']['Battle']['Ally'];
          $Defender = $_SESSION['Absolute']['Battle']['Foe'];
          break;
        case 'Foe':
          $Attacker = $_SESSION['Absolute']['Battle']['Foe'];
          $Defender = $_SESSION['Absolute']['Battle']['Ally'];
          break;
      }

      if ( $Defender->IsFieldEffectActive('Mist') )
      {
        $Effect_Text = 'But it failed.';
      }
      else
      {
        if ( $Defender->Active->Stats['Attack']->Stage <= -6 )
        {
          $Effect_Text = "{$Defender->Active->Display_Name}'s Attack can't go any lower!";
        }
        else
        {
          $Stages = $this->Attack_Boost;

          $Defender->Active->Stats['Attack']->SetValue($Stages);

          $Effect_Text = "{$Defender->Active->Display_Name}'s Attack has fallen!";
        }
      }

      return [
        'Text' => "{$Attacker->Display_Name} used {$this->Name}.",
        'Effect_Text' => (isset($Effect_Text) ? $Effect_Text : ''),
        'Damage' => 0,
        'Healing' => 0,
      ];
    }
  }
