#!/bin/bash

# CLI flags
verbose_migrations=false

# Function to display an error message and exit
error_exit() {
  echo "[ERROR] > $1"
  exit 1
}

# Function to generate development certificates using Certbot
generate_dev_certs() {
  if [ ! -f "certbot/conf/live/absoluterpg.com/fullchain.pem" ]; then
    echo "[INFO] Dev certs do not exist. Running certbot script."
    bash certbot/generate.sh

    if [ $? -ne 0 ]; then
      error_exit "Failed to generate development certificates."
    fi
  fi
}

# Function to build Docker containers
build_docker_containers() {
  file="logs/last-commit"
  current_commit=$(git rev-parse --short HEAD)

  if [ "$1" = "--build" ] || [ ! -f "$file" ] || [ "$current_commit" != "$(cat $file)" ]; then
    echo "[INFO] Building Docker containers."
  
    if ! type "docker-compose" > /dev/null; then
      podman-compose build
    else
      docker-compose build
    fi

    echo "$current_commit" > "$file"
  else
    echo "[NOTICE] Already built, not running build script."
  fi
}

# Function to start Docker containers
start_docker_containers() {
  echo "[INFO] Starting Docker containers in the background."

  if ! type "docker-compose" > /dev/null; then
    podman-compose up -d
  else
    docker-compose up -d
  fi

  if [ $? -ne 0 ]; then
    error_exit "Docker compose build failed."
  fi
}

# Function to execute SQL migrations inside the MySQL Docker container
execute_sql_migrations() {
  echo "[INFO] Executing SQL migrations."
  migrations=$(docker exec -it absolute-mysql bash -c "/data/application/migrate.sh")

  if [[ "$migrations" != *"[SUCCESS] All migrations executed successfully."* ]]; then
    error_exit "Migrations failed."
  else
    echo " > Migrations ran successfully."

    if [ "$verbose_migrations" = true ]; then
      echo "[LOGS // MIGRATION] $migrations"
    fi
  fi
}

# Parse command-line flags
while getopts "m" flag; do
  case $flag in
    m) verbose_migrations=true ;;
    *) exit 1 ;;
  esac
done

# Check for development server certificates
generate_dev_certs

# Build Docker containers
build_docker_containers "$1"

# Start Docker containers
start_docker_containers

# Execute SQL migrations
execute_sql_migrations

echo "[SUCCESS] Server successfully started."
