var https = require("https");
var mysql = require('mysql');
var numeral = require('numeral');
var fs = require('fs');
var os = require('os');
var msghndler = require('./message.js');
var messageHandler = new msghndler.messageHandler();
var fn = require('./commands/functions.js');
var runner = require("child_process"); // http://promincproductions.com/blog/run-php-script-node-js/

var COMMAND_PHP_PATH = "command.php";
var FULL_DEBUG = true;
var PHP_ARG_SEPERATOR = '~szpAAce~';

// Get the command line argument for which config is activated at this time.
var server = 'absolute';
var script_location = '';
process.argv.forEach(function (val, index, array) {
    if (index == 2) {
        server = val;
    }
    if (index == 3) {
        script_location = val;
    }
});

if (server != 'tpk6' && server != 'tpk7' && server != 'absolute' ) {
  console.log('Set the server: tpk6 or tpk7 or absolute');
  process.exit();
}

var Commands = [
    'activity',
    'battle',
    'hug',
    'exp',
    'level',
    'nature',
    'ohko',
    'pickaxe',
    'pokemon',
    'rarity',
    'scyther',
    'showdown',
    'tl',
    'tset',
    'whatdo',
    'whois',
    'wtc'
];

var conn;
var config = {
  absolute : {
    logfile        : 'log.txt',
    host           : 'localhost',
    user           : 'root',
    password       : '$bQ721qb9oS3WIh#SQgEGzA7',
    database       : 'absolute',
    game           : 'absolute',
    pass           : '$bQ721qb9oS3WIh#SQgEGzA7',
    messages_port  : 9001,
    chaterpie_port : 3000
  },
};

var config = config[server];

fn.log(os.EOL+  "~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~", config.logfile);
fn.log(     "~ Scyther is loading. Prepare to get your chat handed to you. -B0sh ~", config.logfile);
fn.log(     "~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~" + os.EOL, config.logfile);
fn.log("Server: "+server+" :: Port: " + config.chaterpie_port + "", config.logfile)

var options = {
  //key: fs.readFileSync('/etc/letsencrypt/live/tpkrpg.net/privkey.pem'),
  //cert: fs.readFileSync('/etc/letsencrypt/live/tpkrpg.net/cert.pem'),
  //ca: fs.readFileSync('/etc/letsencrypt/live/tpkrpg.net/chain.pem')
  cert: fs.readFileSync('C:/xampp/apache/conf/ssl.crt/server.crt'),
  key: fs.readFileSync('C:/xampp/apache/conf/ssl.key/server.key')
};

var messages_server = https.createServer(options)
var chaterpie_server = https.createServer(options)

var TPK_Messages = require('socket.io').listen(messages_server);
var Chaterpie = require('socket.io').listen(chaterpie_server);

messages_server.listen(config.messages_port);
chaterpie_server.listen(config.chaterpie_port);

