#!/bin/bash

# Check for dev server certs.
if [ ! -f "certbot/conf/live/absoluterpg.com/fullchain.pem" ]; then
    echo "[INFO] Dev certs do not exist. Running certbot script."

    certs=$(bash certbot/generate.sh)
    echo "$certs"

    if [[ "$certs" != *"[SUCCESS] Succesfully enerated development certifications."* ]]; then
        echo "[ERROR] Wasn't able to generate certs for the development server. Exiting."
        exit 1
    fi
fi

file="logs/last-commit"
current_commit=$(git rev-parse --short HEAD)

# Build the docker containers
if [ "$1" = "--build" ] || [ ! -f "$file" ] || [ "$current_commit" != "$(cat $file)" ]; then
    docker-compose build

    echo $current_commit > $file
else
    echo "[NOTICE] Already built, not running build script"
fi

# Start the containers in the background
docker-compose up -d

# Check the exit status of docker-compose
if [[ $? -ne 0 ]]; then
    echo "[ERROR] Docker compose build failed. Exiting."
    exit 1
fi

# Execute SQL migrations inside of the mysql docker container
migrations=$(docker exec -it absolute-mysql bash -c "/data/application/migrate.sh")
echo "$migrations"

if [[ "$migrations" != *"[SUCCESS] All migrations executed successfully."* ]]; then
    echo "[ERROR] Migrations failed."
fi

echo "[SUCCESS] Server successfully started."
