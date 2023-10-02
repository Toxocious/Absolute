#!/bin/bash

echo "[INFO] Executing migrations"

# Load environment variables
if [ -f "/data/application/.env" ]; then
  source "/data/application/.env"
else
  echo "[ERROR] Couldn't find a .env file found in /data/application/. Create it and re-run the script."
  exit 1
fi

# Start a timer to track total execution time
start_time=$(date +%s%3N)

# Get all *.sql files
# migrations_directory="$(dirname $0)/sql"
migrations_directory="/data/application/sql"
migrations=($(ls "$migrations_directory" | grep '\.sql$' | sort))

# Check if the `migrations` database exists
# Creates the `migrations` database and the correct table if the database doesn't exist
# Also creates the `absolute` database as it shouldn't yet exist if the `migrations` database doesn't exist
if mariadb -u "$MYSQL_ROOT_USER" -p"$MYSQL_ROOT_PASSWORD" -e "USE $MYSQL_MIGRATION_DATABASE;" 2>/dev/null; then
    echo "[NOTICE] Database $MYSQL_MIGRATION_DATABASE already exists."
else
    echo "[NOTICE] Database $MYSQL_MIGRATION_DATABASE does not exist. Creating it and table $MYSQL_MIGRATION_TABLE."
    mariadb -u "$MYSQL_ROOT_USER" -p"$MYSQL_ROOT_PASSWORD" -e "CREATE DATABASE $MYSQL_GAME_DATABASE; CREATE DATABASE $MYSQL_MIGRATION_DATABASE; CREATE TABLE $MYSQL_MIGRATION_DATABASE.$MYSQL_MIGRATION_TABLE (id INT AUTO_INCREMENT PRIMARY KEY, name VARCHAR(255));"
    echo "[SUCCESS] Database $MYSQL_MIGRATION_DATABASE created with table $MYSQL_MIGRATION_TABLE."
fi

# Iterate through all *.sql files and attempt to execute them
for migration in "${migrations[@]}"; do
    # Get the current migration file
    migration_name="$(basename "$migration" .sql)"
    migration_file="$migrations_directory/$migration"

    # Check if this migration has already been executed
    mariadb -h "$MYSQL_HOST" -u "$MYSQL_ROOT_USER" -p"$MYSQL_ROOT_PASSWORD" -D "$MYSQL_MIGRATION_DATABASE" -e "SELECT * FROM $MYSQL_MIGRATION_TABLE WHERE name = '$migration_name'" | grep -q "$migration_name"

    if [ $? -eq 0 ]; then
        echo "[INFO] Migration $migration_name already executed."
    else
        # Ensure that each file is wrapped in a transaction
        if ! grep -q "START TRANSACTION" "$migration_file"; then
            { echo "START TRANSACTION;"; cat "$migration_file"; echo "COMMIT;"; } > "$migration_file.tmp" && mv "$migration_file.tmp" "$migration_file"
        fi

        # Execute the migration using Mariadb
        mariadb -h "$MYSQL_HOST" -u "$MYSQL_ROOT_USER" -p"$MYSQL_ROOT_PASSWORD" "$MYSQL_GAME_DATABASE" < "$migrations_directory/$migration"

            # Record the migration as executedy
        if [ $? -eq 0 ]; then
            mariadb -h "$MYSQL_HOST" -u "$MYSQL_ROOT_USER" -p"$MYSQL_ROOT_PASSWORD" "$MYSQL_MIGRATION_DATABASE" -e "INSERT INTO $MYSQL_MIGRATION_TABLE (name) VALUES ('$migration_name')"
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
