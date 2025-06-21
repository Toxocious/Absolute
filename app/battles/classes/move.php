<?php
  use BattleHandler\Battle;

  spl_autoload_register(function($Class)
  {
    $Moves_Directory = dirname(__DIR__, 1);
    $Class = strtolower($Class);

    if (file_exists($Moves_Directory . "/classes/moves/{$Class}.php"))
      require_once $Moves_Directory . "/classes/moves/{$Class}.php";
  });

  class Move extends Battle
  {
    public $ID = null;
    public $Name = null;
    public $Slot = null;
    public $Disabled = null;
    public $Disabled_For_Turns = null;
    public $Usable = null;
    public $Consecutive_Hits = null;
    public $Target = null;

    public $Flags = null;

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
    public $Recoil = null;
    public $Drain = null;
    public $Healing = null;
    public $Max_Hits = null;
    public $Max_Turns = null;
    public $Min_Hits = null;
    public $Min_Turns = null;
    public $Stat_Chance = null;
    public $Total_Hits = null;

    public $HP_Boost = null;
    public $Attack_Boost = null;
    public $Defense_Boost = null;
    public $Sp_Attack_Boost = null;
    public $Sp_Defense_Boost = null;
    public $Speed_Boost = null;
    public $Accuracy_Boost = null;
    public $Evasion_Boost = null;

    public $Class_Name = null;
    public $Success = null;

    public function __construct
    (
      int $Move,
      int $Slot
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

      if ( !$Move_Data['Usable'] )
      {
        $this->Name = $Move_Data['Name'];
        $this->Disabled = true;

        return $this;
      }

      try
      {
        $Fetch_Flags = $PDO->prepare("
          SELECT `authentic`, `bite`, `bullet`, `charge`, `contact`, `dance`, `defrost`, `distance`, `gravity`, `heal`, `mirror`, `mystery`, `nonsky`, `powder`, `protect`, `pulse`, `punch`, `recharge`, `reflectable`, `snatch`, `sound`
          FROM `moves_flags`
          WHERE `ID` = ?
          LIMIT 1
        ");
        $Fetch_Flags->execute([ $Move_Data['ID'] ]);
        $Fetch_Flags->setFetchMode(PDO::FETCH_ASSOC);
        $Flags = $Fetch_Flags->fetch();
      }
      catch ( PDOException $e )
      {
        HandleError($e);
      }

      $this->ID = $Move_Data['ID'];
      $this->Name = $Move_Data['Name'];
      $this->Slot = $Slot;
      $this->Disabled = false;
      $this->Usable = $Move_Data['Usable'];
      $this->Consecutive_Hits = 0;
      $this->Target = $Move_Data['Target'];

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
      $this->Recoil = $Move_Data['Recoil'];
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
      $this->Sp_Attack_Boost = $Move_Data['Sp_Attack_Boost'];
      $this->Sp_Defense_Boost = $Move_Data['Sp_Defense_Boost'];
      $this->Speed_Boost = $Move_Data['Speed_Boost'];
      $this->Accuracy_Boost = $Move_Data['Accuracy_Boost'];
      $this->Evasion_Boost = $Move_Data['Evasion_Boost'];

      $this->Class_Name = $Move_Data['Class_Name'];

      if ( !empty($Flags) )
      {
        foreach ( $Flags as $Flag => $Value )
        {
          if ( $Value )
            $this->Flags[$Flag] = $Value;
        }
      }
    }

    /**
     * Begin processing an attack.
     * @param string $Side
     */
    public function ProcessAttack
    (
      string $Side
    )
    {
      if ( !$this->Usable )
      {
        return [
          'Type' => 'Error',
          'Text' => 'This move is not usable.',
          'Damage' => 0,
          'Heal' => 0,
        ];
      }

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

      if ( $Attacker->Ability->Name == 'Truant' && $Attacker->Ability->Procced )
      {
        $Attacker->Ability->SetProcStatus(false);

        return [
          'Type' => 'Error',
          'Text' => "{$Attacker->Display_Name} is loafing around.",
          'Damage' => 0,
          'Heal' => 0
        ];
      }

      if ( $Defender->Ability->Name == 'Queenly Majesty' && $this->Priority > 0 )
      {
        return [
          'Type' => 'Error',
          'Text' => "{$Attacker->Display_Name} can't use {$this->Name}!",
          'Damage' => 0,
          'Heal' => 0
        ];
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

      if ( $Attacker->Item->Name == 'Assault Vest' && $this->Damage_Type == 'Status' && $this->Name != 'Me First' )
      {
        return [
          'Type' => 'Error',
          'Text' => "{$Attacker->Display_Name} can't use Status moves due to its Assault Vest!",
          'Damage' => 0,
          'Heal' => 0,
        ];
      }


      /**
       * Hidden Power check here, before anything further gets processed.
       */
      if ( $this->Name == 'Hidden Power' )
        $this->Move_Type = $this->DetermineMoveType($Attacker->IVs);

      /**
       * Abilities that change move type, etc. need to happen here.
       */
      switch ( $Attacker->Ability->Name )
      {
        case 'Aerilate':
          if ( $this->Move_Type == 'Normal' )
            $this->Move_Type = 'Flying';
          break;

        case 'Galvanize':
          $this->Move_Type = 'Electric';
          break;

        case 'Libero':
          $Attacker->SetTyping('Primary', $this->Move_Type, true);
          break;

        case 'Liquid Voice':
          if ( $this->HasFlag('sound') )
            $this->Move_Type = 'Water';
          break;

        case 'Long Reach':
          if ( $this->HasFlag('contact') )
            $this->SetFlag('contact', false);
          break;

        case 'Mimicry':
          if ( !empty($this->Terrain) )
          {
            switch ($this->Terrain->Name)
            {
              case 'Electric':
                $Attacker->SetTyping('Primary', 'Electric', true);
                break;
              case 'Grassy':
                $Attacker->SetTyping('Primary', 'Grass', true);
                break;
              case 'Misty':
                $Attacker->SetTyping('Primary', 'Fairy', true);
                break;
              case 'Psychic':
                $Attacker->SetTyping('Primary', 'Psychic', true);
                break;
            }
          }
          break;

        case 'Normalize':
          $this->Move_Type = 'Normal';
          break;

        case 'Pixilate':
          if ( $this->Move_Type == 'Normal' )
            $this->Move_Type = 'Fairy';
          break;

        case 'Protean':
          $Attacker->SetTyping('Primary', $this->Move_Type, true);
          break;

        case 'Refrigerate':
          if ( $this->Move_Type == 'Normal' )
            $this->Move_Type = 'Ice';
          break;
      }

      /**
       * Items that change move type need to happen here.
       */
      if ( !empty($Attacker->Item) )
      {
        if ( $this->Name == 'Judgement' )
        {
          switch ($Attacker->Item->Name)
          {
            case 'Draco Plate':
              $this->Move_Type == 'Dragon';
              break;
            case 'Dread Plate':
              $this->Move_Type == 'Earth';
              break;
            case 'Earth Plate':
              $this->Move_Type == 'Ground';
              break;
            case 'Fist Plate':
              $this->Move_Type == 'Fighting';
              break;
            case 'Flame Plate':
              $this->Move_Type == 'Fire';
              break;
            case 'Icicle Plate':
              $this->Move_Type == 'Ice';
              break;
            case 'Insect Plate':
              $this->Move_Type == 'Bug';
              break;
            case 'Iron Plate':
              $this->Move_Type == 'Steel';
              break;
            case 'Meadow Plate':
              $this->Move_Type == 'Grass';
              break;
            case 'Mind Plate':
              $this->Move_Type == 'Psychic';
              break;
            case 'Pixie Plate':
              $this->Move_Type == 'Fairy';
              break;
            case 'Sky Plate':
              $this->Move_Type == 'Flying';
              break;
            case 'Splash Plate':
              $this->Move_Type == 'Water';
              break;
            case 'Spooky Plate':
              $this->Move_Type == 'Ghost';
              break;
            case 'Stone Plate':
              $this->Move_Type == 'Rock';
              break;
            case 'Toxic Plate':
              $this->Move_Type == 'Poison';
              break;
            case 'Zap Plate':
              $this->Move_Type == 'Electric';
              break;
            default:
              $this->Move_Type = 'Normal';
              break;
          }
        }

        if ( $this->Name == 'Multi-Attack' )
        {
          switch ( $Attacker->Item->Name )
          {
            case 'Bug Memory':
              $this->Move_Type = 'Bug';
              break;
            case 'Dark Memory':
              $this->Move_Type = 'Dark';
              break;
            case 'Dragon Memory':
              $this->Move_Type = 'Dragon';
              break;
            case 'Electric Memory':
              $this->Move_Type = 'Electric';
              break;
            case 'Fairy Memory':
              $this->Move_Type = 'Fairy';
              break;
            case 'Fighting Memory':
              $this->Move_Type = 'Fighting';
              break;
            case 'Fire Memory':
              $this->Move_Type = 'Fire';
              break;
            case 'Flying Memory':
              $this->Move_Type = 'Flying';
              break;
            case 'Ghost Memory':
              $this->Move_Type = 'Ghost';
              break;
            case 'Grass Memory':
              $this->Move_Type = 'Grass';
              break;
            case 'Ground Memory':
              $this->Move_Type = 'Ground';
              break;
            case 'Ice Memory':
              $this->Move_Type = 'Ice';
              break;
            case 'Poison Memory':
              $this->Move_Type = 'Poison';
              break;
            case 'Psychic Memory':
              $this->Move_Type = 'Psychic';
              break;
            case 'Rock Memory':
              $this->Move_Type = 'Rock';
              break;
            case 'Steel Memory':
              $this->Move_Type = 'Steel';
              break;
            case 'Water Memory':
              $this->Move_Type = 'Water';
              break;
          }
        }

        if ( $this->Name == 'Techno Blast' )
        {
          switch ( $Attacker->Item->Name )
          {
            case 'Burn Drive':
              $this->Move_Type = 'Fire';
              break;
            case 'Chill Drive':
              $this->Move_Type = 'Ice';
              break;
            case 'Douse Drive':
              $this->Move_Type = 'Water';
              break;
            case 'Shock Drive':
              $this->Move_Type = 'Electric';
              break;
          }
        }
      }

      $Move_Effectiveness = $this->MoveEffectiveness($Attacker, $Defender);
      if ( $Move_Effectiveness['Mult'] > 0 )
        $Does_Move_Crit = $this->DoesMoveCrit($Side);
      else
        $Does_Move_Crit = false;

      $STAB = $this->CalculateSTAB($Side);

      if ( class_exists($this->Class_Name) )
        $Move_Class = new $this->Class_Name($this->ID, $this->Slot);

      /**
       * Use class specific DoesMoveHit() method
       */
      if
      (
        isset($Move_Class) &&
        method_exists($Move_Class, 'DoesMoveHit')
      )
        $Does_Move_Hit = $Move_Class->DoesMoveHit($Side, $STAB, $Does_Move_Crit, $Move_Effectiveness);
      else
        $Does_Move_Hit = $this->DoesMoveHit($Side, $Move_Effectiveness['Mult']);

      /**
       * If the move doesn't hit, return w/ proper dialogue here.
       */
      if ( !$Does_Move_Hit )
      {
        if ( isset($Does_Move_Hit['Damage']) && $Does_Move_Hit['Damage'] > 0 )
          $Attacker->DecreaseHP($Does_Move_Hit['Damage']);

        return [
          'Type' => 'Success',
          'Text' => "{$Attacker->Display_Name} used {$this->Name}, but it missed!" .
                    (isset($Does_Move_Hit['Effect_Text']) ? "<br />{$Does_Move_Hit['Effect_Text']}" : ''),
          'Damage' => 0,
          'Heal' => 0,
        ];
      }

      if ( isset($Move_Class) )
        $Handle_Move = $Move_Class->ProcessMove($Side, $STAB, $Does_Move_Crit, $Move_Effectiveness);
      else
        $Handle_Move = $this->HandleMove($Side, $STAB, $Does_Move_Crit, $Move_Effectiveness);

      $this->ProcessMoveDisablement($Attacker);

      $Defender->Last_Damage_Taken = isset($Damage) ? $Damage : 0;

      $this->Consecutive_Hits++;

      $Attacker->Last_Move = [
        'Name' => $this->Name,
        'Slot' => $this->Slot,
        'Type' => $this->Damage_Type,
        'Consecutive_Hits' => $this->Consecutive_Hits
      ];

      if ( in_array($Attacker->Item->Name, ['Choice Band', 'Choice Scarf', 'Choice Specs']) )
        $Attacker->SetStatus('Move Locked');

      return [
        'Type' => 'Success',
        'Text' => $Handle_Move['Text'] .
                  (isset($Handle_Move['Effect_Text']) && $Handle_Move['Effect_Text'] != '' ? "<br />{$Handle_Move['Effect_Text']}" : '') .
                  (isset($Disable_Dialogue) && $Disable_Dialogue != '' ? "<br />{$Disable_Dialogue}" : ''),
        'Damage' => (isset($Handle_Move['Damage']) && $Handle_Move['Damage'] > 0 ? $Handle_Move['Damage'] : 0),
        'Heal' => (isset($Handle_Move['Healing']) && $Handle_Move['Healing'] > 0 ? $Handle_Move['Healing'] : 0),
      ];
    }

    /**
     * Generic move handler for moves that do not have, or not require, a stand-alone class.
     * @param string $Side
     * @param int $STAB,
     * @param bool $Does_Move_Crit
     * @param array $Move_Effectiveness
     */
    public function HandleMove
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
          $Ally = 'Ally';
          $Foe = 'Foe';
          $Attacker = $_SESSION['EvoChroniclesRPG']['Battle']['Ally']->Active;
          $Defender = $_SESSION['EvoChroniclesRPG']['Battle']['Foe']->Active;
          break;
        case 'Foe':
          $Ally = 'Foe';
          $Foe = 'Ally';
          $Attacker = $_SESSION['EvoChroniclesRPG']['Battle']['Foe']->Active;
          $Defender = $_SESSION['EvoChroniclesRPG']['Battle']['Ally']->Active;
          break;
      }

      if ( isset($_SESSION['EvoChroniclesRPG']['Battle']['Turn_Data']['Turn_' . $this->Turn_ID]['First_Attacker']) )
        $Turn_First_Attacker = $_SESSION['EvoChroniclesRPG']['Battle']['Turn_Data']['Turn_' . $this->Turn_ID]['First_Attacker'];
      else
        $Turn_First_Attacker = $Side;

      /**
       * Handle Magic Coat (Move) and Magic Bounce (Ability)
       */
      if
      (
        $Defender->Ability->Name == 'Magic Bounce' ||
        ( $Turn_First_Attacker == 'Foe' && $Defender->Last_Move['Name'] == 'Magic Coat' )
      )
      {
        if ( $this->HasFlag('reflectable') && $this->Target == 'Foe' )
        {
          $this->Target == 'Ally';
        }
      }

      switch ( $this->Target )
      {
        case 'Ally':
          $Target = $_SESSION['EvoChroniclesRPG']['Battle'][$Ally];
          break;
        case 'Foe':
          $Target = $_SESSION['EvoChroniclesRPG']['Battle'][$Foe];
          break;
      }

      if ( $Attacker->HasStatus('Paralysis') )
      {
        if ( mt_rand(1, 4) === 1 )
        {
          return [
            'Type' => 'Success',
            'Text' => "{$Attacker->Display_Name} is fully paralyzed!",
            'Damage' => 0,
            'Heal' => 0,
          ];
        }
      }

      if ( $Attacker->HasStatus('Freeze') )
      {
        if ( mt_rand(1, 100) <= 20 )
        {
          $Attacker->RemoveStatus('Freeze');
        }
        else
        {
          return [
            'Type' => 'Success',
            'Text' => "{$Attacker->Display_Name} is frozen!",
            'Damage' => 0,
            'Heal' => 0,
          ];
        }
      }

      if ( $this->HasFlag('charge') )
      {
        switch ( $this->Name )
        {
          case 'Bounce':
            $Charge_Dialogue = "{$Attacker->Display_Name} bounced into the air!";
            break;

          case 'Dig':
            $Charge_Dialogue = "{$Attacker->Display_Name} dug underground!";
            break;

          case 'Dive':
            $Charge_Dialogue = "{$Attacker->Display_Name} dove underwater!";
            break;

          case 'Fly':
            $Charge_Dialogue = "{$Attacker->Display_Name} flew up into the air!";
            break;

          case 'Freeze Shock':
            $Charge_Dialogue = "{$Attacker->Display_Name} is charging up a ball of electrically-charged ice!";
            break;

          case 'Geomancy':
            $Charge_Dialogue = "{$Attacker->Display_Name} is charging up energy!";
            break;

          case 'Ice Burn':
            $Charge_Dialogue = "{$Attacker->Display_Name} is creating a special aura!";
            break;

          case 'Meteor Beam':
            $Charge_Dialogue = "{$Attacker->Display_Name} is gathering the powers of space!";
            break;

          case 'Phantom Force':
          case 'Shadow Force':
            $Charge_Dialogue = "{$Attacker->Display_Name} has vanished!";
            break;

          case 'Razor Wind':
            $Charge_Dialogue = "{$Attacker->Display_Name} whipped up a whirlwind!";
            break;

          case 'Skull Bash':
            $Charge_Dialogue = "{$Attacker->Display_Name} lowered its head!";
            break;

          case 'Sky Attack':
            $Charge_Dialogue = "{$Attacker->Display_Name} became cloaked in a harsh light!";
            break;

          case 'Sky Drop':
            $Charge_Dialogue = "{$Attacker->Display_Name} grabbed {$Defender->Display_Name} and flew into the air!";
            break;

          case 'Solar Beam':
            $Charge_Dialogue = "{$Attacker->Display_Name} has taken in sunlight!";
            break;

          case 'Solar Blade':
            $Charge_Dialogue = "{$Attacker->Display_Name} is charging up sunlight!";
            break;

          default:
            $Charge_Dialogue = "{$Attacker->Display_Name} is charging up an attack!";
            break;
        }

        if ( !$Attacker->HasStatus('Charging') )
        {
          if ( $Attacker->HasItem(['Power Herb']) )
          {
            $Attacker->Item->Consume();

            $Charge_Dialogue .= "<br />{$Attacker->Display_Name} consumed its Power Herb!<br />";
          }
          else
          {
            $Attacker->SetStatus('Charging');

            return [
              'Type' => 'Success',
              'Text' => $Charge_Dialogue,
              'Damage' => 0,
              'Heal' => 0,
            ];
          }
        }
        else
        {
          $Attacker->RemoveStatus('Charging');
        }
      }

      if
      (
        $Defender->HasStatus('Protect') && $this->HasFlag('protect') &&
        ($Attacker->Ability->Name != 'Unseen Fist' && !$this->HasFlag('contact'))
      )
      {
        return [
          'Type' => 'Success',
          'Text' => "{$Attacker->Display_Name} used {$this->Name}.<br />" .
                    "{$Defender->Display_Name} was protected from the attack!",
          'Damage' => 0,
          'Heal' => 0,
        ];
      }

      if
      (
        $this->HasFlag('powder') &&
        ($Defender->HasTyping(['Grass']) || $Defender->Ability->Name == 'Overcoat' || $Defender->Item->Name == 'Safety Goggles')
      )
      {
        return [
          'Type' => 'Success',
          'Text' => "{$Attacker->Display_Name} used {$this->Name}.<br />" .
                    "It had no effect!",
          'Damage' => 0,
          'Heal' => 0,
        ];
      }

      if ( $Attacker->HasStatus('Heal Block') && $this->HasFlag('heal') )
      {
        return [
          'Type' => 'Success',
          'Text' => "{$Attacker->Display_Name} used {$this->Name}.<br />" .
                    "{$Attacker->Display_Name} attack was prevented by its Heal Block!",
          'Damage' => 0,
          'Heal' => 0,
        ];
      }

      if ( $Defender->Ability->Name == 'Bulletproof' && $this->HasFlag('bullet') )
      {
        return [
          'Type' => 'Success',
          'Text' => "{$Attacker->Display_Name} used {$this->Name}.<br />" .
                    "{$Defender->Display_Name} is Bulletproof!",
          'Damage' => 0,
          'Heal' => 0,
        ];
      }

      if ( $Defender->Ability->Name == 'Soundproof' && $this->HasFlag('sound') )
      {
        return [
          'Type' => 'Success',
          'Text' => "{$Attacker->Display_Name} used {$this->Name}.<br />" .
                    "{$Defender->Display_Name} is Soundproof!",
          'Damage' => 0,
          'Heal' => 0,
        ];
      }

      if ( $Defender->Ability->Name == 'Damp' && in_array($this->Name, ['Self-Destruct', 'Explosion', 'Mind Blown', 'Misty Explosion']) )
      {
        return [
          'Type' => 'Success',
          'Text' => "{$Attacker->Display_Name} used {$this->Name}.<br />" .
                    "{$Defender->Display_Name}'s Damp prevented it!",
          'Damage' => 0,
          'Heal' => 0,
        ];
      }

      if ( $Defender->Ability->Name == 'Dazzling' && $this->Priority > 0 )
      {
        return [
          'Text' => "{$Attacker->Display_Name} can't use {$this->Name}!",
          'Damage' => 0,
          'Heal' => 0,
        ];
      }

      if ( $Attacker->HasStatus('Taunt') )
      {
        if ( $this->Damage_Type == 'Status' )
        {
          return [
            'Type' => 'Success',
            'Text' => "{$Attacker->Display_Name} can't use {$this->Name} due to the Taunt.",
            'Damage' => 0,
            'Heal' => 0,
          ];
        }
      }

      if ( $this->hasFlag('contact') )
      {
        $Handle_Contact = $this->HandleContact($Side);
        if ( isset($Handle_Contact['Damage']) && $Handle_Contact['Damage'] == 0 )
        {
          return [
            'Text' =>
              ($this->CanUserMove($Side)['Type'] == 'Success' ? "{$this->CanUserMove($Side)['Text']}" : '') .
              ($Attacker->HasStatus('Move Locked') ? "{$Attacker->Display_Name} is move locked!<br />" : '') .
              "{$Attacker->Display_Name} used {$this->Name}." .
              $Handle_Contact['Text'],
          ];
        }
      }

      if ( $this->Min_Hits == 'None' )
        $this->Min_Hits = 1;

      if ( $this->Max_Hits == 'None' )
        $this->Max_Hits = 1;

      switch ( $Attacker->Ability->Name )
      {
        case 'Parental Bond':
          if ( $this->Max_Hits === 1 )
            $this->Total_Hits = 2;
          break;

        case 'Skill Link':
          if ( $this->Max_Hits > 1 )
            $this->Total_Hits = 5;
          break;

        default:
          $this->Total_Hits = mt_rand($this->Min_Hits, $this->Max_Hits);
          break;
      }

      /**
       * Ability proc dialogue.
       */
      $Ability_Proc_Dialogue = '';

      /**
       * Calculate how much damage will be done.
       */
      $Damage = 0;
      for ( $Hit = 0; $Hit < $this->Total_Hits; $Hit++ )
      {
        $Initial_Damage = $this->CalcDamage($Side, $STAB, $Does_Move_Crit, $Move_Effectiveness['Mult']);

        $Item_Proc = $this->ProcessItemProcs($Attacker, $Defender, $Move_Effectiveness['Mult'], $Damage);
        $Ability_Proc = $this->ProcessAbilityProcs($Attacker, $Defender, true, $Hit, $this->Total_Hits, $Initial_Damage);
        $Ability_Proc_Dialogue .= $Ability_Proc['Text'];

        if ( !empty($Ability_Proc['Damage']) )
          $Damage = $Ability_Proc['Damage'];
        else
          $Damage = $Initial_Damage;
      }

      /**
       * Calculate how much healing will be done.
       */
      $Healing = 0;
      if ( $Attacker->HP < $Attacker->Max_HP )
        if ( $this->Drain > 0 )
          $Healing = $this->CalcHealing($Damage);

      /**
       * Calculate how much recoil will be dealt if applicable.
       */
      $Recoil = 0;
      if ( $this->Recoil > 0 && $Attacker->Ability->Name != 'Rock Head' )
        $Recoil = $this->CalcRecoil($Damage);

      /**
       * Process stat gaines/losses if applicable.
       */
      $Stat_Change_Text = $this->ProcessStatChanges($Target, $Attacker, $Defender, $Does_Move_Crit);

      /**
       * Process rolling and setting ailments if applicable.
       */
      $Ailment_Text = $this->ProcessAilments($Target, $Attacker, $Defender, $Turn_First_Attacker);

      /**
       * Process end of turn ability procs.
       */
      $Ability_Effect = $this->ProcessAbilityProcs($Attacker, $Defender, false, 1, 1, $Damage);
      $Ability_Proc_Dialogue .= $Ability_Effect['Text'];

      if ( $Damage <= 0 )
      {
        $Dialogue = ($this->CanUserMove($Side)['Type'] == 'Success' ? "{$this->CanUserMove($Side)['Text']}" : '') .
                    ($Attacker->HasStatus('Move Locked') ? "{$Attacker->Display_Name} is move locked!<br />" : '') .
                    "{$Attacker->Display_Name} used {$this->Name}." .
                    (!empty($Ailment_Text) ? "<br />{$Ailment_Text}" : '') .
                    (!empty($Stat_Change_Text) ? "<br />{$Stat_Change_Text}" : '') .
                    (!empty($Ability_Effect['Text']) ? "<br />{$Ability_Effect['Text']}" : '');
      }
      else
      {
        $Dialogue = ($this->CanUserMove($Side)['Type'] == 'Success' ? "{$this->CanUserMove($Side)['Text']}" : '') .
                    ($Attacker->HasStatus('Move Locked') ? "{$Attacker->Display_Name} is move locked!<br />" : '') .
                    "{$Attacker->Display_Name} used {$this->Name} and dealt <b>" . number_format($Damage) . "</b> damage to {$Defender->Display_Name}." .
                    ($this->Total_Hits > 1 ? "<br />It hit {$this->Total_Hits} times!" : '') .
                    ($Move_Effectiveness['Text'] != '' ? "<br />{$Move_Effectiveness['Text']}" : '') .
                    ($Does_Move_Crit ? '<br />It critically hit!' : '') .
                    (!empty($Ability_Change_Dialogue) ? $Ability_Change_Dialogue : '') .
                    ($this->Recoil > 0 ? "<br />{$Attacker->Display_Name} took " . number_format($Recoil) . ' damage from the recoil!' : '') .
                    ($Healing > 0 ? "<br />{$Attacker->Display_Name} restored " . number_format($Healing) . ' health!' : '') .
                    ($this->hasFlag('contact') ? $this->HandleContact($Side)['Text'] : '') .
                    (!empty($Ailment_Text) ? "<br />{$Ailment_Text}" : '') .
                    (!empty($Stat_Change_Text) ? "<br />{$Stat_Change_Text}" : '');
      }

      return [
        'Text' => $Dialogue,
        'Effect_Text' => (isset($Ability_Proc_Dialogue) ? $Ability_Proc_Dialogue : ''),
        'Damage' => $Damage,
        'Healing' => $Healing,
      ];
    }

    /**
     * Determine whether or not the user can move.
     * @param string $Side
     */
    public function CanUserMove
    (
      string $Side
    )
    {
      switch ( $Side )
      {
        case 'Ally':
          $Attacker = $_SESSION['EvoChroniclesRPG']['Battle']['Ally']->Active;
          break;
        case 'Foe':
          $Attacker = $_SESSION['EvoChroniclesRPG']['Battle']['Foe']->Active;
          break;
      }

      if ( $Attacker->HasStatus('Freeze') )
      {
        if
        (
          $this->HasFlag('defrost') ||
          $this->Weather == 'Harsh Sunlight'
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
          'Type' => 'Error',
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
        if ( $Attacker->Ability->Name == 'Steadfast' && $Attacker->Stats['Speed']->Stage < 6 )
        {
          $Attacker->Stats['Speed']->SetValue(1);
          $Ability_Text = "{$Attacker->Display_Name}'s Steadfast increased its Speed!<br />";
        }

        return [
          'Type' => 'Error',
          'Text' => "{$Attacker->Display_Name} was flinched.<br />" .
                    (!empty($Ability_Text) ? $Ability_Text : ''),
        ];
      }

      if ( $Attacker->HasStatus('Confusion') )
      {
        if ( mt_rand(1, 3) !== 1 )
        {
          return [
            'Type' => 'Error',
            'Text' => "{$Attacker->Display_Name} hurt itself in confusion.<br />",
          ];
        }
      }

      if ( $Attacker->HasStatus('Infatuation') )
      {
        if ( mt_rand(1, 2) !== 1 )
        {
          return [
            'Type' => 'Error',
            'Text' => "{$Attacker->Display_Name} is immobilized by love.<br />",
          ];
        }
      }

      return [
        'Type' => 'Success',
        'Text' => ''
      ];
    }

    /**
     * Determine if the move will hit.
     * @param string $Side
     * @param int $Move_Effectiveness
     */
    public function DoesMoveHit
    (
      string $Side,
      int $Move_Effectiveness
    )
    {
      if ( $this->Accuracy === 0 )
        return false;

      if ( $this->Accuracy === 'None' )
        return true;

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

      if ( isset($_SESSION['EvoChroniclesRPG']['Battle']['Turn_Data']['Turn_' . $this->Turn_ID]['First_Attacker']) )
        $Turn_First_Attacker = $_SESSION['EvoChroniclesRPG']['Battle']['Turn_Data']['Turn_' . $this->Turn_ID]['First_Attacker'];
      else
        $Turn_First_Attacker = $Side;

      if ( $Defender->Ability == 'Wonder Guard' && $Move_Effectiveness < 2 )
        return false;

      if ( $this->Effect_Short == 'Causes a one-hit KO.' )
      {
        if ( $Attacker->Level < $Defender->Level )
          return false;
        else if ( $Attacker->Stats['Speed']->Current_Value < $Defender->Stats['Speed']->Current_Value )
          return false;
        else if ( $Attacker->HasStatusFromArray(['No Guard', 'Lock-On']) )
          return true;
        else if ( $Defender->HasStatus('Semi-Invulnerable') )
          return false;
        else if ( $Attacker->Level > $Defender->Level + 70 )
          return true;
        else
        {
          $Level_Diff = 30;
          if ( $this->Name == 'Sheer Cold' )
            if ( $Attacker->Primary_Type != 'Ice' || $Attacker->Secondary_Type != 'Ice' )
              $Level_Diff = 20;

          return mt_rand(1, ($Attacker->Level - $Defender->Level + $Level_Diff)) === 1;
        }
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

        case 'Stomp':
          if ( $Defender->Evasion > 1  && !$Defender->HasStatus('Semi-Invulnerable') )
            return true;
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

      if ( $Defender->HasItem(['Ring Target']) && (!$Defender->HasTyping(['Grass']) && !$this->HasFlag('powder')) )
        return true;

      if ( $Defender->Ability->Name == 'Levitate' && !$Defender->IsGrounded() && $this->Move_Type == 'Ground' )
        return false;

      if ( $Defender->Ability->Name == 'Wonder Skin' && $this->Damage_Type == 'Status' )
        $this->Accuracy = 50;

      if ( $Defender->HasItem(['Bright Powder', 'Lax Incense']) )
        $this->Accuracy *= 0.9;

      if ( $Attacker->HasItem(['Zoom Lens']) && $Turn_First_Attacker != $Side )
        $this->Accuracy *= 1.2;

      if ( $Attacker->Ability->Name == 'Compound Eyes' )
        $this->Accuracy *= 1.3;

      if ( $Defender->Ability->Name == 'Sand Veil' && !empty($this->Weather) && $this->Weather->Name == 'Sandstorm' )
        $this->Accuracy *= 0.8;

      if ( $Defender->Ability->Name == 'Snow Cloak' && !empty($this->Weather) && $this->Weather->Name == 'Hail' )
        $this->Accuracy *= 0.8;

      if ( $Attacker->Ability->Name == 'Hustle' && $this->Damage_Type == 'Physical' )
        $this->Accuracy *= 0.8;

      if ( $Defender->Ability->Name == 'Tangled Feet' && $Defender->HasStatus('Confusion') )
        $this->Accuracy *= 0.5;

      if ( $Attacker->Ability->Name == 'Victory Star' )
        $this->Accuracy *= 1.1;

      $Accuracy_Mod = $Attacker->Stats['Accuracy']->Current_Value / $Defender->Stats['Evasion']->Current_Value;

      if ( mt_rand(1, 100) < $this->Accuracy * $Accuracy_Mod )
        return true;

      return false;
    }

    /**
     * Determine if the move will crit.
     * @param string $Side
     */
    public function DoesMoveCrit
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

      if ( in_array($Defender->Ability->Name, ['Battle Armor', 'Shell Armor']) )
        return false;

      if ( $Attacker->Ability->Name == 'Merciless' )
        if ( $Defender->HasStatus('Poisoned') )
          return true;

      if ( $Attacker->HasStatus('Focus Energy') )
        $this->Crit_Chance += 2;

      if ( $Attacker->Ability->Name == 'Super Luck' )
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

      if ( $Attacker->Critical_Hit_Boost > 0 )
        $this->Crit_Chance += $Attacker->Critical_Hit_Boost;

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
     * Determine if the move makes physical contact.
     * @param string $Side
     */
    public function DoesMoveMakeContact
    (
      string $Side
    )
    {
      switch ( $Side )
      {
        case 'Ally':
          $Defender = $_SESSION['EvoChroniclesRPG']['Battle']['Foe']->Active;
          break;
        case 'Foe':
          $Defender = $_SESSION['EvoChroniclesRPG']['Battle']['Ally']->Active;
          break;
      }

      if ( $Defender->HasStatus('Substitute') )
        return false;

      return $this->hasFlag('contact');
    }

    /**
     * @param string $Side
     */
    public function HandleContact
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

      if ( $Defender->HasStatus('Substitute') )
      {
        return [
          'Text' => "<br />It hit {$Defender->Display_Name}'s Substitute!",
        ];
      }

      if ( $Defender->Last_Move['Name'] == 'Baneful Bunker' )
      {
        if
        (
          $Attacker->Item->Name != 'Protective Pads' &&
          (
            !$Attacker->HasTyping(['Poison', 'Steel']) ||
            $Attacker->Ability->Name != 'Immunity'
          )
        )
        {
          $Text = "<br />{$Attacker->Display_Name} was poisoned from the contact!";

          $Attacker->SetStatus('Poison');
        }

        return [
          'Text' => "<br />{$Defender->Display_Name} was protected by it's Baneful Bunker!" .
                    (isset($Text) ? $Text : ''),
          'Damage' => 0
        ];
      }

      if ( $Defender->Last_Move['Name'] == 'Beak Blast')
        if ( $Defender->HasStatus('Charging') )
          if ( $Attacker->Item->Name != 'Protective Pads' )
            $Attacker->SetStatus('Burn');

      if ( $Defender->Last_Move['Name'] == "King's Shield" )
      {
        if ( $this->Damage_Type != 'Status' )
        {
          if ( $Attacker->Item->Name != 'Protective Pads' )
          {
            $Attacker->Stats['Attack']->SetValue(-2);
            $Effect_Text = "{$Attacker->Display_Name}'s Attack harshly dropped!<br />";
          }

          return [
            'Text' => "
              <br />
              {$Defender->Display_Name} was protected from the attack!<br />" .
              (isset($Effect_Text) ? $Effect_Text : ''),
          ];
        }
      }

      if ( $Defender->Last_Move['Name'] == 'Obstruct' )
      {
        if ( $this->Damage_Type != 'Status' )
        {
          if ( $Attacker->Item->Name != 'Protective Pads' )
          {
            $Attacker->Stats['Defense']->SetValue(-2);
            $Effect_Text = "{$Attacker->Display_Name}'s Defense harshly dropped!<br />";

          }

          return [
            'Text' => "
              <br />
              {$Defender->Display_Name} was protected from the attack!<br />" .
            (isset($Effect_Text) ? $Effect_Text : ''),
          ];
        }
      }

      if ( $Defender->Last_Move['Name'] == 'Spiky Shield' )
      {
        if ( $Attacker->Item->Name != 'Protective Pads' )
        {
          $Attacker->DecreaseHP(floor($Attacker->Max_HP / 8));
          $Effect_Text = "{$Attacker->Display_Name}'s was hurt by the foe's Spiky Shield!<br />";
        }

        return [
          'Text' => "
            <br />
            {$Defender->Display_Name} was protected from the attack!<br />" .
            (isset($Effect_Text) ? $Effect_Text : ''),
        ];
      }

      if ( $Defender->HasItem(['Sticky Barb']) && empty($Attacker->Item) )
      {
        $Attacker->Item = $Defender->Item;
        $Defender->Item = null;
      }
    }

    /**
     * Determine how effective the move was.
     * @param {PokemonHandler} $Used_By
     * @param {PokemonHandler} $Used_Against
     */
    public function MoveEffectiveness
    (
      PokemonHandler $Used_By,
      PokemonHandler $Used_Against
    )
    {
      if
      (
        $Used_By->Ability->Name == 'Scrappy' &&
        $Used_Against->HasTyping(['Ghost']) &&
        in_array($this->Move_Type, ['Fighting', 'Normal'])
      )
      {
        return [
          'Mult' => 1,
          'Text' => ''
        ];
      }

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
      if ( !$Type_1_Mult )
        $Primary_Mult = 1;
      else
        $Primary_Mult = $Type_Chart[$Move_Type][$Type_1_Mult];

      $Type_2_Mult = array_search($Used_Against->Secondary_Type, $Types);
      if ( !$Type_2_Mult )
        $Secondary_Mult = 1;
      else
        $Secondary_Mult = $Type_Chart[$Move_Type][$Type_2_Mult];

      $Effective_Mult = $Primary_Mult * $Secondary_Mult;

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
     * Handle move applied stat changes.
     * @param $Target
     * @param {PokemonHandler} $Attacker
     * @param {PokemonHandler} $Defender
     * @param {boolean} $Does_Move_Crit
     * @return {$string} $Stat_Change_Text
     */
    public function ProcessStatChanges
    (
      $Target,
      PokemonHandler $Attacker,
      PokemonHandler $Defender,
      bool $Does_Move_Crit
    )
    {
      $Stat_Change_Text = '';

      if ( $this->Stat_Chance > 0 )
      {
        if ( mt_rand(1, 100) <= $this->Stat_Chance )
        {
          $Total_Stats = 0;

          foreach (['Attack', 'Defense', 'Sp_Attack', 'Sp_Defense', 'Speed', 'Accuracy', 'Evasion'] as $Index => $Stat)
          {
            $Stat_Boost = $Stat . '_Boost';
            if ( empty($this->$Stat_Boost) )
              continue;

            $Total_Stats++;
          }

          foreach (['Attack', 'Defense', 'Sp_Attack', 'Sp_Defense', 'Speed', 'Accuracy', 'Evasion'] as $Index => $Stat)
          {
            $Stat_Boost = $Stat . '_Boost';
            if ( empty($this->$Stat_Boost) )
              continue;

            if ( $Target->Active->Ability->Name == 'Contrary' )
            {
              if ( $this->$Stat_Boost < 0 )
                $this->$Stat_Boost *= 1;
              else
                $this->$Stat_Boost *= -1;
            }

            if
            (
              $this->Target == 'Foe' &&
              $this->IsFieldEffectActive('Foe', 'Mist') &&
              $Attacker->Ability->Name != 'Infiltrator'
            )
            {
              $Stat_Change_Text .= 'But it failed due to the Mist!';

              break;
            }

            $Stat_Name = str_replace('_', 'ecial ', $Stat);

            if ( $this->$Stat_Boost < 0 )
            {
              if ( $Target->Active->HasStatus('Guard Spec') )
                continue;

              if ( $Target->Active->Ability->Name == 'Full Metal Body' && $Target->Active != $Attacker )
                continue;

              if ( $Target->Active->HasAbility(['Clear Body', 'White Smoke']) && !$Attacker->HasAbility(['Mold Breaker', 'Teravolt', 'Turboblaze']) )
                continue;

              if ( $Target->Active->Ability->Name == 'Hyper Cutter' && !$Attacker->HasAbility(['Mold Breaker', 'Teravolt', 'Turboblaze']) && $Stat_Name == 'Attack' )
                continue;

              if ( $Target->Active->Ability->Name == 'Big Pecks' && !$Attacker->HasAbility(['Mold Breaker', 'Teravolt', 'Turboblaze']) && $Stat_Name == 'Defense' )
                continue;

              if ( $Target->Active->Ability->Name == 'Keen Eye' && !$Attacker->HasAbility(['Mold Breaker', 'Teravolt', 'Turboblaze']) && $Target->Active != $Attacker && $Stat_Name == 'Evasion' )
                continue;

              if ( $Target->Active->Ability->Name == 'Mirror Armor' )
              {
                if ( $Target->Active == $Attacker )
                  $Target->Active = $Defender;
                else
                  $Target->Active = $Attacker;
              }

              if ( $Target->Active->HasAbility([ 'Competitive', 'Defiant' ]) && $Target->Active != $Attacker )
              {
                switch ($Target->Active->Ability->Name)
                {
                  case 'Competitive':
                    $Boosted_Stat = [
                      'Stat' => 'Sp_Attack',
                      'Name' => 'Special Attack'
                    ];
                    break;

                  case 'Defiant':
                    $Boosted_Stat =  [
                      'Stat' => 'Attack',
                      'Name' => 'Attack'
                    ];
                    break;
                }

                if ( $Target->Active->Stats[$Boosted_Stat['Stat']]->Stage < 6 )
                {
                  $Target->Active->Stats[$Boosted_Stat['Stat']]->SetValue(2);
                  $Stat_Change_Text .= "{$Target->Active->Display_Name}'s {$Target->Active->Ability->Name} boosted its {$Boosted_Stat['Name']}!";
                }
              }
            }

            if
            (
              $Target->Active->Stats[$Stat]->Stage < 6 &&
              $Target->Active->Stats[$Stat]->Stage > -6
            )
            {
              $Stages = 0;
              if ( $Target->Active->Ability->Name == 'Simple' )
                $Stages = $this->$Stat_Boost * 2;
              else
                $Stages = $this->$Stat_Boost;

              $Target->Active->Stats[$Stat]->SetValue($Stages);

              if ( $this->$Stat_Boost > 0 )
                $Stat_Change_Text .= "{$Target->Active->Display_Name}'s {$Stat_Name} rose sharply!";
              else
                $Stat_Change_Text .= "{$Target->Active->Display_Name}'s {$Stat_Name} harshly dropped!";
            }
            else
            {
              if ( $Target->Active->Stats[$Stat]->Stage >= 6 )
                $Stat_Change_Text .= "{$Target->Active->Display_Name}'s {$Stat_Name} can't go any higher!";
              else
                $Stat_Change_Text .= "{$Target->Active->Display_Name}'s {$Stat_Name} can't go any lower!";
            }

            if ( $Index < $Total_Stats )
              $Stat_Change_Text .= '<br />';
          }
        }
      }

      if ( $Defender->Ability->Name == 'Anger Point' && $Does_Move_Crit )
      {
        $Defender->Stats['Attack']->SetStage(6);

        $Stat_Change_Text .= "{$Defender->Display_Name}'s Anger Point maximized its Attack!";
      }

      return $Stat_Change_Text;
    }

    /**
     * Handle move applied status ailments.
     * @param $Target
     * @param {PokemonHandler} $Attacker
     * @param {PokemonHandler} $Defender
     * @param {string} $Turn_First_Attacker
     * @return {$string}
     */
    public function ProcessAilments
    (
      $Target,
      PokemonHandler $Attacker,
      PokemonHandler $Defender,
      string $Turn_First_Attacker
    )
    {
      if ( !empty($this->Ailment) )
      {
        $Ailment_Chance = mt_rand(1, 100);

        if ( $this->Effect_Chance == 'None' )
          $this->Effect_Chance = 100;

        if ( $Target->Active->HasStatus('Substitute') )
        {
          return 'But it failed!';
        }

        if ( $Target->Active->Ability->Name == 'Leaf Guard' )
        {
          if ( !empty($this->Weather) && in_array($this->Weather->Name, ['Extremely Harsh Sunlight', 'Harsh Sunlight']) )
          {
            return 'But it failed!';
          }
        }

        if ( $Target->Active->Ability->Name == 'Overcoat' && $this->HasFlag('powder') )
        {
          return 'But it failed!';
        }

        if ( $Target->Active->Ability->Name == 'Shield Dust' && $this->Damage_Type != 'Status' )
          return;

        if ( $Attacker->Ability->Name == 'Serene Grace' && $this->Damage_Type != 'Status' )
        {
          $Flinch_Chance = 20;
          $this->Effect_Chance *= 2;
        }

        switch ($this->Ailment)
        {
          case 'None':
            break;

          case 'Flinch':
            if ( $Turn_First_Attacker == $Attacker->Side && $Ailment_Chance <= $this->Effect_Chance )
            {
              if ( $Target->Active->Ability->Name == 'Inner Focus' && !$Attacker->HasAbility(['Mold Breaker', 'Teravolt', 'Turboblaze']) )
                return "{$Target->Active->Ability->Name} won't flinch because of its Inner Focus!";

              $Set_Status = $Target->Active->SetStatus($this->Ailment);
              return;
            }
            break;

          case 'Infatuation':
            if ( $Ailment_Chance <= $this->Effect_Chance )
            {
              $Set_Status = $Target->Active->SetStatus($this->Ailment);
              if ( !empty($Set_Status) )
              {
                $Status_Dialogue = $Set_Status->Dialogue;
                if ( $Target->Active->HasItem(['Destiny Knot']) && !$Attacker->HasStatus($this->Ailment) )
                {
                  $Sync_Status = $Attacker->SetStatus($this->Ailment);
                  if ( !empty($Sync_Status) )
                  {
                    $Status_Dialogue .= $Sync_Status->Dialogue;
                  }
                }

                return $Status_Dialogue;
              }
            }
            break;

          case 'Burn':
            if ( $Target->Active->HasAbility(['Magma Armor', 'Water Bubble', 'Water Veil']) && !$Attacker->HasAbility(['Mold Breaker', 'Teravolt', 'Turboblaze']) )
              return 'But it failed!';

          case 'Freeze':
            if ( !empty($this->Weather) && strpos($this->Weather->Name, 'Harsh Sunlight') )
              return 'But it failed!';

          case 'Paralysis':
            if ( $Target->Active->Ability->Name == 'Limber' && !$Attacker->HasAbility(['Mold Breaker', 'Teravolt', 'Turboblaze']) )
              return 'But it failed!';

            if ( $Target->Active->HasTyping([ 'Electric' ]) )
              return 'But it failed!';

          case 'Badly Poisoned':
          case 'Poison':
            if
            (
              $Target->Active->HasTyping([ 'Poison', 'Steel' ]) ||
              ( $Target->Active->HasTyping([ 'Poison', 'Steel' ]) && $Attacker->Ability->Name != 'Corrosion' && $this->Damage_Type != 'Status' ) ||
              $Target->Active->HasAbility(['Immunity', 'Pastel Veil'])
            )
            {
              return 'But it failed!';
            }

          case 'Sleep':
            if ( $Target->Active->HasAbility(['Insomnia', 'Sweet Veil', 'Vital Spirit']) )
            {
              return 'But it failed!';
            }

          default:
            if ( $Ailment_Chance <= $this->Effect_Chance )
            {
              $Set_Status = $Target->Active->SetStatus($this->Ailment);
              if ( !empty($Set_Status) )
              {
                $Status_Dialogue = $Set_Status->Dialogue;
                if ( $Target->Active->Ability == 'Synchronize' && $Target->Active != $Attacker && !$Attacker->HasStatus($this->Ailment) )
                {
                  $Sync_Status = $Attacker->SetStatus($this->Ailment);
                  if ( !empty($Sync_Status) )
                  {
                    $Status_Dialogue .= $Sync_Status->Dialogue;
                  }
                }

                return $Status_Dialogue;
              }
              else
              {
                return 'But it failed!';
              }
            }
            else
            {
              return 'But it failed!';
            }
          break;
        }
      }
      else
      {
        if ( empty($Flinch_Chance) )
          $Flinch_Chance = 10;

        if
        (
          $this->HasFlag('Kings_Rock') &&
          ( $Attacker->HasItem(["King's Rock", 'Razor Fang']) || $Attacker->HasAbility(['Stench']) ) &&
          $Turn_First_Attacker == $Attacker->Side &&
          !$Defender->HasStatus('Substitute') &&
          mt_rand(1, 100) <= $Flinch_Chance
        )
        {
          $Target->Active->SetStatus('Flinch');
          return;
        }
      }
    }

    /**
     * Handle ability procs at the end of the Pokemon's move.
     * @param {PokemonHandler} $Attacker
     * @param {PokemonHandler} $Defender
     * @param {int} $Hit
     * @param {int} $Total_Hits
     * @param {int} $Damage
     * @return {$array} $Ability_Effect
     */
    public function ProcessAbilityProcs
    (
      PokemonHandler $Attacker,
      PokemonHandler $Defender,
      bool $Mid_Hit = false,
      int $Hit = 1,
      int $Total_Hits = 1,
      int $Damage = 0
    )
    {
      $Ability_Effect_Text = '';

      switch ($Mid_Hit)
      {
        case true:
          switch ($Defender->Ability->Name)
          {
            case 'Aftermath':
              if ( $Attacker->Ability->Name != 'Damp' && $Attacker->Item->Name != 'Protective Pads' )
              {
                if ( $Defender->HP <= 0 )
                {
                  $Attacker->DecreaseHP($Attacker->Max_HP / 4);
                  $Ability_Effect_Text .= "{$Attacker->Display_Name}'s took damage from the Aftermath!<br />";
                }
              }
              break;

            case 'Disguise':
              if ( $Damage > 0 )
              {
                $Set_Disguise = $Defender->SetStatus('Busted');
                if ( !empty($Set_Disguise) )
                {
                  $Ability_Effect_Text .= "{$Defender->Display_Name}'s {$Set_Disguise['Text']}";
                  $Ability_Effect_Damage = 0;
                }
              }
              break;

            case 'Flame Body':
              if ( $this->HasFlag('contact') && mt_rand(1, 100) <= 30 )
              {
                $Set_Ailment = $Defender->SetStatus('Burn');
                if ( !empty($Set_Ailment) )
                {
                  $Ability_Effect_Text .= "{$Attacker->Display_Name} {$Set_Ailment['Text']}";
                }
              }
              break;

            case 'Gooey':
              if ( $this->HasFlag('contact') )
              {
                $Attacker_Speed = $Attacker->Stats['Speed'];
                if ( $Attacker_Speed->Stage > -6 )
                {
                  $Attacker_Speed->SetValue(-1);
                  $Ability_Effect_Text .= "{$Attacker->Display_Name}'s Speed was dropped by {$Defender->Display_Name}'s Gooey!";
                }
              }
              break;

            case 'Illusion':
              if ( !$Defender->Ability->Procced && $Damage > 0 )
              {
                $Defender->Ability->SetProcStatus(true);
                $Defender->RevertCopy();

                $Ability_Effect_Text .= "{$Defender->Display_Name}'s Illusion broke!";
              }
              break;

            case 'Iron Barbs':
            case 'Rough Skin':
              if ( $this->HasFlag('contact') )
              {
                $Attacker->DecreaseHP($Attacker->Max_HP / 8);
                $Ability_Effect_Text .= "{$Attacker->Display_Name} took damage from {$Defender->Display_Name}'s Iron Barbs!<br />";
              }
              break;

            case 'Justified':
              if ( $this->Move_Type == 'Dark' )
              {
                if ( $Defender->Stats['Attack']->Stage < 6 )
                {
                  $Defender->Stats['Attack']->SetValue(1);
                  $Ability_Effect_Text .="{$Defender->Display_Name}'s Justified raised its Attack!<br />";
                }
              }
              break;

            case 'Lightning Rod':
              if ( $this->Move_Type == 'Electric' )
              {
                if ( $Defender->Stats['Sp_Attack']->Stage < 6 )
                {
                  $Defender->Stats['Sp_Attack']->SetValue(1);
                  $Ability_Effect_Text .= "{$Defender->Display_Name}'s Lightning Rod boosted its Special Attack!<br />";
                }

                $Ability_Effect_Damage = 0;
              }
              break;

            case 'Liquid Ooze':
              if ( !empty($this->Drain) && $this->Drain > 0 && $Damage > 0 )
              {
                $Attacker->DecreaseHP($Damage);
                $Ability_Effect_Text .= "{$Attacker->Display_Name} took damage from the Liquid Ooze!";
                $Ability_Effect_Damage = 0;
              }
              break;

            case 'Mummy':
              if ( $this->HasFlag('contact') && !$Attacker->Ability->Name != 'Mummy' )
              {
                $Attacker->SetAbility('Mummy');
                $Ability_Effect_Text .= "{$Attacker->Display_Name}'s ability became Mummy!";
              }
              break;

            case 'Parental Bond':
              if ( $this->Total_Hits === 2 && $this->Hit === 2 )
              {
                $Damage *= 0.25;
              }
              break;

            case 'Poison Point':
              if ( $this->HasFlag('contact') && mt_rand(1, 100) <= 30 )
              {
                $Set_Ailment = $Attacker->SetStatus('Poison');
                if ( !empty($Set_Ailment) )
                {
                  $Ability_Effect_Text .= "{$Attacker->Display_Name} {$Set_Ailment['Text']}";
                }
              }
              break;

            case 'Poison Touch':
              if ( $this->HasFlag('contact') && mt_rand(1, 100) <= 30 )
              {
                $Set_Ailment = $Defender->SetStatus('Poison');
                if ( !empty($Set_Ailment) )
                {
                  $Ability_Effect_Text .= "{$Defender->Display_Name} {$Set_Ailment['Text']}";
                }
              }
              break;

            case 'Rattled':
              if ( in_array($this->Move_Type, ['Bug', 'Dark', 'Ghost']) && $Damage > 0 )
              {
                if ( $Defender->Stats['Speed']->Stage < 6 )
                {
                  $Defender->Stats['Speed']->SetValue(1);
                  $Ability_Effect_Text .= "{$Defender->Display_Name}'s Rattled boosted its Speed!<br />";
                }
              }
              break;

            case 'Stamina':
              if ( $Defender->Stats['Defense']->Stage < 6 )
              {
                $Defender->Stats['Defense']->SetValue(1);
                $Ability_Effect_Text .= "{$Defender->Display_Name}'s Rattled boosted its Defense!<br />";
              }
              break;

            case 'Static':
              if ( $Attacker->Item->Name != 'Protective Pads' )
              {
                if ( mt_rand(1, 100) <= 30 )
                {
                  $Attacker->SetStatus('Paralysis');
                  $Ability_Effect_Text .= "{$Attacker->Display_Name} was paralyzed!<br />";
                }
              }
              break;

            case 'Sturdy':
              if ( $Defender->HP == $Defender->Max_HP && $Damage >= $Defender->HP )
              {
                $Damage = $Defender->HP - 1;
                $Ability_Effect_Text .= "{$Defender->Display_Name}'s Sturdy!<br />";
              }
              break;

            case 'Tangling Hair':
              if ( $this->HasFlag('contact') && $Attacker->Stats['Speed']->Stage > -6 )
              {
                $Attacker->Stats['Speed']->SetValue(-1);
                $Ability_Effect_Text .= "{$Attacker->Display_Name}'s Speed dropped from {$Defender->Display_Name}'s Tangling Hair!<br />";
              }
              break;

            case 'Water Compaction':
              if ( $this->Move_Type == 'Water' && $Defender->Stats['Defense']->Stage < 6 )
              {
                $Defender->Stats['Defense']->SetValue(2);
                $Ability_Effect_Text .= "{$Defender->Display_Name}'s Defense rose from its Water Compaction!<br />";
              }
              break;

            case 'Weak Armor':
              if ( $this->Damage_Type == 'Physical' )
              {
                foreach (['Defense', 'Speed'] as $Stat)
                {
                  if ( $Stat == 'Defense' )
                    $Stat_Mod = -1;
                  else
                    $Stat_Mod = 2;

                  if ( $Attacker->Stats[$Stat]->Stage > -6 && $Attacker->Stats[$Stat]->Stage < 6 )
                    $Attacker->Stats[$Stat]->SetValue($Stat_Mod);
                }
              }
              break;
          }
          break;

        case false;
          switch ($Defender->Ability->Name)
          {
            case 'Berserk':
              if ( $Defender->Stats['Sp_Attack']->Stage < 6 )
              {
                if ( $Defender->HP <= $Defender->Max_HP / 2 && !$Defender->Ability->Procced )
                {
                  $Defender->Ability->SetProcStatus(true);

                  $Defender->Stats['Sp_Attack']->SetValue(1);
                  $Ability_Effect_Text .= "{$Defender->Display_Name}'s Berserk rose its Special Attack!";
                }
              }
              break;

            case 'Color Change':
              if
              (
                !$Defender->HasTyping([ $this->Move_Type ]) &&
                $Defender->HasStatus("Forest's Curse") && $this->Move_Type != 'Grass' &&
                $Defender->HasStatus("Trick-or-Treat") && $this->Move_Type != 'Ghost'
              )
              {
                $Defender->Primary_Type = $this->Move_Type;
                $Defender->Secondary_Type = null;

                $Ability_Effect_Text .= "{$Defender->Display_Name}'s Color Change made it the {$this->Move_Type}-type!";
              }
              break;

            case 'Cotton Down':
              if ( $Attacker->Stats['Speed']->Stage > -6 )
              {
                $Attacker->Stats['Speed']->SetValue(-1);
                $Ability_Effect_Text .= "<br />{$Defender->Display_Name}'s Cotton Down dropped {$Attacker->Display_Name}'s Speed!";
              }
              break;

            case 'Cursed Body':
              if
              (
                !$Defender->HasStatus('Substitute') &&
                mt_rand(1, 100) <= 30 &&
                $Damage > 0
              )
              {
                $Attacker->Moves[$Attacker->Last_Move['Slot']]->Disable();

                $Ability_Effect_Text .= "{$Attacker->Display_Name}'s was disabled due to {$Defender->Display_Name}'s Cursed Body!";
              }
              break;
            case 'Cute Charm':
              for ( $i = 0; $i <= $Total_Hits; $i++ )
              {
                if ( mt_rand(1, 100) <= 30 && $Attacker->Gender != 'G' && $Attacker->Gender != $Defender->Gender )
                {
                  $Set_Status = $Attacker->SetStatus('Infatuation');
                  if ( !empty($Set_Status) )
                    $Ability_Effect_Text .= $Set_Status->Dialogue;
                }
              }
              break;

            case 'Defeatist':
              if ( $Defender->HP <= $Defender->Max_HP / 2 && !$Defender->Ability->Procced )
              {
                $Defender->Ability->SetProcStatus(true);

                foreach (['Attack', 'Sp_Attack'] as $Stat)
                {
                  if
                  (
                    $Defender->Stats[$Stat]->Stage < 6 &&
                    $Defender->Stats[$Stat]->Stage > -6
                  )
                  {
                    $Defender->Stats[$Stat]->CurrentValue *= 0.5;
                  }
                }

                $Ability_Effect_Text .= "{$Defender->Display_Name}'s Defeatist lowered its stats!";
              }
              break;

            case 'Dry Skin':
              if ( $this->Move_Type == 'Water' )
                $Defender->IncreaseHP($Defender->Max_HP / 4);
              if ( $this->Move_Type == 'Fire' )
                $Damage *= 1.25;
              break;
            case 'Effect Spore':
              if ( $this->HasFlag('contact') )
              {
                for ( $i = 0; $i < $Total_Hits; $i++ )
                {
                  $Random_Int = mt_rand(1, 100);
                  if ( $Random_Int <= 9 )
                    $Ailment = 'Poison';
                  else if ( $Random_Int <= 21 )
                    $Ailment = 'Paralysis';
                  else if ( $Random_Int <= 30 )
                    $Ailment = 'Sleep';

                  if ( !empty($Ailment) )
                  {
                    $Set_Ailment = $Defender->SetStatus($Ailment);
                    if ( !empty($Set_Ailment) )
                    {
                      $Ability_Effect_Text .= "{$Attacker->Display_Name} {$Set_Ailment['Text']}";
                    }
                  }
                }
              }
              break;
            case 'Emergency Exit':
              $Trainer = $_SESSION['EvoChroniclesRPG']['Battle'][$Defender->Side];
              if ( $Trainer->NextPokemon() && $Defender->HP <= $Defender->Max_HP / 2 )
              {
                // How do we handle swapping out mid turn?
                // Hmm
              }
              break;

            case 'Flash Fire':
              if ( $this->Move_Type == 'Fire' )
              {
                if ( !$Defender->Ability->Procced )
                {
                  $Defender->Ability->SetProcStatus(true);
                  $Ability_Effect_Text .= "{$Defender->Display_Name}'s Flash Fire absorbed the attack!";
                }
              }
              break;

            case 'Grim Neigh':
              if ( $Defender->HP <= 0 && $Attacker->Stats['Sp_Attack']->Stage < 6 )
              {
                $Attacker->Stats['Sp_Attack']->SetValue(1);
                $Ability_Effect_Text .= "{$Attacker->Display_Name}'s Grim Neigh boosted its Special Attack!";
              }
              break;

            case 'Motor Drive':
              if ( $this->Move_Type == 'Electric' )
              {
                if ( $Defender->Stats['Speed']->Stage < 6 )
                {
                  $Defender->Stats['Speed']->SetValue(1);
                  $Ability_Effect_Text .= "{$Defender->Display_Name} absorbed the attack and gained Speed due to its Motor Drive!";
                }

                $Damage = 0;
              }
              break;

            case 'Perish Body':
              if ( $Attacker->Ability->Name != 'Long Reach' && $Attacker->Item->Name != 'Protective Pads' && !$Attacker->HasStatus('Perish Body') )
              {
                $Attacker->SetStatus('Perish Body');
                $Defender->SetStatus('Perish Body');

                $Ability_Effect_Text .= "
                  <br />
                  {$Attacker->Display_Name} will perish in 3 turns.<br />
                  {$Defender->Display_Name} will perish in 3 turns.<br />
                ";
              }
              break;

            case 'Pickpocket':
              if ( !empty($Attacker->Item) && empty($Defender->Item) )
              {
                if ( $Attacker->Ability != 'Sticky Hold' || ($Attacker->Ability == 'Sticky Hold' && $Defender->HasAbility(['Mold Breaker', 'Teravolt', 'Turboblaze']) ) )
                {
                  $Ability_Effect_Text .= "{$Defender->Display_Name} Pickpocketed {$Attacker->Display_Name}'s {$Attacker->Item->Name}!";

                  $Defender->Item = $Attacker->Item;
                  unset($Attacker->Item);
                }
              }
              break;

            case 'Sand Spit':
              if ( empty($this->Weather) || (!empty($this->Weather) && $this->Weather->Name) )
              {
                if ( $Defender->Item->Name == 'Smooth Rock' )
                  $Turn_Count = 8;

                $Set_Weather = new Weather('Rain', !empty($Turn_Count) ?: 5);
                if ( $Set_Weather )
                {
                  $this->Weather[$Set_Weather->Name] = $Set_Weather;
                  $Ability_Effect_Text .= $Set_Weather->Dialogue;
                }
              }
              break;

            case 'Sap Sipper':
              if ( $this->Move_Type == 'Grass' )
              {
                if ( $Defender->Stats['Attack']->Stage < 6 )
                {
                  $Defender->Stats['Attack']->SetValue(1);
                  $Ability_Effect_Text .= "{$Defender->Display_Name}'s Sap Sipper boosted its Attack!";
                }

                $Ability_Effect_Damage = 0;
              }
              break;

            case 'Steam Engine':
              if ( in_array($this->Move_Type, ['Fire', 'Water']) )
              {
                if ( $Defender->Stats['Speed']->Stage < 6 )
                {
                  $Defender->Stats['Speed']->SetValue(1);
                  $Ability_Effect_Text .= "{$Defender->Display_Name}'s Steam Engine boosted its Speed!";
                }
              }
              break;

            case 'Volt Absorb':
              if ( $Defender->HP < $Defender->Max_HP && $this->Move_Type == 'Electric' )
              {
                $Defender->IncreaseHP($Defender->Max_HP / 4);
                $Ability_Effect_Damage = 0;
              }
              break;

            case 'Wandering Spirit':
              if ( $this->HasFlag('contact') )
              {
                if ( !in_array($Defender->Ability->Name, ['Disguise', 'Flower Gift', 'Gulp Missile', 'Ice Face', 'Illusion', 'Imposter', 'Receiver', 'RKS System', 'Schooling', 'Stance Change', 'Wonder Guard', 'Zen Mode']) )
                {
                  $Attacker_Ability = $Attacker->Ability->Name;
                  $Defender_Ability = $Defender->Ability->Name;

                  $Attacker->Ability->Name = $Defender_Ability;
                  $Defender->Ability->Name = $Attacker_Ability;

                  $Ability_Effect_Text .= "<br />{$Attacker->Display_Name} has swapped abilities with {$Defender->Display_Name}!<br />";
                }
              }
              break;

            case 'Water Absorb':
              if ( $Defender->HP < $Defender->Max_HP && $this->Move_Type == 'Water' )
              {
                $Defender->IncreaseHP($Defender->Max_HP / 4);
                $Ability_Effect_Damage = 0;
              }
              break;
          }

          switch ($Attacker->Ability->Name)
          {
            case 'Magician':
              if ( empty($Attacker->Item) && !empty($Defender->Item) && !$Defender->HasAbility(['Sticky Hold']) )
              {
                $Ability_Effect_Text .= "{$Attacker->Display_Name} magically stole {$Defender->Display_Name}'s {$Defender->Item->Name}!";

                $Attacker->Item = $Defender->Item;
                unset($Defender->Item);
              }
              break;
          }
          break;
      }

      return [
        'Text' => $Ability_Effect_Text,
        'Damage' => (!empty($Ability_Effect_Damage) ? $Ability_Effect_Damage : null),
      ];
    }

    /**
     * Handle item procs.
     * @param {PokemonHAndler} $Attacker
     * @param {PokemonHandler} $Defender
     * @param {int} $Damage
     */
    public function ProcessItemProcs
    (
      PokemonHandler $Attacker,
      PokemonHandler $Defender,
      float $Move_Effectiveness = 1,
      int $Damage = 0
    )
    {
      $Item_Proc_Text = '';

      if ( !empty($Defender->Item) )
      {
        switch ( $Defender->Item->Name )
        {
          case 'Absorb Bulb':
            if ( $this->Move_Type == 'Water' && $Defender->Stats['Sp_Attack']->Stage < 6 && $Defender->Stats['Sp_Attack'] > -6 )
            {
              $Defender->Item->Consume();

              if ( $Defender->Ability->Name == 'Contrary' )
                $Defender->Stats['Sp_Attack']->SetValue(-1);
              else
                $Defender->Stats['Sp_Attack']->SetValue(1);

              $Item_Proc_Text .= "{$Defender->Display_Name} absorbed the hit with its Absorb Bulb, and modified its Special Attack!";
            }
            break;

          case 'Apricot Berry':
            if
            (
              $this->Move_Type == 'Water' &&
              $Defender->Stats['Sp_Attack']->Stage < 6 &&
              $Defender->Stats['Sp_Attack']->Stage > -6
            )
            {
              $Defender->Item->Consume();

              if ( $Defender->HasAbility(['Contrary']) )
                $Defender->Stats['Sp_Attack']->SetValue(-1);
              else
                $Defender->Stats['Sp_Attack']->SetValue(1);

              $Item_Proc_Text .= "{$Defender->Display_Name}'s ate its Maranga Berry and modified its Special Attack!";
            }
            break;

          case 'Aguav Berry':
            if
            (
              $Defender->HP >= $Defender->Max_HP / 4 &&
              $Defender->HP - $Damage <= $Defender->Max_HP / 4
            )
            {
              $Defender->Item->Consume();
              $Defender->IncreaseHP(floor($Defender->Max_HP / 3));

              $Item_Proc_Text .= "{$Defender->Display_Name} ate its Mago Berry and regained HP!";

              if ( $Defender->HasNature(['Lax', 'Naive', 'Naughty', 'Rash']) )
              {
                $Set_Confusion = $Defender->SetStatus('Confusion');
                if ( !empty($Set_Confusion) )
                  $Item_Proc_Text .= "<br />{$Set_Confusion['Dialogue']}";
              }
            }
            break;

          case 'Aspear Berry':
            if ( $Defender->HasStatus('Freeze') )
            {
              $Defender->Item->Consume();
              $Defender->RemoveStatus('Freeze');

              $Item_Proc_Text .= "{$Defender->Display_Name} ate its Aspear Berry and thawed out!";
            }
            break;

          case 'Berry Juice':
            if
            (
              $Defender->HP >= $Defender->Max_HP / 2 &&
              $Defender->HP - $Damage <= $Defender->Max_HP / 2
            )
            {
              $Defender->Item->Consume();
              $Defender->IncreaseHP(20);

              $Item_Proc_Text .= "{$Defender->Display_Name} drank its Berry Juice and regained 20 HP!";
            }
            break;

          case 'Cell Battery':
            if ( $this->Move_Type == 'Electric' && $Defender->Stats['Attack']->Stage < 6 && $Defender->Stats['Attack'] > -6 )
            {
              $Defender->Item->Consume();

              if ( $Defender->Ability->Name == 'Contrary' )
                $Defender->Stats['Attack']->SetValue(-1);
              else
                $Defender->Stats['Attack']->SetValue(1);

              $Item_Proc_Text .= "{$Defender->Display_Name} absorbed the hit with its Absorb Bulb, and modified its Attack!";
            }
            break;

          case 'Cheri Berry':
            if ( $Defender->HasStatus('Paralysis') )
            {
              $Defender->Item->Consume();
              $Defender->RemoveStatus('Paralysis');

              $Item_Proc_Text .= "{$Defender->Display_Name} ate its Aspear Berry and was cured of its Paralysis!";
            }
            break;

          case 'Chesto Berry':
            if ( $Defender->HasStatus('Sleep') )
            {
              $Defender->Item->Consume();
              $Defender->RemoveStatus('Sleep');

              $Item_Proc_Text .= "{$Defender->Display_Name} ate its Aspear Berry and awoke from its slumber!";
            }
            break;

          case 'Enigma Berry':
            if ( $Move_Effectiveness > 1 )
            {
              $Defender->Item->Consume();
              $Defender->IncreaseHP(floor($Defender->Max_HP / 4));

              $Item_Proc_Text .= "{$Defender->Display_Name} consumed its Enigma Berry and restored HP!";
            }
            break;

          case 'Figy Berry':
            if ( $Defender->HP >= $Defender->Max_HP / 2 && $Defender->HP - $Damage <= $Defender->Max_HP )
            {
              $Defender->Item->Consume();
              $Defender->IncreaseHP($Defender->Max_HP / 8);

              $Item_Proc_Text .= "{$Defender->Display_Name} ate its Figy Berry and regained HP!";

              if ( $Defender->HasNature(['Bold', 'Calm', 'Modest', 'Timid']) )
              {
                $Set_Confusion = $Defender->SetStatus('Confusion');
                if ( !empty($Set_Confusion) )
                  $Item_Proc_Text .= "<br />{$Set_Confusion['Dialogue']}";
              }
            }
            break;

          case 'Focus Band':
            if ( $Defender->HP - $Damage <= 0 && mt_rand(1, 100) <= 10 )
            {
              $Defender->HP = 1;

              $Item_Proc_Text .= "{$Defender->Display_Name} hung on due to its Focus Band!";
            }
            break;

          case 'Focus Sash':
            if ( $Defender->HP == $Defender->Max_HP && $Defender->HP - $Damage <= 0 )
            {
              $Defender->HP = 1;

              $Item_Proc_Text .= "{$Defender->Display_Name} hung on due to its Focus Sash!";
            }
            break;

          case 'Ganlon Berry':
            if
            (
              $Defender->HP >= $Defender->Max_HP / 4 &&
              $Defender->HP - $Damage <= $Defender->Max_HP / 4 &&
              $Defender->Stats['Defense']->Stage < 6 &&
              $Defender->Stats['Defense']->Stage > -6
            )
            {
              $Defender->Item->Consume();

              if ( $Defender->HasAbility(['Contrary']) )
                $Defender->Stats['Defense']->SetValue(-1);
              else
                $Defender->Stats['Defense']->SetValue(1);

              $Item_Proc_Text .= "{$Defender->Display_Name} ate its Ganlon Berry and raised its Defense!";
            }
            break;

          case 'Iapapa Berry':
            if
            (
              $Defender->HP >= $Defender->Max_HP / 4 &&
              $Defender->HP - $Damage <= $Defender->Max_HP / 4
            )
            {
              $Defender->Item->Consume();
              $Defender->IncreaseHP(floor($Defender->Max_HP / 3));

              $Item_Proc_Text .= "{$Defender->Display_Name} ate its Iapapa Berry and regained HP!";

              if ( $Defender->HasNature(['Gentle', 'Hasty', 'Lonely', 'Mild']) )
              {
                $Set_Confusion = $Defender->SetStatus('Confusion');
                if ( !empty($Set_Confusion) )
                  $Item_Proc_Text .= "<br />{$Set_Confusion['Dialogue']}";
              }
            }
            break;

          case 'Jacoba Berry':
            if ( $this->Damage_Type == 'Physical' )
            {
              $Defender->Item->Consume();
              $Attacker->DecreaseHP(floor($Attacker->Max_HP / 8));

              $Item_Proc_Text .= "{$Defender->Display_Name} ate its Jacoba Berry and made {$Attacker->Display_Name} take damage!";
            }
            break;

          case 'Kee Berry':
            if
            (
              $this->Damage_Type == 'Physical' &&
              $Defender->Stats['Defense']->Stage < 6 &&
              $Defender->Stats['Defense']->Stage > -6
            )
            {
              $Defender->Item->Consume();

              if ( $Defender->HasAbility(['Contrary']) )
                $Defender->Stats['Defense']->SetValue(-1);
              else
                $Defender->Stats['Defense']->SetValue(1);

              $Item_Proc_Text .= "{$Defender->Display_Name} ate its Kee Berry and modified its Defense!";
            }
            break;

          case 'Lansat Berry':
            $Defender->Item->Consume();
            $Defender->Critical_Hit_Boost += 2;

            $Item_Proc_Text .= "{$Defender->Display_Name} ate its Lansat Berry and boosted its Critical Hit Ratio!";
            break;

          case 'Liechi Berry':
            if
            (
              $Defender->HP >= $Defender->Max_HP / 4 &&
              $Defender->HP - $Damage <= $Defender->Max_HP / 4 &&
              $Defender->Stats['Attack']->Stage < 6 &&
              $Defender->Stats['Attack']->Stage > -6
            )
            {
              $Defender->Item->Consume();

              if ( $Defender->HasAbility(['Contrary']) )
                $Defender->Stats['Attack']->SetValue(-1);
              else
                $Defender->Stats['Attack']->SetValue(1);

              $Item_Proc_Text .= "{$Defender->Display_Name} ate its Kee Berry and modified its Attack!";
            }
            break;

          case 'Lum Berry':
            foreach ( ['Burn', 'Freeze', 'Paralysis', 'Badly Poisoned', 'Poisoned', 'Sleep', 'Confusion'] as $Non_Volatile_Status )
            {
              if ( $Defender->HasStatus($Non_Volatile_Status) )
              {
                $Defender->Item->Consume();
                $Defender->RemoveStatus($Non_Volatile_Status);

                $Item_Proc_Text .= "{$Defender->Display_Name} ate its Lum Berry and was cured of its {$Non_Volatile_Status}!";
                break;
              }
            }
            break;

          case 'Luminous Moss':
            if
            (
              $this->Move_Type == 'Water' &&
              $Defender->Stats['Sp_Defense']->Stage < 6 &&
              $Defender->Stats['Sp_Defense']->Stage > -6
            )
            {
              $Defender->Item->Consume();

              if ( $Defender->HasAbility(['Contrary']) )
                $Defender->Stats['Sp_Defense']->SetValue(-1);
              else
                $Defender->Stats['Sp_Defense']->SetValue(1);

              $Item_Proc_Text .= "{$Defender->Display_Name}'s Luminous Moss modified its Special Defense!";
            }
            break;

          case 'Mago Berry':
            if
            (
              $Defender->HP >= $Defender->Max_HP / 4 &&
              $Defender->HP - $Damage <= $Defender->Max_HP / 4
            )
            {
              $Defender->Item->Consume();
              $Defender->IncreaseHP(floor($Defender->Max_HP / 3));

              $Item_Proc_Text .= "{$Defender->Display_Name} ate its Mago Berry and regained HP!";

              if ( $Defender->HasNature(['Brave', 'Quiet', 'Relaxed', 'Sassy']) )
              {
                $Set_Confusion = $Defender->SetStatus('Confusion');
                if ( !empty($Set_Confusion) )
                  $Item_Proc_Text .= "<br />{$Set_Confusion['Dialogue']}";
              }
            }
            break;

          case 'Maranga Berry':
            if
            (
              $this->Damage_Type == 'Special' &&
              $Defender->Stats['Sp_Defense']->Stage < 6 &&
              $Defender->Stats['Sp_Defense']->Stage > -6
            )
            {
              $Defender->Item->Consume();

              if ( $Defender->HasAbility(['Contrary']) )
                $Defender->Stats['Sp_Defense']->SetValue(-1);
              else
                $Defender->Stats['Sp_Defense']->SetValue(1);

              $Item_Proc_Text .= "{$Defender->Display_Name}'s ate its Maranga Berry and modified its Special Defense!";
            }
            break;

          case 'Mental Herb':
            foreach ( ['Disable', 'Encore', 'Heal Block', 'Infatuation', 'Taunt'] as $Status_Effect )
            {
              if ( $Defender->HasStatus($Status_Effect) )
              {
                $Defender->Item->Consume();
                $Defender->RemoveStatus($Status_Effect);

                $Item_Proc_Text .= "{$Defender->Display_Name} consumed its Mental Herb and cured its {$Status_Effect}.";
                break;
              }
            }
            break;

          case 'Oran Berry':
            if
            (
              $Defender->HP >= $Defender->Max_HP / 2 &&
              $Defender->HP - $Damage <= $Defender->Max_HP / 2
            )
            {
              $Defender->Item->Consume();
              $Defender->IncreaseHP(10);

              $Item_Proc_Text .= "{$Defender->Display_Name} drank its Berry Juice and regained 10 HP!";
            }
            break;

          case 'Pecha Berry':
            foreach ( ['Badly Poisoned', 'Poison'] as $Status_Effect )
            {
              if ( $Defender->HasStatus($Status_Effect) )
              {
                $Defender->Item->Consume();
                $Defender->RemoveStatus($Status_Effect);

                $Item_Proc_Text .= "{$Defender->Display_Name} ate its Pecha Berry and was healed of its {$Status_Effect}!";
                break;
              }
            }
            break;

          case 'Persim Berry':
            if ( $Defender->HasStatus('Confusion') )
            {
              $Defender->Item->Consume();
              $Defender->RemoveStatus('Confusion');

              $Item_Proc_Text .= "{$Defender->Display_Name} ate its Aspear Berry and was cured of its Confusion!";
            }
            break;

          case 'Petaya Berry':
            if
            (
              $Defender->HP >= $Defender->Max_HP / 4 &&
              $Defender->HP - $Damage <= $Defender->Max_HP / 4 &&
              $Defender->Stats['Sp_Attack']->Stage < 6 &&
              $Defender->Stats['Sp_Attack']->Stage > -6
            )
            {
              $Defender->Item->Consume();

              if ( $Defender->HasAbility(['Contrary']) )
                $Defender->Stats['Sp_Attack']->SetValue(-1);
              else
                $Defender->Stats['Sp_Attack']->SetValue(1);

              $Item_Proc_Text .= "{$Defender->Display_Name} ate its Ganlon Berry and raised its Special Attack!";
            }
            break;

          case 'Rawst Berry':
            if ( $Defender->HasStatus('Burn') )
            {
              $Defender->Item->Consume();
              $Defender->RemoveStatus('Burn');

              $Item_Proc_Text .= "{$Defender->Display_Name} ate its Aspear Berry and was cured of its Burn!";
            }
            break;

          case 'Rocky Helmet':
            if ( $this->HasFlag('contact') && $Attacker->Item->Name != 'Protective Pads' )
            {
              $Attacker->DecreaseHP(floor($Attacker->Max_HP / 6));

              $Item_Proc_Text .= "{$Attacker->Display_Name} hurt itself on {$Defender->Display_Name}'s {$Defender->Item->Name}!<br />";
            }
            break;

          case 'Rowap Berry':
            if ( $this->Damage_Type == 'Special' )
            {
              $Defender->Item->Consume();
              $Attacker->DecreaseHP(floor($Attacker->Max_HP / 8));

              $Item_Proc_Text .= "{$Defender->Display_Name} ate its Rowap Berry and made {$Attacker->Display_Name} take damage!";
            }
            break;

          case 'Salac Berry':
            if
            (
              $Defender->HP >= $Defender->Max_HP / 4 &&
              $Defender->HP - $Damage <= $Defender->Max_HP / 4 &&
              $Defender->Stats['Speed']->Stage < 6 &&
              $Defender->Stats['Speed']->Stage > -6
            )
            {
              $Defender->Item->Consume();

              if ( $Defender->HasAbility(['Contrary']) )
                $Defender->Stats['Speed']->SetValue(-1);
              else
                $Defender->Stats['Speed']->SetValue(1);

              $Item_Proc_Text .= "{$Defender->Display_Name} ate its Salac Berry and raised its Speed!";
            }
            break;

          case 'Sitrus Berry':
            if
            (
              $Defender->HP >= $Defender->Max_HP / 2 &&
              $Defender->HP - $Damage <= $Defender->Max_HP / 2
            )
            {
              $Defender->Item->Consume();
              $Defender->IncreaseHP(floor($Defender->Max_HP / 4));

              $Item_Proc_Text .= "{$Defender->Display_Name} ate its Sitrus Berry and regained HP!";
            }
            break;

          case 'Snowball':
            if
            (
              $this->Move_Type == 'Ice' &&
              $Damage > 0 &&
              $Defender->Stats['Attack']->Stage < 6 &&
              $Defender->Stats['Attack']->Stage > -6
            )
            {
              $Defender->Item->Consume();

              if ( $Defender->HasAbility(['Contrary']) )
                $Defender->Stats['Attack']->SetValue(-1);
              else
                $Defender->Stats['Attack']->SetValue(1);

              $Item_Proc_Text .= "{$Defender->Display_Name}'s Snowball modified its Attack!";
            }
            break;

          case 'Starf Berry':
            if
            (
              $Defender->HP >= $Defender->Max_HP / 4 &&
              $Defender->HP - $Damage <= $Defender->Max_HP / 4
            )
            {
              $Stats = ['Attack', 'Defense', 'Sp_Attack', 'Sp_Defense', 'Speed'];
              $Random_Stat = $Stats[mt_rand(0, count($Stats) - 1)];
              $Stat_Name = str_replace('_', 'ecial ', $Random_Stat);

              if
              (
                $Defender->Stats[$Random_Stat]->Stage < 6 &&
                $Defender->Stats[$Random_Stat]->Stage > -6
              )
              {
                $Defender->Item->Consume();

                if ( $Defender->HasAbility(['Contrary']) )
                  $Defender->Stats[$Random_Stat]->SetValue(-1);
                else
                  $Defender->Stats[$Random_Stat]->SetValue(1);

                $Item_Proc_Text .= "{$Defender->Display_Name} ate its Starf Berry and modified its {$Stat_Name}!";
              }
            }
            break;

          case 'Weakness Policy':
            if ( $Move_Effectiveness > 1 )
            {
              if
              (
                $Defender->Stats['Attack']->Stage < 6 && $Defender->Stats['Attack']->Stage > -6 &&
                $Defender->Stats['Sp_Attack']->Stage < 6 && $Defender->Stats['Sp_Attack']->Stage > -6
              )
              {
                $Defender->Item->Consume();

                if ( $Defender->HasAbility(['Contrary']) )
                {
                  $Defender->Stats['Attack']->SetValue(-2);
                  $Defender->Stats['Sp_Attack']->SetValue(-2);
                }
                else
                {
                  $Defender->Stats['Attack']->SetValue(2);
                  $Defender->Stats['Sp_Attack']->SetValue(2);
                }

                $Item_Proc_Text .= "{$Defender->Display_Name}'s Weakness Policy modified its Attack and Special Attack!";
              }
            }
            break;

          case 'Wiki Berry':
            if
            (
              $Defender->HP >= $Defender->Max_HP / 4 &&
              $Defender->HP - $Damage <= $Defender->Max_HP / 4
            )
            {
              $Defender->Item->Consume();
              $Defender->IncreaseHP(floor($Defender->Max_HP / 3));

              $Item_Proc_Text .= "{$Defender->Display_Name} ate its Wiki Berry and regained HP!";

              if ( $Defender->HasNature(['Adamant', 'Careful', 'Impish', 'Jolly']) )
              {
                $Set_Confusion = $Defender->SetStatus('Confusion');
                if ( !empty($Set_Confusion) )
                  $Item_Proc_Text .= "<br />{$Set_Confusion['Dialogue']}";
              }
            }
            break;
        }
      }

      if ( !empty($Attacker->Item) )
      {
        switch ( $Attacker->Item->Name )
        {
          case 'Throat Spray':
            if
            (
              $this->HasFlag('sound') &&
              $Attacker->Stats['Sp_Attack']->Stage < 6 &&
              $Attacker->Stats['Sp_Attack']->Stage > -6
            )
            {
              $Attacker->Item->Consume();

              if ( $Defender->HasAbility(['Contrary']) )
                $Defender->Stats['Sp_Attack']->SetValue(-1);
              else
                $Defender->Stats['Sp_Attack']->SetValue(1);

              $Item_Proc_Text .= "{$Attacker->Display_Name} Throat Spray modified its Special Attack!";
            }
            break;
        }
      }

      return $Item_Proc_Text;
    }

    /**
     * Disable the move.
     * @param int $Turns
     */
    public function Disable
    (
      int $Turns
    )
    {
      if ( $this->Disabled )
        return;

      $this->Disabled = true;
      $this->Disabled_For_Turns = $Turns;
    }

    /**
     * Enable the move.
     */
    public function Enable()
    {
      if ( !$this->Disabled )
        return;

      $this->Disabled = false;
      $this->Disabled_For_Turns = null;
    }

    /**
     * Determine if the move gets STAB applied to it.
     * @param string $Side
     */
    public function CalculateSTAB
    (
      string $Side
    )
    {
      $Attacker = $_SESSION['EvoChroniclesRPG']['Battle'][$Side]->Active;

      if ( $Attacker->HasTyping([ $this->Move_Type ]) )
      {
        if ( $Attacker->Ability->Name == 'Adaptibility' )
          return 2;

        return 1.5;
      }

      return 1;
    }

    /**
     * Calculates how much damage the move will do.
     * @param string $Side
     * @param int $STAB
     * @param bool $Crit
     * @param float $Move_Effectiveness
     */
    public function CalcDamage
    (
      string $Side,
      int $STAB,
      bool $Crit,
      float $Move_Effectiveness
    )
    {
      if ( !isset($STAB) || !isset($Crit) || !isset($Move_Effectiveness) )
        return -1;

      /**
       * Some moves do a fixed amount of damage no matter what.
       */
      switch ($this->Name)
      {
        case 'Dragon Rage':
          return 40;
          break;

        case 'Sonic Boom':
          return 20;
          break;
      }

      switch ( $Side )
      {
        case 'Ally':
          $Attacker = $_SESSION['EvoChroniclesRPG']['Battle']['Ally']->Active;
          $Defender = $_SESSION['EvoChroniclesRPG']['Battle']['Foe']->Active;
          $Foe = 'Foe';
          break;
        case 'Foe':
          $Attacker = $_SESSION['EvoChroniclesRPG']['Battle']['Foe']->Active;
          $Defender = $_SESSION['EvoChroniclesRPG']['Battle']['Ally']->Active;
          $Foe = 'Ally';
          break;
      }

      if ( isset($_SESSION['EvoChroniclesRPG']['Battle']['Turn_Data']['Turn_' . $this->Turn_ID]['First_Attacker']) )
        $Turn_First_Attacker = $_SESSION['EvoChroniclesRPG']['Battle']['Turn_Data']['Turn_' . $this->Turn_ID]['First_Attacker'];
      else
        $Turn_First_Attacker = $Side;

      $Crit_Mult = 1;
      if ( $Crit )
        if ( $Attacker->Ability->Name == 'Sniper' )
          $Crit_Mult = 2.25;
        else
          $Crit_Mult = 1.5;

      $Weather_Mult = 1;
      if
      (
        !$Attacker->Ability->Name == 'Air Lock' &&
        !$Defender->Ability->Name == 'Air Lock'
      )
      {
        switch ( $this->Weather )
        {
          case 'Rain':
            if ( $this->Move_Type == 'Water' )
              $Weather_Mult = 1.5;
            else if ( $this->Move_Type == 'Fire' )
              $Weather_Mult = 0.5;
            break;

          case 'Harsh Sunlight':
            if ( $this->Move_Type == 'Fire' )
              $Weather_Mult = 1.5;
            else if ( $this->Move_Type == 'Water' )
              $Weather_Mult = 0.5;
            break;
        }
      }

      $Status_Mult = 1;
      if ( $Attacker->Ability->Name == 'Guts' )
        if ( $Attacker->HasStatusFromArray(['Burn', 'Freeze', 'Paralyze', 'Poison', 'Sleep']) )
          $Status_Mult = 1.5;
      else
        if ( $Attacker->HasStatus('Burn') )
          $Status_Mult = 0.5;

      $Physical_Damage_Mult = 1.0;
      $Special_Damage_Mult = 1.0;
      if ( $this->IsFieldEffectActive($Foe, 'Aurora Veil') )
      {
        $Physical_Damage_Mult = 0.5;
        $Special_Damage_Mult = 0.5;
      }
      else if ( $this->IsFieldEffectActive($Foe, 'Reflect') )
      {
        $Physical_Damage_Mult = 0.5;
      }
      else if ( $this->IsFieldEffectActive($Foe, 'Light Screen') )
      {
        $Special_Damage_Mult = 0.5;
      }

      switch ( $this->Name )
      {
        case 'Acrobatics':
          if ( empty($Attacker->Item) )
            $this->Power *= 2;
          break;
      }

      switch ( $Attacker->Item->Name )
      {
        case 'Bug Gem':
          if ( $this->Move_Type == 'Bug' )
          {
            $this->Power *= 1.3;
            $Attacker->Item->Consume();
          }
          break;

        case 'Dark Gem':
          if ( $this->Move_Type == 'Dark' )
          {
            $this->Power *= 1.3;
            $Attacker->Item->Consume();
          }
          break;

        case 'Dragon Gem':
          if ( $this->Move_Type == 'Dragon' )
          {
            $this->Power *= 1.3;
            $Attacker->Item->Consume();
          }
          break;

        case 'Electric Gem':
          if ( $this->Move_Type == 'Electric' )
          {
            $this->Power *= 1.3;
            $Attacker->Item->Consume();
          }
          break;

        case 'Fairy Gem':
          if ( $this->Move_Type == 'Fairy' )
          {
            $this->Power *= 1.3;
            $Attacker->Item->Consume();
          }
          break;

        case 'Fighting Gem':
          if ( $this->Move_Type == 'Fighting' )
          {
            $this->Power *= 1.3;
            $Attacker->Item->Consume();
          }
          break;

        case 'Fire Gem':
          if ( $this->Move_Type == 'Fire' )
          {
            $this->Power *= 1.3;
            $Attacker->Item->Consume();
          }
          break;

        case 'Flying Gem':
          if ( $this->Move_Type == 'Flying' )
          {
            $this->Power *= 1.3;
            $Attacker->Item->Consume();
          }
          break;

        case 'Ghost Gem':
          if ( $this->Move_Type == 'Ghost' )
          {
            $this->Power *= 1.3;
            $Attacker->Item->Consume();
          }
          break;

        case 'Grass Gem':
          if ( $this->Move_Type == 'Grass' )
          {
            $this->Power *= 1.3;
            $Attacker->Item->Consume();
          }
          break;

        case 'Ground Gem':
          if ( $this->Move_Type == 'Ground' )
          {
            $this->Power *= 1.3;
            $Attacker->Item->Consume();
          }
          break;

        case 'Ice Gem':
          if ( $this->Move_Type == 'Ice' )
          {
            $this->Power *= 1.3;
            $Attacker->Item->Consume();
          }
          break;

        case 'Normal Gem':
          if ( $this->Move_Type == 'Normal' )
          {
            $this->Power *= 1.3;
            $Attacker->Item->Consume();
          }
          break;

        case 'Poison Gem':
          if ( $this->Move_Type == 'Poison' )
          {
            $this->Power *= 1.3;
            $Attacker->Item->Consume();
          }
          break;

        case 'Psychic Gem':
          if ( $this->Move_Type == 'Psychic' )
          {
            $this->Power *= 1.3;
            $Attacker->Item->Consume();
          }
          break;

        case 'Rock Gem':
          if ( $this->Move_Type == 'Rock' )
          {
            $this->Power *= 1.3;
            $Attacker->Item->Consume();
          }
          break;

        case 'Steel Gem':
          if ( $this->Move_Type == 'Steel' )
          {
            $this->Power *= 1.3;
            $Attacker->Item->Consume();
          }
          break;

        case 'Water Gem':
          if ( $this->Move_Type == 'Water' )
          {
            $this->Power *= 1.3;
            $Attacker->Item->Consume();
          }
          break;

        case 'Draco Plate':
          if ( $this->Move_Type == 'Dragon' )
            $this->Power *= 1.2;
          break;

        case 'Dread Plate':
          if ( $this->Move_Type == 'Earth' )
            $this->Power *= 1.2;
          break;

        case 'Earth Plate':
          if ( $this->Move_Type == 'Ground' )
            $this->Power *= 1.2;
          break;

        case 'Fist Plate':
          if ( $this->Move_Type == 'Fighting' )
            $this->Power *= 1.2;
          break;

        case 'Flame Plate':
          if ( $this->Move_Type == 'Fire' )
            $this->Power *= 1.2;
          break;

        case 'Icicle Plate':
          if ( $this->Move_Type == 'Ice' )
            $this->Power *= 1.2;
          break;

        case 'Insect Plate':
          if ( $this->Move_Type == 'Bug' )
            $this->Power *= 1.2;
          break;

        case 'Iron Plate':
          if ( $this->Move_Type == 'Steel' )
            $this->Power *= 1.2;
          break;

        case 'Meadow Plate':
          if ( $this->Move_Type == 'Grass' )
            $this->Power *= 1.2;
          break;

        case 'Mind Plate':
          if ( $this->Move_Type == 'Psychic' )
            $this->Power *= 1.2;
          break;

        case 'Pixie Plate':
          if ( $this->Move_Type == 'Fairy' )
            $this->Power *= 1.2;
          break;

        case 'Sky Plate':
          if ( $this->Move_Type == 'Flying' )
            $this->Power *= 1.2;
          break;

        case 'Splash Plate':
          if ( $this->Move_Type == 'Water' )
            $this->Power *= 1.2;
          break;

        case 'Spooky Plate':
          if ( $this->Move_Type == 'Ghost' )
            $this->Power *= 1.2;
          break;

        case 'Stone Plate':
          if ( $this->Move_Type == 'Rock' )
            $this->Power *= 1.2;
          break;

        case 'Toxic Plate':
          if ( $this->Move_Type == 'Poison' )
            $this->Power *= 1.2;
          break;

        case 'Zap Plate':
          if ( $this->Move_Type == 'Electric' )
            $this->Power *= 1.2;
          break;

        case 'Expert Belt':
          if ( $Move_Effectiveness['Mult'] > 1 )
            $this->Power *= 1.2;
          break;

        case 'Life Orb':
          $this->Power *= 1.3;
          break;

        case 'Metronome':
          $Damage_Mod = [ 4096, 4915, 5734, 6553, 7372, 8192 ];
          $Damage_Boost = $Damage_Mod[$Attacker->Last_Move['Consecutive_Hits']] / 4096;
          $this->Power *= round($Damage_Boost);
          break;

        case 'Muscle Band':
          if ( $this->Damage_Type == 'Physical' )
            $this->Power *= 1.1;
          break;

        case 'Odd Incense':
          if ( $this->Move_Type == 'Psychic' )
            $this->Power *= 1.2;
          break;

        case 'Rock Incense':
          if ( $this->Move_Type == 'Rock' )
            $this->Power *= 1.2;
          break;

        case 'Rose Incense':
          if ( $this->Move_Type == 'Grass' )
            $this->Power *= 1.2;
          break;

        case 'Sea Incense':
        case 'Wave Incense':
          if ( $this->Move_Type == 'Water' )
            $this->Power *= 1.2;
          break;

        case 'Wise Glasses':
          if ( $this->Damage_Type == 'Special' )
            $this->Power *= 1.1;
          break;
      }

      switch ( $Attacker->Ability->Name )
      {
        case 'Analytic':
          if ( $Turn_First_Attacker != $Side )
            $this->Power *= 1.3;
          break;

        case 'Battery':
          if ( $this->Damage_Type == 'Special' )
            $this->Power *= 1.3;
          break;

        case 'Blaze':
          if ( $Attacker->HP <= $Attacker->Max_HP / 2 && $this->Move_Type == 'Fire' )
            $this->Power *= 1.5;
          break;

        case 'Dark Aura':
          if ( $this->Move_Type == 'Dark' )
            if ( $Defender->Ability->Name == 'Aura Break' )
              $this->Power /= 1.33;
            else
              $this->Power *= 1.33;
          break;

        case "Dragon's Maw":
          if ( $this->Move_Type == 'Dragon' )
            $this->Power *= 1.5;
          break;

        case 'Fairy Aura':
          if ( $this->Move_Type == 'Dark' )
            if ( $Defender->Ability->Name == 'Aura Break' )
              $this->Power /= 1.33;
            else
              $this->Power *= 1.33;
          break;

        case 'Flare Boost':
          if ( $Attacker->HasStatus('Burn') && $this->Damage_Type == 'Special' )
            $this->Power *= 1.5;
          break;

        case 'Flash Fire':
          if ( $Attacker->Ability->Procced && $this->Move_Type == 'Fire' )
            $this->Power *= 1.5;
          break;

        case 'Galvanize':
          if ( $this->Move_Type == 'Electric' )
            $this->Power *= 1.2;
          break;

        case 'Infiltrator':
          $Physical_Damage_Mult = 1.0;
          $Special_Damage_Mult = 1.0;
          break;

        case 'Iron Fist':
          if ( $this->HasFlag('punch') )
            $this->Power *= 1.2;
          break;

        case 'Mega Launcher':
          if ( $this->HasFlag('pulse') )
            $this->Power *= 1.5;
          break;

        case 'Normalize':
          if ( $this->Move_Type == 'Normal' )
            $this->Power *= 1.2;
          break;

        case 'Overgrow':
          if ( $this->Move_Type == 'Grass' )
            $this->Power *= 1.5;
          break;

        case 'Pixilate':
          if ( $this->Move_Type == 'Fairy' )
            $this->Power *= 1.2;
          break;

        case 'Punk Rock':
          if ( $this->HasFlag('sound') )
            $this->Power *= 1.3;
          break;

        case 'Strong Jaw':
          if ( $this->HasFlag('bite') )
            $this->Power *= 1.5;
          break;

        case 'Reckless':
          if ( $this->Recoil > 0 )
            $this->Power *= 1.2;
          break;

        case 'Refrigerate':
          if ( $this->Move_Type == 'Ice' )
            $this->Power *= 1.2;
          break;

        case 'Rivalry':
          if ( $Attacker->Gender != 'G' && $Defender->Gender != 'G' )
          {
            if ( $Attacker->Gender == $Defender->Gender )
              $this->Power *= 1.25;
            else
              $this->Power *= 0.75;
          }
          break;

        case 'Sand Force':
          if ( !empty($this->Weather) && $this->Weather->Name == 'Sandstorm' )
          {
            if ( in_array($this->Move_Type, ['Ground', 'Rock', 'Steel']) )
              $this->Power *= 1.3;
          }
          break;

        case 'Steelworker':
          if ( $this->Move_Type == 'Steel' )
            $this->Power *= 1.5;
          break;

        case 'Swarm':
          if ( $this->Move_Type == 'Bug' && $Attacker->HP <= $Attacker->Max_HP / 2 )
            $this->Power *= 1.5;
          break;

        case 'Technician':
          if ( $this->Power <= 60 )
            $this->Power *= 1.5;
          break;

        case 'Torrent':
          if ( $this->Move_Type == 'Water' && $Attacker->HP <= $Attacker->Max_HP / 2 )
            $this->Power *= 1.5;
          break;

        case 'Tough Claws':
          if ( $this->HasFlag('contact') )
            $this->Power *= 1.3;
          break;

        case 'Toxic Boost':
          if ( $Attacker->HasStatusFromArray(['Badly Poisoned', 'Poisoned']) && $this->Damage_Type == 'Physical' )
            $this->Power *= 1.5;
          break;

        case 'Transistor':
          if ( $this->Move_Type == 'Electric' )
            $this->Power *= 1.5;
          break;

        case 'Water Bubble':
          if ( $this->Move_Type == 'Water' )
            $this->Power *= 2;
          break;
      }

      switch ($this->Damage_Type)
      {
        case 'Physical':
          $Damage = floor(((2 * $Attacker->Level / 5 + 2) * $this->Power * $Attacker->Stats['Attack']->Current_Value / $Defender->Stats['Defense']->Current_Value / 50 + 2) * 1 * $Weather_Mult * $Crit_Mult * (mt_rand(185, 200) / 200) * $STAB * $Move_Effectiveness * $Status_Mult * $Physical_Damage_Mult * $Special_Damage_Mult * 1);
          break;

        case 'Special':
          $Damage = $Damage = floor(((2 * $Attacker->Level / 5 + 2) * $this->Power * $Attacker->Stats['Sp_Attack']->Current_Value / $Defender->Stats['Sp_Defense']->Current_Value / 50 + 2) * 1 * $Weather_Mult * $Crit_Mult * (mt_rand(185, 200) / 200) * $STAB * $Move_Effectiveness * $Status_Mult * $Physical_Damage_Mult * $Special_Damage_Mult * 1);
          break;

        default:
          $Damage = 0;
      }

      if ( $Damage > 0 )
      {
        if ( $Defender->HasAbility(['Heatproof', 'Water Bubble']) && $this->Move_Type == 'Fire' )
          $Damage /= 2;

        if ( $Defender->Ability->Name == 'Filter' && $Move_Effectiveness > 1 )
          $Damage *= 0.75;

        if ( $Defender->Ability->Name == 'Fur Coat' && $this->Damage_Type == 'Physical' )
          $Damage /= 2;

        if ( $Defender->Ability->Name == 'Ice Scales' && $this->Damage_Type == 'Special' )
          $Damage /= 2;

        if ( $Defender->Ability->Name == 'Punk Rock' && $this->HasFlag('sound') )
          $Damage /= 2;

        if ( $Defender->Ability->Name == 'Thick Fat' && in_array($this->Move_Type, ['Fire', 'Ice']) )
          $Damage /= 2;

        if ( $Defender->Ability->Name == 'Fluffy' )
        {
          if ( $this->HasFlag('contact') )
            $Damage /= 2;

          if ( $this->Move_Type == 'Fire' )
            $Damage *= 2;
        }

        if ( $Attacker->HasAbility(['Tinted Lens']) && $Move_Effectiveness < 1 )
          $Damage *= 2;

        if ( $Attacker->HasAbility(['Neuroforce']) && $Move_Effectiveness > 1 )
          $Damage *= 1.25;

        if ( $Attacker->HasAbility(['Prism Armor', 'Solid Rock']) && $Move_Effectiveness > 1 )
          $Damage *= 0.75;

        if ( $Defender->HasAbility(['Multiscale', 'Shadow Shield']) && $Defender->HP === $Defender->Max_HP )
          $Damage /= 2;

        if ( $Damage > $Defender->HP )
          $Damage = $Defender->HP;
      }
      else
      {
        $Damage = 0;
      }

      return $Damage;
    }

    /**
     * Calculates how much healing the move will do.
     * @param int $Damage_Dealt
     */
    public function CalcHealing
    (
      int $Damage_Dealt
    )
    {
      return floor($Damage_Dealt * ($this->Drain / 100));
    }

    /**
     * Calculate how much damage is taken from recoil.
     * @param int $Damage_Dealt
     */
    public function CalcRecoil
    (
      int $Damage_Dealt
    )
    {
      return $Damage_Dealt * ($this->Recoil / 100);
    }

    /**
     * Process disabled moves.
     * Enables a move if it's no longer supposed to be disabled.
     * @param PokemonHandler $Attacker
     */
    public function ProcessMoveDisablement
    (
      PokemonHandler $Attacker
    )
    {
      $Disable_Dialogue = '';

      if ( $this->Disabled )
      {
        if ( isset($this->Disabled_For_Turns) && $this->Disabled_For_Turns > 0 )
        {
          $this->Disabled_For_Turns -= 1;
        }
      }

      if ( isset($this->Disabled_For_Turns) && $this->Disabled_For_Turns === 0 )
      {
        $this->Enable();
        $Disable_Dialogue .= "{$Attacker->Display_Name}'s {$this->Name} is re-enabled!";
      }

      return $Disable_Dialogue;
    }

    /**
     * Check to see if the move has a given flag.
     * @param string $Flag_Name
     */
    public function HasFlag
    (
      string $Flag_Name
    )
    {
      if ( empty($this->Flags) )
        return false;

      foreach ( $this->Flags as $Flag => $Value )
        if ( $Flag == $Flag_Name )
          return true;

      return false;
    }

    /**
     * Set the value of a given flag.
     * @param {string} $Flag
     * @param {bool} $Value
     * @return {bool}
     */
    public function SetFlag
    (
      string $Flag,
      bool $Value
    )
    {
      if ( !in_array($Flag, ['authentic', 'bite', 'bullet', 'charge', 'contact', 'dance', 'defrost', 'distance', 'gravity', 'heal', 'mirror', 'mystery', 'nonsky', 'powder', 'protect', 'pulse', 'punch', 'recharge', 'reflectable', 'snatch', 'sound']) )
        return false;

      $this->Flags[$Flag] = $Value;

      return true;
    }

    /**
     * Given the IVs of the User, determine the move-type.
     */
    public function DetermineMoveType
    (
      array $IVs
    )
    {
      $Typings = [
        'Fighting', 'Flying', 'Poison', 'Ground', 'Rock',
        'Bug', 'Ghost', 'Steel', 'Fire', 'Water',
        'Grass', 'Electric', 'Psychic', 'Ice', 'Dragon', 'Dark'
      ];

      $Formula = ($IVs[0] + ($IVs[1] * 2) + ($IVs[3] * 4) + ($IVs[6] * 8) + ($IVs[4] * 16) + ($IVs[5] * 32) * 15) / 63;

      return $Typings[$Formula];
    }
  }
