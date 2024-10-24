#!/bin/bash

# Generates https certificates to use during development

if ! [ -x "$(command -v openssl)" ]; then
    if (( $EUID != 0 )); then
        echo "Please run as root"
        exit
    fi

    apt-get install -y openssl
fi

DIR="$(dirname "$0")"

mkdir -p "$DIR/conf/live/absoluterpg.com" "$DIR/www"

cd "$DIR/conf/live/absoluterpg.com/"

openssl genrsa -des3 -passout pass:x -out server.pass.key 2048
openssl rsa -passin pass:x -in server.pass.key -out privkey.pem

rm server.pass.key

openssl req -new -key privkey.pem -out server.csr -subj "/C=US/ST=RPG/L=Toxocious's/O=Absolute RPG/OU=IT Department/CN=absoluterpg.com"
openssl x509 -req -days 3650 -in server.csr -signkey privkey.pem -out fullchain.pem

echo "[SUCCESS] Succesfully generated development certifications."
