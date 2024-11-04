<div align="center">
  <img src="./app/images/Assets/banner.png" title="Pokemon Absolute Logo" alt="Pokemon Absolute Logo" />
  <h1 align="center">Pok&eacute;mon Absolute</h1>

  **Pok&eacute;mon Absolute** is an online text-based Pok&eacute;mon RPG, comprised of numerous features adapted from the official Pok&eacute;mon games, as well as entirely new features that enhance the playing experience of Pok&eacute;mon.

  <img alt="Github Issues" src="https://img.shields.io/github/issues/Toxocious/Absolute?style=for-the-badge&logo=appveyor" />
  <img alt="Github Forks" src="https://img.shields.io/github/forks/Toxocious/Absolute?style=for-the-badge&logo=appveyor" />
  <img alt="Github Stars" src="https://img.shields.io/github/stars/Toxocious/Absolute?style=for-the-badge&logo=appveyor" />
  <br />

  <img alt="GitHub contributors" src="https://img.shields.io/github/contributors/Toxocious/Absolute?style=for-the-badge">
    <a href="https://visitorbadge.io/status?path=https%3A%2F%2Fgithub.com%2FToxocious%2FAbsolute">
    <img src="https://api.visitorbadge.io/api/visitors?path=https%3A%2F%2Fgithub.com%2FToxocious%2FAbsolute&label=Views&countColor=%234a618f&labelStyle=upper" />
  </a>
  <br />

  <img alt="License" src="https://img.shields.io/github/license/Toxocious/Absolute?style=for-the-badge&logo=appveyor" />

  Come join our comfy community over on Discord!

  <a href="https://discord.gg/SHnvbsS" target="_blank">
    <img src="https://discord.com/api/guilds/269182206621122560/widget.png?style=banner2" alt="Discord Invite Banner" />
  </a>
</div>



# Table of Contents
- [Table of Contents](#table-of-contents)
- [About The Project](#about-the-project)
  - [Tech Stack](#tech-stack)
  - [Features](#features)
- [Getting Started](#getting-started)
  - [Prerequisites](#prerequisites)
  - [Installation](#installation)
  - [Project Setup](#project-setup)
  - [Accessing PHPMyAdmin](#accessing-phpmyadmin)
  - [Chat Setup](#chat-setup)
  - [Discord Bot Setup](#discord-bot-setup)
  - [CI/CD Pipeline Setup](#cicd-pipeline-setup)
- [Setting The Root User's MySQL Password](#setting-the-root-users-mysql-password)
- [Contributing](#contributing)
- [License](#license)



# About The Project
## Tech Stack
- PHP
- Node.js
- JavaScript
- TypeScript
- MySQL
- Socket.io
- MariaDB
- Linux
- CI/CD
- xDebug

## Features
Absolute has been developed from the ground up with love, and thus comes with a wide variety of features, such as:

- Dedicated Battles
- Open World Mapping
- Come Together With Clans
- Real Time In-Game Chat
- Live Trading
- Staff Panel

You may read about Absolute's features in further detail in our [FEATURES.md](docs/FEATURES.md) documentation.



# Getting Started
## Prerequisites
This project spins up [Docker](https://www.docker.com/get-started/) containers to set up the environment, so you will need that installed and configured on the machine that you're setting up this project on.

> [!NOTE]
> It is possible to set-up this project without Docker, but the steps to do so are not currently documented.

## Installation
Clone the repository to the necessary directory.

If you would like to also install Absolute's chat system and discord bot, clone this repository recursively. If you do not want them, do not clone it recursively.

```bash
git clone --recursive https://github.com/Toxocious/Absolute.git
```

## Project Setup
Once you have Docker installed and have cloned this repository, all you need to do is run the [./start.sh](start.sh) script inside of your terminal.

You can do so as such:

**Windows**
```sh
bash ./start.sh
```

**Linux/MacOS**
```sh
./start.sh
```

This script does a few things in order to set-up the game on your machine:
1. Generates SSL certificates
2. Builds all necessary Docker containers
3. Sets up your database by running all necessary migrations

If you're intending on running this project on a dedicated server with your own domain name, you will need to manually set the domain name for the SSL certificates. This can be done in [./certbot/generate.sh](certbot/generate.sh).

A number of flags are included with the start script:
1. `-b` will force Docker to build even if the current commit hasn't changed
2. `-c` will force Docker to build without using cached images
3. `-v` will give you verbose messages during the SQL migration process

A [./shutdown.sh](./shutdown.sh) script is also included for safely shutting down the Docker environment and should be always be used.


## Accessing PHPMyAdmin
Once you have successfully built all Docker containers, you can access PHPMyAdmin via [https://localhost/db/](https://localhost/db/) when the environment is running.

> [!NOTE]
> The leading / is necessary, otherwise the page will fail to load necessary resources.

## Chat Setup
The source code used for Absolute's chat system can be found [HERE](https://github.com/Toxocious/Absolute-Chat) and includes a separate, in-depth README with set-up documentation.

> [!NOTE]
> Docker has not yet been configured to set-up the chat system for you, but will in a future update.

## Discord Bot Setup
The source code used for Absolute's Discord Bot can be found [HERE](https://github.com/Toxocious/Absolute-Discord-Bot) and includes a separate, in-depth README with documentation regarding included features.

Absolute's docker configuration includes the necessary dockerfile to automatically build and run the Discord bot for you.

## CI/CD Pipeline Setup
Absolute uses a continue integration and deployment pipeline to automatically sync the repository ``main`` branch with the remote server.

We used to use a CI/CD pipeline through Gitlab to synchronize our code with a remote server, but since moving to Github and using Docker for development, we do not currently have a working Github CI/CD workflow configuration.

> [!NOTE]
> This project does not yet dedicated hosting and thus doesn't have a valid github workflow configuration.



# Setting The Root User's MySQL Password
> [!IMPORTANT]
> ### This is deprecated, and both the root user and absolute user will set their passwords based on the supplied values in the .env file.
> ### This section will remain in the case where you have downgraded your mariadb container image.

When you first setup Absolute, the root MySQL password is an empty string. It is highly suggested that you change this to a very secure password with the following CLI command, where `'NEW_PASSWORD'` is the password that you want the root MySQL account to have.

```sh
docker exec -it absolute-mysql bash
mariadb -u root -p'' password 'NEW_PASSWORD'
```

Do make sure to update the `MYSQL_ROOT_PASSWORD` `.env` value to reflect the new password that you've set.



# Contributing
If you're interested in contributing to Absolute, please check out [CONTRIBUTING.md](docs/CONTRIBUTING.md) for more information.



# License
This project is licensed under MIT.

For more information about the license, check out the [LICENSE](LICENSE).
