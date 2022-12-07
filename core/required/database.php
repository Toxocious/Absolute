<?php
  /**
   * Database credentials.
   * Uses server environment variables if set.
   * Otherwise defaults to localhost credentials.
   */
  if ( isset($_ENV['DATABASE_TABLE']) )
    define('DATABASE_TABLE', $_ENV['DATABASE_TABLE']);
  else
    define('DATABASE_TABLE', 'absolute');

  if ( isset($_ENV['DATABASE_USER']) )
    define('DATABASE_USER', $_ENV['DATABASE_USER']);
  else
    define('DATABASE_USER', 'absolute');

  if ( isset($_ENV['DATABASE_PASSWORD']) )
    define('DATABASE_PASSWORD', $_ENV['DATABASE_PASSWORD']);
  else
    define('DATABASE_PASSWORD', 'qwerty');

	/**
   * Setup the connection to our database.
   */
	function DatabaseConnect()
	{
		$Host = 'localhost';
		$Char_Set = 'utf8mb4';

		$PDO_Attributes = [
			PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
			PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
			PDO::ATTR_EMULATE_PREPARES   => false,
		];

		try
		{
			$PDO = new PDO(
        "mysql:host={$Host};
        dbname=" . DATABASE_TABLE . ";
        charset={$Char_Set};",
        DATABASE_USER,
        DATABASE_PASSWORD,
        $PDO_Attributes
      );
		}
		catch (PDOException $e)
		{
			echo "
				<div>
					The database has failed to connect.<br />
					Contact Toxocious on Discord at Jess#5596.
				</div>
			";

			HandleError($e);
			exit;
		}

		return $PDO;
	}

	/**
	 * Log error messages to a file.
   *
   * @param {string} $Message
	 */
	function HandleError
  (
    PDOException $PDOException
  )
	{
    global $User_Data;

		$Fetch_Date = date('m/d/y h:i A');

		$Error_Message = ConstructErrorMessage($PDOException);

    if ( !empty($User_Data) )
      if ( empty($User_Data['Username']) )
        $User_Dialogue = "User ID #{$User_Data} ||";
      else
        $User_Dialogue = "{$User_Data['Username']} #{$User_Data['ID']} ||";
    else
      $User_Dialogue = ' No User Data ||';

		file_put_contents(
      __DIR__ . '/../../_logs/pdo_errors.txt',
      "[ {$Fetch_Date} ] {$User_Dialogue} {$Error_Message}\n",
      FILE_APPEND | LOCK_EX
    );
	}

  /**
   * Given a PDOException object, construct an error message.
   * Will return a proper error string.
   * Example return string:
   *  -> [SQL Error Code: 42502] 'Table 'absolute.obtainable_items' doesn't exist in 'C:\xampp\htdocs\core\classes\shop.php' on line 111\n\n
   *
   * @param {PDOException} $PDOException
   * @return {string}
   */
  function ConstructErrorMessage
  (
    PDOException $PDOException
  )
  {
    if (empty($PDOException) )
      return 'No PDOException was sent to the error handler.';

    $SQL_ERROR_CODE = $PDOException->getCode();     // Code       :: 42S02
    $TRIGGERED_BY_FILE = $PDOException->getFile();  // Filename   :: 'C:\xampp\htdocs\core\classes\shop.php'
    $TRIGGERED_ON_LINE = $PDOException->getLine();  // Line       :: 111
    $ERROR_MESSAGE = $PDOException->getMessage();   // Error Msg  :: Return a string of the error message
    $TRACE_INFO = $PDOException->getTrace();        // Trace      :: Returns an array including filename, line number, function, and function args
    $ORIGINATED_IN = $TRACE_INFO[count($TRACE_INFO) - 1];

    $Custom_Error = $ERROR_MESSAGE;
    switch ( $SQL_ERROR_CODE )
    {
      case '42S02':
        $Custom_Error = str_replace('SQLSTATE[42S02]: Base table or view not found: 1146 ', '', $ERROR_MESSAGE);
        break;

      default:
        $Custom_Error = $ERROR_MESSAGE;
        break;
    }

    return "
      [SQL Error: {$SQL_ERROR_CODE}] {$Custom_Error} in {$TRIGGERED_BY_FILE} on line {$TRIGGERED_ON_LINE} (Originated In: {$ORIGINATED_IN['file']})
    ";
  }
