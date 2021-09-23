<div align="center">
  <img src="./images/Assets/banner.png" title="Pokemon Absolute Logo" alt="Pokemon Absolute Logo" />
  <h1 align="center">Pok&eacute;mon Absolute</h1>
</div>

**Pok&eacute;mon Absolute** is an unofficial online text-based Pok&eacute;mon RPG, comprised of numerous fleshed out features.

The code-base currently runs on PHP 7.2, with plans to upgrade the code-base to PHP 7.4+ in the future.<br />
You may check out Pok&eacute;mon Absolute's roadmap [here](https://absoluterpg.com/roadmap.php).

## Features
- feature 1
- feature 2

## Contributing
If you're interested in contributing to Pok&eacute;mon Absolute'S code-base, check out [CONTRIBUTING.md](/CONTRIBUTING.md).

## Project Set-up (Local)
In this example, XAMPP for Windows will be used.<br />
To begin setting up Pok&eacute;mon Absolute on a local server...

## Project Set-up (Non-local)
In this example, Linode will be used.<br />
To begin setting up Pok&eacute;mon Absolute on a hosted server...

## Environment Variables
For Absolute, we set a number of Environment Variables for setting up the credentials that are needed to use our database.

### PHP 7.x
1. SSH into your server.
2. Navigate to /etc/apache2
3. Edit `envvars`, and append the following lines to the file:
```
# Database table to access
export DATABASE_TABLE="absolute"

# Database user to login with
export DATABASE_USER="absolute"

# Database password to login with
# Change this to something secure.
export DATABASE_PASSWORD="DATABASE_PASSWORD"
```

## License
See [LICENSE](/LICENSE).
