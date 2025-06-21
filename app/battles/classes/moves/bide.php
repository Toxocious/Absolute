<?php
  class Bide extends Move
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
          $Attacker = $_SESSION['EvoChroniclesRPG']['Battle']['Ally']->Active;
          $Defender = $_SESSION['EvoChroniclesRPG']['Battle']['Foe']->Active;
          break;
        case 'Foe':
          $Attacker = $_SESSION['EvoChroniclesRPG']['Battle']['Foe']->Active;
          $Defender = $_SESSION['EvoChroniclesRPG']['Battle']['Ally']->Active;
          break;
      }

      if ( $Attacker->HasStatus('Bide') )
      {
        if ( $Attacker->Statuses['Bide']->Turns_Left > 0 )
        {
          $Attacker->RemoveStatus('Bide');

          $Damage = $Attacker->Bide_Damage * 2;
          $Effect_Text = "It dealt <b>" . number_format($Damage) . "</b> damage to {$Defender->Display_Name}.";
        }
      }
      else
      {
        $Set_Status = $Attacker->SetStatus('Bide');
        if ( $Set_Status )
        {
          $Attacker->Bide_Damage = 0;

          $Effect_Text = "{$Attacker->Display_Name} is getting pumped!";
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
