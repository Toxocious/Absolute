<?php
  use BattleHandler\Battle;

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

      return $this;
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
          switch ($this->Weather)
          {
            case 'Rain':
              return true;
            case 'Sunlight':
              $this->Accuracy = 50;
              break;
            default:
              break;
          }
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

      $Accuracy_Mod = $Attacker->Accuracy / $Defender->Evasion;

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
     * Calculates how much damage the move will do.
     */
    public function CalcDamage
    (
      $Side,
      $STAB,
      $Crit,
      $Move_Effectiveness
    )
    {
      if ( !isset($STAB) || !isset($Crit) || !isset($Move_Effectiveness) )
        return -1;

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

      $Crit_Mult = 1;
      if ( $Crit )
        if ( $Attacker->Ability == 'Sniper' )
          $Crit_Mult = 2.25;
        else
          $Crit_Mult = 1.5;

      $Weather_Mult = 1;
      switch ( $this->Weather )
      {
        case 'Rain':
          if ( $this->Move_Type == 'Water' )
            $Weather_Mult = 1.5;
          else if ( $this->Move_type == 'Fire' )
            $Weather_Mult = 0.5;
          break;

        case 'Harsh Sunlight':
          if ( $this->Move_Type == 'Fire' )
            $Weather_Mult = 1.5;
          else if ( $this->Move_type == 'Water' )
            $Weather_Mult = 0.5;
          break;
      }

      $Status_Mult = 1;
      if ( $Attacker->Ability == 'Guts' )
        if ( $Attacker->HasStatusFromArray(['Burn', 'Freeze', 'Paralyze', 'Poison', 'Sleep']) )
          $Status_Mult = 1.5;
      else
        if ( $Attacker->HasStatus('Burn') )
          $Status_Mult = 0.5;

      $Damage = floor(((2 * $Attacker->Level / 5 + 2) * $this->Power * $Attacker->Stats['Current']['Attack'] / $Defender->Stats['Current']['Defense'] / 50 + 2) * 1 * $Weather_Mult * $Crit_Mult * (mt_rand(185, 200) / 200) * $STAB * $Move_Effectiveness * $Status_Mult * 1);
      if ( $Damage < 0 )
        $Damage = 0;

      return $Damage;
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
