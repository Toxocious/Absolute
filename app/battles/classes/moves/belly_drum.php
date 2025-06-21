<?php
  class Belly_Drum extends Move
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
          $Attacker = $_SESSION['EvoChroniclesRPG']['Battle']['Ally']->Active;
          $Defender = $_SESSION['EvoChroniclesRPG']['Battle']['Foe']->Active;
          break;
        case 'Foe':
          $Attacker = $_SESSION['EvoChroniclesRPG']['Battle']['Foe']->Active;
          $Defender = $_SESSION['EvoChroniclesRPG']['Battle']['Ally']->Active;
          break;
      }

      if
      (
        $Attacker->HP < $Attacker->Max_HP / 2 ||
        $Attacker->Stats['Attack']->Stage >= 6
      )
      {
        $Effect_Text = 'But it failed!';
      }
      else
      {
        if ( $Attacker->Ability == 'Contrary' )
        {
          if ( $Attacker->Stats['Attack']->Stage <= 6 )
          {
            $Effect_Text = "{$Attacker->Display_Name}'s Attack can't go any lower!";
          }
          else
          {
            $Attacker->DecreaseHP($Attacker->Max_HP / 2);
            $Stages = $this->Attack_Boost;
            $Attacker->Stats['Attack']->SetValue($Stages);

            $Effect_Text = "{$Attacker->Display_Name}'s Attack was minimized!";
          }
        }
        else
        {
          if ( $Attacker->Stats['Attack']->Stage >= 6 )
          {
            $Effect_Text = "{$Attacker->Display_Name}'s Attack can't go any higher!";
          }
          else
          {
            $Attacker->DecreaseHP($Attacker->Max_HP / 2);
            $Stages = $this->Attack_Boost;
            $Attacker->Stats['Attack']->SetValue($Stages);

            $Effect_Text = "{$Attacker->Display_Name}'s Attack was maximized!";
          }
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
