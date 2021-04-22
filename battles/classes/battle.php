<?php
  namespace BattleHandler;

  spl_autoload_register(function($Class)
  {
    $Battle_Directory = dirname(__DIR__, 1);
    $Class = strtolower($Class);

    if ( file_exists($Battle_Directory . "\\classes\\{$Class}.php"))
      require_once $Battle_Directory . "\\classes\\{$Class}.php";

    if ( file_exists($Battle_Directory . "\\fights\\{$Class}.php"))
      require_once $Battle_Directory . "\\fights\\{$Class}.php";
  });

  class Battle
  {
    public $Started = false;
    public $Ended = false;

    public $Battle_Sim = false;
    public $Battle_Sim_Difficulty = false;

    public $Battle_Type = null;

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
    public $Layout = null;

    public $Ally = null;
    public $Foe = null;

    public $Battle_ID = null;

    public $Turn_ID = 1;

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
    }
  }
