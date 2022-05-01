import * as fs from 'fs';
import * as http from 'http';
import { Server, Socket } from 'socket.io';

import MySQL from '../classes/mysql';

class Absol {
  server: Server | undefined;
  messages: [] | undefined;

  start(server: http.Server, initType: string) {
    this.server = new Server(server, {
      cors: {
        origin: '*',
      },
    });

    this.server.on('connection', (socket: Socket) => {
      socket.on('auth', (authData: any) => {
        console.log('[Server] Client Auth:', authData);
      });

      socket.on('disconnect', () => {
        console.log('[Server] Client Disconnected.');
      });

      socket.on('input', async (inputData: any) => {
        console.log('[Server] Client Input', inputData);
      });
    });

    switch (initType) {
      case 'debug':
        console.log('[Server] Debug mode. Emit message.');
        break;

      case 'update':
        console.log('[Server] Updated! Emit message.');
        break;

      default:
        console.log('[Server] Absolute Chat is online. Emit message.');
        break;
    }
  }
}

export default Absol;
