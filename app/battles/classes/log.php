<?php
  class Log
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
          INSERT INTO `battle_logs`
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
          $_SESSION['EvoChroniclesRPG']['Battle']['Ally_ID'],
          $_SESSION['EvoChroniclesRPG']['Battle']['Foe_ID'],
          $_SESSION['EvoChroniclesRPG']['Battle']['Battle_ID'],
          $_SESSION['EvoChroniclesRPG']['Battle']['Battle_Type'],
          empty($_SESSION['EvoChroniclesRPG']['Battle']['Battle_Layout']) ? $User_Data['Battle_Theme'] : $_SESSION['EvoChroniclesRPG']['Battle']['Battle_Layout'],
          $_SESSION['EvoChroniclesRPG']['Battle']['Time_Started'],
          true,
          $_SERVER['REMOTE_ADDR'],
          $Client_User_Agent['User_Agent']
        ]);

        $_SESSION['EvoChroniclesRPG']['Battle']['Logging']['Actions'] = [];
        $_SESSION['EvoChroniclesRPG']['Battle']['Logging']['Log_ID'] = $PDO->lastInsertId();

        $PDO->commit();
      }
      catch ( \PDOException $e )
      {
        $PDO->rollBack();

        HandleError($e);
      }

      $_SESSION['EvoChroniclesRPG']['Battle']['Logging']['All_Inputs_Trusted'] = true;
      $_SESSION['EvoChroniclesRPG']['Battle']['Logging']['All_Postcodes_Matched'] = true;
      $_SESSION['EvoChroniclesRPG']['Battle']['Logging']['Page_Always_In_Focus'] = true;
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
      $Action = $Action + (int) $_SESSION['EvoChroniclesRPG']['Battle']['Logging']['Input']['Client_X'];
      $Action = $Action << 13;
      $Action = $Action + (int) $_SESSION['EvoChroniclesRPG']['Battle']['Logging']['Input']['Client_Y'];

      $_SESSION['EvoChroniclesRPG']['Battle']['Logging']['Actions'][] = $Action;

      if ( !$_SESSION['EvoChroniclesRPG']['Battle']['Logging']['Input']['Is_Trusted'] )
        $_SESSION['EvoChroniclesRPG']['Battle']['Logging']['All_Inputs_Trusted'] = false;

      if ( !$_SESSION['EvoChroniclesRPG']['Battle']['Logging']['In_Focus'] )
        $_SESSION['EvoChroniclesRPG']['Battle']['Logging']['Page_Always_In_Focus'] = false;

      if
      (
        !empty($_SESSION['EvoChroniclesRPG']['Battle']['Logging']['Postcode']) &&
        count($_SESSION['EvoChroniclesRPG']['Battle']['Logging']['Postcode']) == 2
      )
      {
        $Postcode_Match = $_SESSION['EvoChroniclesRPG']['Battle']['Logging']['Postcode']['Expected'] == $_SESSION['EvoChroniclesRPG']['Battle']['Logging']['Postcode']['Received'];
        if ( !$Postcode_Match )
          $_SESSION['EvoChroniclesRPG']['Battle']['Logging']['All_Postcodes_Matched'] = false;
      }
    }

    /**
     * Update the current battle log w/ the finalized information.
     */
    public function Finalize()
    {
      global $PDO;

      if ( empty($_SESSION['EvoChroniclesRPG']['Battle']['Logging']) )
        return false;

      $_SESSION['EvoChroniclesRPG']['Battle']['Last_Action_Time'] = (microtime(true) - $_SESSION['EvoChroniclesRPG']['Battle']['Time_Started']) * 1000;

      $Actions = '';
      if ( !empty($_SESSION['EvoChroniclesRPG']['Battle']['Logging']['Actions']) )
        $Actions = pack('l*', ...$_SESSION['EvoChroniclesRPG']['Battle']['Logging']['Actions']);

      try
      {
        $PDO->beginTransaction();

        $Update_Battle_Log = $PDO->prepare("
          UPDATE `battle_logs`
          SET
            `Battle_Duration` = ?,
            `Actions_Performed` = ?,
            `Turn_Count` = ?,
            `All_Inputs_Trusted` = ?,
            `Window_In_Focus` = ?,
            `All_Postcodes_Matched` = ?
          WHERE `ID` = ?
          LIMIT 1
        ");
        $Update_Battle_Log->execute([
          $_SESSION['EvoChroniclesRPG']['Battle']['Last_Action_Time'],
          $Actions,
          $_SESSION['EvoChroniclesRPG']['Battle']['Turn_ID'],
          $_SESSION['EvoChroniclesRPG']['Battle']['Logging']['All_Inputs_Trusted'],
          $_SESSION['EvoChroniclesRPG']['Battle']['Logging']['Page_Always_In_Focus'],
          $_SESSION['EvoChroniclesRPG']['Battle']['Logging']['All_Postcodes_Matched'],
          $_SESSION['EvoChroniclesRPG']['Battle']['Logging']['Log_ID']
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

      return [
        'Action' => $Action,
        'Coords' => [
          'x' => self::GetBits($Encoded_Move, 0, 13),
          'y' => self::GetBits($Encoded_Move, 13, 26),
        ],
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
