import * as fs from 'fs';
import * as http from 'http';
import * as https from 'https';

import { SERVER_PORT } from './config/server';
import MySQL from './classes/mysql';
import Absol from './classes/server';

let AbsolServer: Absol;

let initType: string = '';
process.argv.forEach((arg, index) => {
  index === 2 ? (initType = arg) : '';
});

let SERVER_INSTANCE: http.Server;
let SERVER_SSL: Object;
if (fs.existsSync('/etc/letsencrypt/live/www.absoluterpg.com/fullchain.pem')) {
  try {
    SERVER_SSL = {
      cert:
        fs.readFileSync(
          '/etc/letsencrypt/live/www.absoluterpg.com/fullchain.pem'
        ) ?? '',
      key:
        fs.readFileSync(
          '/etc/letsencrypt/live/www.absoluterpg.com/privkey.pem'
        ) ?? '',
    };
  } catch (error) {
    console.log(
      '[Absolute Chat | Init] Production SSL certs not found.',
      error
    );
    process.exit();
  }

  SERVER_INSTANCE = https.createServer(SERVER_SSL).listen(SERVER_PORT);
} else {
  SERVER_INSTANCE = http.createServer().listen(SERVER_PORT);
}

const MYSQL_INSTANCE: MySQL = MySQL.instance;
MYSQL_INSTANCE.connectDatabase().finally(() => {
  if (MYSQL_INSTANCE.isConnected()) {
    AbsolServer = new Absol();
    AbsolServer.start(SERVER_INSTANCE, initType);
  }
});
