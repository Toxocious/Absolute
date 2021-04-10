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

    public $Last_Move = null;

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

      switch ($Action)
      {
        case 'Switch':
          if ( !isset($_GET['Slot']) )
          {
            $Output['Message'] = [
              'Type' => 'Error',
              'Text' => 'An error occurred while switching your active Pok&eacute;mon.'
            ];
          }
          else
          {
            $Slot = Purify($_GET['Slot']) - 1;

            if ( $_SESSION['Battle']['Ally']['Active']->Pokemon_ID != $_SESSION['Battle']['Ally']['Roster'][$Slot]->Pokemon_ID )
            {
              if ( $_SESSION['Battle']['Ally']['Roster'][$Slot]->HP <= 0 )
              {
                $Output['Message'] = [
                  'Type' => 'Error',
                  'Text' => 'You can not swap into a fainted Pok&eacute;mon.'
                ];
              }
              else
              {
                foreach ($_SESSION['Battle']['Ally']['Roster'] as $Roster_Pokemon)
                  $Roster_Pokemon->Active = false;

                $_SESSION['Battle']['Ally']['Roster'][$Slot]->Active = true;

                $Output['Message'] = $_SESSION['Battle']['Ally']['Roster'][$Slot]->SwitchInto();
              }
            }
            else
            {
              $Output['Message'] = [
                'Type' => 'Error',
                'Text' => 'The Pok&eacute;mon that you\'re switching into is already out!'
              ];
            }
          }
          break;

        default:
          break;
      }

      return $Output['Message'];
    }
  }