Chaterpie.on('connection', function (socket) {

  // On main client connection
  socket.on('connection', function(client) {
    if (FULL_DEBUG) {
      console.log("Client Connected");
    }
  });

  // On page disconnect
  socket.on('disconnect', function() {
    delete CLIENTS["user" + socket['tpk_clientID']];
  });

  // After page load, auth call made
  socket.on('auth', function(client) {
    socket['tpk_clientID'] = CLIENT_ID;
    CLIENT_ID++;

    CLIENTS["user" + socket['tpk_clientID']] = {};

    auth_user(socket, client.user, client.postcode);
  });

  // Request messages to display in chaterpie
  socket.on('chaterpie-request-msg', function(data) {
    console.log("Requesting message history...");
    if (chat_check(socket)) return false;
    user = CLIENTS["user"+socket['tpk_clientID']];

    if (data > 500) {
      data = 500;
    }

    MessageLog = messageHandler.MessageLog;
    length = MessageLog.length;

    //Return the last 30 messages to the connecting client
    for(var i = data; i > 0; i--) {
      if (
        typeof MessageLog[length-i] !== "undefined" &&
        (
          MessageLog[length-i].info.private_to == user['id'] ||
          typeof MessageLog[length-i].info.private_to === "undefined"
        )
      ) {
        socket.emit("irc-message", MessageLog[length-i]);
      }
    }
  });

  // Send a message
  socket.on('input', function(data) {
    if (chat_check(socket)) return false;

    user = CLIENTS["user"+socket['tpk_clientID']];
    conn.query({
      sql: "SELECT `id`, `Username`, `Rank`, `Power` FROM users WHERE id = ? LIMIT 1",
      values: [user.id]
    }, function (error, results, fields) {
      if (error) {
        return console.error(error);
      }

      if (results.length === 0) {
        return false;
      }

      check_ban_user(socket, results[0]);

      userinfo = {
        nick: user.Username,
        userID: user.id,
        rank: user.Rank
      };

      if (messageHandler.isSpam(user)) {
        socket.emit("irc-message", messageHandler.self({
          nick: 'Error',
          userID: -1,
          rank: 'admin',
        }, "You have sent too many message in the last few seconds. Please wait and try again.", {private_to:user['id']}, config.logfile));

        return false;
      } else if (data.text.length > 250) {
        socket.emit("irc-message", messageHandler.self({
          nick: 'Error',
          userID: -1,
          rank: 'admin',
        }, "Messages must be less than 250 characters.", {private_to:user['id']}, config.logfile));

        return false;
      }

      info = {}
      if ( data.text == "crash absolute" && user['id'] <= 2 )
      {  
        setTimeout(function()
        {
          throw new Error("An Administrator initiated AbsoChat crash has occurred.");
        }, 2000);
      } 
      
      if (data.text.substring(0, 4) == "/me ") {
        data.text = data.text.replace("/me", "");
        info.command = "action";
        userinfo.display = 0;

        Chaterpie.sockets.emit("irc-message", messageHandler.add(userinfo, data.text, info, config.logfile));
        return false;
      } else if (data.text.substring(0, 6) == "/kick " && user.Power >= 3) {
        var parts = data.text.split(/\s+/);
        var id = parts[1];
        var reason = parts.slice(2).join(' ');

        conn.query({
          sql: "SELECT * FROM users WHERE id=? OR Username=? LIMIT 1",
          values: [id, id]
        }, function(error, results) {
          if (error) {
            return console.error(error);
          }

          if (results.length == 1) {
            u = results[0];
            kickUser(socket, u['id'], reason);
          }
        });
        return false;
      }
      else if ( data.text.substring(0, 6) == "/clear" && user.Power >= 3 )
      {
        messageHandler.clear();
        Chaterpie.sockets.emit("irc-message",
          messageHandler.add(
            { nick: 'Absol', userID: 3, rank: 'bot', image: '', clear: true },
            "The chat has been cleared by "+user.Username+".", undefined, config.logfile
          )
        );
        return false;
      }
      else if (data.text.substring(0, 5) == "/ban " && user.Power >= 3)
      {
        var parts = data.text.split(/\s+/);
        var id = parts[1];
        var time = parts[2];
        var reason = parts.slice(3).join(' ');

        if (time === '' || isNaN(time)) {
          time = 300;
          reason = parts.slice(2).join(' ');
        }

        conn.query({
          sql: "SELECT * FROM users WHERE id=? OR Username=? LIMIT 1",
          values: [id, id]
        }, function(error, results) {
          if (error) {
            return console.error(error);
          }

          if (results.length == 1) {
            u = results[0];
            banUser(socket, u['id'], reason, time);
          }
        });
        return false;
      } else if (data.text.substring(0, 7) == "/unban " && user.Power >= 3) {
        // TODO
        return false;
      } else if (user.chat_sprite != '') {
        userinfo.image = user.chat_sprite;
      }

      if (data.text.charAt(0) == '~' && isCommand(data.text.substring(1).split(' ')[0].toLowerCase())) {
        info.private_to = user.id;
        socket.emit("irc-message", messageHandler.self(userinfo, data.text, info, config.logfile));
      } else {
        Chaterpie.sockets.emit("irc-message", messageHandler.add(userinfo, data.text, info, config.logfile));
      }

      var cmd = parseCommand(user.nick, data.text, 'Chaterpie', socket);

      // update online list for chatting users
      // 28 is page_id of Chaterpie
      //conn.query({
      //  sql: "UPDATE `users` SET `online_time`=?, `on_page`=? WHERE `id`=? LIMIT 1",
      //  values: [ Math.floor(Date.now()/1000), 28, user.id ]
      //}, function (error, results) {
      //  // query result doesn't matter, can happen at anytime
      //});

    });
  });

  // the nick list of both online users in TPK, and from the #TPK_Test Channel.
  socket.on('nicklist', function() {
    if (chat_check(socket))
    return false;

    conn.query({
      sql: "SELECT id, username FROM `users` WHERE `online_time` > ? ORDER BY `online_time` DESC",
      values: [ Math.floor(Date.now()/1000) - 60 * 10]
    }, function (error, results, fields) {
      socket.emit("nick-list", [ results,  {} ]);
    });
  });

  // Returns information about a user to construct a User Info page
  socket.on('chaterpie-user-info', function(id) {
    if (chat_check(socket)) return false;

    conn.query({
      sql: "SELECT * FROM users WHERE id=? LIMIT 1",
      values: [id]
    }, function(error, results, fields) {
      if (error) {
        return console.error(error);
      }

      if (results.length == 1) {
        u = results[0];
        socket.emit("chaterpie-user-info", {
          user: {
            userID: u['id'],
            username: u['username'],
          }
        });
      }
    });
  });

  socket.on('chaterpie-kick-user', function (userid, reason) { return kickUser(socket, userid, reason); });
  socket.on('chaterpie-ban-user', function (userid, reason, time) { return banUser(socket, userid, reason, time); });
});

