<?php
  namespace BattleHandler;

  spl_autoload_register(function($Class)
  {
    $Battle_Directory = dirname(__DIR__, 1);
    $Class = strtolower($Class);

    if (file_exists($Battle_Directory . "\\classes\\{$Class}.php"))
      require_once $Battle_Directory . "\\classes\\{$Class}.php";

    if (file_exists($Battle_Directory . "\\fights\\{$Class}.php"))
      require_once $Battle_Directory . "\\fights\\{$Class}.php";
  });

  class Battle
  {
    public $Started = false;
    public $Ended = false;

    private $Battle_Type = null;
    private $Battle_Sim = false;
    private $Battle_Sim_Difficulty = false;

    public $Earn_Pokemon_Exp = true;
    public $Earn_Trainer_Exp = true;
    public $Earn_Clan_Exp = true;
    public $Earn_Money = true;
    public $Earn_Abso_Coins = true;

    public $Ally_Move = null;
    public $Foe_Move = null;
    public $Last_Move = null;

    public $Field_Effects = null;

    public $Terrain = null;
    public $Terrain_Turns_Left = null;

    public $Weather = null;
    public $Weather_Turns_Left = null;

    public $Time_Started = null;
    public $Time_Ended = null;

    public $Ally = null;
    public $Foe = null;

    public $Battle_ID = null;

    public $Turn_ID = 1;
    private $Turn_Dialogue = [
      'Type' => null,
      'Text' => null,
    ];

    /**
     * Once the client has chosen an action, process the current turn.
     * @param string $Action
     * @param string|int $Data
     */
    public function ProcessTurn
    (
      string $Action,
      $Data
    )
    {
      if ( !isset($_SESSION['Battle']) )
      {
        return [
          'Type' => 'Error',
          'Text' => 'An error occurred while processing your battle action.',
        ];
      }

      if
      (
        !isset($Action) ||
        !isset($Data) ||
        !in_array($Action, ['Switch', 'Attack', 'Continue', 'Restart', 'UseItem', 'Flee'])
      )
      {
        return [
          'Type' => 'Error',
          'Text' => 'An error occurred while performing your desired action.'
        ];
      }

      $_SESSION['Battle']['Turn_ID']++;
      $this->Turn_ID = $_SESSION['Battle']['Turn_ID'];

      /**
       * Process the requested action.
       */
      switch ($Action)
      {
        case 'Switch':
          $this->Turn_Dialogue = $this->HandleSwitch($Data);
          break;

        case 'Attack':
          $this->Turn_Dialogue = $this->HandleAttack($Data);
          break;

        case 'Continue':
          $this->Turn_Dialogue = $this->Continue($Data);
          break;

        case 'Restart':
          $this->Turn_Dialogue = $this->Restart($Data);
          break;

        default:
          return [
            'Type' => 'Error',
            'Text' => "Attempting to process action of {$Action}.",
          ];
          break;
      }

      $End_Of_Turn = $this->ProcessEndOfTurn();
      if ( !empty($End_Of_Turn) )
      {
        if ( !empty($End_Of_Turn['Text']) )
          $this->Turn_Dialogue['Text'] .= $End_Of_Turn['Text'];

        if ( !empty($End_Of_Turn['Continue']) )
          $this->Turn_Dialogue['Text'] = $this->RenderContinueButton($this->Turn_Dialogue['Text']);

        if ( !empty($End_Of_Turn['Restart']) )
          $this->Turn_Dialogue['Text'] = $this->RenderRestartButton($this->Turn_Dialogue['Text']);
      }

      return $this->Turn_Dialogue;
    }

    /**
     * Process logic that happens at the end of the turn.
     *
     * -. Moves that proc after the turn.
     *  - https://bulbapedia.bulbagarden.net/wiki/Rage_(move)
     *  - https://bulbapedia.bulbagarden.net/wiki/Rapid_Spin_(move)
     * 1. Move Lock (Petal Dance, Outrage, etc)
     *  - If the Petal Dance duration is disrupted (such as by full paralysis or hurting itself due to confusion), it will immediately end.
     *  - Confuse the user
     *  - Enable all moves of the user
     *  - Remove 'Trap' status
     * 2. Perish Song, etc
     * 3. Field Effects
     *  - https://bulbapedia.bulbagarden.net/wiki/Mist_(move)
     * 4. Burn/Poison/etc Damage Ticks
     *  - Bind, Wrap, Fire Spin, etc ticks for 1/8 Max HP (1/6 if User Holding Binding Band)
     *  - Shed Bell can prevent 'Trap' status
     *  - https://bulbapedia.bulbagarden.net/wiki/Leech_Seed_(move)
     * 5. Weather Ticks
     * 6. Process battle status effects
     *  - Bide, etc
     */
    public function ProcessEndOfTurn()
    {
      /**
       * Process Weather and Status effects for both side's active Pokemon.
       */
      foreach (['Ally', 'Foe'] as $Side)
      {
        switch ($Side)
        {
          case 'Ally':
            $Active_Ally = $_SESSION['Battle']['Ally'];
            $Active_Foe = $_SESSION['Battle']['Foe'];
            break;

          case 'Foe':
            $Active_Ally = $_SESSION['Battle']['Foe'];
            $Active_Foe = $_SESSION['Battle']['Ally'];
            break;
        }

        if ( $Active_Ally->Active->HP <= 0 )
          continue;

        /**
         * Process active Weather effects.
         */
        if ( !empty($_SESSION['Battle']['Weather']) )
        {
          if
          (
            !in_array($Active_Ally->Active->Ability->Name, ['Magic Guard', 'Overcoat']) ||
            $Active_Ally->Active->Item->Name != 'Safety Goggles'
          )
          {
            switch ($_SESSION['Battle']['Weather']->Name)
            {
              case 'Hail':
                if ( $Active_Ally->Active->Ability->Name == 'Ice Body' )
                  $Active_Ally->Active->IncreaseHP($Active_Ally->Active->Max_HP / 16);
                else if ( !$Active_Ally->Active->HasTyping([ 'Ice' ]) && !$Active_Ally->Active->HasAbility(['Magic Guard', 'Snow Cloak']) )
                  $Active_Ally->Active->DecreaseHP($Active_Ally->Active->Max_HP / 16);
                break;

              case 'Extremely Harsh Sunlight':
              case 'Harsh Sunlight':
                if ( $Active_Ally->Active->HasAbility([ 'Dry Skin', 'Solar Power' ]) )
                  $Active_Ally->Active->DecreaseHP($Active_Ally->Active->Max_HP / 8);
                break;

              case 'Heavy Rain':
              case 'Rain':
                if ( $Active_Ally->Active->Ability->Name == 'Dry Skin' )
                  $Active_Ally->Active->IncreaseHP($Active_Ally->Active->Max_HP / 8);

                if ( $Active_Ally->Active->Ability->Name == 'Rain Dish' )
                  $Active_Ally->Active->IncreaseHP($Active_Ally->Active->Max_HP / 16);

                if ( $Active_Ally->Active->Ability->Name == 'Hydration' )
                {
                  foreach ($Active_Ally->Active->Statuses as $Status)
                  {
                    if ( !$Status->Volatile )
                      unset($Active_Ally->Active->Statuses[$Status->Name]);
                  }
                }
                break;

              case 'Sandstorm':
                if ( !$Active_Ally->Active->HasTyping(['Ground', 'Steel', 'Rock']) && !$Active_Ally->Active->HasAbility(['Magic Guard', 'Sand Force', 'Sand Rush', 'Sand Veil']) )
                  $Active_Ally->Active->DecreaseHP($Active_Ally->Active->Max_HP / 16);
                break;

              case 'Shadowy Aura':
                if ( !$Active_Ally->Active->HasTyping(['Shadow']) && $Active_Ally->Active->Ability->Name != 'Magic Guard' )
                  $Active_Ally->Active->DecreaseHP($Active_Ally->Active->Max_HP / 16);
                break;
            }
          }
        }

        /**
         * Process ability procs.
         */
        switch ($Active_Ally->Active->Ability->Name)
        {
          case 'Moody':
            $Stats = ['Attack', 'Defense', 'Sp_Attack', 'Sp_Defense', 'Speed'];

            $Minus_Stats = [];
            $Plus_Stats = [];
            foreach ($Stats as $Index => $Stat)
            {
              if ( $Active_Ally->Active->Stats[$Stat]->Stage < 6 )
                $Plus_Stats[] = $Stat;

              if ( $Active_Ally->Active->Stats[$Stat]->Stage > -6 )
                $Minus_Stats[] = $Stat;
            }

            if ( count($Plus_Stats) > 0 )
            {
              $Increase_Stat = $Plus_Stats[mt_rand(0, count($Plus_Stats))];
              $Active_Ally->Active->Stats[$Increase_Stat]->SetValue(1);
            }

            $Reduce_Stat = $Minus_Stats[mt_rand(0, count($Minus_Stats))];
            if ( count($Reduce_Stat) > 0 )
            {
              $Decrease_Stat = $Reduce_Stat[mt_rand(0, count($Reduce_Stat))];
              $Active_Ally->Active->Stats[$Decrease_Stat]->SetValue(-1);
            }
            break;

          case 'Shed Skin':
            if ( !empty($Active_Ally->Active->Statuses) )
            {
              foreach ($Active_Ally->Active->Statuses as $Status)
              {
                if ( $Active_Ally->Ability->Procced )
                  break;

                if ( $Status->Volatile )
                  continue;

                if ( mt_rand(1, 100) <= 30 )
                {
                  $Active_Ally->Ability->SetProcStatus(true);
                  unset($Active_Ally->Active->Statuses[$Status->Name]);
                }
              }
            }
            break;

          case 'Speed Boost':
            if ( $Active_Ally->Active->Stats['Speed']->Stage < 6 )
            {
              $Active_Ally->Active->Stats['Speed']->SetValue(1);
            }
            break;
        }

        /**
         * Process the Pokemon's active Statuses.
         */
        if ( !empty($Active_Ally->Active->Statuses) )
        {
          foreach ($Active_Ally->Active->Statuses as $Status)
          {
            switch ( $Status->Name )
            {
              case 'Burn':
                if ( $Active_Ally->Active->Ability->Name == 'Magma Armor' )
                  unset($Active_Ally->Active->Statuses[$Status->Name]);

                $Burn_Mult = 1;
                if ( $Active_Ally->Active->Ability->Name == 'Heatproof' )
                  $Burn_Mult = 2;

                if ( $Active_Ally->Active->Ability->Name !== 'Magic Guard' )
                  $Active_Ally->Active->DecreaseHP($Active_Ally->Active->Max_HP / (16 * $Burn_Mult));
                break;

              case 'Paralysis':
                if ( $Active_Ally->Active->Ability->Name == 'Limber' )
                  unset($Active_Ally->Active->Statuses[$Status->Name]);
                break;

              case 'Badly Poisoned':
              case 'Poison':
                if ( $Status->Name == 'Badly Poisoned' )
                  $Tick_Damage = ($Active_Ally->Active->Max_HP / 16) * $Status->Stacks;
                else
                  $Tick_Damage = $Active_Ally->Active->Max_HP / 8;

                if ( $Active_Ally->Active->Ability->Name == 'Poison Heal' )
                  $Active_Ally->Active->IncreaseHP($Active_Ally->Active->Max_HP / 8);
                else if ( $Active_Ally->Active->Ability->Name != 'Magic Guard' )
                  $Active_Ally->Active->DecreaseHP($Tick_Damage);
                break;

              case 'Sleep':
                if ( $Active_Foe->Active->Ability->Name == 'Bad Dreams' && $Active_Ally->Active->Ability->Name != 'Comatose' )
                  $Active_Ally->Active->DecreaseHP($Active_Ally->Active->Max_HP / 8);
                break;

              case 'Leech Seed':
                if ( $Active_Ally->Active->Ability == 'Liquid Ooze' )
                  $Active_Foe->Active->DecreaseHP($Active_Ally->Active->Max_HP / 8);
                else
                {
                  if ( $Active_Ally->Active->Ability->Name != 'Magic Guard' )
                    $Active_Ally->Active->DecreaseHP($Active_Ally->Active->Max_HP / 8);

                  $Active_Foe->Active->IncreaseHP($Active_Ally->Active->Max_HP / 8);
                }
                break;
            }

            $Active_Ally->Active->UpdateStatus($Status->Name);
          }
        }
        else
        {
          if ( $Active_Ally->Active->Ability->Name == 'Marvel Scale' && $Active_Ally->Active->Ability->Procced )
          {
            $Active_Ally->Active->Ability->SetProcStatus(false);
            $Active_Ally->Active->Stats['Defense']->Current_Value /= 1.5;
          }

          if ( $Active_Ally->Active->Ability->Name == 'Quick Feet' && $Active_Ally->Active->Ability->Procced )
          {
            $Active_Ally->Active->Ability->SetProcStatus(false);
            $Active_Ally->Active->Stats['Speed']->Current_Value /= 1.5;
          }
        }

        /**
         * Check the HP again, after weather and status ticks occur.
         */
        if ( $Active_Ally->Active->HP <= 0 )
        {
          $Handle_Faint = $Active_Ally->Active->HandleFaint();
          if ( !empty($Handle_Faint) )
          {
            if ( $Handle_Faint['Continue'] )
            {
              $Faint_Dialogue = [
                'Text' => "
                  <br /><br />
                  {$Handle_Faint['Text']}<br />
                ",
                'Continue' => true,
              ];


              return $Faint_Dialogue;
            }

            if ( $Handle_Faint['Restart'] )
            {
              $End_Battle = $this->EndBattle($Side);

              $Faint_Dialogue = [
                'Text' => "
                  <br /><br />
                  {$Handle_Faint['Text']}<br />
                  {$End_Battle['Text']}
                ",
                'Restart' => true
              ];

              return $Faint_Dialogue;
            }
          }
        }
      }

      /**
       * Process active terrain effects.
       */
      if ( !empty($_SESSION['Battle']['Terrain']->Terrain) )
      {
        $_SESSION['Battle']['Terrain']->Terrain->TickTerrain();

        if ( $_SESSION['Battle']['Terrain']->Terrain->Turns_Left === 0 )
          $_SESSION['Battle']['Terrain']->Terrain->EndTerrain();
      }

      /**
       * Decrement Weather turn count, and end the weather if necessary.
       */
      if ( !empty($_SESSION['Battle']['Weather']) )
      {
        $_SESSION['Battle']['Weather']->TickWeather();

        if ( $_SESSION['Battle']['Weather']->Turns_Left == 0 )
        {
          $_SESSION['Battle']['Weather']->EndWeather();
          unset($_SESSION['Battle']['Weather']);
        }
      }

      /**
       * Process field effects.
       */
      if ( !empty($this->Field_Effects) )
      {
        foreach ($this->Field_Effects as $Field_Effect)
        {
          if ( $Field_Effect->Turns_Left === 0 )
            unset($this->Field_Effects[$Field_Effect->Name]);

          if ( $Field_Effect->Turns_Left > 0 )
            $Field_Effect->TickField();
        }
      }
    }

    /**
     * Sets the battle state up to be continued.
     * @param string $Postcode
     */
    public function Continue
    (
      string $Postcode
    )
    {
      if ( !isset($Postcode) )
      {

      }

      $Dialogue = '';

      if ( $_SESSION['Battle']['Ally']->Active->HP <= 0 )
      {
        $Ally_Next_Pokemon = $_SESSION['Battle']['Ally']->NextPokemon();
        if ( $Ally_Next_Pokemon )
        {
          $Ally_Switch_Into = $_SESSION['Battle']['Ally']->Roster[$Ally_Next_Pokemon]->SwitchInto();

          $Dialogue .= $Ally_Switch_Into['Text'];
        }
      }

      if ( $_SESSION['Battle']['Foe']->Active->HP <= 0 )
      {
        $Foe_Next_Pokemon = $_SESSION['Battle']['Foe']->NextPokemon();
        if ( $Foe_Next_Pokemon )
        {
          $Foe_Switch_Into = $_SESSION['Battle']['Foe']->Roster[$Foe_Next_Pokemon]->SwitchInto();
          $_SESSION['Battle']['Foe']->Active->EnableMoves();

          $Dialogue .= $Foe_Switch_Into['Text'];
        }
      }

      $_SESSION['Battle']['Ally']->Active->EnableMoves();

      if ( isset($_SESSION['Battle']['Postcodes']['Continue']) )
        unset($_SESSION['Battle']['Postcodes']['Continue']);

      return [
        'Type' => 'Success',
        'Text' => $Dialogue,
      ];
    }

    /**
     * Restarts the battle.
     * @param string $Postcode
     */
    public function Restart
    (
      string $Postcode
    )
    {
      if ( !isset($Postcode) )
      {

      }

      $Ally_ID = $_SESSION['Battle']['Ally']->ID;
      $Foe_ID = $_SESSION['Battle']['Foe']->ID;
      $Fight = $_SESSION['Battle']['Battle_Type'];

      unset($_SESSION['Battle']);

      $Battle = new $Fight();
      $Restart = $Battle->CreateBattle($Ally_ID, $Foe_ID);

      if ( !$Restart )
        $Dialogue = 'An error occurred while restarting your battle.';
      else
        $Dialogue = 'The battle has begun.';

      return [
        'Type' => 'Success',
        'Text' => isset($Dialogue) ? $Dialogue : '',
      ];
    }

    /**
     * Performs the necessary code at the end of a battle.
     * @param string $Side
     */
    public function EndBattle
    (
      string $Side
    )
    {
      if ( $Side == 'Ally' )
      {
        return [
          'Type' => 'Success',
          'Text' => 'You have been defeated.',
        ];
      }

      switch ( $Side )
      {
        case 'Ally':
          $Loser = $_SESSION['Battle']['Ally'];
          $Winner = $_SESSION['Battle']['Foe'];
          break;
        case 'Foe':
          $Loser = $_SESSION['Battle']['Foe'];
          $Winner = $_SESSION['Battle']['Ally'];
          break;
      }

      foreach ( ['Ally', 'Foe'] as $Side )
      {
        $_SESSION['Battle'][$Side]->Active->DisableMoves();
      }

      $Dialogue = "
        {$Loser->Username} has been defeated.
        <br />
      ";

      $Rewards = new \Rewards();
      $Dialogue .= $Rewards->ProcessRewards()['Text'];

      return [
        'Type' => 'Success',
        'Text' => $Dialogue,
      ];
    }

    /**
     * Handle the process of attacking the foe's Pokemon.
     * @param int $Move_Slot
     */
    public function HandleAttack
    (
      int $Move_Slot
    )
    {
      $Ally_Active = $_SESSION['Battle']['Ally']->Active;
      $Foe_Active = $_SESSION['Battle']['Foe']->Active;

      $Move_Slot = Purify($Move_Slot) - 1;
      $Ally_Move = $Ally_Active->Moves[$Move_Slot];

      if ( empty($Ally_Move) )
      {
        return [
          'Type' => 'Error',
          'Text' => 'There was an error when processing your selected move.',
          'Debug' => $Ally_Active->Moves[$Move_Slot],
        ];
      }

      if
      (
        $Ally_Active->HP == 0 ||
        $Foe_Active->HP == 0
      )
      {
        return [
          'Type' => 'Error',
          'Text' => 'Moves may not be used while an active Pok&eacute;mon is fainted.'
        ];
      }

      $this->Foe_Move = $Foe_Active->FetchRandomMove();

      if ( $Ally_Active->Ability->Name == 'Dancer' && $this->Foe_Move->HasFlag('dance') )
      {
        $First_Attacker = 'Foe';
        $this->Ally_Move = $this->Foe_Move;
      }
      else
      {
        $this->Ally_Move = $Ally_Move;
        $First_Attacker = $this->DetermineFirstAttacker($this->Ally_Move, $this->Foe_Move);
      }

      $_SESSION['Battle']['Turn_Data']['Turn_' . $this->Turn_ID]['First_Attacker'] = $First_Attacker;

      $Attack_Dialogue = '';

      switch ( $First_Attacker )
      {
        case 'Ally':
          $Ally_Attack = $Ally_Active->Attack($this->Ally_Move);

          $Attack_Dialogue .= $Ally_Attack['Text'];
          $Attack_Dialogue .= '<br /><br />';

          if ( $this->Ally_Move->Name == 'Baton Pass' )
          {
            $Attack_Dialogue .= 'Please choose a Pokemon to swap into.';
            break;
          }

          $Foe_Active->DecreaseHP($Ally_Attack['Damage']);

          if ( $Foe_Active->HP > 0 )
          {
            $Foe_Attack = $Foe_Active->Attack($this->Foe_Move);

            $Attack_Dialogue .= $Foe_Attack['Text'];

            $Ally_Active->DecreaseHP($Foe_Attack['Damage']);

            if ( $Ally_Active->HP <= 0 )
            {
              $Ally_Active->DisableMoves();

              $Faint_Data = $Ally_Active->HandleFaint(false, $Foe_Attack['Damage']);

              $Attack_Dialogue .= '<br /><br />';
              $Attack_Dialogue .= $Faint_Data['Text'];

              if ( $Faint_Data['Restart'] )
              {
                $End_Battle = $this->EndBattle('Ally');
                $Attack_Dialogue .= "<br />{$End_Battle['Text']}";
              }
            }
          }
          else
          {
            $Ally_Active->DisableMoves();

            $Faint_Data = $Foe_Active->HandleFaint(false, $Ally_Attack['Damage']);

            $Attack_Dialogue .= $Faint_Data['Text'];

            if ( $Faint_Data['Restart'] )
            {
              $End_Battle = $this->EndBattle('Foe');
              $Attack_Dialogue .= "<br />{$End_Battle['Text']}";
            }
          }
          break;

        case 'Foe':
          $Foe_Attack = $Foe_Active->Attack($this->Foe_Move);

          $Attack_Dialogue .= $Foe_Attack['Text'];
          $Attack_Dialogue .= '<br /><br />';

          $Ally_Active->DecreaseHP($Foe_Attack['Damage']);

          if ( $Ally_Active->HP > 0 )
          {
            $Ally_Attack = $Ally_Active->Attack($this->Ally_Move);

            $Attack_Dialogue .= $Ally_Attack['Text'];

            $Foe_Active->DecreaseHP($Ally_Attack['Damage']);

            if ( $Foe_Active->HP <= 0 )
            {
              $Ally_Active->DisableMoves();

              $Faint_Data = $Foe_Active->HandleFaint(false, $Ally_Attack['Damage']);

              $Attack_Dialogue .= $Faint_Data['Text'];

              if ( $Faint_Data['Restart'] )
              {
                $End_Battle = $this->EndBattle('Foe');
                $Attack_Dialogue .= "<br />{$End_Battle['Text']}";
              }
            }
          }
          else
          {
            $Ally_Active->DisableMoves();

            $Faint_Data = $Ally_Active->HandleFaint(false, $Foe_Attack['Damage']);

            $Attack_Dialogue .= '<br /><br />';
            $Attack_Dialogue .= $Faint_Data['Text'];

            if ( $Faint_Data['Restart'] )
            {
              $End_Battle = $this->EndBattle('Ally');
              $Attack_Dialogue .= "<br />{$End_Battle['Text']}";
            }
          }
          break;
      }

      /**
       * Render the 'Continue Battle' or 'Restart Battle' button if applicable.
       */
      if ( isset($Faint_Data) )
      {
        if ( $Faint_Data['Continue'] )
        {
          $Attack_Dialogue = $this->RenderContinueButton($Attack_Dialogue);
        }

        if ( $Faint_Data['Restart'] )
        {
          $Attack_Dialogue = $this->RenderRestartButton($Attack_Dialogue);
        }
      }

      return [
        'Type' => 'Success',
        'Text' => $Attack_Dialogue
      ];
    }

    /**
     * Handle the process of switching your active Pokemon.
     * @param int $Roster_Slot
     *
     * https://bulbapedia.bulbagarden.net/wiki/Recall
     *
     * On switch out, a check for abilities, moves, etc. that prevent
     * the Ally's active Pokemon from switching out needs to be done.
     *  - Abilities :: Arena Trap, etc
     *  - Moves :: Mean Look, etc
     */
    public function HandleSwitch
    (
      int $Roster_Slot
    )
    {
      $Ally_Active = $_SESSION['Battle']['Ally']->Active;
      $Foe_Active = $_SESSION['Battle']['Foe']->Active;

      $Slot = Purify($Roster_Slot) - 1;
      if ( !isset($_SESSION['Battle']['Ally']->Roster[$Slot]) )
      {
        return [
          'Type' => 'Error',
          'Text' => 'You may not switch into an invalid Pok&eacute;mon.'
        ];
      }

      if ( $Ally_Active->HasStatus('Trap') )
      {
        return [
          'Type' => 'Error',
          'Text' => "{$Ally_Active->Display_Name} is trapped and may not switch out!"
        ];
      }

      if
      (
        $Foe_Active->Ability->Name == 'Shadow Tag' &&
        $Ally_Active->Item->Name != 'Shed Bell' &&
        !$Ally_Active->HasTyping(['Ghost']) &&
        !in_array($Ally_Active->Last_Move['Name'], ['Baton Pass', 'Flip Turn', 'Parting Shot', 'U-turn', 'Volt Switch'])
      )
      {
        return [
          'Type' => 'Error',
          'Text' => "{$Ally_Active->Display_Name} is trapped and may not switch out!"
        ];
      }

      if
      (
        $Foe_Active->Ability->Name == 'Arena Trap' &&
        $Ally_Active->IsGrounded() &&
        $Ally_Active->Item->Name != 'Shed Bell' &&
        !in_array($Ally_Active->Last_Move['Name'], ['Baton Pass', 'Flip Turn', 'Parting Shot', 'U-turn', 'Volt Switch'])
      )
      {
        return [
          'Type' => 'Error',
          'Text' => "{$Ally_Active->Display_Name} is trapped and may not switch out!"
        ];
      }

      $Switch_Dialogue = '';

      $this->Foe_Move = $Foe_Active->FetchRandomMove();

      /**
       * Checking to see if the selected move is Pursuit.
       */
      if
      (
        $this->Foe_Move->Name == 'Pursuit'
      )
      {
        if
        (
          $Foe_Active->HP > 0 &&
          $Ally_Active->HP > 0
        )
        {
          $Foe_Attack = $Foe_Active->Attack($this->Foe_Move);
          $Ally_Active->DecreaseHP($Foe_Attack['Damage']);

          $Switch_Dialogue .= $Foe_Attack['Text'];
          $Switch_Dialogue .= '<br /><br />';
        }

        $Perform_Switch = $_SESSION['Battle']['Ally']->Roster[$Slot]->SwitchInto();
        $Switch_Dialogue .= $Perform_Switch['Text'];
      }
      else
      {
        $Perform_Switch = $_SESSION['Battle']['Ally']->Roster[$Slot]->SwitchInto();
        $Switch_Dialogue .= $Perform_Switch['Text'];

        if
        (
          $Foe_Active->HP > 0 &&
          $Ally_Active->HP > 0
        )
        {
          $Foe_Attack = $Foe_Active->Attack($this->Foe_Move);
          $Ally_Active->DecreaseHP($Foe_Attack['Damage']);

          $Switch_Dialogue .= '<br /><br />';
          $Switch_Dialogue .= $Foe_Attack['Text'];
        }
      }

      return [
        'Type' => 'Success',
        'Text' => $Switch_Dialogue,
      ];
    }

    /**
     * Render the Continue button.
     * @param {string} $Dialogue
     * @return {string} $Button_Text
     */
    public function RenderContinueButton
    (
      string $Dialogue
    )
    {
      $this->GeneratePostcode('Continue');

      return "
        <input
          type='button'
          value='Continue Battle'
          style='font-weight: bold; padding: 5px 0px;'
          onmousedown='Battle.Continue(\"{$_SESSION['Battle']['Postcodes']['Continue']}\", event);'
        />
        <br /><br />
        {$Dialogue}
      ";
    }

    /**
     * Render the  Restart button.
     * @param {string} $Dialogue
     * @return {string} $Button_Text
     */
    public function RenderRestartButton
    (
      string $Dialogue
    )
    {
      $this->GeneratePostcode('Restart');

      return "
        <input
          type='button'
          value='Restart Battle'
          style='font-weight: bold; padding: 5px 0px;'
          onmousedown='Battle.Restart(\"{$_SESSION['Battle']['Postcodes']['Restart']}\", event);'
        />
        <br /><br />
        {$Dialogue}
      ";
    }

    /**
     * Generate the necessary postcodes.
     * @param string $Codename
     */
    public function GeneratePostcode
    (
      string $Codename
    )
    {
      $_SESSION['Battle']['Postcodes'][$Codename] = bin2hex(random_bytes(10));
    }

    /**
     * Determine which Pokemon attacks first.
     * Determined by Move Priority, then Pokemon Speed, and if tied, randomly chosen.
     * @param object $Ally_Move
     * @param object $Foe_Move
     */
    public function DetermineFirstAttacker
    (
      object $Ally_Move,
      object $Foe_Move
    )
    {
      if ( !isset($Ally_Move) || !isset($Foe_Move) )
        return false;

      $Ally = $_SESSION['Battle']['Ally']->Active;
      $Foe = $_SESSION['Battle']['Foe']->Active;

      $Move_Data = [
        'Ally' => [
          'Name' => $Ally_Move->Name,
          'Priority' => $Ally_Move->Priority,
          'Damage_Type' => $Ally_Move->Damage_Type,
          'Move_Type' => $Ally_Move->Move_Type
        ],
        'Foe' => [
          'Name' => $Foe_Move->Name,
          'Priority' => $Foe_Move->Priority,
          'Damage_Type' => $Foe_Move->Damage_Type,
          'Move_Type' => $Foe_Move->Move_Type
        ],
      ];

      foreach (['Ally', 'Foe'] as $Side)
      {
        if ( in_array($_SESSION['Battle'][$Side]->Active->Ability->Name, ['Gale Wings', 'Prankster']) )
        {
          if ( $_SESSION['Battle'][$Side]->Active->HP == $_SESSION['Battle'][$Side]->Active->Max_HP )
          {
            if
            (
              $Move_Data[$Side]['Damage_Type'] == 'Status' ||
              $Move_Data[$Side]['Move_Type'] == 'Flying'
            )
            {
              $Move_Data[$Side]['Priority'] += 1;
            }
          }
        }

        if ( $_SESSION['Battle'][$Side]->Active->Ability->Name == 'Triage' )
        {
          if
          (
            $Move_Data[$Side]['Category'] == 'Heal' ||
            $Move_Data[$Side]['Drain'] > 0 ||
            $Move_Data[$Side]['Healing'] > 0
          )
          {
            $Move_Data[$Side]['Priority'] += 3;
          }
        }
      }

      if ( $Move_Data['Ally']['Priority'] == $Move_Data['Foe']['Priority'] )
      {
        if ( $Ally->Ability->Name == 'Quick Draw' && mt_rand(1, 100) <= 30 )
          return 'Ally';

        if ( $Foe->Ability->Name == 'Quick Draw' && mt_rand(1, 100) <= 30 )
          return 'Foe';

        if ( $Ally->Stats['Speed']->Current_Value > $Foe->Stats['Speed']->Current_Value )
          return 'Ally';
        else if ( $Ally->Stats['Speed']->Current_Value < $Foe->Stats['Speed']->Current_Value )
          return 'Foe';
        else
          return mt_rand(1, 2) === 1 ? 'Ally' : 'Foe';
      }
      else if ( $Move_Data['Ally']['Priority'] > $Move_Data['Foe']['Priority'] )
      {
        return 'Ally';
      }
      else
      {
        return 'Foe';
      }
    }

    /**
     * Get the information of a currently set field effect.
     * @param {string} $Side
     * @param {string} $Field_Effect
     * @return {object} $Field_Data
     */
    public function GetFieldEffectData
    (
      string $Side,
      string $Field_Effect
    )
    {
      if ( !isset($this->Field_Effects) )
        return false;

      $Field_Effect_Is_Active = $this->IsFieldEffectActive($Side, $Field_Effect);
      if ( !$Field_Effect_Is_Active )
        return false;

      return $Field_Effect_Is_Active;
    }

    /**
     * Sets a global field effect.
     * @param string Side
     * @param string $Field_Effect
     * @param int $Turn_Count
     */
    public function SetFieldEffect
    (
      string $Side,
      string $Field_Effect,
      int $Turn_Count = null
    )
    {
      if ( $this->IsFieldEffectActive($Side, $Field_Effect) )
        return false;

      $Set_Field = new \Field(
        $Side,
        $Field_Effect,
        !empty($Turn_Count) ?: null
      );

      $this->Field_Effects[$Side][] = $Set_Field;
      $_SESSION['Battle']['Field_Effects'][$Side][] = $Set_Field;

      return $Set_Field;
    }

    /**
     * Remove the desired field effect.
     * @param {string} $Side
     * @param {string} $Field_Effect
     * @return {bool}
     */
    public function RemoveFieldEffect
    (
      string $Side,
      string $Field_Effect
    )
    {
      if ( !isset($this->Field_Effects) )
        return false;

      $Field_Effect_Is_Active = $this->IsFieldEffectActive($Side, $Field_Effect);
      if ( !$Field_Effect_Is_Active )
        return false;

      unset($this->Field_Effects[$Field_Effect]);

      return true;
    }

    /**
     * Determines if a global field effect is active.
     * @param string $Side
     * @param string $Field_Effect
     */
    public function IsFieldEffectActive
    (
      string $Side,
      string $Field_Effect
    )
    {
      if ( empty($_SESSION['Battle']['Field_Effects']) )
        return false;

      foreach ( $_SESSION['Battle']['Field_Effects'] as $Current_Side => $Active_Fields )
      {
        if ( $Side != $Current_Side )
          continue;

        foreach ( $Active_Fields as $Current_Field )
          if ( $Current_Field->Name == $Field_Effect )
            return $Current_Field;
      }

      return false;
    }

    /**
     * Set a global weather effect.
     * @param string $Weather
     * @param int $Turns
     */
    public function SetWeather
    (
      string $Weather,
      $Turns = 5
    )
    {
      if ( !empty($_SESSION['Battle']['Weather']) && $_SESSION['Battle']['Weather']->Name == $Weather )
        return false;

      $Weather_Data = \Weather::WeatherList()[$Weather];
      if ( empty($Weather_Data) )
        return false;

      if ( !empty($_SESSION['Battle']['Weather']) )
      {
        switch ( $Weather )
        {
          case 'Harsh Sunlight':
            if ( in_array($_SESSION['Battle']['Weather']->Name, ['Strong Winds', 'Heavy Rain', 'Harsh Sunlight', 'Extremely Harsh Sunlight']) )
              return false;

            break;

          case 'Rain':
            if ( in_array($_SESSION['Battle']['Weather']->Name, ['Strong Winds', 'Rain', 'Heavy Rain', 'Extremely Harsh Sunlight']) )
              return false;

            break;
        }
      }

      $Set_Weather = new \Weather($Weather, $Turns);

      $this->Weather = $Set_Weather;
      $_SESSION['Battle']['Weather'] = $this->Weather;

      return $Set_Weather;
    }
  }
