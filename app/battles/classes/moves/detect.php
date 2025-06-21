<?php
  class Detect extends Move
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

      if ( $Attacker->HasStatus('Protect') )
      {
        if ( !isset($Attacker->Statuses['Protect']['Successions']) )
        {
          $Attacker->Statuses['Protect']['Successions'] = 1;
          $Success_Rate = floor(100 / pow(3, $Attacker->Statuses['Protect']['Successions']));
        }
        else
        {
          $Success_Rate = 100;
        }

        if ( $Success_Rate < 1 )
          $Success_Rate = 1;

        if ( mt_rand(1, 100) <= $Success_Rate )
        {
          $Attacker->AddStatus('Protect');
          $Attacker->Statuses['Protect']['Successions']++;

          $Effect_Text = "{$Attacker->Display_Name} protected itself!";
        }
        else
        {
          unset($Attacker->Statuses['Protect']['Successions']);

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