function updateClient(clientID, thing, val) {
  CLIENTS["user"+clientID][thing] = val;
  return true;
}

// Socket.io connection from inside TPK
TPK_Messages.on('connection', function (socket) {
  socket.on('msg', function(msg) {
    scyther({}, msg);
  });
});

// Time the server initiated
var startTime = Math.floor(Date.now() / 1000);

// Command called when a Scyther command response is made.
// Logs, and sends a chat from Scyther
function scyther(cmd, message) {
  if (cmd.private_message != true) {
    if (cmd.image == "null")
      cmd.image = []
    else if (typeof cmd.image === "undefined")
      cmd.image = fn.getPokeIcon(359, 0, "shiny");
    else
      cmd.image = fn.getPokeIcon(cmd.image[0], cmd.image[1], cmd.image[2]);

    if (cmd.hidden_command != true || typeof cmd.socket === "undefined") {
      Chaterpie.sockets.emit('irc-message', messageHandler.add({
        nick: cmd.nick ? cmd.nick : 'Absol',
        userID: 3,
        rank: 'bot',
        image: cmd.image
      }, message, undefined, config.logfile));
    } else {
      cmd.socket.emit('irc-message', messageHandler.self({
        nick: cmd.nick ? cmd.nick : 'Absol',
        userID: 3,
        rank: 'bot',
        image: cmd.image
      }, message, undefined, config.logfile));
    }
  }
}

