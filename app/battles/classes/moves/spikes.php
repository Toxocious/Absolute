<?php
  class Spikes extends Move
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
          $Target = 'Foe';
          $Attacker = $_SESSION['EvoChroniclesRPG']['Battle']['Ally'];
          $Defender = $_SESSION['EvoChroniclesRPG']['Battle']['Foe'];
          break;
        case 'Foe':
          $Target = 'Ally';
          $Attacker = $_SESSION['EvoChroniclesRPG']['Battle']['Foe'];
          $Defender = $_SESSION['EvoChroniclesRPG']['Battle']['Ally'];
          break;
      }

      $Spikes_Field = $this->IsFieldEffectActive($Target, 'Spikes');
      if ( $Spikes_Field )
      {
        if ( $Spikes_Field->Stacks < 3 )
        {
          $Spikes_Field->AddStack();

          $Effect_Text = "Spikes were scattered around the enemy's team!";
        }
        else
        {
          $Effect_Text = 'But it failed!';
        }
      }
      else
      {
        $this->SetFieldEffect($Target, 'Spikes');

        $Effect_Text = "Spikes were scattered around the enemy's team!";
      }

      return [
        'Text' => "{$Attacker->Active->Display_Name} used {$this->Name}.",
        'Effect_Text' => (isset($Effect_Text) ? $Effect_Text : ''),
        'Damage' => 0,
        'Healing' => 0,
      ];
    }
  }
