<?php
  class Stockpile extends Move
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
      if ( $Stockpiling && $Stockpiling->Stacks == 3 )
      {
        $Effect_Text = 'But it failed!';
      }
      else if ( $Stockpiling && $Stockpiling->Stacks < 3 )
      {
        $Attacker->Statuses['Stockpile']->IncrementStacks();
        $Effect_Text = "{$Attacker->Display_Name} has stockpiled {$Stockpiling->Stacks} stacks!";
      }
      else
      {
        $Attacker->SetStatus('Stockpile');
        $Effect_Text = "{$Attacker->Display_Name} has begun stockpiling!";
      }

      return [
        'Text' => "{$Attacker->Display_Name} used {$this->Name}.",
        'Effect_Text' => (isset($Effect_Text) ? $Effect_Text : ''),
        'Damage' => 0,
        'Healing' => 0,
      ];
    }
  }