function kickUser(socket, userid, reason) {
  if (chat_check(socket)) return false;
  user = CLIENTS["user"+socket['tpk_clientID']];

  if (user['power'] < 3)
  return;

  if (typeof reason === "undefined" || reason.trim() == '')
  reason = '';
  else
  reason = "("+reason+")";

  //Loop through CLIENTS to see if one of them has the user ID of a person
  for (var cl in CLIENTS) {
    if (CLIENTS.hasOwnProperty(cl) && CLIENTS[cl]['id'] == userid) {
      var KickedUser = CLIENTS[cl];
    }
  }

  if (typeof KickedUser !== "undefined") {
    Chaterpie.sockets.emit('irc-kick', messageHandler.add([
      {
        nick: KickedUser.nick,
        userID: KickedUser.id,
        rank: KickedUser.rank,
        display: 0
      },
      {
        nick: user.nick,
        userID: user.id,
        rank: user.rank,
        display: 20
      }
    ], " has been kicked by . "+reason+"", {
      command: 'kick',
      reason: reason
    }, config.logfile));

    scyther({private_message:true}, KickedUser['username']+ " has been kicked by "+user['username']+". "+reason+"");
  }
}

function banUser(socket, userid, reason, time) {
  if (chat_check(socket)) return false;
  user = CLIENTS["user"+socket['tpk_clientID']];

  if (user['power'] < 3 || typeof time === "undefined")
  return;

  if (typeof reason === "undefined" || reason.trim() == '')
  reason = '';
  else
  reason = "("+reason+")";

  var chat_ban = parseInt((parseInt(time) + parseInt(Math.round(Date.now()/1000))))+'"'+user['id']+'"'+reason+'"'+parseInt(Math.round(Date.now()/1000));

  //Loop through CLIENTS to see if one of them has the user ID of a person
  BannedYet = false;
  for (var cl in CLIENTS) {
    if (CLIENTS.hasOwnProperty(cl) && CLIENTS[cl]['id'] == userid) {
      var BannedUser = CLIENTS[cl];
      CLIENTS[cl]['authenticated'] = false;

      if (BannedYet == false) {
        BannedYet = true;

        var timeText = "";
        if(time <= 120) timeText = ""+time+" seconds";
        else if(time >= 120 && time <= 3599*2+1) timeText = Math.floor(time / 60)+" minutes";
        else if(time >= 3600*2) timeText = numeral(time / 3600).format('0.[00]')+" hours";

        Chaterpie.sockets.emit('irc-ban', messageHandler.add([
          {
            nick: BannedUser.nick,
            userID: BannedUser.id,
            rank: BannedUser.rank,
            display: 0
          },
          {
            nick: user.nick,
            userID: user.id,
            rank: user.rank,
            display: 20
          }
        ], " has been banned by  for "+timeText+". "+reason, {
          command: 'ban',
          reason: reason,
          banlength: parseInt(time)
        }, config.logfile));

        conn.query({
          sql: "UPDATE users SET ChatBanned=? WHERE id=? LIMIT 1",
          values: [chat_ban, BannedUser['id']]
        }, function (error, results, fields) {

        });

        scyther({private_message:true}, BannedUser['username']+" has been banned by "+user['username']+" for "+timeText+". "+reason);
      }
    }
  }
}

function isCommand(c) {
  return Commands.indexOf(c) !== -1;
}

