<?php
  class Swallow extends Move
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

      $Stockpiling = $Attacker->HasStatus('Stockpile');
      if ( $Stockpiling )
      {
        switch ( $Stockpiling->Stacks )
        {
          case 1:
            $Restore_HP = 0.25;
            break;
          case 2:
            $Restore_HP = 0.5;
            break;
          case 3:
            $Restore_HP = 1;
            break;
        }

        $Attacker->IncreaseHP($Attacker->Max_HP * $Restore_HP);
        $Attacker->RemoveStatus('Stockpile');

        $Effect_Text = "{$Attacker->Display_Name} has become healthy!";
      }
      else
      {
        $Effect_Text = 'But it failed!';
      }

      return [
        'Text' => "{$Attacker->Display_Name} used {$this->Name}.",
        'Effect_Text' => (isset($Effect_Text) ? $Effect_Text : ''),
        'Damage' => (isset($Damage) ? $Damage : 0),
        'Healing' => (isset($Healing) ? $Healing : 0),
      ];
    }
  }
