<?php
  use BattleHandler\Battle;

  class Log extends Battle
  {
    const ACTIONS = [
      'Unknown',
      'Attack',
      'Continue',
      'Restart',
      'Switch',
      'UseItem',
      'Bag',
      'Misclick'
    ];

    public function __construct() { }

    /**
     * Create a new log in the database.
     * Called when $Fight->CreateBattle() is called.
     */
    public function Initialize()
    {
      global $PDO, $User_Data;

      $Client_User_Agent = GetUserAgent();

      try
      {
        $PDO->beginTransaction();

        $Initialize_Battle_Log = $PDO->prepare("
          INSERT INTO battle_logs
          (
            `User_ID`,
            `Foe_ID`,
            `Session_Battle_ID`,
            `Battle_Type`,
            `Battle_Layout`,
            `Time_Battle_Started`,
            `Window_In_Focus`,
            `Client_IP`,
            `Client_User_Agent`
          )
          VALUES ( ?, ?, ?, ?, ?, ?, ?, ?, ? )
        ");
        $Initialize_Battle_Log->execute([
          $_SESSION['Battle']['Ally_ID'],
          $_SESSION['Battle']['Foe_ID'],
          $_SESSION['Battle']['Battle_ID'],
          $_SESSION['Battle']['Battle_Type'],
          empty($_SESSION['Battle']['Battle_Layout']) ? $User_Data['Battle_Theme'] : $_SESSION['Battle']['Battle_Layout'],
          $_SESSION['Battle']['Time_Started'],
          true,
          $_SERVER['REMOTE_ADDR'],
          $Client_User_Agent['User_Agent']
        ]);

        $_SESSION['Battle']['Logging']['Actions'] = [];
        $_SESSION['Battle']['Logging']['Log_ID'] = $PDO->lastInsertId();

        $PDO->commit();
      }
      catch ( \PDOException $e )
      {
        $PDO->rollBack();

        HandleError($e);
      }
    }

    /**
     * When an action is performed, add a new entry to the session log.
     *
     * @param {string} $Action
     *  - The action that was performed.
     *  - (Attack, Continue, Restart, etc.)
     */
    public function AddAction
    (
      $Action
    )
    {
      $Get_Action = array_search($Action, self::ACTIONS);
      if ( !$Get_Action )
        $Get_Action = 0;

      $Action = $Get_Action << 13;
      $Action = $Action + (int) $_SESSION['Battle']['Logging']['Input']['Client_X'];
      $Action = $Action << 13;
      $Action = $Action + (int) $_SESSION['Battle']['Logging']['Input']['Client_Y'];
      $Action = $Action << 13;
      $Action = $Action + (int) $_SESSION['Battle']['Logging']['Input']['Is_Trusted'];
      $Action = $Action << 13;
      $Action = $Action + (int) $_SESSION['Battle']['Logging']['In_Focus'];
      $Action = $Action << 13;

      $Postcode_Match = false;
      if ( !empty($_SESSION['Battle']['Logging']['Postcode']) && count($_SESSION['Battle']['Logging']['Postcode']) == 2 )
      {
        $Postcode_Match = $_SESSION['Battle']['Logging']['Postcode']['Expected'] == $_SESSION['Battle']['Logging']['Postcode']['Received'];
        $Action = $Action + (int) $Postcode_Match;
      }

      $_SESSION['Battle']['Logging']['Actions'][] = $Action;
    }

    /**
     * Update the current battle log w/ the finalized information.
     */
    public function Finalize()
    {
      global $PDO;

      if ( empty($_SESSION['Battle']['Logging']) )
        return false;

      $_SESSION['Battle']['Last_Action_Time'] = (microtime(true) - $_SESSION['Battle']['Time_Started']) * 1000;

      $Actions = '';
      if ( !empty($_SESSION['Battle']['Logging']['Actions']) )
        $Actions = pack('l*', ...$_SESSION['Battle']['Logging']['Actions']);

      try
      {
        $PDO->beginTransaction();

        $Update_Battle_Log = $PDO->prepare("
          UPDATE `battle_logs`
          SET `Battle_Duration` = ?, `Actions_Performed` = ?, `Turn_Count` = ?
          WHERE `ID` = ?
          LIMIT 1
        ");
        $Update_Battle_Log->execute([
          $_SESSION['Battle']['Last_Action_Time'],
          $Actions,
          $_SESSION['Battle']['Turn_ID'],
          $_SESSION['Battle']['Logging']['Log_ID']
        ]);

        $PDO->commit();
      }
      catch ( \PDOException $e )
      {
        $PDO->rollBack();

        HandleError($e);
      }
    }

    /**
     * Parse the battle log's performed actions.
     *
     * @param $Encoded_Move
     */
    public static function Parse
    (
      $Encoded_Move
    )
    {
      $Action = self::ACTIONS[$Encoded_Move >> 26];

      $Postcode_Match = null;
      if ( in_array($Action, [2, 3]) )
        $Postcode_Match = self::GetBits($Encoded_Move, 52, 65);

      return [
        'Action' => $Action,
        'Coords' => [
          'x' => self::GetBits($Encoded_Move, 0, 13),
          'y' => self::GetBits($Encoded_Move, 13, 26),
        ],
        'Is_Trusted' => self::GetBits($Encoded_Move, 26, 39),
        'Window_In_Focus' => self::GetBits($Encoded_Move, 39, 52),
        'Postcode_Match' => $Postcode_Match
      ];
    }

    /**
     * Get the bits of a performed action.
     */
    public static function GetBits
    (
      $Encoded_Move,
      $Start_Position,
      $End_Position
    )
    {
      $Mask = (1 << ($End_Position - $Start_Position)) - 1;

      return ($Encoded_Move >> $Start_Position) & $Mask;
    }
  }
