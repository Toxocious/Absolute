var socket;

AbsoChat = {
  updatedOn: '3/25/2018',
  version: '1.0',
  window: 'chat',
  port: '3000',
  active: '',
  staff: false,
  user: {},

  // AbsoChat has been enabled.
  enable: function()
  {
    AbsoChat.active = true;
    $('#AbsoChat').css('display', 'block');

    // Create the socket commands.
    if (typeof socket != "object") {
      ChatMessage.Messages = new Array();

      socket = io('localhost:3000', {
        reconnection: true,
        secure: true
      });

      socket.emit('auth', {
        user: AbsoChat.user.user_id,
        postcode: AbsoChat.user.postcode
      });

      socket.on("disconnect", function() {
        AbsoChat.active = false;
        AbsoChat.addErrorMessage('You have been disconnected from AbsoChat. Please refresh the page.');
      });

      socket.on("nick-list", function(data) {
        AbsoChat.NickList = data;
        AbsoChat.changeWindow('settings');
      });

      socket.on("absochat-user-info", function(data) {
        AbsoChat.UserInfo = data;
        AbsoChat.changeWindow('userinfo');
      });
    }
  },

  disable: function()
  {
    AbsoChat.active = true;
    $('#AbsoChat').css('display', 'none');
  },

  changeWindow: function(window)
  {
    AbsoChat.window = window;

    switch(window)
    {
      case 'settings':
        text =  '<div class="msg1">';
        text +=   '<b>AbsoChat Settings</b>';
        text +=   '<hr />';
        text +=   '&nbsp;&nbsp;<a href="/options.php" target="_blank">AbsoChat Settings</a><br />';
        text +=   '<a href="javascript:void(0);" onclick="AbsoChat.help(); return false;" id="chat_length_toggle">Absol Chat Commands</a><br />';
        text +=   '<a href="/title.php" target="_blank">Change AbsoChat Icon</a><br />';
        text +=   '<a href="javascript:void(0);" onclick="AbsoChat.changeWindow(\'chat\');">Return To Chat</a>';
        text += '</div>';

        var nicks = RPGOnline = [];
        
        for ( var prop in nicks )
        {
          if ( nicks.hasOwnProperty(prop) )
          {
            RPGOnline.push( nicks[prop] );
          }
        }

        RPGOnline.sort(function(a, b)
        {
          if (a.user_name.toLowerCase() < b.user_name.toLowerCase())
            return -1;
          if (a.user_name.toLowerCase() > b.user_name.toLowerCase())
            return 1;
          return 0;
        });

        text += '<div style="margin-top: 95px;"><B>Users Online:</b> ' + RPGOnline.length + '<br />';
        for (var prop in RPGOnline) {
          if (RPGOnline.hasOwnProperty(prop)) {
            u = RPGOnline[prop];
            text += '&nbsp;&nbsp;<a href="javascript:void(0);" onclick="AbsoChat.userinfo(\''+u.user_id+'\'); return false;">'+u.user_name+'</a><br />';
          }
        }

        text += '</div>';

        text += '<div class="message0" style="margin-top: 0px; padding: 5px;">';
        text +=   '<div class="userSprite" style="margin: 5px 5px 0px 0px;">';
        text +=     '<img src="images/Icons/359M.png">';
        text +=   '</div>';
        text +=   '<div style="margin-top: 5px;">';
        text +=     '<b>Absol & AbsoChat</b><br />';
        text +=     '&copy; <b>Toxocious</b><br />';
        text +=   '</div>';
        text += '</div>';

        $('#chatContent').html(text).scrollTop(0);

        break;

      case 'userinfo':
        var thisUser = AbsoChat.userinfo.user;

        text  = 'User Info';
        text += '<a class="userLink" id="' + thisUser.rank + '" href="javascript:void(0);">' + thisUser.username + '- #' + thisUser.user_id + '</a><br />';
        
        if ( AbsoChat.staff == true )
        {
          text += '&nbsp;&nbsp;<a href="javascript:void" onclick="AbsoChat.staff.kick(\''+thisUser.userID+'\');">Kick '+thisUser.username+'</a><br>';
          text += '&nbsp;&nbsp;<a href="javascript:void" onclick="AbsoChat.staff.ban(\''+thisUser.userID+'\');">Ban '+thisUser.username+'</a><br><i>Textbox is reason</i><br><br>';
          text += '&nbsp;&nbsp;<b>Ban Length (sec.):</b><br><input type="text" size="6" id="messageBanTimeStaff" value="300"><br><br>';
        }
        
        text += '<br /><br />';
        text += '<a href="javascript:void(0);" onclick="AbsoChat.changeWindow(\'chat\');">Return To Chat</a>';

        $('#chatContent').html(text).scrollTop(0);

        break;

      case 'ban':
        text = 'Ban Screen';

        $('#chatContent').html(text).scrollTop(0);

        break;

      case 'chat':
        ChatMessage.display();

        break;
    }
  },

  help: function()
  {
    AbsoChat.changeWindow('chat');
    socket.emit("input", {
      text: "~list",
      username: AbsoChat.user.username
    });
  },

  settings: function()
  {
    if (AbsoChat.active != true)
    {
      return;
    }

    if (AbsoChat.window != 'settings')
    {
      socket.emit('nicklist', true);
    }
    else
    {
      AbsoChat.changeWindow('chat');
    }
  },

  userinfo: function(userid) {
    if (AbsoChat.active != true || userid == -1)
      return;

    socket.emit('AbsoChat-user-info', userid);
  },

  reset: function()
  {
    ChatMessage.add({
      users: [{nick: "Absol", userID: 3, rank: 'admin', image: 'images/Icons/359M.png'}],
      timestamp: Math.floor(Date.now() / 1000),
      info: {},
      id: ChatMessage.Messages.length,
      text: "The chat has been cleared."
    });
  },

  addErrorMessage: function(error_message)
  {
    if (AbsoChat.active != true || ChatMessage.Messages.text == "AbsoChat has been terminated. Please refresh the page.")
    {
      return false;
    }

    ChatMessage.add({
      users: [{nick: "Absol", userID: 3, rank: 'admin', image: 'images/Icons/359M.png'}],
      timestamp: Math.floor(Date.now() / 1000),
      info: {},
      id: ChatMessage.Messages.length,
      text: error_message
    });
  },
};