//This function is seriously improvable
function parseCommand(nick, msg, location, socket) {
  parsed = msg.substring(1).split(' ');
  if ((msg.charAt(0) != '!' && msg.charAt(0) != '~') || !isCommand(parsed[0].toLowerCase())) {
    return false; //no command called
  }

  var cmd = parsed;
  cmd.hidden_command = msg.charAt(0) == '~';

  if (cmd !== false) {
    cmd.nick = nick;
    cmd.private_message = false;

    if (location == 'Chaterpie')
      cmd.socket = socket;

    argsString = '';
    for (var property in cmd) {
      if (cmd.hasOwnProperty(property) && (property%1)===0) {
        if (argsString != '')
          argsString += PHP_ARG_SEPERATOR;
        argsString += cmd[property];
      }
    }

    //encode the args string to base 64 to prevent any kind of bash injection
    var buffer = new Buffer(argsString);
    var php_input = buffer.toString('base64');

    //Execute a php script; send the command
    runner.exec("php " + COMMAND_PHP_PATH + " " +php_input + " "+config['game'], function(err, response, stderr) {
      if (err) {
        console.log(""); /* log error */
        console.log("Command Error! args: "+argsString.replace(/~szpAAce~/g, " ")); /* log error */
        console.log(""); /* log error */
        console.log(err); /* log error */
        Chaterpie.sockets.emit("irc-message", messageHandler.add({
          nick: 'Error',
          userID: -1,
          rank: 'admin'
        }, "An error has occurred with your command.", undefined, config.logfile));
        return;
      }

      try {
        response = JSON.parse(response); // parse the message
      } catch (e) {
        console.log("Command Error! args: "+argsString); /* log error */
        console.log(response); /* log error */
        Chaterpie.sockets.emit("irc-message", messageHandler.add({
          nick: 'Error',
          userID: -1,
          rank: 'admin'
        }, "The command failed to run.", undefined, config.logfile));
        return;
      }

      //Send the Scyther messages as appropriate
      for (var property in response.messages){
        if (response.messages.hasOwnProperty(property)){
          cmd.image = response.messages[property]['image'];
          cmd.nick = '';
          scyther(cmd, ""+response.messages[property]['message']);
        }
      }

      //Send the Scyther messages as appropriate
      for (var property in response.log){
        if (response.log.hasOwnProperty(property)){
          console.log(""+response.log[property]['message']);
        }
      }
    });
  }
}

var AlreadySaid = [];
var RetrieveGameMessage = setInterval(function () {
  results = [];
  conn.query({
    sql: "SELECT * FROM chat WHERE type='scyther_message'",
    values: []
  }, function (error, results, fields) {
    if (error) {
      return console.error(error);
    }

    if (results.length == 0) {
      return;
    }

    for (var i = 0; i < results.length; i++) {
      if (AlreadySaid.indexOf(results[i]['id']) == -1) {
        data = {};

        if (results[i]['icon'].split(';').length == 3) {
          var image = results[i]['icon'].split(';');
          data.image = [image[1], image[2], image[0]];
        }

        if (results[i]['title']) { data.nick = results[i]['title']; }

        if (results[i]['private_to'] != 0 && results[i]['private_to'] != null) {
          data.hidden_command = true;
          for (var property in CLIENTS) {
            if (CLIENTS.hasOwnProperty(property)) {
              if (results[i]['private_to'] == CLIENTS[property]['id']) {
                data.socket = CLIENTS[property].socket;
              }
            }
          }

          if (typeof data.socket === "undefined") {
            messageHandler.add({
              nick: results[i]['title'],
              userID: -1,
              rank: '',
              image: data.image
            }, results[i]['message'], {background:'white', private_to:results[i]['private_to']}, config.logfile);
          } else {
            data.socket.emit('irc-message', messageHandler.add({
              nick: results[i]['title'],
              userID: -1,
              rank: '',
              image: data.image
            }, results[i]['message'], {background:'white', private_to:results[i]['private_to']}, config.logfile));
          }
        } else {
          scyther(data, results[i]['message']);
        }
        AlreadySaid[AlreadySaid.length] = results[i]['id'];
      }
      conn.query({
        sql: "DELETE FROM chat WHERE type='scyther_message' AND id=?",
        values: [results[i]['id']]
      }, function (errorf, results, fields) {});
    }
  });
}, 500);

function handleDisconnect() {
  conn = mysql.createConnection(config); 

  conn.connect(function(err) {
    if(err) { 
      console.log('error when connecting to db:', err);
      // attempt to reconnect to database
      setTimeout(handleDisconnect, 2000); 
    }
  });

  conn.on('error', function(err) {
    console.log('db error', err);
    // if a fatal error has occurred, quit. force restart
    if (err.fatal && err.fatal == true) {
      throw new Error("A MySQL Fatal Error has occurred.");
    }

    if(err.code === 'PROTOCOL_CONNECTION_LOST') { 
      handleDisconnect();
    } else {
      throw err;
    }

  });
}

handleDisconnect();

process.stdin.resume();//so the program will not close instantly

