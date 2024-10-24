#!/bin/bash

echo "[INFO] Executing migrations"

# Load environment variables
env_file="/data/application/.env"
if [ ! -f "$env_file" ]; then
  echo "[ERROR] Couldn't find a .env file in /data/application/. Create it and re-run the script."
  exit 1
fi

source "$env_file"

# Wait for the MySQL service to finish initializing.
# This sucks and used to not be necessary, but I can't figure out any alternative solutions.
until mysqladmin ping -h "$MYSQL_HOST" -u "$MYSQL_ROOT_USER" -p"$MYSQL_ROOT_PASSWORD" --silent; do
    sleep 1
done

# Start a timer to track total execution time
start_time=$(date +%s%3N)

# Establish a database connection\
echo "[INFO] Establishing database connection."
mysql_cmd="mariadb -h $MYSQL_HOST -u $MYSQL_ROOT_USER -p$MYSQL_ROOT_PASSWORD"

# Create the migrations database and the migration table if they do not exist
if ! $mysql_cmd -e "USE $MYSQL_MIGRATION_DATABASE" 2>/dev/null; then
  # Update user privileges.
  $mysql_cmd -e "REVOKE ALL PRIVILEGES ON *.* FROM 'absolute'@'%';
    REVOKE GRANT OPTION ON *.* FROM 'absolute'@'%';
    GRANT SELECT, INSERT, UPDATE, DELETE, FILE ON *.* TO 'absolute'@'%' REQUIRE NONE WITH MAX_QUERIES_PER_HOUR 0 MAX_CONNECTIONS_PER_HOUR 0 MAX_UPDATES_PER_HOUR 0 MAX_USER_CONNECTIONS 0;
    FLUSH PRIVILEGES;
  "

  echo "[SUCCESS] Updated user privileges for user 'absolute'@'%'."

  # Create game and migration databases/tables.
  $mysql_cmd -e "CREATE DATABASE $MYSQL_GAME_DATABASE; CREATE DATABASE $MYSQL_MIGRATION_DATABASE; CREATE TABLE $MYSQL_MIGRATION_DATABASE.$MYSQL_MIGRATION_TABLE (id INT AUTO_INCREMENT PRIMARY KEY, name VARCHAR(255));"

  echo "[SUCCESS] Database $MYSQL_MIGRATION_DATABASE created with table $MYSQL_MIGRATION_TABLE."
else
  echo "[NOTICE] Database $MYSQL_MIGRATION_DATABASE already exists."
fi

# Get all *.sql files
migrations_directory="/data/application/sql"
migrations=("$migrations_directory"/*.sql)
sorted_migrations=($(for f in "${migrations[@]}"; do echo "$f"; done | sort))

# Iterate through all *.sql files and attempt to execute them
for migration_file in "${sorted_migrations[@]}"; do
  migration_name=$(basename "$migration_file" .sql)

  if $mysql_cmd -e "SELECT * FROM $MYSQL_MIGRATION_DATABASE.$MYSQL_MIGRATION_TABLE WHERE name = '$migration_name'" | grep -q "$migration_name"; then
    echo "[INFO] Migration $migration_name already executed."
  else
    # Ensure that each file is wrapped in a transaction
    if ! grep -q "START TRANSACTION" "$migration_file"; then
      { echo "START TRANSACTION;"; cat "$migration_file"; echo "COMMIT;"; } > "$migration_file.tmp" && mv "$migration_file.tmp" "$migration_file"
    fi

    # Execute the migration using MariaDB
    if $mysql_cmd "$MYSQL_GAME_DATABASE" < "$migration_file"; then
      $mysql_cmd -e "INSERT INTO $MYSQL_MIGRATION_DATABASE.$MYSQL_MIGRATION_TABLE (name) VALUES ('$migration_name')"
      echo "[SUCCESS] Executed migration $migration_name."
    else
      echo "[ERROR] Error executing migration $migration_name. Exiting."
      exit 1
    fi
  fi
done

# Record the end time
end_time=$(date +%s%3N)

# Calculate the execution time
execution_time=$((end_time - start_time))

echo "[SUCCESS] All migrations executed successfully. ($execution_time ms)"
