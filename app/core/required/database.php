<?php
  $database_connections = [];

	/**
   * Setup the connection to our database.
   */
	function DatabaseConnect()
	{
    global $database_connections;

    try {
      $mysql_host = getenv('MYSQL_HOST');
      $mysql_user = getenv('MYSQL_USER');
      $mysql_password = getenv('MYSQL_PASSWORD');
      $database = getenv('MYSQL_GAME_DATABASE');
      $charset = getenv('MYSQL_CHARSET');

      $DBH = new PDOWrapper(
        "mysql:host={$mysql_host};dbname={$database};charset={$charset}",
        $mysql_user,
        $mysql_password
      );
    } catch (PDOException $e) {
      // echo "<b>[ERROR]</b> Unable to create PDO instance.<br />";
      // echo "<b>[INFO]</b> Verify environment variables.<br />";
      // echo "<b>[MIGRATIONS / DEBUG]</b> host = {$mysql_host}, user = {$mysql_user}, pass = {$mysql_password}<br />";
      // echo "<b>[MIGRATIONS / DEBUG]</b> database = {$database}<br />";
      // echo "PDOException Message (Line {$e->getLine()}) -><br />";
      // echo '&nbsp;&nbsp;&nbsp;&nbsp;' . $e;

      // http_response_code(503);
      // header("Location: /503.php");
      // exit;
    }

    $DBH->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
    $DBH->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $DBH->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

    $database_connections['absolute'] = $DBH;

    return $DBH;
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

  class PDOWrapper extends PDO
  {
    private $queryCount = 0;
    private $querys = [];
    public $runtime = [];

    public function __construct($dsn, $username = '', $password = '', $driver_options = array())
    {
      parent::__construct($dsn, $username, $password, $driver_options);
      $this->setAttribute(PDO::ATTR_STATEMENT_CLASS, array('PDOStatementWrapper', array($this)));
    }

    public function query($query)
    {
      ++$this->queryCount;

      $this->querys[] = $query;

      return parent::query($query);
    }

    public function prepare($query, $options = null)
    {
      ++$this->queryCount;
      $this->querys[] = $query;

      return parent::prepare($query);
    }

    public function GetCount()
    {
      return $this->queryCount;
    }

    public function GetQuerys()
    {
      $text = '';

      foreach ($this->querys as $x => $v) {
        $text .= '#' . ($x + 1) .' - (' . number_format($this->runtime[$x], 3) . 's)<br>' . trim($v) . '<br><br>';
      }

      return $text;
    }
}

class PDOStatementWrapper extends PDOStatement
{
  protected $pdo;

  protected function __construct($pdo)
  {
    $this->pdo = $pdo;
  }

  public function execute($args = null)
  {
    $time_start = microtime(true);

    $x = parent::execute($args);

    $time_end = microtime(true);
    $time = $time_end - $time_start;

    $this->pdo->runtime[] = $time;

    return $x;
  }
}
