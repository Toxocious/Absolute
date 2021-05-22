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

    private $Turn_ID = 1;
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
      string $Action,
      $Move = null
    )
    {
      if ( !$Action || !in_array($Action, ['Switch', 'Attack', 'UseItem', 'Flee']) )
      {
        return [
          'Type' => 'Error',
          'Text' => 'An error occurred while performing your desired action.'
        ];
      }

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
        case 'Switch':
          $Slot = Purify($_GET['Slot']) - 1;
          if ( $Slot < 0 || $Slot > 5 )
          {
            return [
              'Type' => 'Error',
              'Text' => 'You may not switch into an invalid Pok&eacute;mon.'
            ];
          }
          else
          {
            $Output['Message'] = $_SESSION['Battle']['Ally']['Roster'][$Slot]->SwitchInto();
          }
          break;

        default:
          break;
      }

      return $Output['Message'];
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
        if ( $Ally->Stats['Current']['Speed'] > $Foe->Stats['Current']['Speed'] )
          return 'Ally';
        else if ( $Ally->Stats['Current']['Speed'] < $Foe->Stats['Current']['Speed'] )
          return 'Foe';
        else
          return mt_rand(1, 2) === 1 ? 'Ally' : 'Foe';
      }
      else
      {
        if ( $Move_Data['Ally']['Priority'] == $Move_Data['Foe']['Priority'] )
        {
          if ( $Ally->Stats['Current']['Speed'] > $Foe->Stats['Current']['Speed'] )
            return 'Ally';
          else if ( $Ally->Stats['Current']['Speed'] < $Foe->Stats['Current']['Speed'] )
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
  }
