var socket;

Absolute = {
  updatedOn: '5-1-2019',
  version: '1.0',
  window: 'chat',
  port: '3000',
  active: '',
  staff: false,
  user: {},

  // User connected.
  isConnected: function()
  {
    return socket.connected;
  },

  setTextarea: function()
  {
    if ( this.isConnected() )
    {
      $('#chatMessage').css('background', '')
    }
    else
    {
      $('#chatMessage').css('background', '#190000')
    }
  },

  // Absolute has been enabled.
  enable: function()
  {
    Absolute.active = true;
    $('#chatContent').css('display', 'block');

    // Create the socket commands.
    if (typeof socket != "object")
    {
      ChatMessage.Messages = new Array();

      socket = io('localhost:3000',
      {
        reconnection: true,
        secure: true
      });

      socket.emit('auth',
      {
        user: Absolute.user.user_id,
        postcode: Absolute.user.postcode
      });

      socket.on("connect", function()
      {
        Absolute.setTextarea();

        this.failed_connects = 0;

        socket.emit('auth',
        {
          user: Absolute.user.user_id,
          postcode: Absolute.user.postcode
        });
      });

      socket.on("disconnect", function()
      {
        Absolute.active = false;
        Absolute.addErrorMessage('You have been disconnected from Absolute Chat. Please refresh the page.');

        Absolute.setTextarea();
      });

      socket.on("irc-message", function(data)
      {
        ChatMessage.add(data);
      });

      socket.on("nick-list", function(data)
      {
        Absolute.NickList = data;
        Absolute.changeWindow('settings');
      });

      socket.on("Absolute-user-info", function(data)
      {
        Absolute.UserInfo = data;
        Absolute.changeWindow('userinfo');
      });
    }
  },

  disable: function()
  {
    Absolute.active = true;
    $('#Absolute').css('display', 'none');
  },

  addErrorMessage: function(error_message)
  {
    var msg = ChatMessage.Messages[ChatMessage.Messages.length-1] || {};
    if ( Absolute.active != true || msg.text == "Absol & Absolute Chat have been terminated. Please refresh the page." )
    {
      return false;
    }

    ChatMessage.add({
      users: [{nick: "Error", userID: -1, rank: 'admin', image:''}],
      timestamp: Math.floor(Date.now() / 1000),
      info: {},
      id: ChatMessage.Messages.length,
      text: error_message
    });
  },
},

