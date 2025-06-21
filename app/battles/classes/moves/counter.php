<?php
  class Counter extends Move
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

      if ( $Defender->Last_Move['Type'] == 'Physical' )
      {
        $Damage = $Attacker->Last_Damage_Taken * 2;
        $Effect_Text = "The Counter dealt " . number_format($Damage) . " damage to {$Defender->Display_Name}!";
      }
      else
      {
        $Damage = 0;
        $Effect_Text = 'But it failed.';
      }

      return [
        'Text' => "{$Attacker->Display_Name} used {$this->Name}.",
        'Effect_Text' => (isset($Effect_Text) ? $Effect_Text : ''),
        'Damage' => $Damage,
        'Healing' => 0,
      ];
    }
  }
