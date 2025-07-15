#!/bin/bash

declare -A dependencies

dependencies["battle-server"]="git@github.com:PokemonAbsolute/Absolute-Battle-Server.git"
dependencies["chat"]="git@github.com:PokemonAbsolute/Absolute-Chat.git"
dependencies["discord"]="git@github.com:PokemonAbsolute/Absolute-Discord-Bot.git"

echo "[INFO] Installing Absolute's dependencies."

for DEP in "${!dependencies[@]}"; do
  echo "[INFO] Processing dependency: $DEP"
  if [ -d "absolute/$DEP" ]; then
    echo "[INFO] absolute/$DEP already exists, skipping clone."
    continue
  fi

  echo "[INFO] Installing Absolute's $DEP (${dependencies[$DEP]})"
  git clone "${dependencies[$DEP]}" "absolute/$DEP"
  if [ $? -ne 0 ]; then
    echo "[ERROR] Failed to clone $DEP. Please check the repository URL."
    exit 1
  fi
done

echo "[INFO] All dependencies installed successfully."
