<?php
  class Disable extends Move
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

      if ( $Defender->Ability->Name == 'Aroma Veil' )
      {
        $Effect_Text = 'But it failed!';
      }
      else
      {
        $Last_Move = $Defender->Moves[$Defender->Last_Move['Slot']];
        if ( $Last_Move->Disabled )
        {
          $Effect_Text = "{$Defender->Display_Name}'s {$Last_Move->Name} is already disabled!";
        }
        else
        {
          $Last_Move->Disable(mt_rand(1, 8));

          $Effect_Text = "{$Defender->Display_Name}'s {$Last_Move->Name} has been disabled!";
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
