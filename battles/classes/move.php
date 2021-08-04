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

    public $Accuracy = null;
    public $Power = null;
    public $Priority = null;
    public $Max_PP = null;
    public $Current_PP = null;
    public $Damage_Type = null;
    public $Move_Type = null;
    public $Contact = null;

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
      $this->Contact = $Move_Data['Contact'];

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

      if ( $Defender->HasStatus('Protect') )
      {
        return [
          'Type' => 'Success',
          'Text' => "{$Attacker->Display_Name} used {$this->Name}.<br />" .
                    "{$Defender->Display_Name} was protected from the attack!",
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
          $Attacker = $_SESSION['Battle']['Ally']->Active;
          $Defender = $_SESSION['Battle']['Foe']->Active;
          break;
        case 'Foe':
          $Attacker = $_SESSION['Battle']['Foe']->Active;
          $Defender = $_SESSION['Battle']['Ally']->Active;
          break;
      }

      switch ( $this->Target )
      {
        case 'Ally':
          $Target = $_SESSION['Battle']['Ally']->Active;
          break;
        case 'Foe':
          $Target = $_SESSION['Battle']['Foe']->Active;
          break;
      }

      if ( $this->Contact )
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

      if ( isset($_SESSION['Battle'][$this->Turn_ID]['First_Attacker']) )
        $Turn_First_Attacker = $_SESSION['Battle'][$this->Turn_ID]['First_Attacker'];
      else
        $Turn_First_Attacker = $Side;

      if ( $this->Min_Hits == 'None' )
        $this->Min_Hits = 1;

      if ( $this->Max_Hits == 'None' )
        $this->Max_Hits = 1;

      if
      (
        $Attacker->Ability == 'Skill Link' &&
        $this->Max_Hits != 1
      )
        $this->Total_Hits = 5;
      else
        $this->Total_Hits = mt_rand($this->Min_Hits, $this->Max_Hits);

      /**
       * Calculate how much damage will be done.
       */
      $Damage = 0;
      for ( $Hits = 0; $Hits < $this->Total_Hits; $Hits++ )
        $Damage += $this->CalcDamage($Side, $STAB, $Does_Move_Crit, $Move_Effectiveness['Mult']);

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
       * Process rolling and setting ailments if applicable.
       */
      if ( isset($this->Ailment) )
      {
        switch ($this->Ailment)
        {
          case 'Flinch':
            if ( $Turn_First_Attacker == $Side )
              if ( mt_rand(1, 100) <= $this->Effect_Chance )
                $Target->SetStatus('Flinch');
            break;

          default:
            if ( mt_rand(1, 100) <= $this->Effect_Chance )
            {
              $Set_Status = $Target->SetStatus($this->Ailment);
              if ( $Set_Status )
                $Status_Dialogue = $Set_Status->Dialogue;
            }
            break;
        }
      }
      else
      {
        if ( $Attacker->Item->Name == "King's Rock" )
          if ( $Turn_First_Attacker == $Side )
            if ( mt_rand(1, 100) <= 10 )
              $Target->SetStatus('Flinch');
      }

      $Text = ($this->CanUserMove($Side)['Type'] == 'Success' ? "{$this->CanUserMove($Side)['Text']}" : '') .
              ($Attacker->HasStatus('Move Locked') ? "{$Attacker->Display_Name} is move locked!<br />" : '') .
              "{$Attacker->Display_Name} used {$this->Name} and dealt <b>" . number_format($Damage) . "</b> damage to {$Defender->Display_Name}." .
              ($this->Total_Hits > 1 ? "<br />It hit {$this->Total_Hits} times!" : '') .
              ($Move_Effectiveness['Text'] != '' ? "<br />{$Move_Effectiveness['Text']}" : '') .
              ($Does_Move_Crit ? '<br />It critically hit!' : '') .
              ($this->Recoil > 0 ? "<br />{$Attacker->Display_Name} took " . number_format($Recoil) . ' damage from the recoil!' : '') .
              ($Healing > 0 ? "<br />{$Attacker->Display_Name} restored " . number_format($Healing) . ' health!' : '') .
              ($this->Contact ? $this->HandleContact($Side)['Text'] : '') .
              (isset($Status_Dialogue) ? "<br />{$Target->Display_Name} {$Status_Dialogue}" : '');

      return [
        'Text' => $Text,
        'Effect_Text' => '',
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

      if ( in_array($Defender->Ability, ['Battle Armor', 'Shell Armor']) )
        return false;

      if ( $Attacker->Ability == 'Merciless' )
        if ( $Defender->HasStatus('Poisoned') )
          return true;

      if ( $Attacker->HasStatus('Focus Energy') )
        $this->Crit_Chance += 2;

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

      return $this->Contact;
    }

    /**
     * Handle contact effects.
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
            $Attacker->Ability != 'Immunity'
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

      if ( $Defender->Ability == 'Aftermath' )
      {
        if ( $Attacker->Ability != 'Damp' )
        {
          if ( $Attacker->Item->Name != 'Protective Pads' )
          {
            $Attacker->DecreaseHP(floor($Attacker->Max_HP / 4));

            $Text .= "<br />{$Attacker->Display_Name} took damage from the Aftermath!<br />";
          }
        }
      }

      if ( $Defender->Ability == 'Cute Charm' )
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

      if ( $Defender->Ability == 'Effect Spore' )
      {
        if
        (
          !$Attacker->HasTyping(['Grass']) ||
          $Attacker->Ability != 'Overcoat' ||
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

      if ( $Defender->Ability == 'Flame Body' )
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

      if ( $Defender->Ability == 'Fluffy' )
      {
        $Damage_Mod /= 2;

        if ( $this->Move_Type == 'Fire' )
          $Damage_Mod * 2;
      }

      if ( in_array($Defender->Ability, ['Gooey', 'Tangling Hair']) )
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

      if ( in_array($Defender->Ability, ['Iron Barbs', 'Rough Skin']) )
      {
        if ( $Attacker->Item->Name != 'Protective Pads' )
        {
          $Attacker->DecreaseHP(floor($Attacker->Max_HP / 8));

          $Text .= "<br />{$Attacker->Display_Name} hurt itself on {$Defender->Display_Name}'s {$Defender->Ability}!<br />";
        }
      }

      if ( $Defender->Ability == 'Mummy' )
      {
        if ( $Attacker->Item->Name != 'Protective Pads' )
        {
          $Attacker->SetAbility('Mummy');

          $Text .= "<br />{$Attacker->Display_Name}'s Ability has become Mummy!<br />";
        }
      }

      if ( $Defender->Ability == 'Perish Body' )
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

      if ( $Defender->Ability == 'Pickpocket' )
      {
        if
        (
          $Attacker->Item->Name != 'Protective Pads' ||
          $Attacker->Ability != 'Sticky Hold' ||
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

      if ( $Defender->Ability == 'Poison Point' )
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

      if ( $Defender->Ability == 'Static' )
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

      if ( $Defender->Ability == 'Wandering Spirit' )
      {
        if ( $Attacker->Item->Name != 'Protective Pads' )
        {
          if
          (
            !in_array($Attacker->Ability, ['Disguise', 'Flower Gift', 'Gulp Missile', 'Ice Face', 'Illusion', 'Imposter', 'Receiver', 'RKS System', 'Schooling', 'Stance Change', 'Wonder Guard', 'Zen Mode']) ||
            !in_array($Defender->Ability, ['Disguise', 'Flower Gift', 'Gulp Missile', 'Ice Face', 'Illusion', 'Imposter', 'Receiver', 'RKS System', 'Schooling', 'Stance Change', 'Wonder Guard', 'Zen Mode'])
          )
          {
            $Attacker_Ability = $Attacker->Ability;
            $Defender_Ability = $Defender->Ability;

            $Attacker->Ability = $Defender->Ability;
            $Defender->Ability = $Attacker->Ability;

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
      if
      (
        $_SESSION['Battle'][$Side]->Active->Primary_Type == $this->Move_Type ||
        $_SESSION['Battle'][$Side]->Active->Secondary_Type == $this->Move_Type
      )
      {
        if ( $_SESSION['Battle'][$Side]->Active->Ability == 'Adaptibility' )
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

      $Status_Mult = 1;
      if ( $Attacker->Ability == 'Guts' )
        if ( $Attacker->HasStatusFromArray(['Burn', 'Freeze', 'Paralyze', 'Poison', 'Sleep']) )
          $Status_Mult = 1.5;
      else
        if ( $Attacker->HasStatus('Burn') )
          $Status_Mult = 0.5;


      switch ($this->Damage_Type)
      {
        case 'Physical':
          $Damage = floor(((2 * $Attacker->Level / 5 + 2) * $this->Power * $Attacker->Stats['Attack']->Current_Value / $Defender->Stats['Defense']->Current_Value / 50 + 2) * 1 * $Weather_Mult * $Crit_Mult * (mt_rand(185, 200) / 200) * $STAB * $Move_Effectiveness * $Status_Mult * 1);
          break;

        case 'Special':
          $Damage = $Damage = floor(((2 * $Attacker->Level / 5 + 2) * $this->Power * $Attacker->Stats['Sp_Attack']->Current_Value / $Defender->Stats['Sp_Defense']->Current_Value / 50 + 2) * 1 * $Weather_Mult * $Crit_Mult * (mt_rand(185, 200) / 200) * $STAB * $Move_Effectiveness * $Status_Mult * 1);
          break;

        default:
          $Damage = 0;
      }

      if ( $Damage < 0 )
        $Damage = 0;

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
  }
