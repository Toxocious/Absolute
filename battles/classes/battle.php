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

      $this->ProcessEndOfTurn();

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
        $Side = $_SESSION['Battle'][$Side];
        $Active_Pokemon = $Side->Active;

        /**
         * Process the Pokemon's active Statuses.
         */
        if ( !empty($Active_Pokemon->Statuses) )
        {
          foreach ($Active_Pokemon->Statuses as $Status)
          {
            if ( $Status->Volatile )
            {
              switch ( $Status->Name )
              {
                case 'Burn':
                  $Burn_Mult = 1;
                  if ( $Active_Pokemon->Ability == 'Heatproof' )
                    $Burn_Mult = 2;

                  $Active_Pokemon->DecreaseHP($Active_Pokemon->Max_HP / (16 * $Burn_Mult));
                  break;

                case 'Poison':
                  if ( $Active_Pokemon->Ability == 'Poison Heal' )
                    $Active_Pokemon->IncreaseHP($Active_Pokemon->Max_HP / 8);
                  else
                    $Active_Pokemon->DecreaseHP($Active_Pokemon->Max_HP / 8);
              }
            }

            if ( $Status->Turns_Left === 0 )
              unset($Active_Pokemon->Statuses[$Status->Name]);

            if ( $Status->Turns_Left > 0 )
              $Status->UpdateStatus();
          }
        }

        /**
         * Process active Weather effects.
         */
        if ( !empty($this->Weather) )
        {
          if
          (
            !in_array($Active_Pokemon->Ability, ['Magic Guard', 'Overcoat']) ||
            $Active_Pokemon->Item->Name != 'Safety Goggles'
          )
          {
            switch ($this->Weather->Name)
            {
              case 'Hail':
                if ( !$Active_Pokemon->HasTyping(['Ice']) )
                  $Active_Pokemon->DecreaseHP($Active_Pokemon->Max_HP / 16);
                break;

              case 'Sandstorm':
                if ( !$Active_Pokemon->HasTyping(['Ground', 'Steel', 'Rock']) )
                  $Active_Pokemon->DecreaseHP($Active_Pokemon->Max_HP / 16);
                break;

              case 'Shadowy Aura':
                if ( !$Active_Pokemon->HasTyping(['Shadow']) )
                  $Active_Pokemon->DecreaseHP($Active_Pokemon->Max_HP / 16);
                break;
            }
          }
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
            $Field_Effect->DecrementTurnCount();
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

      if ( !isset($Ally_Active->Moves[$Move_Slot]) )
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

      $this->Ally_Move = $Ally_Active->Moves[$Move_Slot];
      $this->Foe_Move = $Foe_Active->FetchRandomMove();

      $First_Attacker = $this->DetermineFirstAttacker($this->Ally_Move, $this->Foe_Move);
      $_SESSION['Battle'][$this->Turn_ID]['First_Attacker'] = $First_Attacker;

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

              $Faint_Data = $Ally_Active->HandleFaint();

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

            $Faint_Data = $Foe_Active->HandleFaint();

            $Attack_Dialogue .= $Faint_Data['Text'];
            $Attack_Dialogue .= '<br /><br />';
            $Attack_Dialogue .= $Ally_Active->IncreaseExp()['Text'];

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

              $Faint_Data = $Foe_Active->HandleFaint();

              $Attack_Dialogue .= $Faint_Data['Text'];
              $Attack_Dialogue .= '<br /><br />';
              $Attack_Dialogue .= $Ally_Active->IncreaseExp()['Text'];

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

            $Faint_Data = $Ally_Active->HandleFaint();

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
          $Attack_Dialogue = "
            <input
              type='button'
              value='Continue Battle'
              style='font-weight: bold; padding: 5px 0px;'
              onmousedown='Battle.Continue(\"{$_SESSION['Battle']['Postcodes']['Continue']}\", event);'
            />
            <br /><br />
            {$Attack_Dialogue}
          ";
        }

        if ( $Faint_Data['Restart'] )
        {
          $Attack_Dialogue = "
            <input
              type='button'
              value='Restart Battle'
              style='font-weight: bold; padding: 5px 0px;'
              onmousedown='Battle.Restart(\"{$_SESSION['Battle']['Postcodes']['Restart']}\", event);'
            />
            <br /><br />
            {$Attack_Dialogue}
          ";
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
        if ( in_array($_SESSION['Battle'][$Side]->Active->Ability, ['Gale Wings', 'Prankster']) )
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

        if ( $_SESSION['Battle'][$Side]->Active->Ability == 'Triage' )
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

      if ( $Move_Data['Ally']['Priority'] == 0 && $Move_Data['Foe']['Priority'] == 0 )
      {
        if ( $Ally->Stats['Speed']->Current_Value > $Foe->Stats['Speed']->Current_Value )
          return 'Ally';
        else if ( $Ally->Stats['Speed']->Current_Value < $Foe->Stats['Speed']->Current_Value )
          return 'Foe';
        else
          return mt_rand(1, 2) === 1 ? 'Ally' : 'Foe';
      }
      else
      {
        if ( $Move_Data['Ally']['Priority'] == $Move_Data['Foe']['Priority'] )
        {
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
      int $Turn_Count
    )
    {
      if ( $this->IsFieldEffectActive($Side, $Field_Effect) )
        return false;

      if ( !isset($Turn_Count) )
        $Turn_Count = -1;

      $Set_Field = new \Field(
        $Side,
        $Field_Effect,
        $Turn_Count
      );

      if ( !$Set_Field )
        return false;

      $this->Field_Effects[$Set_Field->Name] = $Set_Field;

      return $Set_Field;
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
      if ( !isset($this->Field_Effects) )
        return false;

      foreach ( $this->Field_Effects as $Field )
      {
        if
        (
          $Field->Name == $Field_Effect &&
          $Field->Side == $Side
        )
          return $Field;
      }

      return false;
    }
  }
