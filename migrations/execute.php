<?php
  ini_set('max_execution_time', '0');

  // Get and set database credentials.
  $db      = getenv('MYSQL_MIGRATION_DATABASE');
  $table   = getenv('MYSQL_MIGRATION_TABLE');
  $host    = getenv('MYSQL_HOST');
  $user    = getenv('MYSQL_ROOT_USER');
  $pass    = getenv('MYSQL_ROOT_PASSWORD');
  $charset = getenv('MYSQL_CHARSET');

  echo "[MIGRATIONS / DEBUG] host = {$host}, dbname = {$db}, user = {$user}, pass = {$pass}\n";
  echo "[MIGRATIONS / DEBUG] database = {$db}, table = {$table}\n";

  // Get all *.sql files.
  $migrations_directory = __DIR__ . '/sql';
  $migrations = scandir($migrations_directory);
  $migrations = array_filter($migrations, function ($x) {
    return strpos($x, '.sql') != false;
  });
  sort($migrations);

  // Create new PDO instance.
  try
  {
    $pdo = new PDO(
      "mysql:host={$host};dbname={$db};charset={$charset}",
      $user,
      $pass
    );
  }
  catch ( PDOException $e )
  {
    echo "[ERROR] Unable to create PDO instance. Killing script.\n";
    echo "{$e->getMessage()} (Line {$e->getLine()})\n";
    echo "[INFO] Verify environment variables.\n";
    echo "[MIGRATIONS / DEBUG] host = {$host}, dbname = {$db}, user = {$user}, pass = {$pass}\n";
    echo "[MIGRATIONS / DEBUG] database = {$db}, table = {$table}\n";

    die();
  }

  // Iterate through all *.sql files and attempt to execute them.
  foreach ( $migrations as $migration )
  {
    $migration_name = basename($migration, '.sql');

    $stmt = $pdo->prepare("SELECT * FROM `{$db}`.`{$table}` WHERE name = :name");
    $stmt->execute([ 'name' => $migration_name ]);
    $result = $stmt->fetch();

    // Check if this migration has already been executed.
    // Move on to the next file if it has been.
    if ( $result )
    {
      echo "[INFO] Migration {$migration_name} already executed.\n";
      continue;
    }

    $migration_sql = file_get_contents($migrations_directory . '/' . $migration);

    try
    {
      $pdo->beginTransaction();

      $pdo->exec($migration_sql);

      $stmt = $pdo->prepare("INSERT INTO `{$db}`.`{$table}` (name) VALUES (:name)");
      $stmt->execute([ 'name' => $migration_name ]);

      $pdo->commit();

      echo "[SUCCESS] Executed migration {$migration_name}.\n";
    }
    catch ( Exception $e )
    {
      echo "[ERROR] Error executing migration {$migration_name}; rolling back...:\n\t{$e->getMessage()}\n";
      $pdo->rollBack();

      die();
    }
  }
