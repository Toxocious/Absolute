<?php
  class Conversion_2 extends Move
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

      $Foes_Last_Move_Type = $Defender->Last_Move['Type'];
      switch ( $Foes_Last_Move_Type )
      {
        case 'Normal':
          $Types = ['Ghost', 'Rock', 'Steel'];
          break;
        case 'Fire':
          $Types = ['Fire', 'Water', 'Rock', 'Dragon'];
          break;
        case 'Water':
          $Types = ['Water', 'Grass', 'Dragon'];
          break;
        case 'Electric':
          $Types = ['Electric', 'Grass', 'Ground', 'Dragon'];
          break;
        case 'Grass':
          $Types = ['Fire', 'Grass', 'Poison', 'Flying', 'Bug', 'Dragon', 'Steel'];
          break;
        case 'Ice':
          $Types = ['Fire', 'Water', 'Ice', 'Steel'];
          break;
        case 'Fighting':
          $Types = ['Poison', 'Flying', 'Psychic', 'Bug', 'Ghost', 'Fairy'];
          break;
        case 'Poison':
          $Types = ['Poison', 'Ground', 'Rock', 'Ghost', 'Steel'];
          break;
        case 'Ground':
          $Types = ['Grass', 'Flying', 'Bug'];
          break;
        case 'Flying':
          $Types = ['Electric', 'Rock', 'Steel'];
          break;
        case 'Psychic':
          $Types = ['Psychic', 'Dark', 'Steel'];
          break;
        case 'Bug':
          $Types = ['Fire', 'Fighting', 'Poison', 'Flying', 'Ghost', 'Steel', 'Fairy'];
          break;
        case 'Rock':
          $Types = ['Fighting', 'Ground', 'Steel'];
          break;
        case 'Ghost':
          $Types = ['Normal', 'Dark'];
          break;
        case 'Dragon':
          $Types = ['Steel', 'Fairy'];
          break;
        case 'Dark':
          $Types = ['Poison', 'Dark', 'Fairy'];
          break;
        case 'Steel':
          $Types = ['Fire', 'Water', 'Electric', 'Steel'];
          break;
        case 'Fairy':
          $Types = ['Fire', 'Poison', 'Steel'];
          break;
        default:
          $Types = ['Normal'];
          break;
      }

      $Random_Type = $Types[mt_rand(0, count($Types))];
      $Attacker->Primary_Type = $Random_Type;
      $Effect_Text = "{$Attacker->Display_Name} became {$Random_Type}-type!";

      return [
        'Text' => "{$Attacker->Display_Name} used {$this->Name}.",
        'Effect_Text' => (isset($Effect_Text) ? $Effect_Text : ''),
        'Damage' => 0,
        'Healing' => 0,
      ];
    }
  }
