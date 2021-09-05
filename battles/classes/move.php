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
          $Attacker = $_SESSION['Battle']['Ally']->Active;
          $Defender = $_SESSION['Battle']['Foe']->Active;
          break;
        case 'Foe':
          $Attacker = $_SESSION['Battle']['Foe']->Active;
          $Defender = $_SESSION['Battle']['Ally']->Active;
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

      /**
       * Hidden Power check here, before anything further gets processed.
       */
      if ( $this->Name == 'Hidden Power' )
        $this->Move_Type = $this->DetermineMoveType($Attacker->IVs);

      /**
       * Abilities that change move type, etc. need to happen here.
       */
      if ( $Attacker->Ability->Name == 'Galvanize' )
      {
        $this->Move_Type = 'Electric';
        $this->Power *= 1.2;
      }

      $Move_Effectiveness = $this->MoveEffectiveness($Defender);
      if ( $Move_Effectiveness['Mult'] > 0 )
        $Does_Move_Crit = $this->DoesMoveCrit($Side);
      else
        $Does_Move_Crit = false;

      $STAB = $this->CalculateSTAB($Side);

      if ( class_exists($this->Class_Name) )
        $Move_Class = new $this->Class_Name($this);

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
        $Does_Move_Hit = $this->DoesMoveHit($Side);

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
          $Attacker = $_SESSION['Battle']['Ally']->Active;
          $Defender = $_SESSION['Battle']['Foe']->Active;
          break;
        case 'Foe':
          $Ally = 'Foe';
          $Foe = 'Ally';
          $Attacker = $_SESSION['Battle']['Foe']->Active;
          $Defender = $_SESSION['Battle']['Ally']->Active;
          break;
      }

      if ( isset($_SESSION['Battle']['Turn_Data']['Turn_' . $this->Turn_ID]['First_Attacker']) )
        $Turn_First_Attacker = $_SESSION['Battle']['Turn_Data']['Turn_' . $this->Turn_ID]['First_Attacker'];
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
          $Target = $_SESSION['Battle'][$Ally];
          break;
        case 'Foe':
          $Target = $_SESSION['Battle'][$Foe];
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
        if ( !$Attacker->HasStatus('Charging') )
        {
          $Attacker->SetStatus('Charging');

          return [
            'Type' => 'Success',
            'Text' => "{$Attacker->Display_Name} is charging up an attack!",
            'Damage' => 0,
            'Heal' => 0,
          ];
        }
      }

      if ( $Defender->HasStatus('Protect') && $this->HasFlag('protect') )
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

      if
      (
        $Attacker->Ability->Name == 'Skill Link' &&
        $this->Max_Hits > 1
      )
        $this->Total_Hits = 5;
      else
        $this->Total_Hits = mt_rand($this->Min_Hits, $this->Max_Hits);

      if ( $Attacker->Ability->Name == 'Aerilate' && $this->Move_Type == 'Normal' )
        $this->Move_Type = 'Flying';

      /**
       * Ability proc dialogue.
       */
      $Ability_Proc_Dialogue = '';

      /**
       * Calculate how much damage will be done.
       */
      $Damage = 0;
      for ( $Hits = 0; $Hits < $this->Total_Hits; $Hits++ )
      {
        $Ability_Proc = $this->ProcessAbilityProcs($Attacker, $Defender, true, 1, $Damage);
        $Ability_Proc_Dialogue .= $Ability_Proc['Text'];

        if ( empty($Ability_Proc['Damage']) )
          $Damage += $this->CalcDamage($Side, $STAB, $Does_Move_Crit, $Move_Effectiveness['Mult']);
        else if ( $Ability_Proc['Damage'] > 0 )
          $Damage += $Ability_Proc['Damage'];
        else
          $Damage += 0;
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
      if ( $this->Recoil > 0 )
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
      $Ability_Effect = $this->ProcessAbilityProcs($Attacker, $Defender, false, $Hits, $Damage);
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
          $Attacker = $_SESSION['Battle']['Ally']->Active;
          break;
        case 'Foe':
          $Attacker = $_SESSION['Battle']['Foe']->Active;
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

      switch ( $Side )
      {
        case 'Ally':
          $Attacker = $_SESSION['Battle']['Ally']->Active;
          $Defender = $_SESSION['Battle']['Foe']->Active;
          break;
        case 'Foe':
          $Attacker = $_SESSION['Battle']['Foe']->Active;
          $Defender = $_SESSION['Battle']['Ally']->Active;
          break;
      }

      if ( $this->Effect_Short == 'Causes a one-hit KO.' )
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

      if ( $Attacker->Ability->Name == 'Compound Eyes' )
        $this->Accuracy *= 1.3;

      if ( $Attacker->Ability->Name == 'Hustle' && $this->Damage_Type == 'Physical' )
        $this->Accuracy *= 0.8;

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
          $Attacker = $_SESSION['Battle']['Ally']->Active;
          $Defender = $_SESSION['Battle']['Foe']->Active;
          break;
        case 'Foe':
          $Attacker = $_SESSION['Battle']['Foe']->Active;
          $Defender = $_SESSION['Battle']['Ally']->Active;
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
          $Defender = $_SESSION['Battle']['Foe']->Active;
          break;
        case 'Foe':
          $Defender = $_SESSION['Battle']['Ally']->Active;
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
          $Attacker = $_SESSION['Battle']['Ally']->Active;
          $Defender = $_SESSION['Battle']['Foe']->Active;
          break;
        case 'Foe':
          $Attacker = $_SESSION['Battle']['Foe']->Active;
          $Defender = $_SESSION['Battle']['Ally']->Active;
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

      $Text = '';
      $Damage_Mod = 1;

      if ( $Defender->Ability->Name == 'Aftermath' )
      {
        if ( $Attacker->Ability->Name != 'Damp' )
        {
          if ( $Attacker->Item->Name != 'Protective Pads' )
          {
            $Attacker->DecreaseHP(floor($Attacker->Max_HP / 4));

            $Text .= "<br />{$Attacker->Display_Name} took damage from the Aftermath!<br />";
          }
        }
      }

      if ( $Defender->Ability->Name == 'Cute Charm' )
      {
        if
        (
          $Attacker->Gender != 'Genderless' &&
          $Defender->Gender != 'Genderless' &&
          $Attacker->Gender != $Defender->Gender
        )
        {
          if ( $Attacker->Item->Name != 'Protective Pads' )
          {
            $Attacker->SetStatus('Infatuated');

            $Text .= "<br />{$Attacker->Display_Name} has become infatuated!<br />";
          }
        }
      }

      if ( $Defender->Ability->Name == 'Effect Spore' )
      {
        if
        (
          !$Attacker->HasTyping(['Grass']) ||
          $Attacker->Ability->Name != 'Overcoat' ||
          $Attacker->Item->Name != 'Safety Goggles' ||
          $Attacker->Item->Name != 'Protective Pads'
        )
        {
          for ( $i = 0; $i < $this->Total_Hits; $i++ )
          {
            if ( mt_rand(1, 10) === 1 )
            {
              $Affliction_Odds = mt_rand(1, 3);

              switch ($Affliction_Odds)
              {
                case 1:
                  $Attacker->SetStatus('Paralysis');
                  $Text .= "<br />{$Attacker->Display_Name} has been paralyzed by the {$Defender->Display_Name}'s Effect Spore!<br />";
                  break;
                case 2:
                  $Attacker->SetStatus('Poisoned');
                  $Text .= "<br />{$Attacker->Display_Name} has been poisoned by the {$Defender->Display_Name}'s Effect Spore!<br />";
                  break;
                case 3:
                  $Attacker->SetStatus('Sleep');
                  $Text .= "<br />{$Attacker->Display_Name} has been forced asleep by the {$Defender->Display_Name}'s Effect Spore!<br />";
                  break;
              }
            }
          }
        }
      }

      if ( $Defender->Ability->Name == 'Flame Body' )
      {
        if ( $Attacker->Item->Name != 'Protective Pads' )
        {
          for ( $i = 0; $i < $this->Total_Hits; $i++ )
          {
            if ( mt_rand(1, 10) <= 3 )
            {
              $Attacker->SetStatus('Burned');

              $Text .= "<br />{$Attacker->Display_Name} was burned!<br />";
            }
          }
        }
      }

      if ( $Defender->Ability->Name == 'Fluffy' )
      {
        $Damage_Mod /= 2;

        if ( $this->Move_Type == 'Fire' )
          $Damage_Mod * 2;
      }

      if ( in_array($Defender->Ability->Name, ['Gooey', 'Tangling Hair']) )
      {
        if ( $Attacker->Item->Name != 'Protective Pads' )
        {
          for ( $i = 0; $i < $this->Total_Hits; $i++ )
          {
            $Attacker->Stats['Speed']->SetModifier(-1);

            $Text .= "<br />{$Attacker->Display_Name} speed has dropped from the goo!<br />";
          }
        }
      }

      if ( in_array($Defender->Ability->Name, ['Iron Barbs', 'Rough Skin']) )
      {
        if ( $Attacker->Item->Name != 'Protective Pads' )
        {
          $Attacker->DecreaseHP(floor($Attacker->Max_HP / 8));

          $Text .= "<br />{$Attacker->Display_Name} hurt itself on {$Defender->Display_Name}'s {$Defender->Ability->Name}!<br />";
        }
      }

      if ( $Defender->Ability->Name == 'Mummy' )
      {
        if ( $Attacker->Item->Name != 'Protective Pads' )
        {
          $Attacker->SetAbility('Mummy');

          $Text .= "<br />{$Attacker->Display_Name}'s Ability has become Mummy!<br />";
        }
      }

      if ( $Defender->Ability->Name == 'Perish Body' )
      {
        if ( $Attacker->Item->Name == 'Protective Pads' )
        {
          $Defender->SetStatus('Perish Body');

          $Text .= "<br />{$Defender->Display_Name} will perish in 3 turns.<br />";
        }
        else
        {
          $Attacker->SetStatus('Perish Body');
          $Defender->SetStatus('Perish Body');

          $Text .= "
            <br />
            {$Attacker->Display_Name} will perish in 3 turns.<br />
            {$Defender->Display_Name} will perish in 3 turns.<br />
          ";
        }
      }

      if ( $Defender->Ability->Name == 'Pickpocket' )
      {
        if
        (
          $Attacker->Item->Name != 'Protective Pads' ||
          $Attacker->Ability->Name != 'Sticky Hold' ||
          ($Attacker->Pokedex_ID != 382 && $Attacker->Item->Name == 'Blue Orb') ||
          ($Defender->Pokedex_ID != 382 && $Defender->Item->Name == 'Blue Orb') ||
          ($Attacker->Pokedex_ID != 383 && $Attacker->Item->Name == 'Red Orb') ||
          ($Defender->Pokedex_ID != 383 && $Defender->Item->Name == 'Red Orb') ||
          ($Attacker->Pokedex_ID != 487 && $Attacker->Item->Name == 'Griseous Orb') ||
          ($Defender->Pokedex_ID != 487 && $Defender->Item->Name == 'Griseous Orb') ||
          ($Attacker->Pokedex_ID != 493 && strpos($Attacker->Item->Name, 'Plate') > -1) ||
          ($Defender->Pokedex_ID != 493 && strpos($Defender->Item->Name, 'Plate') > -1) ||
          ($Attacker->Pokedex_ID != 773 && strpos($Attacker->Item->Name, 'Memory') > -1) ||
          ($Defender->Pokedex_ID != 773 && strpos($Defender->Item->Name, 'Memory') > -1) ||
          ($Attacker->Pokedex_ID != 649 && strpos($Attacker->Item->Name, 'Drive') > -1) ||
          ($Defender->Pokedex_ID != 649 && strpos($Defender->Item->Name, 'Drive') > -1)
        )
        {
          if
          (
            !$Defender->Item &&
            $Attacker->Item
          )
          {
            $Defender->Item = new HeldItem($Attacker->Item->ID);

            $Text .= "<br />{$Defender->Display_Name} stole {$Attacker->Display_Name}'s {$Attacker->Item->Name}!<br />";
          }
        }
      }

      if ( $Defender->Ability->Name == 'Poison Point' )
      {
        if ( $Attacker->Item->Name != 'Protective Pads' )
        {
          for ( $i = 0; $i < $this->Total_Hits; $i++ )
          {
            if ( mt_rand(1, 10) <= 3 )
            {
              $Attacker->SetStatus('Poisoned');

              $Text .= "<br />{$Attacker->Display_Name} was poisoned!<br />";
            }
          }
        }
      }

      if ( $Defender->Ability->Name == 'Static' )
      {
        if ( $Attacker->Item->Name != 'Protective Pads' )
        {
          for ( $i = 0; $i < $this->Total_Hits; $i++ )
          {
            if ( mt_rand(1, 10) <= 3 )
            {
              $Attacker->SetStatus('Paralysis');

              $Text .= "<br />{$Attacker->Display_Name} was paralyzed!<br />";
            }
          }
        }
      }

      if ( $Defender->Ability->Name == 'Wandering Spirit' )
      {
        if ( $Attacker->Item->Name != 'Protective Pads' )
        {
          if
          (
            !in_array($Attacker->Ability->Name, ['Disguise', 'Flower Gift', 'Gulp Missile', 'Ice Face', 'Illusion', 'Imposter', 'Receiver', 'RKS System', 'Schooling', 'Stance Change', 'Wonder Guard', 'Zen Mode']) ||
            !in_array($Defender->Ability->Name, ['Disguise', 'Flower Gift', 'Gulp Missile', 'Ice Face', 'Illusion', 'Imposter', 'Receiver', 'RKS System', 'Schooling', 'Stance Change', 'Wonder Guard', 'Zen Mode'])
          )
          {
            $Attacker_Ability = $Attacker->Ability->Name;
            $Defender_Ability = $Defender->Ability->Name;

            $Attacker->Ability->Name = $Defender->Ability->Name;
            $Defender->Ability->Name = $Attacker->Ability->Name;

            $Text .= "<br />{$Attacker->Display_Name} has swapped abilities with {$Defender->Display_Name}!<br />";
          }
        }
      }

      if ( $Defender->Item->Name == 'Rocky Helmet' )
      {
        if ( $Attacker->Item->Name != 'Protective Pads' )
        {
          $Attacker->DecreaseHP(floor($Attacker->Max_HP / 6));

          $Text .= "<br />{$Attacker->Display_Name} hurt itself on {$Defender->Display_Name}'s {$Defender->Item->Name}!<br />";
        }
      }
    }

    /**
     * Determine how effective the move was.
     * @param object $Used_Against
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
     * @param {UserHandler} $Target
     * @param {PokemonHandler} $Attacker
     * @param {PokemonHandler} $Defender
     * @param {boolean} $Does_Move_Crit
     * @return {$string} $Stat_Change_Text
     */
    public function ProcessStatChanges
    (
      UserHandler $Target,
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
              if ( $Target->Active->Ability->Name == 'Full Metal Body' && $Target->Active != $Attacker )
                continue;

              if ( $Target->Active->Ability->Name == 'Clear Body' && $Attacker->Ability->Name != 'Mold Breaker' )
                continue;

              if ( $Target->Active->Ability->Name == 'Hyper Cutter' && $Attacker->HasAbility(['Mold Breaker', 'Teravolt', 'Turboblaze']) && $Stat_Name == 'Attack' )
                continue;

              if ( $Target->Active->Ability->Name == 'Big Pecks' && $Attacker->HasAbility(['Mold Breaker', 'Teravolt', 'Turboblaze']) && $Stat_Name == 'Defense' )
                continue;

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

            if ( $Index > 0 && $Index < $Total_Stats )
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
     * @param {UserHandler} $Target
     * @param {PokemonHandler} $Attacker
     * @param {PokemonHandler} $Defender
     * @param {string} $Turn_First_Attacker
     * @return {$string}
     */
    public function ProcessAilments
    (
      UserHandler $Target,
      PokemonHandler $Attacker,
      PokemonHandler $Defender,
      string $Turn_First_Attacker
    )
    {
      $Ailment_Chance = mt_rand(1, 100);

      if ( $this->Effect_Chance == 'None' )
        $this->Effect_Chance = 100;

      if ( !empty($this->Ailment) )
      {
        if ( $Target->Active->HasStatus('Substitute') )
        {
          return 'But it failed!';
        }

        if
        (
          $Target->Active->Ability->Name == 'Overcoat' &&
          ( strpos($this->Name, 'Powder') || strpos($this->Name, 'Spore') )
        )
        {
          return 'But it failed!';
        }

        switch ($this->Ailment)
        {
          case 'None':
            break;

          case 'Flinch':
            if ( $Turn_First_Attacker == $Attacker->Side )
            {
              if ( $Ailment_Chance <= $this->Effect_Chance )
              {
                if ( $Target->Active->Ability->Name == 'Inner Focus' && !$Attacker->HasAbility(['Mold Breaker', 'Teravolt', 'Turboblaze']) )
                  return "{$Target->Active->Ability->Name} won't flinch because of its Inner Focus!";

                $Set_Status = $Target->Active->SetStatus($this->Ailment);
                return;
              }
            }
            break;

          case 'Freeze':
            if ( !empty($this->Weather) && strpos($this->Weather->Name, 'Harsh Sunlight') )
              return 'But it failed!';

            if ( $Ailment_Chance <= $this->Effect_Chance )
            {
              $Set_Status = $Target->Active->SetStatus($this->Ailment);
              $Status_Props = array_filter(get_object_vars($Set_Status));
              if ( isset($Set_Status) && !empty($Status_Props) )
              {
                return $Set_Status->Dialogue;
              }
            }
            break;

          case 'Paralysis':
            if ( $Target->Active->HasTyping([ 'Electric' ]) )
              return 'But it failed!';
            break;

          case 'Badly Poison':
          case 'Poison':
            if
            (
              !$Target->Active->HasTyping([ 'Poison', 'Steel' ]) ||
              ( $Target->Active->HasTyping([ 'Poison', 'Steel' ]) && $Attacker->Ability->Name == 'Corrosion' && $this->Damage_Type == 'Status' ) ||
              $Target->Active->Ability->Name == 'Immunity'
            )
            {
              if ( $Ailment_Chance <= $this->Effect_Chance )
              {
                $Set_Status = $Target->Active->SetStatus($this->Ailment);
                $Status_Props = array_filter(get_object_vars($Set_Status));
                if ( isset($Set_Status) && !empty($Status_Props) )
                {
                 return $Set_Status->Dialogue;
                }
              }
            }
            else
            {
              return 'But it failed';
            }
            break;

          case 'Sleep':
            if ( !$Target->Active->Ability->Name == 'Insomnia' )
            {
              if ( $Ailment_Chance <= $this->Effect_Chance )
              {
                $Set_Status = $Target->Active->SetStatus($this->Ailment);
                $Status_Props = array_filter(get_object_vars($Set_Status));
                if ( isset($Set_Status) && !empty($Status_Props) )
                {
                  return $Set_Status->Dialogue;
                }
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

          default:
            if ( $Ailment_Chance <= $this->Effect_Chance )
            {
              $Set_Status = $Target->Active->SetStatus($this->Ailment);
              $Status_Props = array_filter(get_object_vars($Set_Status));
              if ( isset($Set_Status) && !empty($Status_Props) )
              {
                return $Set_Status->Dialogue;
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
        if
        (
          $this->Kings_Rock &&
          $Attacker->Item->Name == "King's Rock" &&
          !$Defender->HasStatus('Substitute') &&
          $Turn_First_Attacker == $Attacker->Side &&
          mt_rand(1, 100) <= 10
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
     * @param {int} $Hits
     * @param {int} $Damage
     * @return {$array} $Ability_Effect
     */
    public function ProcessAbilityProcs
    (
      PokemonHandler $Attacker,
      PokemonHandler $Defender,
      bool $Mid_Hit = false,
      int $Hits = 1,
      int $Damage = 0
    )
    {
      $Ability_Effect_Text = '';

      switch ($Mid_Hit)
      {
        case true:
          switch ($Defender->Ability->Name)
          {
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

            case 'Iron Barbs':
              if ( $this->HasFlag('contact') )
              {
                $Attacker->DecreaseHP($Attacker->Max_HP / 8);
                $Ability_Effect_Text .= "{$Attacker->Display_Name} took damage from {$Defender->Display_Name}'s Iron Barbs!<br />";
              }
              break;

            case 'Mummy':
              if ( $this->HasFlag('contact') && !$Attacker->Ability->Name != 'Mummy' )
              {
                $Attacker->SetAbility('Mummy');
                $Ability_Effect_Text .= "{$Attacker->Display_Name}'s ability became Mummy!";
              }
              break;
          }
          break;

        case false;
          switch ($Defender->Ability->Name)
          {
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
              $Attacker->Stats['Speed']->SetValue(-1);
              $Ability_Effect_Text .= "<br />{$Defender->Display_Name}'s Cotton Down dropped {$Attacker->Display_Name}'s Speed!";
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
              for ( $i = 0; $i <= $Hits; $i++ )
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
              if ( $Defender->HP <= $Defender->Max_HP / 2 )
              {
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
                for ( $i = 0; $i < $Hits; $i++ )
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
              $Trainer = $_SESSION['Battle'][$Defender->Side];
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
                  $Defender->Ability->Proc();
                  $Ability_Effect_Text .= "{$Defender->Display_Name}'s Flash Fire absorbed the attack!";
                }
              }
              break;

            case 'Grim Neigh':
              if ( $Defender->HP <= 0 && $Attacker->Stats['Sp_Attack']->Stage < 6 )
              {
                $Attacker->Stats['Sp_Attack']->SetValue(1);
                $Ability_Effect_Text .= "{$Attacker->Display_Name}'s Grim Neight boosted its Special Attack!";
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
      $Attacker = $_SESSION['Battle'][$Side]->Active;

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
     * @param array $Move_Effectiveness
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
          $Attacker = $_SESSION['Battle']['Ally']->Active;
          $Defender = $_SESSION['Battle']['Foe']->Active;
          $Foe = 'Foe';
          break;
        case 'Foe':
          $Attacker = $_SESSION['Battle']['Foe']->Active;
          $Defender = $_SESSION['Battle']['Ally']->Active;
          $Foe = 'Ally';
          break;
      }

      if ( isset($_SESSION['Battle']['Turn_Data']['Turn_' . $this->Turn_ID]['First_Attacker']) )
        $Turn_First_Attacker = $_SESSION['Battle']['Turn_Data']['Turn_' . $this->Turn_ID]['First_Attacker'];
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

      if ( $Attacker->Ability->Name == 'Battery' && $this->Category == 'Special' )
        $this->Power *= 1.3;

      if ( $Attacker->Ability->Name == 'Flare Boost' && $Attacker->HasStatus('Burn') && $this->Category == 'Special' )
        $this->Power *= 1.5;

      if ( $Attacker->Ability->Name == 'Flash Fire' && $Attacker->Ability->Procced && $this->Move_Type == 'Fire' )
        $this->Power *= 1.5;

      if ( $Attacker->Ability->Name == 'Blaze' && $Attacker->HP <= $Attacker->Max_HP / 2 && $this->Move_Type == 'Fire' )
        $this->Power *= 1.5;

      if ( $Attacker->Ability->Name == "Dragon's Maw" && $this->Move_Type == 'Dragon' )
        $this->Power *= 1.5;

      if ( $Attacker->Ability->Name == 'Mega Launcher' && $this->HasFlag('pulse') )
        $this->Power *= 1.5;

      if ( $Attacker->Ability->Name == 'Strong Jaw' && $this->HasFlag('bite') )
        $this->Power *= 1.5;

      if ( $Attacker->Ability->Name == 'Iron Fist' && $this->HasFlag('punch') )
        $this->Power *= 1.2;

      if ( $Turn_First_Attacker != $Side && $Attacker->Ability->Name == 'Analytic' )
        $this->Power *= 1.3;

      if ( $Defender->Ability->Name == 'Aura Break' )
      {
        if ( $Attacker->Ability->Name == 'Dark Aura' && $this->Move_Type == 'Dark' )
          $this->Power /= 1.33;

        if ( $Attacker->Ability->Name == 'Fairy Aura' && $this->Move_Type == 'Fairy' )
          $this->Power /= 1.33;
      }
      else
      {
        if ( $Attacker->Ability->Name == 'Dark Aura' && $this->Move_Type == 'Dark' )
          $this->Power *= 1.33;

        if ( $Attacker->Ability->Name == 'Fairy Aura' && $this->Move_Type == 'Fairy' )
          $this->Power *= 1.33;
      }

      $Physical_Damage_Mult = 1.0;
      $Special_Damage_Mult = 1.0;
      if ( $Attacker->Ability->Name != 'Infiltrator' )
      {
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
        if ( $Defender->Ability->Name == 'Heatproof' && $this->Move_Type == 'Fire' )
          $Damage /= 2;

        if ( $Defender->Ability->Name == 'Filter' && $Move_Effectiveness['Mult'] > 1 )
          $Damage *= 0.75;

        if ( $Defender->Ability->Name == 'Fur Coat' && $this->Damage_Type == 'Physical' )
          $Damage /= 2;

        if ( $Defender->Ability->Name == 'Ice Scales' && $this->Damage_Type == 'Special' )
          $Damage /= 2;

        if ( $Defender->Ability->Name == 'Fluffy' )
        {
          if ( $this->HasFlag('contact') )
            $Damage /= 2;

          if ( $this->Move_Type == 'Fire' )
            $Damage *= 2;
        }

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
      foreach ( $this->Flags as $Flag => $Value )
        if ( $Flag == $Flag_Name )
          return true;

      return false;
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
