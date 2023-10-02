#!/bin/bash

# Generates https certificates to use during development

cd "$(dirname "$0")"

mkdir -p www
mkdir -p conf/live/absoluterpg.com
cd conf/live/absoluterpg.com

openssl genrsa -des3 -passout pass:x -out server.pass.key 4096
openssl rsa -passin pass:x -in server.pass.key -out privkey.pem

rm server.pass.key

openssl req -new -key privkey.pem -out server.csr -subj "/C=US/ST=/L=/O=Toxocious/OU=IT Department/CN=absoluterpg.com"
openssl x509 -req -days 3650 -in server.csr -signkey privkey.pem -out fullchain.pem

echo "[SUCCESS] Succesfully generated development certifications."
