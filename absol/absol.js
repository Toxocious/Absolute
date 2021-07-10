const PATH = require('path');
const HTTPS = require('https');
const FS = require('fs');
const MESSAGEHANDLER = require('./Handler');

const PATH_ROOT = PATH.resolve('../').indexOf('xampp') ? 'https://localhost' : PATH.resolve('../');

/**
 * Fetch all of our function files, and set them dynamically.
 */
const FUNCTIONLIST = new Set();
const FUNCTIONS = FS.readdirSync('./functions').filter(file => file.endsWith('.js'));
for ( const FILE of FUNCTIONS )
{
  FUNCTION = require( PATH.resolve(`./functions/${FILE}`) );
  FUNCTIONLIST.add(FUNCTION);
}

/**
 * Fetch all of our command files, and set them dynamically.
 */
const COMMANDLIST = new Map();
const COMMANDS = FS.readdirSync('./commands').filter(file => file.endsWith('.js'));
for ( const FILE of COMMANDS )
{
  COMMAND = require( PATH.resolve(`./commands/${FILE}`) );
  COMMANDLIST.set(COMMAND.name + '.js', COMMAND);
}

/**
 * Server Configuration
 */
const CONFIG = {
  PREFIX: '~',
  LOGFILE: './chatlog.txt',
  SERVER: {
    SERVER_PORT: 3001,
    MESSAGE_PORT: 9001,
    SSL: {
      cert: FS.readFileSync('C:/xampp/apache/conf/ssl.crt/server.crt'),
      key: FS.readFileSync('C:/xampp/apache/conf/ssl.key/server.key'),
    }
  }
};

/**
 * Create our servers, and listen for pings.
 */
const ABSOLUTE_SERVER = HTTPS.createServer(CONFIG.SERVER.SSL);
const ABSOLUTE = require('socket.io').listen(ABSOLUTE_SERVER);
ABSOLUTE_SERVER.listen(CONFIG.SERVER.SERVER_PORT);

/**
 * Keep track of connected clients.
 */
let CLIENTS = {};
let CLIENT_ID = 0;

/**
 * Listen for connections to the socket.
 */
