<?php
declare(strict_types=1);

function HandleError
(
  PDOException $PDOException
)
{
  if (empty($PDOException) )
  {
    return 'No PDOException was sent to the error handler.';
  }

  global $User_Data;

  $Fetch_Date = date('m/d/y h:i A');

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

  $Error_Message = "
    [SQL Error: {$SQL_ERROR_CODE}] {$Custom_Error} in {$TRIGGERED_BY_FILE} on line {$TRIGGERED_ON_LINE} (Originated In: {$ORIGINATED_IN['file']})
  ";

  if ( !empty($User_Data) )
    if ( empty($User_Data['Username']) )
      $User_Dialogue = "User ID #{$User_Data} ||";
    else
      $User_Dialogue = "{$User_Data['Username']} #{$User_Data['ID']} ||";
  else
    $User_Dialogue = ' No User Data ||';

  file_put_contents(
    '/logs/pdo_errors.log',
    "[ {$Fetch_Date} ] {$User_Dialogue} {$Error_Message}\n",
    FILE_APPEND | LOCK_EX
  );
}

class DatabaseConfig {
    private static array $config;

    public static function init(): void
    {
        self::$config = [
            'host' => getenv('MYSQL_HOST') ?: 'localhost',
            'user' => getenv('MYSQL_USER') ?: 'root',
            'password' => getenv('MYSQL_PASSWORD') ?: '',
            'database' => getenv('MYSQL_GAME_DATABASE') ?: 'absolute',
            'charset' => getenv('MYSQL_CHARSET') ?: 'utf8mb4'
        ];
    }

    public static function get(string $key): string
    {
        return self::$config[$key] ?? '';
    }
}

class DatabaseConnectionPool
{
    private static array $connections = [];
    private static int $maxConnections = 10;
    private static int $retryAttempts = 3;
    private static int $retryDelay = 1; // seconds

    public static function getConnection(string $database): PDOWrapper
    {
        if (isset(self::$connections[$database]))
        {
            return self::$connections[$database];
        }

        if (count(self::$connections) >= self::$maxConnections)
        {
            throw new RuntimeException('Maximum connections reached');
        }

        return self::createConnection($database);
    }

    private static function createConnection(string $database): PDOWrapper
    {
        DatabaseConfig::init();

        $attempts = 0;

        while ($attempts < self::$retryAttempts)
        {
            try
            {
                $connection = new PDOWrapper(
                    sprintf(
                        "mysql:host=%s;dbname=%s;charset=%s",
                        DatabaseConfig::get('host'),
                        DatabaseConfig::get('database'),
                        DatabaseConfig::get('charset')
                    ),
                    DatabaseConfig::get('user'),
                    DatabaseConfig::get('password'),
                    [
                        PDO::ATTR_EMULATE_PREPARES => false,
                        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
                    ]
                );

                self::$connections[$database] = $connection;

                return $connection;
            }
            catch (PDOException $e)
            {
                $attempts++;

                if ($attempts >= self::$retryAttempts)
                {
                    self::handleConnectionError($e);
                }

                sleep(self::$retryDelay);
            }
        }

        throw new RuntimeException('Failed to create database connection');
    }

    private static function handleConnectionError(PDOException $e): void
    {
        error_log(sprintf(
            "[Database Connection Error] %s in %s on line %d",
            $e->getMessage(),
            $e->getFile(),
            $e->getLine()
        ));

        http_response_code(503);
        header("Location: /503.php");

        exit;
    }
}

class PDOWrapper extends PDO
{
    private int $queryCount = 0;
    private array $queries = [];
    private array $runtime = [];
    private array $preparedStatements = [];

    private array $queryStats = [];
    private float $pageLoadStart;

    public function __construct($dsn, $username = null, $password = null, $options = null) {
        parent::__construct($dsn, $username, $password, $options);

        $this->pageLoadStart = microtime(true);
        $this->setAttribute(PDO::ATTR_STATEMENT_CLASS, ['PDOStatementWrapper', [$this]]);
    }

    public function prepare($query, $options = null): PDOStatement|false {
        $key = md5($query);

        if (!isset($this->preparedStatements[$key])) {
            $this->queries[] = $query;
            $this->queryCount++;

            $stmt = parent::prepare($query);

            $this->preparedStatements[$key] = $stmt;
        }

        return $this->preparedStatements[$key];
    }

    public function execute(PDOStatement $statement, ?array $params = null): bool {
        $startTime = microtime(true);

        // Call execute directly on PDOStatement to avoid recursion
        if ($statement instanceof PDOStatementWrapper) {
            $result = $statement->parentExecute($params);
        } else {
            $result = $statement->execute($params);
        }

        $duration = microtime(true) - $startTime;

        $this->logQueryExecution($statement->queryString, $params, $duration);
        return $result;
    }

    public function query($query, $fetchMode = null, ...$fetchModeArgs): PDOStatement|false {
        $startTime = microtime(true);
        $result = parent::query($query);
        $duration = microtime(true) - $startTime;

        $this->logQueryExecution($query, null, $duration);
        return $result;
    }

    private function logQueryExecution(string $query, ?array $params, float $duration): void {
        $this->queryStats[] = [
            'query' => $query,
            'params' => $params,
            'duration' => $duration,
            'time' => microtime(true) - $this->pageLoadStart
        ];
    }

    public function getQueryStats(): array {
        $total = 0;
        $max = 0;
        $min = PHP_FLOAT_MAX;

        foreach ($this->queryStats as $stat) {
            $total += $stat['duration'];
            $max = max($max, $stat['duration']);
            $min = min($min, $stat['duration']);
        }

        return [
            'count' => count($this->queryStats),
            'total_time' => $total,
            'average_time' => $total / max(1, count($this->queryStats)),
            'max_time' => $max,
            'min_time' => $min,
            'queries' => $this->queryStats
        ];
    }

    public function getQueryCount(): int
    {
        return $this->queryCount;
    }

    public function getQueries(): array
    {
        return $this->queries;
    }
}

class PDOStatementWrapper extends PDOStatement {
    private PDOWrapper $pdo;

    protected function __construct(PDOWrapper $pdo) {
        $this->pdo = $pdo;
    }

    public function parentExecute(?array $params = null): bool {
        return parent::execute($params);
    }

    public function execute(?array $params = null): bool {
        return $this->pdo->execute($this, $params);
    }
}
