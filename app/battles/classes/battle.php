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
      if ( !isset($_SESSION['EvoChroniclesRPG']['Battle']) )
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
        !in_array($Action, ['Switch', 'Attack', 'Continue', 'Restart', 'Bag', 'UseItem', 'Flee'])
      )
      {
        return [
          'Type' => 'Error',
          'Text' => 'An error occurred while performing your desired action.'
        ];
      }

      $_SESSION['EvoChroniclesRPG']['Battle']['Turn_ID']++;
      $this->Turn_ID = $_SESSION['EvoChroniclesRPG']['Battle']['Turn_ID'];

      $Data = json_decode($Data);

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

        case 'UseItem':
          return [
            'Type' => 'Success',
            'Text' => $this->UseItem($Data->Item, $Data->Slot),
          ];

        case 'Bag':
          return [
            'Type' => 'Success',
            'Text' => $this->DisplayBag()
          ];
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

        if ( !empty($End_Of_Turn['Continue']) && $End_Of_Turn['Continue'] )
          $this->Turn_Dialogue['Text'] = $this->RenderContinueButton($this->Turn_Dialogue['Text']);

        if ( !empty($End_Of_Turn['Restart']) && $End_Of_Turn['Restart'] )
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
      $End_Of_Turn = '<br /><br />';

      /**
       * Process Weather and Status effects for both side's active Pokemon.
       */
      foreach (['Ally', 'Foe'] as $Side)
      {
        switch ($Side)
        {
          case 'Ally':
            $Active_Ally = $_SESSION['EvoChroniclesRPG']['Battle']['Ally'];
            $Active_Foe = $_SESSION['EvoChroniclesRPG']['Battle']['Foe'];
            break;

          case 'Foe':
            $Active_Ally = $_SESSION['EvoChroniclesRPG']['Battle']['Foe'];
            $Active_Foe = $_SESSION['EvoChroniclesRPG']['Battle']['Ally'];
            break;
        }

        if ( $Active_Ally->Active->HP <= 0 )
          continue;

        /**
         * Process active Weather effects.
         */
        if ( !empty($_SESSION['EvoChroniclesRPG']['Battle']['Weather']) )
        {
          if
          (
            !in_array($Active_Ally->Active->Ability->Name, ['Magic Guard', 'Overcoat']) ||
            $Active_Ally->Active->Item->Name != 'Safety Goggles'
          )
          {
            switch ($_SESSION['EvoChroniclesRPG']['Battle']['Weather']->Name)
            {
              case 'Hail':
                if ( $Active_Ally->Active->Ability->Name == 'Ice Body' )
                {
                  $End_Of_Turn .= "{$Active_Ally->Active->Display_Name} restored HP because of the Hail!<br />";
                  $Active_Ally->Active->IncreaseHP($Active_Ally->Active->Max_HP / 16);
                }
                else if ( !$Active_Ally->Active->HasTyping([ 'Ice' ]) && !$Active_Ally->Active->HasAbility(['Magic Guard', 'Snow Cloak']) )
                {
                  $End_Of_Turn .= "{$Active_Ally->Active->Display_Name} took damage from the Hail!<br />";
                  $Active_Ally->Active->DecreaseHP($Active_Ally->Active->Max_HP / 16);
                }
                break;

              case 'Extremely Harsh Sunlight':
              case 'Harsh Sunlight':
                if ( $Active_Ally->Active->HasAbility([ 'Dry Skin', 'Solar Power' ]) )
                {
                  $End_Of_Turn .= "{$Active_Ally->Active->Display_Name} took damage from the sunlight!<br />";
                  $Active_Ally->Active->DecreaseHP($Active_Ally->Active->Max_HP / 8);
                }
                break;

              case 'Heavy Rain':
              case 'Rain':
                if ( $Active_Ally->Active->Ability->Name == 'Dry Skin' )
                {
                  $End_Of_Turn .= "{$Active_Ally->Active->Display_Name} restored HP because of the Rain!<br />";
                  $Active_Ally->Active->IncreaseHP($Active_Ally->Active->Max_HP / 8);
                }

                if ( $Active_Ally->Active->Ability->Name == 'Rain Dish' )
                {
                  $End_Of_Turn .= "{$Active_Ally->Active->Display_Name}'s restored HP because of the Rain! <br />";
                  $Active_Ally->Active->IncreaseHP($Active_Ally->Active->Max_HP / 16);
                }

                if ( $Active_Ally->Active->Ability->Name == 'Hydration' )
                {
                  foreach ($Active_Ally->Active->Statuses as $Status)
                  {
                    if ( !$Status->Volatile )
                    {
                      $End_Of_Turn .= "{$Active_Ally->Active->Display_Name} Hydration cured its {$Status->Name}!<br />";
                      unset($Active_Ally->Active->Statuses[$Status->Name]);
                    }
                  }
                }
                break;

              case 'Sandstorm':
                if ( !$Active_Ally->Active->HasTyping(['Ground', 'Steel', 'Rock']) && !$Active_Ally->Active->HasAbility(['Magic Guard', 'Sand Force', 'Sand Rush', 'Sand Veil']) )
                {
                  $End_Of_Turn .= "{$Active_Ally->Active->Display_Name} took damage from the Sandstorm!<br />";
                  $Active_Ally->Active->DecreaseHP($Active_Ally->Active->Max_HP / 16);
                }
                break;

              case 'Shadowy Aura':
                if ( !$Active_Ally->Active->HasTyping(['Shadow']) && $Active_Ally->Active->Ability->Name != 'Magic Guard' )
                {
                  $End_Of_Turn .= "{$Active_Ally->Active->Display_Name} took damage from the Shadowy Aura!<br />";
                  $Active_Ally->Active->DecreaseHP($Active_Ally->Active->Max_HP / 16);
                }
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

              $End_Of_Turn .= "{$Active_Ally->Active->Display_Name}'s Moody increased its {$Increase_Stat}!<br />";
            }

            $Reduce_Stat = $Minus_Stats[mt_rand(0, count($Minus_Stats))];
            if ( count($Reduce_Stat) > 0 )
            {
              $Decrease_Stat = $Reduce_Stat[mt_rand(0, count($Reduce_Stat))];
              $Active_Ally->Active->Stats[$Decrease_Stat]->SetValue(-1);

              $End_Of_Turn .= "{$Active_Ally->Active->Display_Name}'s Moody decreased its {$Increase_Stat}!<br />";
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
                  $End_Of_Turn .= "{$Active_Ally->Active->Display_Name}'s Shed Skin cured its {$Status->Name}!<br />";

                  $Active_Ally->Ability->SetProcStatus(true);
                  unset($Active_Ally->Active->Statuses[$Status->Name]);
                }
              }
            }
            break;

          case 'Speed Boost':
            if ( $Active_Ally->Active->Stats['Speed']->Stage < 6 )
            {
              $End_Of_Turn .= "{$Active_Ally->Active->Display_Name}'s Speed Boost increased its Speed!<br />";

              $Active_Ally->Active->Stats['Speed']->SetValue(1);
            }
            break;

          case 'Truant':
            if ( $Active_Ally->Active->Ability->Procced )
              $Active_Ally->Active->Ability->SetProcStatus(false);
            else
            {
              $End_Of_Turn .= "{$Active_Ally->Active->Display_Name} is loafing around.<br />";
              $Active_Ally->Active->Ability->SetProcStatus(true);
            }
            break;

          case 'Water Bubble':
          case 'Water Veil':
            if ( $Active_Ally->Active->HasStatus('Burn') )
            {
              $End_Of_Turn .= "{$Active_Ally->Active->Display_Name}'s {$Active_Ally->Active->Ability->Name} cured its Burn!<br />";
              unset($Active_Ally->Active->Statuses['Burn']);
            }
            break;
        }

        /**
         * Procees held item procs.
         * These items proc before any status ailments do.
         */
        if ( !empty($Active_Ally->Active->Item) )
        {
          switch ( $Active_Ally->Active->Item->Name )
          {
            case 'Black Sludge':
              if ( $Active_Ally->Active->HasTyping(['Poison']) )
              {
                $End_Of_Turn .= "{$Active_Ally->Active->Display_Name}'s restored its HP due to its Black Sludge!<br />";
                $Active_Ally->Active->IncreaseHP(floor($Active_Ally->Active->Max_HP / 16));
              }
              else
              {
                if ( $Active_Ally->Active->Ability->Name != 'Magic Guard' )
                {
                  $End_Of_Turn .= "{$Active_Ally->Active->Display_Name} took damage from its Black Sludge!<br />";
                  $Active_Ally->Active->DecreaseHP(floor($Active_Ally->Active->Max_HP / 8));
                }
              }
              break;
          }
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
                {
                  $End_Of_Turn .= "{$Active_Ally->Active->Display_Name} cured its Burn with its Magma Armor!<br />";
                  unset($Active_Ally->Active->Statuses[$Status->Name]);
                }

                $Burn_Mult = 1;
                if ( $Active_Ally->Active->Ability->Name == 'Heatproof' )
                  $Burn_Mult = 2;

                if ( $Active_Ally->Active->Ability->Name !== 'Magic Guard' )
                {
                  $End_Of_Turn .= "{$Active_Ally->Active->Display_Name} took damage from its Burn!<br />";
                  $Active_Ally->Active->DecreaseHP($Active_Ally->Active->Max_HP / (16 * $Burn_Mult));
                }
                break;

              case 'Paralysis':
                if ( $Active_Ally->Active->Ability->Name == 'Limber' )
                {
                  $End_Of_Turn .= "{$Active_Ally->Active->Display_Name}'s Limber cured its Paralysis!<br />";
                  unset($Active_Ally->Active->Statuses[$Status->Name]);
                }
                break;

              case 'Badly Poisoned':
              case 'Poison':
                if ( $Status->Name == 'Badly Poisoned' )
                  $Tick_Damage = ($Active_Ally->Active->Max_HP / 16) * $Status->Stacks;
                else
                  $Tick_Damage = $Active_Ally->Active->Max_HP / 8;

                if ( $Active_Ally->Active->Ability->Name == 'Poison Heal' )
                {
                  $End_Of_Turn .= "{$Active_Ally->Active->Display_Name} restored HP due to its Poison Heal!<br />";
                  $Active_Ally->Active->IncreaseHP($Active_Ally->Active->Max_HP / 8);
                }
                else if ( $Active_Ally->Active->Ability->Name != 'Magic Guard' )
                {
                  $End_Of_Turn .= "{$Active_Ally->Active->Display_Name} took damage from its Poison!<br />";
                  $Active_Ally->Active->DecreaseHP($Tick_Damage);
                }
                break;

              case 'Sleep':
                if ( $Active_Foe->Active->Ability->Name == 'Bad Dreams' && $Active_Ally->Active->Ability->Name != 'Comatose' )
                {
                  $End_Of_Turn .= "{$Active_Ally->Active->Display_Name} took damage in its Sleep!<br />";
                  $Active_Ally->Active->DecreaseHP($Active_Ally->Active->Max_HP / 8);
                }
                break;

              case 'Bind':
                if ( $Active_Foe->Active->Item->Name == 'Binding Band' )
                  $Active_Ally->Active->DecreaseHP($Active_Ally->Active->Max_HP / 6);
                else
                  $Active_Ally->Active->DecreaseHP($Active_Ally->Active->Max_HP / 8);

                $End_Of_Turn .= "{$Active_Ally->Active->Display_Name} took damage from being bound!<br />";
                break;

              case 'Leech Seed':
                $End_Of_Turn .= "{$Active_Ally->Active->Display_Name} is seeded!<br />";
                if ( $Active_Ally->Active->Ability == 'Liquid Ooze' )
                {
                  $End_Of_Turn .= "{$Active_Foe->Active->Display_Name}'s Liquid Ooze made it take damage!<br />";
                  $Active_Foe->Active->DecreaseHP($Active_Ally->Active->Max_HP / 8);
                }
                else
                {
                  if ( $Active_Ally->Active->Ability->Name != 'Magic Guard' )
                  {
                    $Active_Ally->Active->DecreaseHP($Active_Ally->Active->Max_HP / 8);
                  }

                  $Active_Foe->Active->IncreaseHP($Active_Ally->Active->Max_HP / 8);
                  $End_Of_Turn .= "{$Active_Foe->Active->Display_Name} regained HP from the Leech Seed!<br />";
                }
                break;
            }

            if ( $Status->Name != 'Charging' )
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
         * Procees held item procs.
         * These items explicitly proc AFTER any status ailments.
         */
        if ( !empty($Active_Ally->Active->Item) )
        {
          switch ( $Active_Ally->Active->Item->Name )
          {
            case 'Flame Orb':
              if ( !$Active_Ally->Active->HasStatus('Burn') )
              {
                $End_Of_Turn .= "{$Active_Ally->Active->Display_Name} became burned due to its Flame Orb!<br />";
                $Active_Ally->Active->SetStatus('Burn');
              }
              break;

            case 'Leftovers':
              {
                $End_Of_Turn .= "{$Active_Ally->Active->Display_Name} restored HP due to its Leftovers!<br />";
                $Active_Ally->Active->IncreaseHP(floor($Active_Ally->Active->Max_HP / 16));
              }
              break;

            case 'Life Orb':
              {
                $End_Of_Turn .= "{$Active_Ally->Active->Display_Name} took damage from its Life Orb!<br />";
                $Active_Ally->Active->DecreaeHP(floor($Active_Ally->Active->Max_HP / 10));
              }
              break;

            case 'Sticky Barb':
              if ( !$Active_Ally->Active->HasAbility(['Magic Guard']) )
              {
                $End_Of_Turn .= "{$Active_Ally->Active->Display_Name} took damage from its Sticky Barb!<br />";
                $Active_Ally->Active->DecreaeHP(floor($Active_Ally->Active->Max_HP / 8));
              }
              break;

            case 'Toxic Orb':
              if ( !$Active_Ally->Active->HasStatus('Badly Poisoned') )
              {
                $End_Of_Turn .= "{$Active_Ally->Active->Display_Name} became poisoned due to its Toxic Orb!<br />";
                $Active_Ally->Active->SetStatus('Badly Poisoned');
              }
              break;
          }
        }

        /**
         * Check the HP again, after weather and status ticks occur.
         */
        if ( $Active_Ally->Active->HP <= 0 )
        {
          $Process_Fainted_Pokemon = $this->ProcessFaintedPokemon(0, 0, true);
          if ( $Process_Fainted_Pokemon['Dialogue'] != '' )
            $End_Of_Turn .= $Process_Fainted_Pokemon['Dialogue'] . '<br />';
        }
      }

      /**
       * Process active terrain effects.
       */
      if ( !empty($_SESSION['EvoChroniclesRPG']['Battle']['Terrain']->Terrain) )
      {
        $_SESSION['EvoChroniclesRPG']['Battle']['Terrain']->Terrain->TickTerrain();

        if ( $_SESSION['EvoChroniclesRPG']['Battle']['Terrain']->Terrain->Turns_Left === 0 )
          $_SESSION['EvoChroniclesRPG']['Battle']['Terrain']->Terrain->EndTerrain();
      }

      /**
       * Decrement Weather turn count, and end the weather if necessary.
       */
      if ( !empty($_SESSION['EvoChroniclesRPG']['Battle']['Weather']) )
      {
        $_SESSION['EvoChroniclesRPG']['Battle']['Weather']->TickWeather();

        if ( $_SESSION['EvoChroniclesRPG']['Battle']['Weather']->Turns_Left == 0 )
        {
          $_SESSION['EvoChroniclesRPG']['Battle']['Weather']->EndWeather();
          unset($_SESSION['EvoChroniclesRPG']['Battle']['Weather']);
        }
      }

      /**
       * Process field effects.
       */
      if ( !empty($_SESSION['EvoChroniclesRPG']['Battle']['Field_Effects']) )
      {
        foreach ( $_SESSION['EvoChroniclesRPG']['Battle']['Field_Effects'] as $Field_Side => $Field_Effects )
        {
          foreach ( $Field_Effects as $Index => $Field_Effect )
          {
            $Field_Effect->TickField();

            if ( $Field_Effect->Turns_Left == 0 )
            {
              unset($Field_Effect);
            }
          }

          if ( empty($_SESSION['EvoChroniclesRPG']['Battle']['Field_Effects'][$Field_Side]) )
            unset($_SESSION['EvoChroniclesRPG']['Battle']['Field_Effects'][$Field_Side]);
        }
      }

      $End_Of_Turn = rtrim($End_Of_Turn, "<br />");

      return [
        'Text' => $End_Of_Turn,
        'Continue' => !empty($Process_Fainted_Pokemon['Continue']) ? $Process_Fainted_Pokemon['Continue'] : false,
        'Restart' => !empty($Process_Fainted_Pokemon['Restart']) ? $Process_Fainted_Pokemon['Restart'] : false,
      ];
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

      if ( $_SESSION['EvoChroniclesRPG']['Battle']['Ally']->Active->HP <= 0 )
      {
        $Ally_Next_Pokemon = $_SESSION['EvoChroniclesRPG']['Battle']['Ally']->NextPokemon();
        if ( $Ally_Next_Pokemon )
        {
          $Ally_Switch_Into = $_SESSION['EvoChroniclesRPG']['Battle']['Ally']->Roster[$Ally_Next_Pokemon]->SwitchInto();

          $Dialogue .= $Ally_Switch_Into['Text'];
        }
      }

      if ( $_SESSION['EvoChroniclesRPG']['Battle']['Foe']->Active->HP <= 0 )
      {
        $Foe_Next_Pokemon = $_SESSION['EvoChroniclesRPG']['Battle']['Foe']->NextPokemon();
        if ( $Foe_Next_Pokemon )
        {
          $Foe_Switch_Into = $_SESSION['EvoChroniclesRPG']['Battle']['Foe']->Roster[$Foe_Next_Pokemon]->SwitchInto();
          $_SESSION['EvoChroniclesRPG']['Battle']['Foe']->Active->EnableMoves();

          $Dialogue .= $Foe_Switch_Into['Text'];
        }
      }

      $_SESSION['EvoChroniclesRPG']['Battle']['Ally']->Active->EnableMoves();

      if ( isset($_SESSION['EvoChroniclesRPG']['Battle']['Postcodes']['Continue']) )
        unset($_SESSION['EvoChroniclesRPG']['Battle']['Postcodes']['Continue']);

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

      $Ally_ID = $_SESSION['EvoChroniclesRPG']['Battle']['Ally']->ID;
      $Foe_ID = $_SESSION['EvoChroniclesRPG']['Battle']['Foe']->ID;
      $Fight = $_SESSION['EvoChroniclesRPG']['Battle']['Battle_Type'];

      unset($_SESSION['EvoChroniclesRPG']['Battle']);

      $Battle = new $Fight($Ally_ID, $Foe_ID);
      $Restart = $Battle->CreateBattle();

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
          $Loser = $_SESSION['EvoChroniclesRPG']['Battle']['Ally'];
          $Winner = $_SESSION['EvoChroniclesRPG']['Battle']['Foe'];
          break;
        case 'Foe':
          $Loser = $_SESSION['EvoChroniclesRPG']['Battle']['Foe'];
          $Winner = $_SESSION['EvoChroniclesRPG']['Battle']['Ally'];
          break;
      }

      foreach ( ['Ally', 'Foe'] as $Side )
      {
        $_SESSION['EvoChroniclesRPG']['Battle'][$Side]->Active->DisableMoves();
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
      $Ally_Active = $_SESSION['EvoChroniclesRPG']['Battle']['Ally']->Active;
      $Foe_Active = $_SESSION['EvoChroniclesRPG']['Battle']['Foe']->Active;

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

      $Move_Slot = Purify($Move_Slot) - 1;
      $Ally_Move = $Ally_Active->Moves[$Move_Slot];
      $this->Foe_Move = $Foe_Active->FetchRandomMove();

      if ( empty($Ally_Move) )
      {
        return [
          'Type' => 'Error',
          'Text' => 'There was an error when processing your selected move.',
          'Debug' => $Ally_Active->Moves[$Move_Slot],
        ];
      }

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

      $_SESSION['EvoChroniclesRPG']['Battle']['Turn_Data']['Turn_' . $this->Turn_ID]['First_Attacker'] = $First_Attacker;

      $Attack_Dialogue = '';

      switch ( $First_Attacker )
      {
        case 'Ally':
          if ( $this->Ally_Move->Name == 'Baton Pass' )
          {
            $Attack_Dialogue .= 'Please choose a Pok&eacute;mon to swap into.';
            break;
          }

          $Ally_Attack = $this->PerformAttack('Ally', $this->Ally_Move);
          $Attack_Dialogue .= $Ally_Attack['Dialogue'];

          $Attack_Dialogue .= '<br /><br />';

          // Check for foe's switch-out ability/held item here, before performing attack
          $Foe_Attack = $this->PerformAttack('Foe', $this->Foe_Move);
          $Attack_Dialogue .= $Foe_Attack['Dialogue'];
          break;

        case 'Foe':
          $Foe_Attack = $this->PerformAttack('Foe', $this->Foe_Move);
          $Attack_Dialogue .= $Foe_Attack['Dialogue'];

          $Attack_Dialogue .= '<br /><br />';

          // Check for ally's switch-out ability/held item here, before performing attack
          $Ally_Attack = $this->PerformAttack('Ally', $this->Ally_Move);
          $Attack_Dialogue .= $Ally_Attack['Dialogue'];
          break;
      }

      $Process_Fainted_Pokemon = $this->ProcessFaintedPokemon($Ally_Attack['Damage'], $Foe_Attack['Damage']);
      $Attack_Dialogue .= $Process_Fainted_Pokemon['Dialogue'];

      if ( !empty($Process_Fainted_Pokemon['Continue']) )
      {
        $Attack_Dialogue = $this->RenderContinueButton($Attack_Dialogue);
      }
      else if ( !empty($Process_Fainted_Pokemon['Restart']) )
      {
        $Attack_Dialogue = $this->RenderRestartButton($Attack_Dialogue);
      }

      return [
        'Type' => 'Success',
        'Text' => $Attack_Dialogue
      ];
    }

    /**
     * Perform the logic behind an attack.
     *
     * @param {string} $Attacker
     * @param {object} $Move_Data
     */
    public function PerformAttack
    (
      string $Attacker,
      object $Move_Data
    )
    {
      switch ( $Attacker )
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

      if ( $Attacker->HP > 0 && $Defender->HP > 0 )
      {
        $Ally_Attack = $Attacker->Attack($Move_Data);

        $Attack_Dialogue = $Ally_Attack['Text'];

        $Defender->DecreaseHP($Ally_Attack['Damage']);

        return [
          'Damage' => $Ally_Attack['Damage'],
          'Dialogue' => $Attack_Dialogue,
        ];
      }

      return [
        'Damage' => null,
        'Dialogue' => '',
      ];
    }

    /**
     * Check for and handle Pokemon that have fainted.
     *
     * @param {int} $Ally_Damage_Dealt
     * @param {int} $Foe_Damage_Dealt
     */
    public function ProcessFaintedPokemon
    (
      int $Ally_Damage_Dealt = null,
      int $Foe_Damage_Dealt = null,
      bool $Triggered_By_Non_Attack = false
    )
    {
      if ( empty($Ally_Damage_Dealt) )
        $Ally_Damage_Dealt = 0;

      if ( empty($Foe_Damage_Dealt) )
        $Foe_Damage_Dealt = 0;

      $Attacker = $_SESSION['EvoChroniclesRPG']['Battle']['Ally'];
      $Defender = $_SESSION['EvoChroniclesRPG']['Battle']['Foe'];

      $Continue = false;
      $Faint_Dialogue = '';

      if ( $Defender->Active->HP <= 0 )
      {
        $Attacker->Active->DisableMoves();

        $Faint_Dialogue .= $Defender->Active->HandleFaint($Triggered_By_Non_Attack, $Ally_Damage_Dealt)['Text'];

        if ( !$Defender->NextPokemon() )
        {
          $Faint_Dialogue .= '<br />' . $this->EndBattle('Foe')['Text'];

          return [
            'Dialogue' => $Faint_Dialogue,
            'Restart' => true,
          ];
        }
        else
        {
          $Continue = true;
        }
      }

      if ( $Attacker->Active->HP <= 0 )
      {
        $Attacker->Active->DisableMoves();

        $Faint_Dialogue .= $Attacker->Active->HandleFaint($Triggered_By_Non_Attack, $Foe_Damage_Dealt)['Text'];

        if ( !$Attacker->NextPokemon() )
        {
          $Faint_Dialogue .= '<br />' . $this->EndBattle('Foe')['Text'];

          return [
            'Dialogue' => $Faint_Dialogue,
            'Restart' => true,
          ];
        }
        else
        {
          $Continue = true;
        }
      }

      return [
        'Dialogue' => $Faint_Dialogue,
        'Continue' => $Continue,
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
      $Ally_Active = $_SESSION['EvoChroniclesRPG']['Battle']['Ally']->Active;
      $Foe_Active = $_SESSION['EvoChroniclesRPG']['Battle']['Foe']->Active;

      $Slot = Purify($Roster_Slot) - 1;
      if ( !isset($_SESSION['EvoChroniclesRPG']['Battle']['Ally']->Roster[$Slot]) )
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

        $Perform_Switch = $_SESSION['EvoChroniclesRPG']['Battle']['Ally']->Roster[$Slot]->SwitchInto();
        $Switch_Dialogue .= $Perform_Switch['Text'];
      }
      else
      {
        $Perform_Switch = $_SESSION['EvoChroniclesRPG']['Battle']['Ally']->Roster[$Slot]->SwitchInto();
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
     * Render the user's bag.
     *
     * @return {string} $Bag_Dialogue
     */
    public function DisplayBag()
    {
      global $PDO;

      try
      {
        $Fetch_Items = $PDO->prepare("
          SELECT *
          FROM `items`
          WHERE `Owner_Current` = ? AND `Quantity` > 0
        ");
        $Fetch_Items->execute([ $_SESSION['EvoChroniclesRPG']['Battle']['Ally']->ID ]);
        $Fetch_Items->setFetchMode(\PDO::FETCH_ASSOC);
        $Bag_Items = $Fetch_Items->fetchAll();
      }
      catch ( \PDOException $e )
      {
        HandleError($e);
      }

      if ( empty($Bag_Items) )
        return 'Your bag is empty.';

      $Bag_Dialogue = "
        <div class='description'>Select the item that you wish to use, and the Pok&eacute;mon you wish to use it on.</div>
        <div class='flex' style='gap: 5px; justify-content: center;'>
      ";
      foreach ($Bag_Items as $Item)
      {
        $Bag_Dialogue .= "
          <div class='border-gradient hover'>
            <div style='padding: 5px 5px 0px;'>
              <img
                alt='{$Item['Item_Name']}'
                tooltip='{$Item['Item_Name']}'
                style='height: 30px; width: 30px;'
                onclick='Battle.UseItem({$Item['id']}, document.getElementById(\"use_item_on\").value, event);'
                src='" . DOMAIN_SPRITES . "/Items/{$Item['Item_Name']}.png'
              />
            </div>
          </div>
        ";
      }
      $Bag_Dialogue .= "
        </div>

        <br />
        <div>
          <select name='use_item_on' id='use_item_on'>
      ";

      foreach ($_SESSION['EvoChroniclesRPG']['Battle']['Ally']->Roster as $Pokemon)
      {
        $Bag_Dialogue .= "
          <option value='{$Pokemon->Slot}'>{$Pokemon->Display_Name} (Slot: {$Pokemon->Slot})</option>
        ";
      }

      $Bag_Dialogue .= "
          </select>
          <br />
        </div>
      ";

      return $Bag_Dialogue;
    }

    /**
     * Use the selected item.
     *
     * @param {} $Item_ID
     * @param {} $Roster_Slot
     * @return {string} $Use_Dialogue
     */
    public function UseItem
    (
      $Item_ID,
      $Roster_Slot
    )
    {
      global $PDO;

      if ( !in_array($Roster_Slot, ['1', '2', '3', '4', '5', '6']) || empty($_SESSION['EvoChroniclesRPG']['Battle']['Ally']->Roster[$Roster_Slot]) )
        return 'An invalid roster slot was selected.';

      if ( empty($Item_ID) )
        return 'An invalid item was selected.';

      try
      {
        $Fetch_Item = $PDO->prepare("
          SELECT *
          FROM `items`
          WHERE `id` = ? AND `Owner_Current` = ?
          LIMIT 1
        ");
        $Fetch_Item->execute([ $Item_ID, $_SESSION['EvoChroniclesRPG']['Battle']['Ally']->ID ]);
        $Fetch_Item->setFetchMode(\PDO::FETCH_ASSOC);
        $Item = $Fetch_Item->fetch();
      }
      catch ( \PDOException $e )
      {
        HandleError($e);
      }

      if ( empty($Item) )
        return 'An invalid item was selected.';

      if ( $Item['Quantity'] < 1 )
        return 'You do not have enough of this item.';

      $Item_Target = $_SESSION['EvoChroniclesRPG']['Battle']['Ally']->Roster[$Roster_Slot];

      $Use_Item_Dialogue = '';

      /**
       * Fetch all item effects.
       */
      switch ( $Item['Item_Name'] )
      {
        case 'Antidote':
          $Status = [
            ['Name' => 'Poison', 'Type' => 'Cure'],
            ['Name' => 'Badly Poisoned', 'Type' => 'Cure'],
          ];
          break;

        case 'Awakening':
          $Status = [
            ['Name' => 'Sleep', 'Type' => 'Cure'],
          ];
          break;

        case 'Berry Juice':
          if ( $Item_Target->HP < $Item_Target->Max_HP )
          {
            $HP_Change = [
              ['Target' => $Item_Target->Slot, 'Amount' => 20]
            ];
          }
          break;

        case 'Big Malasada':
        case 'Casteliacone':
        case 'Full Heal':
        case 'Heal Powder':
        case 'Lava Cookie':
        case 'Old Gateau':
          $Status = [
            ['Name' => 'Poison', 'Type' => 'Cure'],
            ['Name' => 'Badly Poisoned', 'Type' => 'Cure'],
            ['Name' => 'Sleep', 'Type' => 'Cure'],
            ['Name' => 'Paralysis', 'Type' => 'Cure'],
            ['Name' => 'Burn', 'Type' => 'Cure'],
            ['Name' => 'Freeze', 'Type' => 'Cure'],
          ];
          break;

        case 'Burn Heal':
          $Status = [
            ['Name' => 'Burn', 'Type' => 'Cure'],
          ];
          break;

        case 'Energy Powder':
          if ( $Item_Target->HP < $Item_Target->Max_HP )
          {
            $HP_Change = [
              ['Target' => $Item_Target->Slot, 'Amount' => 60]
            ];
          }
          break;

        case 'Energy Root':
          if ( $Item_Target->HP < $Item_Target->Max_HP )
          {
            $HP_Change = [
              ['Target' => $Item_Target->Slot, 'Amount' => 120]
            ];
          }
          break;

        case 'Fresh Water':
          if ( $Item_Target->HP < $Item_Target->Max_HP )
          {
            $HP_Change = [
              ['Target' => $Item_Target->Slot, 'Amount' => 50]
            ];
          }
          break;

        case 'Full Restore':
          $Status = [
            ['Name' => 'Poison', 'Type' => 'Cure'],
            ['Name' => 'Badly Poisoned', 'Type' => 'Cure'],
            ['Name' => 'Sleep', 'Type' => 'Cure'],
            ['Name' => 'Paralysis', 'Type' => 'Cure'],
            ['Name' => 'Burn', 'Type' => 'Cure'],
            ['Name' => 'Freeze', 'Type' => 'Cure'],
          ];

          if ( $Item_Target->HP < $Item_Target->Max_HP )
          {
            $HP_Change = [
              ['Target' => $Item_Target->Slot, 'Amount' => $Item_Target->Max_HP - $Item_Target->HP]
            ];
          }
          break;

        case 'Hyper Potion':
          if ( $Item_Target->HP < $Item_Target->Max_HP )
          {
            $HP_Change = [
              ['Target' => $Item_Target->Slot, 'Amount' => 120]
            ];
          }
          break;

        case 'Ice Heal':
          $Status = [
            ['Name' => 'Freeze', 'Type' => 'Cure'],
          ];
          break;

        case 'Lemonade':
          if ( $Item_Target->HP < $Item_Target->Max_HP )
          {
            $HP_Change = [
              ['Target' => $Item_Target->Slot, 'Amount' => 80]
            ];
          }
          break;

        case 'Max Potion':
          if ( $Item_Target->HP < $Item_Target->Max_HP )
          {
            $HP_Change = [
              ['Target' => $Item_Target->Slot, 'Amount' => $Item_Target->Max_HP - $Item_Target->HP]
            ];
          }
          break;

        case 'Max Revive':
          if ( $Item_Target->Fainted )
          {
            $Revives = [
              ['Target' => $Item_Target, 'Amount' => $Item_Target->Max_HP]
            ];
          }
          break;

        case 'Moomoo Milk':
          if ( $Item_Target->HP < $Item_Target->Max_HP )
          {
            $HP_Change = [
              ['Target' => $Item_Target->Slot, 'Amount' => 100]
            ];
          }
          break;

        case 'Oran Berry':
          if ( $Item_Target->HP < $Item_Target->Max_HP )
          {
            $HP_Change = [
              ['Target' => $Item_Target->Slot, 'Amount' => 10]
            ];
          }
          break;

        case 'Paralyze Heal':
          $Status = [
            ['Name' => 'Paralysis', 'Type' => 'Cure'],
          ];
          break;

        case 'Potion':
        case 'Sweet Heart':
          if ( $Item_Target->HP < $Item_Target->Max_HP )
          {
            $HP_Change = [
              ['Target' => $Item_Target->Slot, 'Amount' => 20]
            ];
          }
          break;

        case 'Revival Herb':
          if ( $Item_Target->Fainted )
          {
            $Revives = [
              ['Target' => $Item_Target, 'Amount' => $Item_Target->Max_HP]
            ];
          }
          break;

        case 'Revive':
          if ( $Item_Target->Fainted )
          {
            $Revives = [
              ['Target' => $Item_Target, 'Amount' => $Item_Target->Max_HP * 0.5]
            ];
          }
          break;

        case 'Sacred Ash':
          $Revives = [];
          foreach ( $_SESSION['EvoChroniclesRPG']['Battle']['Ally']->Roster as $Current_Pokemon )
          {
            if ( !$Current_Pokemon->Fainted )
              continue;

            $Revives[] = [
              ['Target' => $Current_Pokemon, 'Amount' => $Current_Pokemon->Max_HP]
            ];
          }
          break;

        case 'Sitrus Berry':
          if ( $Item_Target->HP < $Item_Target->Max_HP )
          {
            $HP_Change = [
              ['Target' => $Item_Target->Slot, 'Amount' => $Item_Target->Max_HP * 0.25]
            ];
          }
          break;

        case 'Soda Pop':
        case 'Super Potion':
          if ( $Item_Target->HP < $Item_Target->Max_HP )
          {
            $HP_Change = [
              ['Target' => $Item_Target->Slot, 'Amount' => 60]
            ];
          }
          break;

        case 'Dire Hit':
          $Item_Target->Critical_Hit_Boost += 1;
          $Use_Item_Dialogue .= "{$Item_Target->Display_Name}'s Critical Hit ratio was boosted!";
          break;

        case 'Dire Hit 2':
          $Item_Target->Critical_Hit_Boost += 2;
          $Use_Item_Dialogue .= "{$Item_Target->Display_Name}'s Critical Hit ratio was boosted!";
          break;

        case 'Dire Hit 3':
          $Item_Target->Critical_Hit_Boost += 3;
          $Use_Item_Dialogue .= "{$Item_Target->Display_Name}'s Critical Hit ratio was boosted!";
          break;

        case 'Guard Spec':
          $Status = [
            ['Name' => 'Guard Spec', 'Type' => 'Set'],
          ];
          break;

        case 'Reset Urge':
          $Item_Target->ResetStats();
          $Use_Item_Dialogue .= "{$Item_Target->Display_Name}'s stats have been reset!";
          break;

        case 'Roto Boost':
          $Stats = [
            ['Name' => 'Attack', 'Stages' => 1],
            ['Name' => 'Defense', 'Stages' => 1],
            ['Name' => 'Sp_Attack', 'Stages' => 1],
            ['Name' => 'Sp_Defense', 'Stages' => 1],
            ['Name' => 'Speed', 'Stages' => 1],
            ['Name' => 'Accuracy', 'Stages' => 1],
            ['Name' => 'Evasion', 'Stages' => 1],
          ];
          break;

        case 'Roto HP Restore':
          if ( $Item_Target->HP < $Item_Target->Max_HP )
          {
            $HP_Change = [
              ['Target' => $Item_Target->Slot, 'Amount' => $Item_Target->Max_HP - $Item_Target->HP]
            ];
          }
          break;

        case 'X Accuracy':
          $Stats = [
            ['Name' => 'Accuracy', 'Stages' => 1],
          ];
          break;

        case 'X Accuracy 2':
          $Stats = [
            ['Name' => 'Accuracy', 'Stages' => 2],
          ];
          break;

        case 'X Accuracy 3':
          $Stats = [
            ['Name' => 'Accuracy', 'Stages' => 3],
          ];
          break;

        case 'X Accuracy 6':
          $Stats = [
            ['Name' => 'Accuracy', 'Stages' => 6],
          ];
          break;

        case 'X Attack':
          $Stats = [
            ['Name' => 'Attack', 'Stages' => 1],
          ];
          break;

        case 'X Attack 2':
          $Stats = [
            ['Name' => 'Attack', 'Stages' => 2],
          ];
          break;

        case 'X Attack 3':
          $Stats = [
            ['Name' => 'Attack', 'Stages' => 3],
          ];
          break;

        case 'X Attack 6':
          $Stats = [
            ['Name' => 'Attack', 'Stages' => 6],
          ];
          break;

        case 'X Defense':
          $Stats = [
            ['Name' => 'Defense', 'Stages' => 1],
          ];
          break;

        case 'X Defense 2':
          $Stats = [
            ['Name' => 'Defense', 'Stages' => 2],
          ];
          break;

        case 'X Defense 3':
          $Stats = [
            ['Name' => 'Defense', 'Stages' => 3],
          ];
          break;

        case 'X Defense 6':
          $Stats = [
            ['Name' => 'Defense', 'Stages' => 6],
          ];
          break;

        case 'X Sp. Atk':
          $Stats = [
            ['Name' => 'Sp_Attack', 'Stages' => 1],
          ];
          break;

        case 'X Sp. Atk 2':
          $Stats = [
            ['Name' => 'Sp_Attack', 'Stages' => 2],
          ];
          break;

        case 'X Sp. Atk 3':
          $Stats = [
            ['Name' => 'Sp_Attack', 'Stages' => 3],
          ];
          break;

        case 'X Sp. Atk 6':
          $Stats = [
            ['Name' => 'Sp_Attack', 'Stages' => 6],
          ];
          break;

        case 'X Sp. Def':
          $Stats = [
            ['Name' => 'Sp_Defense', 'Stages' => 1],
          ];
          break;

        case 'X Sp. Def 2':
          $Stats = [
            ['Name' => 'Sp_Defense', 'Stages' => 2],
          ];
          break;

        case 'X Sp. Def 3':
          $Stats = [
            ['Name' => 'Sp_Defense', 'Stages' => 3],
          ];
          break;

        case 'X Sp. Def 6':
          $Stats = [
            ['Name' => 'Sp_Defense', 'Stages' => 6],
          ];
          break;

        case 'X Speed':
          $Stats = [
            ['Name' => 'Speed', 'Stages' => 1],
          ];
          break;

        case 'X Speed 2':
          $Stats = [
            ['Name' => 'Speed', 'Stages' => 2],
          ];
          break;

        case 'X Speed 3':
          $Stats = [
            ['Name' => 'Speed', 'Stages' => 3],
          ];
          break;

        case 'X Speed 6':
          $Stats = [
            ['Name' => 'Speed', 'Stages' => 6],
          ];
          break;

        default:
          return "{$Item['Item_Name']} is not currently supported in the battle system.";
      }

      /**
       * Process Pokemon resurrections.
       */
      if ( !empty($Revives) )
      {
        foreach ( $Revives as $Revive )
        {
          if ( !$Revive['Target']->Fainted )
            continue;

          $Revive['Target']->Revive($Revive['Amount']);

          $Use_Item_Dialogue .= "{$Revive['Target']->Display_Name} hsa been revived!<br />";
        }
      }

      /**
       * Process HP changes.
       */
      if ( !empty($HP_Changes) )
      {
        foreach ( $HP_Changes as $HP_Change )
        {
          if ( $HP_Change['Target']->HasStatus('Heal Block') )
            continue;

          $HP_Change['Target']->IncreaseHP($HP_Change['Amount']);

          $Use_Item_Dialogue .= "{$HP_Change['Target']->Display_Name} restored " . number_format($HP_Change['Amount']) . " HP!<br />";
        }
      }

      /**
       * Process all status changes.
       */
      if ( !empty($Status) )
      {
        foreach ( $Status as $Current_Status )
        {
          switch ( $Status['Type'] )
          {
            case 'Cure':
              if ( $Item_Target->Active->HasStatus($Current_Status['Name']) )
              {
                $Item_Target->Active->RemoveStatus($Current_Status['Name']);
                $Use_Item_Dialogue .= "{$Item_Target->Active} was cured of its {$Current_Status['Name']}!<br />";
              }
              break;

            case 'Set':
                $Set_Status = $Item_Target->Active->SetStatus($Current_Status['Name']);
                if ( !empty($Set_Status) )
                  $Use_Item_Dialogue .= "{$Item_Target->Active->Display_Name} {$Set_Status['Dialogue']}<br />";
              break;

            default:
              return "Invalid status type selected when using the {$Item['Item_Name']} use item.";
          }
        }
      }

      /**
       * Process all stat changes.
       */
      if ( !empty($Stats) )
      {
        $Active_Pokemon = $_SESSION['EvoChroniclesRPG']['Battle']['Ally']->Active;

        foreach ($Stats as $Stat)
        {
          if
          (
            $Active_Pokemon->Stats[$Stat['Name']]->Stage < 6 &&
            $Active_Pokemon->Stats[$Stat['Name']]->Stage > -6
          )
          {
            if ( $Active_Pokemon->Ability->Name == 'Contrary' )
              $Active_Pokemon->Stats[$Stat['Name']]->SetValue($Stat['Stages'] * -1);
            else
              $Active_Pokemon->Stats[$Stat['Name']]->SetValue($Stat['Stages']);
          }
        }
      }

      return $Use_Item_Dialogue;
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
      if ( !empty($_SESSION['EvoChroniclesRPG']['Battle']['Postcodes']['Continue']) )
        return;

      $this->GeneratePostcode('Continue');

      return "
        <input
          type='button'
          value='Continue Battle'
          style='font-weight: bold; padding: 5px 0px;'
          onmousedown='Battle.Continue(\"{$_SESSION['EvoChroniclesRPG']['Battle']['Postcodes']['Continue']}\", event);'
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
      if ( !empty($_SESSION['EvoChroniclesRPG']['Battle']['Postcodes']['Restart']) )
        return;

      if ( $_SESSION['EvoChroniclesRPG']['Battle']['Battle_Type'] == 'Wild' )
        return $Dialogue;

      $this->GeneratePostcode('Restart');

      return "
        <input
          type='button'
          value='Restart Battle'
          style='font-weight: bold; padding: 5px 0px;'
          onmousedown='Battle.Restart(\"{$_SESSION['EvoChroniclesRPG']['Battle']['Postcodes']['Restart']}\", event);'
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
      $_SESSION['EvoChroniclesRPG']['Battle']['Postcodes'][$Codename] = bin2hex(random_bytes(10));
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

      $Ally = $_SESSION['EvoChroniclesRPG']['Battle']['Ally']->Active;
      $Foe = $_SESSION['EvoChroniclesRPG']['Battle']['Foe']->Active;

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
        if ( in_array($_SESSION['EvoChroniclesRPG']['Battle'][$Side]->Active->Ability->Name, ['Gale Wings', 'Prankster']) )
        {
          if ( $_SESSION['EvoChroniclesRPG']['Battle'][$Side]->Active->HP == $_SESSION['EvoChroniclesRPG']['Battle'][$Side]->Active->Max_HP )
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

        if ( $_SESSION['EvoChroniclesRPG']['Battle'][$Side]->Active->Ability->Name == 'Triage' )
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
        if ( $Ally->Ability->Name == 'Stall' && $Foe->Ability->Name == 'Stall')
          return mt_rand(1, 2) === 1 ? 'Ally' : 'Foe';
        else if ( $Ally->Ability->Name == 'Stall' && $Foe->Ability->Name != 'Stall' )
          return 'Foe';
        else if ( $Ally->Ability->Name != 'Stall' && $Foe->Ability->Name == 'Stall' )
          return 'Ally';

        if ( $Ally->Ability->Name == 'Quick Draw' && mt_rand(1, 100) <= 30 )
          return 'Ally';

        if ( $Foe->Ability->Name == 'Quick Draw' && mt_rand(1, 100) <= 30 )
          return 'Foe';

        if ( $Ally->HasItem(['Quick Claw']) && mt_rand(1, 100) <= 20 )
          return 'Ally';

        if ( $Foe->HasItem(['Quick Claw']) && mt_rand(1, 100) <= 20 )
          return 'Foe';

        if ( $Ally->HasItem(['Full Incense', 'Lagging Tail']) )
          return 'Foe';

        if ( $Foe->HasItem(['Full Incense', 'Lagging Tail']) )
          return 'Ally';

        if ( $Ally->HasItem(['Custap Berry']) && $Ally->HP <= $Ally->Max_HP / 4 )
        {
          $Ally->Item->Consume();
          return 'Ally';
        }

        if ( $Foe->HasItem(['Custap Berry']) && $Foe->HP <= $Foe->Max_HP / 4 )
        {
          $Foe->Item->Consume();
          return 'Foe';
        }

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
      $_SESSION['EvoChroniclesRPG']['Battle']['Field_Effects'][$Side][] = $Set_Field;

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
      if ( empty($_SESSION['EvoChroniclesRPG']['Battle']['Field_Effects']) )
        return false;

      foreach ( $_SESSION['EvoChroniclesRPG']['Battle']['Field_Effects'] as $Current_Side => $Active_Fields )
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
      if ( !empty($_SESSION['EvoChroniclesRPG']['Battle']['Weather']) && $_SESSION['EvoChroniclesRPG']['Battle']['Weather']->Name == $Weather )
        return false;

      $Weather_Data = \Weather::WeatherList()[$Weather];
      if ( empty($Weather_Data) )
        return false;

      if ( !empty($_SESSION['EvoChroniclesRPG']['Battle']['Weather']) )
      {
        switch ( $Weather )
        {
          case 'Harsh Sunlight':
            if ( in_array($_SESSION['EvoChroniclesRPG']['Battle']['Weather']->Name, ['Strong Winds', 'Heavy Rain', 'Harsh Sunlight', 'Extremely Harsh Sunlight']) )
              return false;

            break;

          case 'Rain':
            if ( in_array($_SESSION['EvoChroniclesRPG']['Battle']['Weather']->Name, ['Strong Winds', 'Rain', 'Heavy Rain', 'Extremely Harsh Sunlight']) )
              return false;

            break;
        }
      }

      $Set_Weather = new \Weather($Weather, $Turns);

      $this->Weather = $Set_Weather;
      $_SESSION['EvoChroniclesRPG']['Battle']['Weather'] = $this->Weather;

      return $Set_Weather;
    }
  }