ChatMessage = {
  Messages: new Array(),
  Messages_Displayed: 30,
  Temporary_Chat: "",

  debug: function()
  {
    
  },

  purge: function()
  {
    ChatMessage.Messages = new Array();
  },

  add: function(message)
  {
    if ( Absolute.active == true )
    {
      if ( message.text == "Absol & Absolute Chat have been terminated. Please refresh the page." )
      {
        ChatMessage.add({
          users: [{nick: "Error", userID: -1, rank: 'admin', image:''}],
          timestamp: Math.floor(Date.now() / 1000),
          info: {},
          id: ChatMessage.Messages.length,
          text: "Absol has been terminated. Please refresh the page."
        });

        return;
      }

      if ( message.users[0].clear == true )
      {
        ChatMessage.Messages = new Array();
      }
      
      ChatMessage.Messages.push(message);
      ChatMessage.display();
    }
  },

  display: function()
  {
    ChatMessage.Temporary_Chat = '';

    let Log_Length = ChatMessage.Messages.length - 1;

    if ( Log_Length > ChatMessage.Messages_Displayed )
    {
      Start_Log = Log_Length - ChatMessage.Messages_Displayed;
    }
    else
    {
      Start_Log = 0;
    }

    if ( ChatMessage.Messages_Displayed < 200 && Log_Length > 20 )
    {
      ChatMessage.Temporary_Chat = `
        <div style="width: 100%; text-align: center; padding-bottom: 5px;">
          <a href="javascript:void(0);" onclick="Absolute.requestMore(); return false;">
            Go Back Further
          </a>
        </div>
      `;
    }

    for ( let i = Start_Log; i <= Log_Length; ++i )
    {
      ChatMessage.render( ChatMessage.Messages[i] );
    }

    ChatMessage.finishRendering();

    $('#AbsoChat').find('#chatContent').first().html(ChatMessage.Temporary_Chat);
    $('#chatContent').scrollTop( $('#chatContent')[0].scrollHeight );
  },

  render: function(message)
  {
    // Get the time the message was sent on; the text of the message; and the user ID.
    let time = new Date(message.timestamp);
    let text = message.text.split(',');
    let userID = null;

    // Render the user's message(s).
    for (var u in message.users)
    {
      userID = message.users[u].userID;

      if (typeof message.users[u].display !== "undefined")
      {
        if (typeof message.text[message.users[u].display] === "undefined")
        {
          text[message.users[u].display] = "%user#" + u + "#" + message.users[u].nick + "%";
        }
        else
        {
          text[message.users[u].display] = "%user#"+u+"#"+message.users[u].nick+"%"+message.text[message.users[u].display];
        }
      }
    }

    // Get the time the message was sent.
    if (time.getHours() == 12)
    {
      AMPM = 'pm';
      hr = time.getHours();
    }
    else if (time.getHours() > 12)
    {
      AMPM = 'pm';
      hr = time.getHours()-12;
    }
    else
    {
      AMPM = 'am';
      hr =  time.getHours();
    }

    if ( typeof message.info.command != "undefined" )
    {
      ChatMessage.Temporary_Chat +=
      `
        <div class="message">` + 
          `<div class='username'>` +  ChatMessage.user(message.users[0]) + `</div>` +
          `<span class="time">`+ (("0" + hr).slice(-2) + `:` +
            ("0" + time.getMinutes()).slice(-2) + `:` +
            ("0" + time.getSeconds()).slice(-2)) + ` ` + AMPM +
          `</span>
          <div style="clear: right;">` +
            text +
          `</div>
        </div>
      `;
    }
    else
    {
      let banLink1 = '';
      let banLink2 = '';

      if ( Absolute.isStaff )
      {
        banLink1 = '<span onclick="AbsoChat.staff.quickBan(\''+userID+'\')">';
        banLink2 = '</span>';
      }

      ChatMessage.Temporary_Chat +=
      `
        <div class="message">` + 
          `<div class='username'>` +  ChatMessage.user(message.users[0]) + `</div>` +
          `<span class="time">` +
            banLink1 +
            (("0" + hr).slice(-2) + `:` +
            ("0" + time.getMinutes()).slice(-2) + `:` +
            ("0" + time.getSeconds()).slice(-2)) + ` ` + AMPM +
            banLink2 +
          `</span>
          <div style="clear: right;">` +
            text +
          `</div>
        </div>
      `;
    }
  },

  finishRendering: function()
  {
    if ( ChatMessage.Temporary_Chat == '' )
    {
      ChatMessage.Temporary_Chat = `
        <table style="width: 100%; height: 100%;">
          <tr>
            <td style="width:100%; height:100%;" valign="middle">
              <b style="color: #ff0000; font-size: 14px;">Absolute Chat is offline.</b>
              <br /><br />
              Absol is currently not running.
            </td>
          </tr>
        </table>
      `;
    }
  },

  user: function(user)
  {
    if (typeof user.image !== "undefined" && user.image != '')
    {
      image = '<div class="userSprite"> <img src="'+user.image+'"> </div>';
    }
    else
    {
      image = '';
    }

    if ( user.rank == '' )
    {
      user.rank = 'member';
    }

    if ( user.userID != undefined )
    {
      var Link = image+' <a class="userLink" id="'+user.rank+'" href="javascript:void(0);" onclick="Absolute.userinfo(\''+user.userID+'\'); return false;">'+user.nick+'</a>';
    }
    else
    {
      var Link = image+' <a class="userLink" href="javascript:void(0);" id="'+user.rank+'">'+user.nick+'</a>';
    }

    return Link;
  },
};