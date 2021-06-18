<?php
  use BattleHandler\Battle;

  spl_autoload_register(function($Class)
  {
    $Moves_Directory = dirname(__DIR__, 1);
    $Class = strtolower($Class);

    if (file_exists($Moves_Directory . "\\classes\\moves\\{$Class}.php"))
      require_once $Moves_Directory . "\\classes\\moves\\{$Class}.php";
  });

  class Move extends Battle
  {
    public $ID = null;
    public $Name = null;
    public $Slot = null;

    public $Accuracy = null;
    public $Power = null;
    public $Priority = null;
    public $Max_PP = null;
    public $Current_PP = null;
    public $Damage_Type = null;
    public $Move_Type = null;

    public $Flinch_Chance = null;
    public $Crit_Chance = null;
    public $Effect_Chance = null;
    public $Effect_Short = null;
    public $Ailment = null;
    public $Ailment_Chance = null;
    public $Drain = null;
    public $Healing = null;
    public $Max_Hits = null;
    public $Max_Turns = null;
    public $Min_Hits = null;
    public $Min_Turns = null;
    public $Stat_Chance = null;

    public $HP_Boost = null;
    public $Attack_Boost = null;
    public $Defense_Boost = null;
    public $SpAttack_Boost = null;
    public $SpDefense_Boost = null;
    public $Speed_Boost = null;
    public $Accuracy_Boost = null;
    public $Evasion_Boost = null;

    public $Class_Name = null;
    public $Success = null;

    public function __construct
    (
      $Move,
      $Slot
    )
    {
      global $PDO;

      try
      {
        $Fetch_Move = $PDO->prepare("
          SELECT *
          FROM `moves`
          WHERE `ID` = ?
          LIMIT 1
        ");
        $Fetch_Move->execute([ $Move ]);
        $Fetch_Move->setFetchMode(PDO::FETCH_ASSOC);
        $Move_Data = $Fetch_Move->fetch();
      }
      catch ( PDOException $e )
      {
        HandleError($e);
      }

      if ( !$Move_Data )
      {
        $this->Name = 'Invalid Move';
        $this->Disabled = true;

        return $this;
      }

      $this->ID = $Move_Data['ID'];
      $this->Name = $Move_Data['Name'];
      $this->Slot = $Slot;

      $this->Accuracy = $Move_Data['Accuracy'];
      $this->Power = $Move_Data['Power'];
      $this->Priority = $Move_Data['Priority'];
      $this->Max_PP = $Move_Data['PP'];
      $this->Current_PP = $Move_Data['PP'];
      $this->Damage_Type = $Move_Data['Damage_Type'];
      $this->Move_Type = $Move_Data['Move_Type'];

      $this->Flinch_Chance = $Move_Data['Flinch_Chance'];
      $this->Crit_Chance = $Move_Data['Crit_Chance'];
      $this->Effect_Chance = $Move_Data['Effect_Chance'];
      $this->Effect_Short = $Move_Data['Effect_Short'];
      $this->Ailment = $Move_Data['Ailment'];
      $this->Ailment_Chance = $Move_Data['Ailment_Chance'];
      $this->Drain = $Move_Data['Drain'];
      $this->Healing = $Move_Data['Healing'];
      $this->Max_Hits = $Move_Data['Max_Hits'];
      $this->Max_Turns = $Move_Data['Max_Turns'];
      $this->Min_Hits = $Move_Data['Min_Hits'];
      $this->Min_Turns = $Move_Data['Min_Turns'];
      $this->Stat_Chance = $Move_Data['Stat_Chance'];

      $this->HP_Boost = $Move_Data['HP_Boost'];
      $this->Attack_Boost = $Move_Data['Attack_Boost'];
      $this->Defense_Boost = $Move_Data['Defense_Boost'];
      $this->SpAttack_Boost = $Move_Data['SpAttack_Boost'];
      $this->SpDefense_Boost = $Move_Data['SpDefense_Boost'];
      $this->Speed_Boost = $Move_Data['Speed_Boost'];
      $this->Accuracy_Boost = $Move_Data['Accuracy_Boost'];
      $this->Evasion_Boost = $Move_Data['Evasion_Boost'];

      $this->Class_Name = $Move_Data['Class_Name'];
    }

    /**
     * Begin processing an attack.
     */
    public function ProcessAttack
    (
      string $Side
    )
    {
      switch ( $Side )
      {
        case 'Ally':
          $Attacker = $_SESSION['Battle']['Ally']['Active'];
          $Defender = $_SESSION['Battle']['Foe']['Active'];
          break;
        case 'Foe':
          $Attacker = $_SESSION['Battle']['Foe']['Active'];
          $Defender = $_SESSION['Battle']['Ally']['Active'];
          break;
      }

      $Attacker_Can_Move = $this->CanUserMove($Side);
      if ( $Attacker_Can_Move['Type'] == 'Error' )
      {
        return [
          'Type' => $Attacker_Can_Move['Type'],
          'Text' => $Attacker_Can_Move['Text'],
          'Damage' => 0,
          'Heal' => 0,
        ];
      }

      $Does_Move_Hit = $this->DoesMoveHit($Side);
      if ( !$Does_Move_Hit )
      {
        return [
          'Type' => 'Success',
          'Text' => "{$Attacker->Display_Name} used {$this->Name}, but it missed!",
          'Damage' => 0,
          'Heal' => 0,
        ];
      }

      $Attacker->Last_Move = $this->Slot;

      $Move_Effectiveness = $this->MoveEffectiveness($Defender);
      if ( $Move_Effectiveness['Mult'] > 0 )
        $Does_Move_Crit = $this->DoesMoveCrit($Side);
      else
        $Does_Move_Crit = false;

      $STAB = $this->CalculateSTAB($Side);

      if ( !class_exists($this->Class_Name) )
      {
        $Handle_Move = $this->HandleMove($Side, $STAB, $Does_Move_Crit, $Move_Effectiveness['Mult']);
      }
      else
      {
        $Move_Class = new $this->Class_Name($this);
        $Handle_Move = $Move_Class->ProcessMove($Side, $STAB, $Does_Move_Crit, $Move_Effectiveness['Mult']);
      }

      return [
        'Type' => 'Success',
        'Text' =>
          ($Attacker_Can_Move['Type'] == 'Success' ? "{$Attacker_Can_Move['Text']}" : '') .
          ($Attacker->HasStatus('Move Locked') ? "{$Attacker->Display_Name} is move locked!<br />" : '') .
          "{$Attacker->Display_Name} used {$this->Name} and dealt <b>" . number_format($Handle_Move['Damage']) . "</b> damage to {$Defender->Display_Name}." .
          (isset($Handle_Move['Text']) ? "<br />{$Handle_Move['Text']}" : '') .
          ($Handle_Move['Healing'] > 0 ? "<br />{$Attacker->Display_Name} healed for {$Handle_Move['Healing']} HP!" : '') .
          ($Move_Effectiveness['Text'] != '' ? "<br />{$Move_Effectiveness['Text']}" : '') .
          ($Does_Move_Crit ? '<br />It critically hit!' : ''),
        'Damage' => $Handle_Move['Damage'],
        'Heal' => $Handle_Move['Healing'],
      ];
    }

    /**
     * Generic move handler for moves that do not have, or not require, a stand-alone class.
     */
    public function HandleMove
    (
      string $Side,
      int $STAB,
      bool $Does_Move_Crit,
      float $Move_Effectiveness
    )
    {
      switch ( $Side )
      {
        case 'Ally':
          $Attacker = $_SESSION['Battle']['Ally']['Active'];
          $Defender = $_SESSION['Battle']['Foe']['Active'];
          break;
        case 'Foe':
          $Attacker = $_SESSION['Battle']['Foe']['Active'];
          $Defender = $_SESSION['Battle']['Ally']['Active'];
          break;
      }

      if ( $this->Min_Hits == 'None' )
        $this->Min_Hits = 1;

      if ( $this->Max_Hits == 'None' )
        $this->Max_Hits = 1;

      $Total_Hits = mt_rand($this->Min_Hits, $this->Max_Hits);

      $Damage = 0;
      for ( $Hits = 0; $Hits < $Total_Hits; $Hits++ )
        $Damage += $this->CalcDamage($Side, $STAB, $Does_Move_Crit, $Move_Effectiveness);

      return [
        'Text' => ($Total_Hits > 1 ? "It hit {$Total_Hits} times!" : ''),
        'Damage' => $Damage,
        'Healing' => 0,
      ];
    }

    /**
     * Determines whether or not the user can move.
     */
    public function CanUserMove
    (
      string $Side
    )
    {
      switch ( $Side )
      {
        case 'Ally':
          $Attacker = $_SESSION['Battle']['Ally']['Active'];
          $Defender = $_SESSION['Battle']['Foe']['Active'];
          break;
        case 'Foe':
          $Attacker = $_SESSION['Battle']['Foe']['Active'];
          $Defender = $_SESSION['Battle']['Ally']['Active'];
          break;
      }

      if ( $Attacker->HasStatus('Charging') )
      {
        $Attacker->RemoveStatus('Charging');

        return [
          'Type' => 'Error',
          'Text' => ''
        ];;
      }

      if ( $Attacker->HasStatus('Freeze') )
      {
        if
        (
          in_array($this->Name, ['Fusion Flare', 'Flame Wheel', 'Sacred Fire', 'Flare Blitz', 'Scald']) ||
          $this->Weather == 'Sunlight'
        )
        {
          $Attacker->RemoveStatus('Freeze');

          return [
            'Type' => 'Success',
            'Text' => "{$Attacker->Display_Name} has thawed out.<br />",
          ];
        }

        return [
          'Type' => 'Error',
          'Text' => "{$Attacker->Display_Name} is frozen.<br />",
        ];
      }

      if ( $Attacker->HasStatus('Paralysis') )
      {
        if ( mt_rand(1, 5) !== 1 )
        {
          return [
            'Type' => 'Success',
            'Text' => "{$Attacker->Display_Name} is no longer paralyzed.<br />",
          ];
        }

        return [
          'Type' => 'Error',
          'Text' => "{$Attacker->Display_Name} is completely paralyzed.<br />",
        ];
      }

      if ( $Attacker->HasStatus('Sleep') )
      {
        if ( $this->Name == 'Snore' )
        {
          return [
            'Type' => 'Success',
            'Text' => '',
          ];
        }

        return [
          'Type' => 'Success',
          'Text' => "{$Attacker->Display_Name} is sound asleep.<br />",
        ];
      }

      if ( $Attacker->HasStatus('Recharging') )
      {
        return [
          'Type' => 'Error',
          'Text' => "{$Attacker->Display_Name} is recharging.<br />",
        ];
      }

      if ( $Attacker->HasStatus('Flinch') )
      {
        return [
          'Type' => 'Error',
          'Text' => "{$Attacker->Display_Name} was flinched.<br />",
        ];
      }

      if ( $Attacker->HasStatus('Confusion') )
      {
        if ( mt_rand(1, 3) !== 1 )
        {
          return [
            'Type' => 'Success',
            'Text' => '',
          ];
        }

        return [
          'Type' => 'Error',
          'Text' => "{$Attacker->Display_Name} hurt itself in confusion.<br />",
        ];
      }

      if ( $Attacker->HasStatus('Infatuation') )
      {
        if ( mt_rand(1, 2) !== 1 )
        {
          return [
            'Type' => 'Success',
            'Text' => '',
          ];
        }

        return [
          'Type' => 'Error',
          'Text' => "{$Attacker->Display_Name} is immobilized by love.<br />",
        ];
      }

      return [
        'Type' => 'Success',
        'Text' => ''
      ];
    }

    /**
     * Determing if the move will hit.
     */
    public function DoesMoveHit
    (
      string $Side
    )
    {
      if ( $this->Accuracy === 0 )
        return false;

      if ( $this->Accuracy === 'None' )
        return true;

      if ( $this->Effect_Short == 'Causes a one-hit KO.' )
        if ( mt_rand(1, 100) < 30 )
          return true;
        else
          return false;

      switch ( $Side )
      {
        case 'Ally':
          $Attacker = $_SESSION['Battle']['Ally']['Active'];
          $Defender = $_SESSION['Battle']['Foe']['Active'];
          break;
        case 'Foe':
          $Attacker = $_SESSION['Battle']['Foe']['Active'];
          $Defender = $_SESSION['Battle']['Ally']['Active'];
          break;
      }

      switch ($this->Name)
      {
        case 'Flying Press':
          if ( $Defender->HasStatus('Minimize') )
            return true;

          break;

        case 'Thunder':
        case 'Hurricane':
          if ( $this->Weather == 'Rain' )
            return true;

          if ( $this->Weather == 'Sunlight' )
            $this->Accuracy = 50;

          break;

        case 'Blizzard':
          if ( $this->Weather == 'Hail' )
            return true;

          break;

        case 'Dream Eater':
          if ( $Defender->HasStatus('Sleep') )
            return true;
          else
            return false;

          break;
      }

      if ( $Defender->HasStatus('Bounce') )
        if ( !in_array($this->Name, ['Gust', 'Twister', 'Thunder', 'Sky Uppercut']) )
          return false;

      if ( $Defender->HasStatus('Dig') )
        if ( !in_array($this->Name, ['Earthquake', 'Magnitude', 'Fissure']) )
          return false;

      if ( $Defender->HasStatus('Dive') )
        if ( !in_array($this->Name, ['Surf', 'Whirlpool', 'Low Kick']) )
          return false;

      if ( $Defender->HasStatus('Fly') )
        if ( !in_array($this->Name, ['Gust', 'Twister', 'Thunder', 'Sky Uppercut', 'Smack Down']) )
          return false;

      if ( $Defender->HasStatus('Sky Drop') )
        if ( !in_array($this->Name, ['Gust', 'Thunder', 'Twister', 'Sky Uppercut', 'Hurricane', 'Smack Down']) )
          return false;

      $Accuracy_Mod = $Attacker->Stats['Current']['Accuracy'] / $Defender->Stats['Current']['Evasion'];

      if ( mt_rand(1, 100) < $this->Accuracy * $Accuracy_Mod )
        return true;

      return false;
    }

    /**
     * Determine if the move will crit.
     */
    public function DoesMoveCrit
    (
      string $Side
    )
    {
      switch ( $Side )
      {
        case 'Ally':
          $Attacker = $_SESSION['Battle']['Ally']['Active'];
          $Defender = $_SESSION['Battle']['Foe']['Active'];
          break;
        case 'Foe':
          $Attacker = $_SESSION['Battle']['Foe']['Active'];
          $Defender = $_SESSION['Battle']['Ally']['Active'];
          break;
      }

      switch ($this->Name)
      {
        case 'Dragon Rage':
        case 'Seismic Toss':
        case 'Final Gambit':
        case 'Night Shade':
        case 'Sonicboom':
          return false;
          break;
      }

      if
      (
        $this->Crit_Chance == null ||
        $this->Crit_Chance == 0
      )
        return false;

      if ( $Defender->HasStatus('Lucky Chant') )
        return false;

      if ( in_array($Defender->Ability, ['Battle Armor', 'Shell Armor']) )
        return false;

      if ( isset($Defender->Statuses['Lucky Chant']) )
        return false;

      if ( $Attacker->Ability == 'Merciless' )
        if ( isset($Defender->Statuses['Poisoned']) )
          return true;

      if ( $Attacker->Ability == 'Super Luck' )
        $this->Crit_Chance++;

      switch ( $Attacker->Pokedex_ID )
      {
        case 113:
          if ( $Attacker->Item->Name == 'Lucky Punch' )
            $this->Crit_Chance += 2;
          break;

        case 83:
          if ( $Attacker->Item->Name == 'Stick' )
            $this->Crit_Chance += 2;
          break;
      }

      switch ( $Attacker->Item->Name )
      {
        case 'Scope Lens':
        case 'Razor Claw':
          $this->Crit_Chance++;
          break;
      }

      if ( $Attacker->Item->Name == 'Scope Lens' )
        $this->Crit_Chance++;

      switch ( $this->Crit_Chance )
      {
        case 0:
          return mt_rand(1, 24) === 1;
        case 1:
          return mt_rand(1, 8) === 1;
        case 2:
          return mt_rand(1, 2) === 1;
        default:
          return true;
      }
    }

    /**
     * Determine how effective the move was.
     */
    public function MoveEffectiveness
    (
      object $Used_Against
    )
    {
      $Types = [
        'Normal', 'Fire', 'Water', 'Electric',
        'Grass', 'Ice', 'Fighting', 'Poison',
        'Ground', 'Flying', 'Psychic', 'Bug',
        'Rock', 'Ghost', 'Dragon', 'Dark',
        'Steel', 'Fairy'
      ];

      $Type_Chart = [
        // N  FIR  WAT  ELE  GRA  ICE  FIG  POI  GRO  FLY  PSY  BUG  ROC  GHO  DRA  DAR  STE  FAI
        [1.0, 1.0, 1.0, 1.0, 1.0, 1.0, 1.0, 1.0, 1.0, 1.0, 1.0, 1.0, 0.5, 0.0, 1.0, 1.0, 0.5, 1.0], // Normal
        [1.0, 0.5, 0.5, 1.0, 2.0, 2.0, 1.0, 1.0, 1.0, 1.0, 1.0, 2.0, 0.5, 1.0, 0.5, 1.0, 2.0, 1.0], // Fire
        [1.0, 2.0, 0.5, 1.0, 0.5, 1.0, 1.0, 1.0, 2.0, 1.0, 1.0, 1.0, 2.0, 1.0, 0.5, 1.0, 1.0, 1.0], // Water
        [1.0, 1.0, 2.0, 0.5, 0.5, 1.0, 1.0, 1.0, 0.0, 2.0, 1.0, 1.0, 1.0, 1.0, 0.5, 1.0, 1.0, 1.0], // Electric
        [1.0, 0.5, 2.0, 1.0, 0.5, 1.0, 1.0, 0.5, 2.0, 0.5, 1.0, 0.5, 2.0, 1.0, 0.5, 1.0, 0.5, 1.0], // Grass
        [1.0, 0.5, 0.5, 1.0, 2.0, 0.5, 1.0, 1.0, 2.0, 2.0, 1.0, 1.0, 1.0, 1.0, 2.0, 1.0, 0.5, 1.0], // Ice
        [2.0, 1.0, 1.0, 1.0, 1.0, 2.0, 1.0, 0.5, 1.0, 0.5, 0.5, 0.5, 2.0, 0.0, 1.0, 2.0, 2.0, 0.5], // Fighting
        [1.0, 1.0, 1.0, 1.0, 2.0, 1.0, 1.0, 0.5, 0.5, 1.0, 1.0, 1.0, 0.5, 0.5, 1.0, 1.0, 0.0, 2.0], // Poison
        [1.0, 2.0, 1.0, 2.0, 0.5, 1.0, 1.0, 2.0, 1.0, 0.0, 1.0, 0.5, 2.0, 1.0, 1.0, 1.0, 2.0, 1.0], // Ground
        [1.0, 1.0, 1.0, 0.5, 2.0, 1.0, 2.0, 1.0, 1.0, 1.0, 1.0, 2.0, 0.5, 1.0, 1.0, 1.0, 0.5, 1.0], // Flying
        [1.0, 1.0, 1.0, 1.0, 1.0, 1.0, 2.0, 2.0, 1.0, 1.0, 0.5, 1.0, 1.0, 1.0, 1.0, 0.0, 0.5, 1.0], // Psychic
        [1.0, 0.5, 1.0, 1.0, 2.0, 1.0, 0.5, 0.5, 1.0, 0.5, 2.0, 1.0, 1.0, 0.5, 1.0, 2.0, 0.5, 0.5], // Bug
        [1.0, 2.0, 1.0, 1.0, 1.0, 2.0, 0.5, 1.0, 0.5, 2.0, 1.0, 2.0, 1.0, 1.0, 1.0, 1.0, 0.5, 1.0], // Rock
        [0.0, 1.0, 1.0, 1.0, 1.0, 1.0, 1.0, 1.0, 1.0, 1.0, 2.0, 1.0, 1.0, 2.0, 1.0, 0.5, 1.0, 1.0], // Ghost
        [1.0, 1.0, 1.0, 1.0, 1.0, 1.0, 1.0, 1.0, 1.0, 1.0, 1.0, 1.0, 1.0, 1.0, 2.0, 1.0, 0.5, 0.0], // Dragon
        [1.0, 1.0, 1.0, 1.0, 1.0, 1.0, 0.5, 1.0, 1.0, 1.0, 2.0, 1.0, 1.0, 2.0, 1.0, 0.5, 1.0, 0.5], // Dark
        [1.0, 0.5, 0.5, 0.5, 1.0, 2.0, 1.0, 1.0, 1.0, 1.0, 1.0, 1.0, 2.0, 1.0, 1.0, 1.0, 0.5, 2.0], // Steel
        [1.0, 0.5, 1.0, 1.0, 1.0, 1.0, 2.0, 0.5, 1.0, 1.0, 1.0, 1.0, 1.0, 1.0, 2.0, 2.0, 0.5, 1.0], // Fairy
      ];

      $Move_Type = array_search($this->Move_Type, $Types);
      $Type_1_Mult = array_search($Used_Against->Primary_Type, $Types);
      $Type_2_Mult = array_search($Used_Against->Secondary_Type, $Types);

      $Effective_Mult = $Type_Chart[$Move_Type][$Type_1_Mult] * $Type_Chart[$Move_Type][$Type_2_Mult];

      switch ( $Effective_Mult )
      {
        case 0:
          return [
            'Mult' => $Effective_Mult,
            'Text' => 'It was completely ineffective.'
          ];
        case .25:
          return [
            'Mult' => $Effective_Mult,
            'Text' => 'It was quite ineffective.'
          ];
        case .5:
          return [
            'Mult' => $Effective_Mult,
            'Text' => 'It was not very effective.'
          ];
        case 1:
          return [
            'Mult' => $Effective_Mult,
            'Text' => ''
          ];
        case 2:
          return [
            'Mult' => $Effective_Mult,
            'Text' => 'It was super effective.'
          ];
        case 4:
          return [
            'Mult' => $Effective_Mult,
            'Text' => 'It was extremely effective.'
          ];
      }
    }

    /**
     * Determine if the move gets STAB applied to it.
     */
    public function CalculateSTAB
    (
      string $Side
    )
    {
      if
      (
        $_SESSION['Battle'][$Side]['Active']->Primary_Type == $this->Move_Type ||
        $_SESSION['Battle'][$Side]['Active']->Secondary_Type == $this->Move_Type
      )
      {
        if ( $_SESSION['Battle'][$Side]['Active']->Ability == 'Adaptibility' )
          return 2;

        return 1.5;
      }

      return 1;
    }

    /**
     * Calculates how much healing the move will do.
     */
    public function CalcHealing
    (
      int $Damage_Dealt
    )
    {
      return 0;
    }
  }
