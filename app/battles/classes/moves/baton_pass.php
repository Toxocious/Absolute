<?php
  class Baton_Pass extends Move
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

      return [
        'Text' => "{$Attacker->Active->Display_Name} used {$this->Name}.",
        'Effect_Text' => (isset($Effect_Text) ? $Effect_Text : ''),
        'Damage' => 0,
        'Healing' => 0,
      ];
    }
  }
