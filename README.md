<div align="center">
  <img src="./app/images/Assets/banner.png" title="Pokemon Absolute Logo" alt="Pokemon Absolute Logo" />
  <h1 align="center">Pok&eacute;mon Absolute</h1>

  **Pok&eacute;mon Absolute** is an online text-based Pok&eacute;mon RPG, comprised of numerous features adapted from the official Pok&eacute;mon games, as well as entirely new features that enhance the playing experience of Pok&eacute;mon.

  <img src="https://img.shields.io/github/issues/Toxocious/Absolute?style=for-the-badge&logo=appveyor" />
  <img src="https://img.shields.io/github/forks/Toxocious/Absolute?style=for-the-badge&logo=appveyor" />
  <img src="https://img.shields.io/github/stars/Toxocious/Absolute?style=for-the-badge&logo=appveyor" />
  <br />
  <img src="https://img.shields.io/github/license/Toxocious/Absolute?style=for-the-badge&logo=appveyor" />
  <a href="https://visitorbadge.io/status?path=https%3A%2F%2Fgithub.com%2FToxocious%Absolute">
    <img src="https://api.visitorbadge.io/api/visitors?path=https%3A%2F%2Fgithub.com%2FToxocious%Absolute&countColor=%2337d67a" />
  </a>
  <br /><br />

  Check us out on Discord and consider starring the repository if you liked it!

  <a href="https://discord.gg/Km6btPhs" target="_blank">
    <img src="https://discord.com/api/guilds/1002005327555862620/widget.png?style=banner2" alt="Discord Banner" />
  </a>
</div>



## Table of Contents
- [Table of Contents](#table-of-contents)
- [About The Project](#about-the-project)
  - [Tech Stack](#tech-stack)
  - [Features](#features)
- [Getting Started](#getting-started)
  - [Prerequisites](#prerequisites)
  - [Installation](#installation)
  - [Project Setup](#project-setup)
  - [Stylesheet Setup](#stylesheet-setup)
  - [Chat Setup](#chat-setup)
  - [Discord Bot Setup](#discord-bot-setup)
  - [CI/CD Pipeline Setup](#cicd-pipeline-setup)
- [Contributing](#contributing)
- [License](#license)



## About The Project
### Tech Stack
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

### Features
Absolute has been developed from the ground up with love, and thus comes with a wide variety of features, such as:

- Dedicated Battles
- Open World Mapping
- Come Together With Clans
- Real Time In-Game Chat
- Live Trading
- Staff Panel

You may read about Absolute's features in further detail in our [FEATURES.md](docs/FEATURES.md) documentation.



## Getting Started
### Prerequisites
This project spins up [Docker](https://www.docker.com/get-started/) containers to set up the environment, so you will need that installed and configured on the machine that you're setting up this project on.

> It is possible to set-up this project without Docker, but the steps to do so are not currently documented.

### Installation
Clone the repository to the necessary directory.

If you would like to also install Absolute's chat system and discord bot, clone this repository recursively. If you do not want them, do not clone it recursively.

```bash
git clone --recursive https://github.com/Toxocious/Absolute.git
```

### Project Setup
Once you have Docker installed and have cloned this repository, all you need to do is run the [./start.sh](start.sh) script.

This script does a few things in order to set-up the game on your machine:
1. Generates SSL certificates
2. Builds all necessary Docker containers
3. Sets up your database by running all necessary migrations

If you're intending on running this project on a dedicated server with your own domain name, you will need to manually set the domain name for the SSL certificates. This can be done in [./certbot/generate.sh](certbot/generate.sh).

### Stylesheet Setup
When you have gotten the database set-up, you'll notice that - after navigating to your hosted copy of this repository - the stylesheets fail to load.

This is because Absolute's stylesheets are done in SCSS, which need compiled to CSS before they can be used for this project.

Install [SASS](https://sass-lang.com/install) for your operating system, add it to your PATH variables, open a terminal into this directory, and run the following command:

```sh
sass themes/sass:themes/css
```

This will compile the stylesheets a single time. If you wish to do numerous changes back-to-back - such as a layout overhaul or what have you - then use the ``--watch`` flag appended to the command, which will have SASS watch for changes in the ``themes/sass`` directory, and will automatically compile all changes into the ``themes/css`` directory while the sass watch command is active.

> Docker does not currently handle watching and automatically compiling our stylesheets, but will in a future update.

### Chat Setup
The source code used for Absolute's chat system can be found [HERE](https://github.com/Toxocious/Absolute-Chat) and includes a separate, in-depth README with set-up documentation.

> Docker has not yet been configured to set-up the chat system for you, but will in a future update.

### Discord Bot Setup
The source code used for Absolute's Discord Bot can be found [HERE](https://github.com/Toxocious/Absolute-Discord-Bot) and includes a separate, in-depth README with set-up documentation.

Absolute's docker configuration includes the necessary dockerfile to automatically build and run the Discord bot for you.

### CI/CD Pipeline Setup
Absolute uses a continue integration and deployment pipeline to automatically sync the repository ``main`` branch with the remote server.

We used to use a CI/CD pipeline through Gitlab to synchronize our code with a remote server, but since moving to Github and using Docker for development, we do not currently have a working Github CI/CD workflow configuration.

> This project does not yet dedicated hosting and thus doesn't have a valid github workflow configuration.



## Contributing
If you're interested in contributing to Absolute, please check out [CONTRIBUTING.md](docs/CONTRIBUTING.md) for more information.



## License
This project is licensed under MIT.

For more information about the license, check out the [LICENSE](LICENSE).