ChatMessage = {
  Messages: new Array(),
  Message_Limit: 30,
  Temp_Display: '',

  add: function(message)
  {
    if (AbsoChat.active == true)
    {
      if (message.text == "AbsoChat has been terminated. Please refresh the page.")
      {
        ChatMessage.add({
          users: [{nick: "Absol", userID: 3, rank: 'admin', image: 'images/Icons/359M.png'}],
          timestamp: Math.floor(Date.now() / 1000),
          info: {},
          id: ChatMessage.messages.length,
          text: "AbsoChat has stopped. Please refresh the page."
        });
        return;
      }

      if (message.users[0].clear == true)
      {
        ChatMessage.Messages = new Array();
      }

      ChatMessage.Messages.push(message);
      ChatMessage.display();
    }
  },

  display: function()
  {
    ChatMessage.renderBackgroundID = 0;

    MaxMessages = ChatMessage.Message_Limit;
    WasAtBottom = isAtBottom();

    if (AbsoChat.window != 'chat')
    {
      return false;
    }

    ChatMessage.Temp_Display = '';
    var len = ChatMessage.Messages.length - 1;

    if (len > MaxMessages)
    {
      start = len - MaxMessages;
    }
    else
    {
      start = 0;
    }

    for (var i = start; i <= len; ++i)
    { 
      if (AbsoChat.user.block_string.indexOf(ChatMessage.Messages[i].users[0].userID) === -1)
      {
        ChatMessage.render(ChatMessage.Messages[i]);
      }
    }

    ChatMessage.finishRendering();

    var tempScrollTop = $('#chatContent').scrollTop();
    $('#chatContent').html('<div class="chatWindow">' + ChatMessage.Temp_Display + "</div>");
    $('#chatContent').perfectScrollbar({
      useKeyboard: false
    });

    if (WasAtBottom)
    {
      jumpToPageBottom();
    }
    else
    {
      $('#chatContent').scrollTop(tempScrollTop);
    }

    $('#chatContent').perfectScrollbar('update');
  },

  finishRendering: function()
  {
    if ( ChatMessage.Temp_Display == '' )
    {
      ChatMessage.Temp_Display =  '<b>';
      ChatMessage.Temp_Display += '<div style="color: #ff0000; margin-top: 90px;">AbsoChat is offline.</div>';
      ChatMessage.Temp_Display += '<div style="margin-top: 30px;">Absol is currently sleeping.</div>';
      ChatMessage.Temp_Display += '</b>';
    }
  },

  render: function(message)
  {
    var time = new Date(message.timestamp),
        text = message.text.split(''),
        userID = null;

    for (var u in message.users)
    {
      userID = message.users[u].userID;
      if (typeof message.users[u].display !== "undefined")
      {
        if (typeof message.text[message.users[u].display] === "undefined")
        {
          text[message.users[u].display] = "%user#"+u+"#"+message.users[u].nick+"%";
        }
        else
        {
          text[message.users[u].display] = "%user#"+u+"#"+message.users[u].nick+"%"+message.text[message.users[u].display];
        }
      }
    }

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

    text = text.join('');
    style = "";

    ChatMessage.renderBackgroundID = ChatMessage.renderBackgroundID + 1;

    if (typeof message.info.command !== "undefined")
    {
      ChatMessage.Temp_Display +=
        '<div class="message' + (ChatMessage.renderBackgroundID % 2 ) + '"' + style + '>' + 
        '<div style="font-style: italic;">' +
        text +
        '</div></div>';
    }
    else
    {
      var banLink1 = '';
      var banLink2 = '';
      if ( userID < 4 || AbsoChat.staff )
      {
        banLink1 = '<span onclick="AbsoChat.staff.quickBan(\''+userID+'\')">';
        banLink2 = '</span>';
      }

      ChatMessage.Temp_Display +=
        '<div class="message' + (ChatMessage.renderBackgroundID % 2) + ' " ' + style + '>' + ChatMessage.user(message.users[0]) +
        '<div>' +
        text +
        '</div></div>';
    }
  },

  user: function(user) {
    if (typeof user.image !== "undefined" && user.image != '')
    {
      image = '<div class="userSprite"> <img src="'+user.image+'"> </div>';
    }
    else
    {
      image = '';
    }

    if (user.rank == '')
    {
      user.rank = 'member';
    } 

    if (user.userID != undefined)
    {
      var Link = image + ' <a class="userLink" id="'+user.rank+'" href="javascript:void(0);" onclick="AbsoChat.changeWindow(\'userinfo\', \''+user.userID+'\'); return false;">'+user.nick+'</a>';
    }
    else
    {
      var Link = image + ' <a class="userLink" href="javascript:void(0);" id="'+user.rank+'">'+user.nick+'</a>';
    }

    return Link;
  },
};

function isAtBottom()
{
  return ( $('#chatContent').scrollTop() + $('#chatContent').height() + 15 >= $('#chatContent')[0].scrollHeight );
}

function jumpToPageBottom()
{
  $('#chatContent').scrollTop( $('#chatContent')[0].scrollHeight );
}

function isChat()
{
  return $('#chatMessage').is(':focus');
}