var scytherHasCrashedAndSaidItsThing = false;
function exitHandler(options, err) {
  if (!scytherHasCrashedAndSaidItsThing) {
    scytherHasCrashedAndSaidItsThing = true;

    scyther({private_message: false}, "Absol & Absolute Chat have been terminated. Please refresh the page.");

    var Seconds = Math.floor(Date.now() / 1000) - startTime;

    if(Seconds <= 120)
      lastseen = ""+Seconds+" seconds";
    else if(Seconds >= 120 && Seconds <= 3599*2+1)
      lastseen = Math.floor(Seconds / 60)+" minutes";
    else if(Seconds >= 3600*2)
      lastseen = numeral(Seconds / 3600).format('0.[00]')+" hours";

    fn.logSync(os.EOL+"************** CRASH *****************", config.logfile);

    if (err)
      fn.logSync(err.stack, config.logfile)

    if (options.nodemon)
      fn.logSync("Nodemon: File(s) Updated", config.logfile);

    fn.logSync(os.EOL+'Absolute Chat lasted for '+lastseen+'.', config.logfile);
  }

  if (options.cleanup) console.log('clean');
  if (options.exit) process.exit();
  if (options.nodemon) process.kill(process.pid, 'SIGUSR2');
}

//do something when app is closing
process.on('exit', exitHandler.bind(null,{cleanup:true}));

//catches ctrl+c event
process.on('SIGINT', exitHandler.bind(null, {exit:true}));

//catches nodemon exits
process.once('SIGUSR2', exitHandler.bind(null, {nodemon:true}));

//catches uncaught exceptions
process.on('uncaughtException', exitHandler.bind(null, {exit:true}));


function determineUserRank(position) {
  switch (position) {
    case 'Root Administrator':
    case 'Administrator':
      return 'admin'; break;
    case 'Super Moderator':
      return 'super_mod'; break;
    case 'Moderator':
    case 'Moderator/Artist':
      return 'mod'; break;
    case 'Trial Moderator':
    case 'Trial Mod/Artist':
      return 'temp_mod'; break;
    case 'Chat Moderator':
    case 'Chat Mod/Artist':
      return 'chat_mod'; break;
    case 'Artist':
      return 'artist'; break;
  }
  return '';
}
function pad(n, len) {
  return (new Array(len + 1).join('0') + n).slice(-len);
}

//Chat check
function chat_check(socket) {
  if (typeof CLIENTS["user"+socket['tpk_clientID']] === "undefined") {
    console.log("This user is no longer authenticated");
    socket.emit("irc-fail", "auth_fail");
    socket.disconnect();
    return true;
  }

  if (CLIENTS["user"+socket['tpk_clientID']].authenticated != true) {
    console.log("This user failed authentication test");
    socket.emit("irc-fail", "auth_fail");
    socket.disconnect();
    return true;
  }

  return false;
}


var CLIENTS = {};
var confirmInformation = {};
var CLIENT_ID = 0;