ABSOLUTE.on('connection', function(socket)
{
  /**
   * Handle client connection.
   */
  socket.on('connection', function(client)
  {

  });

  /**
   * Handle client disconnection.
   */
  socket.on('disconnect', function(client)
  {

  });

  /**
   * Authenticate a client upon connecting.
   */
  socket.on('auth', function(client)
  {
    FUNCTION.FetchUser(client.user).then(Auth_User =>
    {
      if ( Auth_User )
      {
        /**
         * Check to see if the client has the correct auth code.
         */
        if ( Auth_User[0].Auth_Code !== client.postcode )
        {
          console.log("Invalid authentication.", Auth_User[0], client);
          socket.disconnect();
          return false;
        }

        /**
         * Check to see if the user has been banned from chat.
         */
        if ( Auth_User.Chat_Ban === 'yes' )
        {
          console.log("User is banned.", Auth_User[0].ID, Auth_User[0].Chat_Ban_Data);
          socket.disconnect();
          return false;
        }

        /**
         * Assign socket client ID.
         */
        socket['CLIENT_ID'] = CLIENT_ID;
        CLIENT_ID++;

        /**
         * Assign values to this specific client.
         */
        CLIENTS['Abso_User_' + socket['CLIENT_ID']] = {};
        CLIENTS['Abso_User_' + socket['CLIENT_ID']].User_ID = Auth_User[0].ID;
        CLIENTS['Abso_User_' + socket['CLIENT_ID']].socket = socket;

        /**
         * Return the last 20 messages sent to the client.
         */
        Total_Messages = MESSAGEHANDLER.MessageLog.length;
        Message_Log = MESSAGEHANDLER.MessageLog;
        for ( let i = 20; i > 0; i-- )
        {
          if ( typeof Message_Log[Total_Messages-i] !== "undefined" )
          {
            socket.emit("chat-message", Message_Log[Total_Messages-i]);
          }
        }
      }
    });
  });

  /**
   * Parse sent messages.
   */
  socket.on('chat-message', function(data)
  {
    let User_ID = data.user.user_id;
    const User_Data = {
      ID: data.user.user_id,
    };

    let chat_message = data.text;

    if ( User_ID )
    {
      FUNCTION.FetchUser(User_ID).then(Fetched_User =>
      {
        if ( Fetched_User )
        {
          /**
           * Push the retrieved user data values to User_Data.
           */
          User_Data.Username = Fetched_User[0].Username;
          User_Data.Rank = Fetched_User[0].Rank;
          User_Data.Avatar = Fetched_User[0].Avatar;
          User_Data.Chat_Ban = Fetched_User[0].Chat_Ban;
          User_Data.Chat_Ban_Data = Fetched_User[0].Chat_Ban_Data;

          /**
           * Check to see if the user has been banned from chat.
           */
          if ( User_Data.Chat_Ban === 'yes' )
          {
            console.log("User is banned.", User_Data.ID, User_Data.Chat_Ban_Data);
            socket.disconnect();
            return false;
          }

          /**
           * If the message sent was to use a command, process it here.
           */
          if ( data.text.startsWith(CONFIG.PREFIX) )
          {
            /**
             * Emit the message that was sent by the client.
             * Because it's a command, privately send it.
             * @param object - specific data regarding the sender.
             * @param string - the message that we're outputting.
             * @param bool - whether or not this should be a private message for the user.
             */
            socket.emit("chat-message",
              MESSAGEHANDLER.AddMessage(
                {
                  user_id: User_Data.ID,
                  username: User_Data.Username,
                  rank: User_Data.Rank,
                  avatar: User_Data.Avatar,
                },
                chat_message,
                {
                  isPrivate: true,
                  Private_To: User_Data.ID,
                }
              )
            );

            /**
             * Fetch the command, and the passed arguments.
             */
            const ARGS = data.text.slice(CONFIG.PREFIX.length).split(' ');
            const COMMAND_NAME = ARGS.shift().toLowerCase() + '.js';
            const COMMAND_DATA = COMMANDLIST.get(COMMAND_NAME);

            /**
             * Determine if the command exists or not, and process it.
             */
            if ( COMMAND_DATA != undefined )
            {
              /**
              * Execute the command and return it's response.
              * @param array
              * @param string
              * Returns the following object structure:
              * {
              *  message: STRING,
              * }
              */
              const COMMAND_RESPONSE = COMMAND_DATA.execute(
                {
                  ID: User_Data.ID,
                  Rank: User_Data.Rank
                },
                chat_message,
                ARGS
              );

              /**
               * Emit the response of the command that we executed.
               */
              socket.emit('chat-message',
                MESSAGEHANDLER.AddMessage(
                  {
                    user_id: 3,
                    username: 'Absol',
                    rank: 'bot',
                    avatar: `/Avatars/Custom/3.png`,
                  },
                  COMMAND_RESPONSE.message,
                  {
                    isPrivate: true,
                    Private_To: User_Data.ID,
                  }
                )
              );
            }

            /**
             * Emit that the command doesn't exist to the user.
             */
            else
            {
              socket.emit('chat-message',
                MESSAGEHANDLER.AddMessage(
                  {
                    user_id: 3,
                    username: 'Absol',
                    rank: 'bot',
                    avatar: `/Avatars/Custom/3.png`,
                  },
                  'The desired command does not exist.',
                  {
                    isPrivate: true,
                    Private_To: User_Data.ID,
                  }
                )
              );
            }
          }

          /**
           * Regular message; send it globally.
           */
          else
          {
            /**
             * Emit the message that was sent by the client.
             */
            ABSOLUTE.sockets.emit("chat-message",
              MESSAGEHANDLER.AddMessage(
                {
                  user_id: User_Data.ID,
                  username: User_Data.Username,
                  rank: User_Data.Rank,
                  avatar: User_Data.Avatar,
                },
                chat_message,
                {
                  isPrivate: false,
                  Private_To: null,
                }
              )
            );
          }
        }
        else
        {
          ABSOLUTE.sockets.emit('chat-message',
            MESSAGEHANDLER.AddMessage(
              {
                user_id: 3,
                username: 'Absol',
                rank: 'bot',
                avatar: `/Avatars/Custom/3.png`,
              },
              `An error occurred while attempting to fetch the data for user #${User_Data.ID}.`,
              {
                isPrivate: true,
                Private_To: User_Data.ID,
              }
            )
          );
        }
      });
    }
  });
});

/**
 * Everything is fine; emit a message to the socket and client announcing that the socket has started.
 */
setTimeout(function()
{
  ABSOLUTE.sockets.emit('chat-message',
    MESSAGEHANDLER.AddMessage(
      {
        user_id: 3,
        username: 'Absol',
        rank: 'bot',
        avatar: `/Avatars/Custom/3.png`,
      },
      'Welcome to Pok&eacute;mon Absolute.',
      {
        isPrivate: false,
        Private_To: null,
      }
    )
  );

  return false;
}, 1);
