<?php
  class Gravity extends Move
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

      if ( $this->IsFieldEffectActive('Global', 'Gravity') )
      {
        $Effect_Text = 'But it failed!';
      }
      else
      {
        $Set_Field_Effect = $this->SetFieldEffect('Global', 'Gravity', 5);

        if ( $Set_Field_Effect )
        {
          $Effect_Text = $Set_Field_Effect->Dialogue;
        }
        else
        {
          $Effect_Text = 'But it failed!';
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
