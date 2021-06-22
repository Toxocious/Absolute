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


      $_SESSION['Battle']['Turn_ID']++;
      $this->Turn_ID = $_SESSION['Battle']['Turn_ID'];
      /**
       * Set the used moves now, and determine who will attack first.
       * Done now, so that we can process pre-move conditions:
       *  abilities
       *  pre-move effects
       */
      $this->Ally_Move = $Move ? $Move : null;
      $this->Foe_Move = $_SESSION['Battle']['Foe']['Active']->FetchRandomMove();

      switch ($Action)
      {
        /**
         * On switch out, a check for abilities, moves, etc. that prevent
         * the Ally's active Pokemon from switching out needs to be done.
         *  - Abilities :: Arena Trap, etc
         *  - Moves :: Mean Look, etc
         */
        case 'Switch':
          $Slot = Purify($_GET['Slot']) - 1;
          if ( $Slot < 0 || $Slot > 5 )
          {
            return [
              'Type' => 'Error',
              'Text' => 'You may not switch into an invalid Pok&eacute;mon.'
            ];
          }

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

              $this->Turn_Dialogue['Text'] .= $Foe_Attack['Text'];
              $this->Turn_Dialogue['Text'] .= '<br /><br />';
            }

            $Perform_Switch = $_SESSION['Battle']['Ally']['Roster'][$Slot]->SwitchInto();
            $this->Turn_Dialogue['Text'] .= $Perform_Switch['Text'];
          }
          else
          {
            $Perform_Switch = $_SESSION['Battle']['Ally']['Roster'][$Slot]->SwitchInto();
            $this->Turn_Dialogue['Text'] .= $Perform_Switch['Text'];

            if
            (
              $Foe_Active->HP > 0 &&
              $Ally_Active->HP > 0
            )
            {
              $Foe_Attack = $Foe_Active->Attack($this->Foe_Move);
              $Ally_Active->DecreaseHP($Foe_Attack['Damage']);

              $this->Turn_Dialogue['Text'] .= '<br /><br />';
              $this->Turn_Dialogue['Text'] .= $Foe_Attack['Text'];
            }
          }

          break;
          break;

        default:
          break;
      }

      return $Output['Message'];

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
