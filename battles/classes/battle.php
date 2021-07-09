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

    public $Processing_Side = null;

    public $Ally_Move = null;
    public $Foe_Move = null;
    public $Last_Move = null;

    public $Trick_Room = null;
    public $Trick_Room_Turns_Left = null;

    public $Terrain = null;
    public $Terrain_Turns_Left = null;

    public $Weather = null;
    public $Weather_Turns_Left = null;

    public $Time_Started = null;
    public $Time_Ended = null;

    public $Ally = null;
    public $Foe = null;

    private $Battle_ID = null;

    public $Turn_ID = 1;
    private $Turn_Dialogue = [
      'Type' => null,
      'Text' => null,
    ];

    /**
     * Once the client has chosen an action, process the current turn.
     * @param string $Action
     * @param $Move
     */
    public function ProcessTurn
    (
      string $Action
    )
    {
      if ( !isset($Action) || !in_array($Action, ['Switch', 'Attack', 'UseItem', 'Flee']) )
      {
        return [
          'Type' => 'Error',
          'Text' => 'An error occurred while performing your desired action.'
        ];
      }

      $Ally_Active = $_SESSION['Battle']['Ally']['Active'];
      $Foe_Active = $_SESSION['Battle']['Foe']['Active'];

      $_SESSION['Battle']['Turn_ID']++;
      $this->Turn_ID = $_SESSION['Battle']['Turn_ID'];

      /**
       * Process the requested action.
       */
      switch ($Action)
      {
        case 'Switch':
          $this->Turn_Dialogue = $this->HandleSwitch($_GET['Slot']);
          break;

        case 'Attack':
          $this->Turn_Dialogue = $this->HandleAttack($_GET['Move']);
          break;

        default:
          return [
            'Type' => 'Error',
            'Text' => "Attempting to process action of {$Action}.",
          ];
          break;
      }

      /**
       * If either side's Pokemon has fainted, determine whether or
       * not the battle should prompt the user to continue or to restart.
       */

      return $this->Turn_Dialogue;
    }

    /**
     * Sets the battle state up to be continued.
     */
    public function Continue()
    {
      return [
        'Type' => 'Success',
        'Text' => 'The battle has continued.',
      ];
    }

    /**
     * Restarts the battle.
     */
    public function Restart()
    {
      return [
        'Type' => 'Success',
        'Text' => 'The battle has been restarted.',
      ];
    }

    /**
     * Handle the process of attacking the foe's Pokemon.
     */
    public function HandleAttack
    (
      int $Move
    )
    {
      $Ally_Active = $_SESSION['Battle']['Ally']['Active'];
      $Foe_Active = $_SESSION['Battle']['Foe']['Active'];

      if ( !isset($Ally_Active->Moves[$Move]) )
      {
        return [
          'Type' => 'Error',
          'Text' => 'There was an error when processing your selected move.'
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

      $Move_Slot = Purify($Move) - 1;

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

          $Foe_Active->DecreaseHP($Ally_Attack['Damage']);

          if ( $Foe_Active->HP > 0 )
          {
            $Foe_Attack = $Foe_Active->Attack($this->Foe_Move);

            $Attack_Dialogue .= $Foe_Attack['Text'];

            $Ally_Active->DecreaseHP($Foe_Attack['Damage']);

            if ( $Ally_Active->HP <= 0 )
            {
              for ( $i = 0; $i < 4; $i++ )
                $Ally_Active->Moves[$i]->Disabled = true;

              $Attack_Dialogue .= '<br /><br />';
              $Attack_Dialogue .= "{$Ally_Active->Display_Name} has fainted.";
            }
          }
          else
          {
            for ( $i = 0; $i < 4; $i++ )
              $Ally_Active->Moves[$i]->Disabled = true;

            $Attack_Dialogue .= "{$Foe_Active->Display_Name} has fainted.";
            $Attack_Dialogue .= '<br /><br />';
            $Attack_Dialogue .= $Ally_Active->IncreaseExp()['Text'];
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
              for ( $i = 0; $i < 4; $i++ )
                $Ally_Active->Moves[$i]->Disabled = true;

              $Attack_Dialogue .= '<br /><br />';
              $Attack_Dialogue .= "{$Foe_Active->Display_Name} has fainted.";
            }
          }
          else
          {
            for ( $i = 0; $i < 4; $i++ )
              $Ally_Active->Moves[$i]->Disabled = true;

            $Attack_Dialogue .= "{$Ally_Active->Display_Name} has fainted.";
            $Attack_Dialogue .= '<br /><br />';
            $Attack_Dialogue .= $Ally_Active->IncreaseExp()['Text'];
          }
          break;
      }

      return [
        'Type' => 'Success',
        'Text' => $Attack_Dialogue
      ];
    }

    /**
     * Handle the process of switching your active Pokemon.
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
      int $Slot
    )
    {
      $Ally_Active = $_SESSION['Battle']['Ally']['Active'];
      $Foe_Active = $_SESSION['Battle']['Foe']['Active'];

      $Slot = Purify($Slot) - 1;
      if ( !isset($_SESSION['Battle']['Ally']['Roster'][$Slot]) )
      {
        return [
          'Type' => 'Error',
          'Text' => 'You may not switch into an invalid Pok&eacute;mon.'
        ];
      }

      if ( $Ally_Active->HasStatus('Trapped') )
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

        $Perform_Switch = $_SESSION['Battle']['Ally']['Roster'][$Slot]->SwitchInto();
        $Switch_Dialogue .= $Perform_Switch['Text'];
      }
      else
      {
        $Perform_Switch = $_SESSION['Battle']['Ally']['Roster'][$Slot]->SwitchInto();
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
     * Determine which Pokemon attacks first.
     * Determined by Move Priority, then Pokemon Speed, and if tied, randomly chosen.
     */
    public function DetermineFirstAttacker
    (
      $Ally_Move,
      $Foe_Move
    )
    {
      if ( !isset($Ally_Move) || !isset($Foe_Move) )
        return false;

      $Ally = $_SESSION['Battle']['Ally']['Active'];
      $Foe = $_SESSION['Battle']['Foe']['Active'];

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
        if ( in_array($_SESSION['Battle'][$Side]['Active']->Ability, ['Gale Wings', 'Prankster']) )
        {
          if ( $_SESSION['Battle'][$Side]['Active']->HP == $_SESSION['Battle'][$Side]['Active']->Max_HP )
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

        if ( $_SESSION['Battle'][$Side]['Active']->Ability == 'Triage' )
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
     * Set the current weather.
     */
    public function SetWeather
    (
      string $Weather
    )
    {
      $this->Weather = $Weather;
      $this->Weather_Turns_Left = 5;

      switch ($Weather)
      {
        case 'Clear Skies':
          return [
            'Text' => ''
          ];

        case 'Fog':
          return [
            'Text' => 'The fog is deep...<br />',
          ];

        case 'Hail':
          return [
            'Text' => 'It started to hail!<br />'
          ];

        case 'Rain':
          return [
            'Text' => 'It started to rain!<br />'
          ];

        case 'Heavy Rain':
          return [
            'Text' => 'A heavy rain begain to fall!<br />'
          ];

        case 'Sandstorm':
          return [
            'Text' => 'A sandstorm kicked up!<br />'
          ];

        case 'Harsh Sunlight':
          return [
            'Text' => 'The sunlight turned harsh!<br />'
          ];

        case 'Extremely Harsh Sunlight':
          return [
            'Text' => 'The sunlight turned extremely harsh!<br />'
          ];

        case 'Shadowy Aura':
          return [
            'Text' => 'A shadowy aura filled the sky!<br />'
          ];

        case 'Strong Wings':
          return [
            'Text' => 'Mysterious strong winds are protecting Flying-type Pok&eacute;mon!<br />'
          ];
      }
    }

    /**
     * End the current weather.
     */
    public function EndWeather()
    {
      switch ($this->Weather)
      {
        case 'Clear Skies':
          return [
            'Text' => ''
          ];

        case 'Fog':
          return [
            'Text' => 'The fog has been blown away!<br />',
          ];

        case 'Hail':
          return [
            'Text' => 'The hail stopped.<br />'
          ];

        case 'Rain':
          return [
            'Text' => 'The rain stopped.<br />'
          ];

        case 'Heavy Rain':
          return [
            'Text' => 'The heavy rain has lifted!<br />'
          ];

        case 'Sandstorm':
          return [
            'Text' => 'The sandstorm subsided.<br />'
          ];

        case 'Harsh Sunlight':
          return [
            'Text' => 'The harsh sunlight faded.<br />'
          ];

        case 'Extremely Harsh Sunlight':
          return [
            'Text' => 'The harsh sunlight faded.<br />'
          ];

        case 'Shadowy Aura':
          return [
            'Text' => 'The shadowy aura faded away!<br />'
          ];

        case 'Strong Wings':
          return [
            'Text' => 'The mysterious strong winds have dissipated!<br />'
          ];
      }

      $this->Weather = null;
      $this->Weather_Turns_Left = null;
    }
  }
