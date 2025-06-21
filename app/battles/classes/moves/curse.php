<?php
  class Curse extends Move
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

      $Effect_Text = '';

      if ( $Attacker->HasTyping(['Ghost']) )
      {
        if ( $Defender->HasStatus('Crafty Shield') )
        {
          $Effect_Text .= 'But it failed!';
        }
        else
        {
          $Attacker->DecreaseHP($Attacker->Max_HP / 2);
          $Defender->SetStatus('Curse');

          $Effect_Text .= "{$Attacker->Display_Name} put a curse on {$Defender->Display_Name}!";
        }
      }
      else
      {
        foreach( ['Attack', 'Defense', 'Speed'] as $Index => $Stat )
        {
          $Stat_Boost = $Stat . '_Boost';
          $Stages = $this->$Stat_Boost;

          switch ($Stat)
          {
            case 'Speed':
              if ( $Attacker->Stats[$Stat]->Stage <= -6 )
              {
                $Effect_Text .= "{$Attacker->Display_Name}'s {$Stat} can't go any lower!";
              }
              else
              {
                $Attacker->Stats[$Stat]->SetValue($Stages);
                $Effect_Text .= "{$Attacker->Display_Name}'s {$Stat} has fallen!";
              }
              break;

            default:
              if ( $Attacker->Stats[$Stat]->Stage >= 6 )
              {
                $Effect_Text .= "{$Attacker->Display_Name}'s {$Stat} can't go any lower!";
              }
              else
              {
                if ( $Attacker->Ability->Name == 'Simple' )
                  $Stages *= 2;

                $Attacker->Stats[$Stat]->SetValue($Stages);
                $Effect_Text .= "{$Attacker->Display_Name}'s {$Stat} rose sharply!";
              }
              break;
          }

          if ( $Index < 2 )
            $Effect_Text .= '<br />';
        }
      }

      return [
        'Text' => "{$Attacker->Display_Name} used {$this->Name}.",
        'Effect_Text' => (isset($Effect_Text) ? $Effect_Text : ''),
        'Damage' => (isset($Damage) ? $Damage : ''),
        'Healing' => 0,
      ];
    }
  }
