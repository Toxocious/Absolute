<?php
  class Light_Screen extends Move
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
      string $Side
    )
    {
      switch ( $Side )
      {
        case 'Ally':
          $Attacker = $_SESSION['EvoChroniclesRPG']['Battle']['Ally'];
          break;
        case 'Foe':
          $Attacker = $_SESSION['EvoChroniclesRPG']['Battle']['Foe'];
          break;
      }

      $Turn_Count = 5;
      if ( $Attacker->Active->Item->Name == 'Light Clay' )
        $Turn_Count = 8;

      if ( $Attacker->IsFieldEffectActive('Light Screen') )
      {
        $Effect_Text = 'But it failed!';
      }
      else
      {
        $Set_Field = $Attacker->SetFieldEffect('Light Screen', $Turn_Count);
        if ( $Set_Field )
          $Effect_Text = 'A barrier was placed that reduces damage from Special attacks!';
      }

      return [
        'Text' => "{$Attacker->Active->Display_Name} used {$this->Name}.",
        'Effect_Text' => (isset($Effect_Text) ? $Effect_Text : ''),
        'Damage' => 0,
        'Healing' => 0,
      ];
    }
  }