//Reworked auth user function
function auth_user(socket, userID, postcode)
{
  conn.query({
    sql: "SELECT * FROM users WHERE id = ? LIMIT 1",
    values: [userID]
  }, function (error, results, fields) {
    if (error)
    {
      return console.error(error);
    }

    if (results.length == 0)
    {
      console.log("WARNING: User not found");
      socket.disconnect();
      return false;
    } 
    else if (results[0]['Auth_Code'] != postcode)
    {
      if (FULL_DEBUG)
      {
        console.log("Chat auth failed. Post Codes mismatched. " + results[0]['Auth_Code'] + " :: "+ postcode);
      }

      socket.disconnect();
      return false;
    }

    var user = results[0];

    //If you are banned from chat, disconnect now.
    if (check_ban_user(socket, user) == true)
      return false;

    CLIENTS["user"+socket['tpk_clientID']] = user;
    CLIENTS["user"+socket['tpk_clientID']].nick = user.Username;
    CLIENTS["user"+socket['tpk_clientID']].socket = socket;
    CLIENTS["user"+socket['tpk_clientID']].authenticated = true;

    CLIENTS["user"+socket['tpk_clientID']].rank  = determineUserRank(user['position']);

    //Get the rank of the client
    if (user['donator_status'] != null) {
      donator = user['donator_status'].split(',');
      if (donator.length == 2 && donator[1] > (Date.now()/1000))
        CLIENTS["user"+socket['tpk_clientID']].rank  = 'voice';
    }

    //Master Titles equate to the sprite in your chatroom
    conn.query({
      sql: "SELECT * FROM pokemon WHERE id=? LIMIT 1",
      values: [userID, 'yes']
    }, function (error, results, fields) {
      if (error) {
        return console.error(error);
      }

      if (chat_check(socket)) return false;

      if (results.length === 0) {
        CLIENTS["user"+socket['tpk_clientID']].chat_sprite = '';
      } else {
        CLIENTS["user"+socket['tpk_clientID']].chat_sprite = results[0].icon;
      }
    });

    // Return the last 30 messages to the connecting client
    length = messageHandler.MessageLog.length;
    MessageLog = messageHandler.MessageLog;
    for(var i = 30; i > 0; i--) {
      if (typeof MessageLog[length-i] !== "undefined" &&
        (
          MessageLog[length-i].info.private_to == CLIENTS["user"+socket['tpk_clientID']]['id'] ||
          typeof MessageLog[length-i].info.private_to === "undefined"
        )
      ) {
        socket.emit("irc-message", MessageLog[length-i]);
      }
    }
  });
}

function check_ban_user(socket, user)
{
  //chat_ban = user['chat_ban'].split('"');

  //if (user.banned == 'yes') {
  //  socket.disconnect();
  //  return true;
  //} else if (typeof chat_ban[0] !== "undefined" && chat_ban[0] > Date.now()/1000) {
  //  if (FULL_DEBUG)
  //    console.log("Chat auth failed. "+user['username'] + " is banned." + (chat_ban[0] - Math.round(Date.now()/1000))+"sec");
  //  //Get info of the person who banned you
  //  conn.query({
  //    sql: "SELECT * FROM users WHERE id=? LIMIT 1",
  //    values: [chat_ban[1]]
  //  }, function (error, results, fields) {
  //    if (typeof results[0] === "undefined") results = [{username:'', userID: '', position:''}];
  //    socket.emit('irc-ban',  messageHandler.self([
  //      {
  //        nick: user.Username,
  //        userID: user.id,
  //        rank: determineUserRank(user['position']),
  //        display: 0
  //      },
  //      {
  //        nick: results[0].username,
  //        userID: results[0].id,
  //        rank: determineUserRank(results[0]['position']),
  //        display: 20
  //      }
  //    ], " has been banned by . "+chat_ban[2]+"", {
  //      command: 'ban',
  //      reason: chat_ban[2],
  //      banlength: chat_ban[0] - Math.round(Date.now()/1000),
  //      do_not_log: true
  //    }, config.logfile));
  //    socket.disconnect();
  //  });
  //  return true;
  //}

  return false;
}

setTimeout(function()
{
    if (script_location == 'install')
    {
        scyther({private_message:false}, "Absolute has been updated. Absolute Chat has been activated. Hello!");
    }
    else if (script_location == 'debug')
    {
        scyther({private_message:false}, "Absolute is in debug mode. Expect crashes. Say, nothing.");
    }
    else
    {
        //scyther({private_message:false}, "Absolute Chat has been activated. Say, hey.");
        let icon = fn.getPokeIcon(359, 0, "shiny");
        messageHandler.clear();
        Chaterpie.sockets.emit("irc-message",
          messageHandler.add(
            { nick: 'Absol', userID: 3, rank: 'bot', image: icon, clear: true },
            "Long have we waited. Absolute activated.", undefined, config.logfile
          )
        );
        return false;
    }
}, 0);
