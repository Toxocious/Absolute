#!/bin/bash

# Function to specifically shut down the discord bot.
shutdown_discord_bot() {
  # Load environment variables
  env_file="./absolute/discord/.env"
  if [ ! -f "$env_file" ]; then
    echo "[ERROR] Couldn't find a .env file in ./absolute/discord/. Create it and re-run the script."
    exit 1
  fi
  source "$env_file"

  echo "[INFO] Shutting down Absolute's Discord bot."

  # ???

  echo "[INFO] Discord bot has shut down."
}

# Shut down all docker containers
shutdown_containers() {
  docker compose down
}

# Main script contents.
echo "[INFO] Shutting down all Absolute containers."

shutdown_discord_bot

shutdown_containers

echo "[SUCCESS] Server successfully shutdown."
