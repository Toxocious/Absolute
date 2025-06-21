<?php
  class Sandstorm extends Move
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

      if
      (
        !empty($this->Weather) &&
        in_array($this->Weather->Name, ['Strong Winds', 'Heavy Rain', 'Extremely Harsh Sunlight'])
      )
      {
        $Effect_Text = 'But it failed!';
      }
      else
      {
        if ( isset($this->Weather) )
          unset($this->Weather);

        $Turns = 5;
        if ( $Attacker->Item->Name == 'Smooth Rock' )
          $Turns = 8;

        $Set_Weather = new Weather('Sandstorm', $Turns);

        if ( $Set_Weather )
        {
          $this->Weather = $Set_Weather;

          $Effect_Text = $Set_Weather->Dialogue;
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
