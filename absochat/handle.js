/*!
 * Absolute.js (c) B0sh 2015-2018
 */

var socket;
Absolute = {
  version: '2.2',
  lastupdated: '12/8/2018',
  active:'',
  isStaff: false,
  window: 'chat',
  user: {},
  socket: null,
  failed_connects: 0,

  isConnected: function () {
    return socket.connected;
  },

  // error out the textbox if you aren't connected to chaterpie
  setTextarea: function () {
      if (this.isConnected()) {
        $('#messageChatbox').css('background', '')
      } else {
        $('#messageChatbox').css('background', '#f29e54')
      }
  },

  // reterieve a new post code and reconnect to scyther
  reconnect: function () {

    // ensure you already aren't connected
    if (this.isConnected())
      return;
    // console.log("RECONNECT");

    this.failed_connects++;
    
    if (this.failed_connects > 10) {
      console.log("failed connect");
      return;
    } 

    Absolute.socket.connect();
  },

  enable: function() {
    Absolute.active = true;
    $('#ChaterpieContainer').css('display', 'block');

    // If the Socket is not created We need to set all the commands
    if (typeof socket != "object") {
      ChatMessage.MSGS = new Array();

      socket = io('https://tpkrpg.net:' + CHATERPIE_PORT, {
        // reconnection: true,
        secure: true
      });

      socket.on("connect", function () {
        // console.log("connected to chaterpie");
        // Absolute.addErrorMessage('connected to chaterpie.');

        ChatMessage.purge();
        Absolute.setTextarea();
        this.failed_connects = 0;

        socket.emit('auth', {
          user: Absolute.user.user_id,
          postcode: Absolute.user.postcode
        });

      });

      socket.on("disconnect", function() {
        // console.log("disconnected from chaterpie");

        // Absolute.addErrorMessage('You have been disconnected from Absolute. Please refresh the page.');
        Absolute.setTextarea();

        setTimeout(function () {
          Absolute.reconnect();
        }, 2500);
      });

      socket.on("nick-list", function(data) {
        Absolute.NickList = data;
        Absolute.changeWindow('settings');
      });

      socket.on("chaterpie-user-info", function(data) {
        Absolute.UserInfo = data;
        Absolute.changeWindow('userinfo');
      });

      socket.on("irc-message", function(data) {
        ChatMessage.add(data);
      });

      socket.on("irc-kick", function(data) {
        ChatMessage.add(data);

        if (typeof data.users[0].userID !== 'undefined' && data.users[0].userID == Absolute.user.user_id) {
          Absolute.kickinfo = data;
          Absolute.changeWindow('kick');
        }
      });

      socket.on("irc-ban", function(data) {
        ChatMessage.add(data);

        if (typeof data.users[0].userID !== 'undefined' && data.users[0].userID == Absolute.user.user_id) {
          Absolute.baninfo = data;
          Absolute.changeWindow('ban');
        }
      });

      socket.on("irc-fail", function(data) {
        if (data == 'auth_fail') {
          Absolute.active = false;
          Absolute.addErrorMessage("An error has occurred. Please refresh the page.");
        }
      });

      this.socket = socket;
    }
  },

  help: function() {
    Absolute.changeWindow('chat');
    socket.emit("input", {
      text: "~scyther list",
      username: Absolute.user_name // Need to authenticate like before
    });
  },

  settings: function() {
    if (Absolute.active != true)
      return;

    if (Absolute.window != 'settings') {
      socket.emit('nicklist', true);
    } else {
      Absolute.changeWindow('chat');
    }
  },

  userinfo: function(userid) {
    if (Absolute.active != true || userid == -1)
      return;

    socket.emit('chaterpie-user-info', userid);
  },

  requestMore: function () {
    ChatMessage.msg_display = parseInt(ChatMessage.msg_display)+30;
    if (ChatMessage.msg_display > 200) {
      ChatMessage.msg_display = 200;
    }

    ChatMessage.MSGS = new Array();
    socket.emit('chaterpie-request-msg', ChatMessage.msg_display);
  },

  addErrorMessage: function(error_message) {
    var msg = ChatMessage.MSGS[ChatMessage.MSGS.length-1] || {};
    if (Absolute.active != true || msg.text == "Scyther & Absolute have been terminated. Please refresh the page.")
      return false;

    ChatMessage.add({
      users: [{nick: "Error", userID: -1, rank: 'admin', image:''}],
      timestamp: Math.floor(Date.now() / 1000),
      info: {},
      id: ChatMessage.MSGS.length,
      text: error_message
    });
  },

  changeWindow: function(window) {
    Absolute.window = window;
    switch(window) {
      case 'settings':
        TEXT = `
            <div class="msg1"><b>Absolute Settings</b><br>
                &nbsp;&nbsp;<a href="/title.php" target="_blank">Change Absolute Icon</a><br>
                &nbsp;&nbsp;<a href="/chaterpie.php" target="_blank">Full Screen Absolute</a><br>
                &nbsp;&nbsp;<a href="/options.php" target="_blank">More Settings</a><br>
            </div>

            <div class="msg0"><b>Chat Information</b><br>
                &nbsp;&nbsp;<a href="/rules.php" target="_blank">Chat Rules</a><br>
                &nbsp;&nbsp;<a href="" onclick="Absolute.help(); return false;">Scyther Chat Commands</a>
            </div>
        `;

        var nicks = Absolute.NickList[0],
          RPGOnline = [];
        for (var prop in nicks) {
          if (nicks.hasOwnProperty(prop)) {
            RPGOnline.push( nicks[prop] );
          }
        }

        RPGOnline.sort(function(a, b){
          if (a.user_name.toLowerCase() < b.user_name.toLowerCase())
            return -1;
          if (a.user_name.toLowerCase() > b.user_name.toLowerCase())
            return 1;
          return 0;
        });

        var nicks = Absolute.NickList[1];

        TEXT += '<div class="msg1"><b>Online Users:</b> ('+RPGOnline.length+')<br>';
        for (var prop in RPGOnline) {
          if (RPGOnline.hasOwnProperty(prop)) {
            u = RPGOnline[prop];
            TEXT += '&nbsp;&nbsp;<a href="javascript:void;" onclick="Absolute.userinfo(\''+u.user_id+'\'); return false;">'+u.user_name+'</a><br>';
          }
        }

        TEXT += '</div>';

        TEXT += '<div class="msg0">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Absolute is TPK\'s in-game chat. <a href="'+DOMAIN+'irc.php">More information</a>.<br><br>';

        TEXT += '<div class="msg1"><div class="userSprite" style="margin:-5px;"> <img src="http://sprites.tpkrpg.net/pokemon/icons/normal/010.png">  </div>Scyther & Absolute<br>&nbsp;&nbsp;&nbsp;&nbsp;&copy; <b>B0sh</b> 2015-2018 </div>';

        TEXT += ' </div> ';

        $('.chatWindow').html(TEXT).scrollTop(0);

        break;

    case 'userinfo':
        var u =Absolute.UserInfo.user;

        if (u.lastactive < 60)
          lastactive = u.lastactive + " Second(s) Ago";
        else if (u.lastactive < 3600)
          lastactive = Math.floor(u.lastactive/60) + " Minute(s) Ago";
        else
          lastactive = Math.floor(u.lastactive/3600) + " Hour(s) Ago";

        TEXT = '<div class="msg0" style="min-height:95%;"><div style="width:95%;text-align:center;"><a class="userLink" id="'+u.rank+'" href="'+DOMAIN+'user/'+u.userID+'">'+u.username+'</a> (ID #'+u.userID+')<br>';

        if (u.position == 'Bot') {
          TEXT += '<img src="http://sprites.tpkrpg.net/avatars/'+u.avatar+'.png" style="margin:0 auto;"><br><br><a href="" onclick="Absolute.changeWindow(\'chat\'); return false;"> Return To Chat </a>';
          TEXT += '<div style="width:100%;text-align:left;padding:5px;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Scyther is the official TPK Bot. Scyther runs Absolute, and has many useful commands. All Scyther commands begin with an exclamation mark (Example: !level). Using commands is as simple as typing them in Absolute.</div><br><div style="width:100%;text-align:left;padding:5px;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;For a list of commands use "!scyther list". You can find out help information about all commands by typing "!scyther [CommandName]".';

        } else {
          if (u.position != "Member") {
            TEXT += '<br><b id="'+u.rank+'">'+u.position+'</b><br><br>';
          }
          TEXT += '<img src="http://sprites.tpkrpg.net/avatars/'+u.avatar+'.png" style="margin:0 auto;"><br><b>Last Active:</b><Br>'+lastactive+'<br>';

          TEXT += '<br><div style="width:100%;text-align:center;"><a href="" onclick="Absolute.changeWindow(\'chat\'); return false;"> Return To Chat </a></div><br><b>Interact</b></div>';

          TEXT += '&nbsp;&nbsp;<a target="_blank" href="'+DOMAIN+'user/'+u.userID+'">View Profile</a><br>';

          TEXT += '&nbsp;&nbsp;<a target="_blank" href="'+DOMAIN+'private_chat.php?d=Start&user_id='+u.userID+'">Message '+u.username+'</a><br>';
          TEXT += '&nbsp;&nbsp;<a target="_blank" href="'+DOMAIN+'trade_create.php?UserID='+u.userID+'">Trade with '+u.username+'</a><br>';
          TEXT += '&nbsp;&nbsp;<a target="_blank" href="'+DOMAIN+'friends.php?userID='+u.userID+'">Friend '+u.username+'</a><br>';
          TEXT += '&nbsp;&nbsp;<a target="_blank" href="'+DOMAIN+'battle_create.php?Battle=Trainer&Trainer='+u.userID+'">Battle '+u.username+'</a><br><br>';

          if (Absolute.isStaff == true) {
            TEXT += '&nbsp;&nbsp;<a href="javascript:void" onclick="Absolute.staff.kick(\''+u.userID+'\');">Kick '+u.username+'</a><br>';
            TEXT += '&nbsp;&nbsp;<a href="javascript:void" onclick="Absolute.staff.ban(\''+u.userID+'\');">Ban '+u.username+'</a><br><i>Textbox is reason</i><br><br>';
            TEXT += '&nbsp;&nbsp;<b>Ban Length (sec.):</b><br><input type="text" size="6" id="messageBanTimeStaff" value="300"><br><br>';
          }
        }
        TEXT += "</div>";
        $('.chatWindow').html(TEXT).scrollTop(0);
        break;
      // Kick screen. Has a rejoin button
      case 'kick':
        reason = Absolute.kickinfo;

        TEXT = '<table style="width:100%;height:100%;"><tr><td style="width:100%; height:100%;" valign="middle"><b class="error_text" style="font-size:14px;">You have been kicked from the chatroom.</b><br><br><b>Kicked By:</b><br>'+reason.users[1].nick+'<br><br><b>Reason:</b><br>'+reason.info.reason+' <br><br> <a href="javascript:void" onclick="Absolute.changeWindow(\'chat\'); ">Rejoin Chat</a></td></tr></table>'

        $('.chatWindow').html(TEXT).scrollTop(0);
      // Ban screen. Displays Time remaining
      case 'ban':
        reason = Absolute.baninfo;
        if (reason.info.banlength > 3600) {
          t = Math.round(reason.info.banlength/360)/10 + " hours"
        } else if (reason.info.banlength > 60) {
          t = Math.round(reason.info.banlength/6)/10 + " minutes"
        } else {
          t = reason.info.banlength + " seconds"
        }

        TEXT = '<table style="width:100%;height:100%;"><tr><td style="width:100%; height:100%;" valign="middle"><b class="error_text" style="font-size:14px;">You have been banned from the chatroom.</b><br><br><b>Banned By:</b><br>'+reason.users[1].nick+'<br><br><b>Reason:</b><br>'+reason.info.reason+' <br><br> Your ban will last '+t+'.</td></tr></table>'

        $('.chatWindow').html(TEXT).scrollTop(0);
      // Chat Window has another display function/
      case 'chat':
        ChatMessage.display();
        jumpToPageBottom();
        break;
    }
  },

};

function isAtBottom() {
    return ($('.chatWindow').scrollTop() + $('.chatWindow').height() + 15 >= $('.chatWindow')[0].scrollHeight);
}

function jumpToPageBottom() {
  $('.chatWindow').scrollTop($('.chatWindow')[0].scrollHeight); //Scrolls the chat back down to the bottom
}

ChatMessage = {
  MSGS: new Array(),
  temp_chat:'',
  msg_display: 30,

  purge: function () {
    ChatMessage.MSGS = [];
  },
  
  add: function(msg) {
    if (Absolute.active == true) {
      if (msg.text == "Scyther & Absolute have been terminated. Please refresh the page.") {
        ChatMessage.add({
          users: [{nick: "Error", userID: -1, rank: 'admin', image:''}],
          timestamp: Math.floor(Date.now() / 1000),
          info: {},
          id: ChatMessage.MSGS.length,
          text: "Absolute has stopped. Please refresh the page."
        });
        return;
      }

      if (msg.users[0].clear == true) {
        ChatMessage.MSGS = new Array();
      }

      ChatMessage.MSGS.push( msg );
      ChatMessage.display();
    }
  },

  display: function() {
    ChatMessage.renderBackgroundID = 0;

    max = ChatMessage.msg_display;
    wasatbottom = isAtBottom();

    if (Absolute.window != 'chat')
      return false;

    ChatMessage.temp_chat = '';
    var len = ChatMessage.MSGS.length-1;

    if (len > max)
      start = len - max;
    else  start = 0;

    if (ChatMessage.msg_display < 200 && len > 20) {
      ChatMessage.temp_chat = '<div style="width:100%;text-align:center;padding-bottom:5px;"> <a href="javascript:void" onclick="Absolute.requestMore(); return false;">Go Back Further</a> </div>';
    }

    // removed blocked users messages
    for (var i = start; i <= len; ++i) {
      if (Absolute.user.block_string.indexOf(ChatMessage.MSGS[i].users[0].userID) === -1)
        ChatMessage.render(ChatMessage.MSGS[i]);
    //  else
      //  console.log("BLOCKED");
    }

    ChatMessage.finishRendering();


    switch (Absolute.user.chat_size+'') {
        // normal side chaterpie
        case '0': s= 'height:300px';break;
        // extended chaterpie
        case '1': s= 'height:425px';break;
        // Mobile mode
        case '2':
            w = $(window).width();
            if (w > 600) w = 600;
            s= 'height:'+($(window).height()-170)+'px;width:'+(w)+'px'; break;
        case '3':
            w = 250
            h = 300
            s= 'height:'+h+'px;width:'+(w)+'px'; break;
        // full screen chaterpie calculation
        case '4':
            w = $(window).width();
            h = $(window).height()-350;
            if (w > 600) w = 600;
            if (h > 650) h = 650;
            if (h < 400) h = 400;
            s= 'height:' + h + 'px;width:' + w + 'px';
            break;
    }


    var tempScrollTop = $('.chatWindow').scrollTop();
    $('#ChaterpieContainer').find('.Status').first().html('<div class="chatWindow" style="'+s+';">' + (ChatMessage.temp_chat) + "</div>");
    $('.chatWindow').perfectScrollbar({
      useKeyboard: false
    });

    if (wasatbottom)
      jumpToPageBottom();
    else
      $('.chatWindow').scrollTop(tempScrollTop);

    $('.chatWindow').perfectScrollbar('update');
  },

  finishRendering: function() {
    if (ChatMessage.temp_chat == '') {
      ChatMessage.temp_chat = '<table style="width:100%;height:100%;"><tr><td style="width:100%; height:100%;" valign="middle"><b class="error_text" style="font-size:14px;">Absolute is offline.</b><br><br>Scyther is currently not running.</td></tr></table>';
    }
  },

  render:function (msg) {
    var time = new Date(msg.timestamp),
        text = msg.text.split(''),
        userID = null;

    for (var u in msg.users) {
      userID = msg.users[u].userID;
      if (typeof msg.users[u].display !== "undefined") {
        if (typeof msg.text[msg.users[u].display] === "undefined")
          text[msg.users[u].display] = "%user#"+u+"#"+msg.users[u].nick+"%";
        else
          text[msg.users[u].display] = "%user#"+u+"#"+msg.users[u].nick+"%"+msg.text[msg.users[u].display];
      }
    }

    if (time.getHours() == 12) {
      AMPM = 'pm';
      hr = time.getHours();
    } else if (time.getHours() > 12) {
      AMPM = 'pm';
      hr = time.getHours()-12;
    } else {
      AMPM = 'am';
      hr =  time.getHours();
    }

    text = text.join('');
    style=""
    if (msg.info.background !== "undefined")
      style = ' style="background:'+msg.info.background+';"';


    ChatMessage.renderBackgroundID = ChatMessage.renderBackgroundID + 1;

    if (Absolute.user.auto_caps == "yes") {
      text = text.autocapsify();
    }

    if (typeof msg.info.command !== "undefined") {
      ChatMessage.temp_chat +=
        '<div class="msg'+(ChatMessage.renderBackgroundID%2)+'"'+style+'> <span class="time">'+ (("0" + hr).slice(-2)+":" +
("0" + time.getMinutes()).slice(-2) + ":" +
("0" + time.getSeconds()).slice(-2))+' '+AMPM+'</span><div style="clear:right;text-indent:10px;font-style: italic;">'+
          text.destroyHTML().linkify().emotify().userify(msg).highlightify()
        +'</div></div>';
    } else {
      var banLink1 = '';
      var banLink2 = '';
      if (userID && userID != 3 && Absolute.isStaff) {
        banLink1 = '<span onclick="Absolute.staff.quickBan(\''+userID+'\')">';
        banLink2 = '</span>';
      }

      ChatMessage.temp_chat +=
        '<div class="msg'+(ChatMessage.renderBackgroundID%2)+'"'+style+'>'+ChatMessage.user(msg.users[0])+' <span class="time">'+banLink1+(("0" + hr).slice(-2)   + ":" +
("0" + time.getMinutes()).slice(-2) + ":" +
("0" + time.getSeconds()).slice(-2))+' '+AMPM+banLink2+'</span><div style="clear:right;text-indent:10px;">'+
          text.destroyHTML().linkify().emotify().highlightify()
        +'</div></div>';
    }
  },

  //Renders the user link/ whatever
  user:function (user) {
    if (typeof user.image !== "undefined" && user.image != '') {
      image = '<div class="userSprite"> <img src="'+user.image+'"> </div>';
    } else image = '';

    if (user.rank == '') user.rank = 'member';

    if (user.userID != undefined)
      var Link = image+' <a class="userLink" id="'+user.rank+'" href="javascript:void;" onclick="Absolute.userinfo(\''+user.userID+'\'); return false;">'+user.nick+'</a>';
    else
      var Link = image+' <a class="userLink" href="javascript:void;" id="'+user.rank+'">'+user.nick+'</a>';

    return Link;
  },

}

function sendEmote(e) {
  $('#messageChatbox').val($('#messageChatbox').val() + e).keyup()
  return false;
}

function isChat() {
  return $('#messageChatbox').is(':focus');
}

String.prototype.autocapsify = function () {
  // capatilize first letter
  return this.charAt(0).toUpperCase() + this.slice(1);
};

String.prototype.linkify = function() {
  return Autolinker.link( this, {newWindow: true, twitter: false, truncate: {length: 51, location: 'end'}} );
};

String.prototype.emotify = function() {
  function replaceEmoticons(text) {
    var emoticons = {
      ':010:' : ['40', '30', 'http://sprites.tpkrpg.net/pokemon/icons/normal/010.png'],
      ':059:' : ['40', '30', 'http://sprites.tpkrpg.net/pokemon/icons/normal/059.png'],
      ':069:' : ['40', '30', 'http://sprites.tpkrpg.net/pokemon/icons/normal/069.png'],
      ':134:' : ['40', '30', 'http://sprites.tpkrpg.net/pokemon/icons/normal/134.png'],
      ':147:' : ['40', '30', 'http://sprites.tpkrpg.net/pokemon/icons/normal/147.png'],
      ':158:' : ['40', '30', 'http://sprites.tpkrpg.net/pokemon/icons/normal/158.png'],
      ':172:' : ['40', '30', 'http://sprites.tpkrpg.net/pokemon/icons/normal/172.png'],
      ':214:' : ['40', '30', 'http://sprites.tpkrpg.net/pokemon/icons/normal/214.png'],
      ':246:' : ['40', '30', 'http://sprites.tpkrpg.net/pokemon/icons/normal/246.png'],
      ':249:' : ['40', '30', 'http://sprites.tpkrpg.net/pokemon/icons/normal/249.png'],
      ':251:' : ['40', '30', 'http://sprites.tpkrpg.net/pokemon/icons/normal/251.png'],
      ':349:' : ['40', '30', 'http://sprites.tpkrpg.net/pokemon/icons/normal/349.png'],
      ':371:' : ['40', '30', 'http://sprites.tpkrpg.net/pokemon/icons/normal/371.png'],
      ':381:' : ['40', '30', 'http://sprites.tpkrpg.net/pokemon/icons/normal/381.png'],
      ':403:' : ['40', '30', 'http://sprites.tpkrpg.net/pokemon/icons/normal/403.png'],
      ':681:' : ['40', '30', 'http://sprites.tpkrpg.net/pokemon/icons/normal/681.png'],
      ':800:' : ['40', '30', 'http://sprites.tpkrpg.net/pokemon/icons/normal/800.png'],
    }, url = '', patterns = [],
     metachars = /[[\]{}()*+?.\\|^$\-,&#\s]/g;

    // build a regex pattern for each defined property
    for (var i in emoticons) {
      if (emoticons.hasOwnProperty(i)){ // escape metacharacters
        patterns.push('('+i.replace(metachars, "\\$&")+')');
      }
    }

    // build the regular expression and replace
    var imageCount = 0;
    return text.replace(new RegExp(patterns.join('|'),'g'), function (match) {
      imageCount++;
    if (imageCount <= 5) {
      return typeof emoticons[match] != 'undefined' ?
         '<div class="emoticon" style="width:'+emoticons[match][0]+'px;height:'+emoticons[match][1]+'px; display: inline-flex; justify-content: center; align-items: center"><img src="'+url+emoticons[match][2]+'"/></div>' :
         match;
    } else {
      return '';
    }
    });
  }

  return replaceEmoticons(this);
}

String.prototype.destroyHTML = function() {
  var map = {
    '&': '&amp;',
    '<': '&lt;',
    '>': '&gt;',
    '"': '&quot;',
    "'": '&#039;'
  };

  return this
    .replace(/[&<>"']/g, function(m) { return map[m]; });
}

String.prototype.highlightify = function() {
  function replaceEmoticons(text) {
    var emoticons = new Array(),
      url = DOMAIN,
      patterns = [],
     metachars = /[[\]{}()*+?.\\|^$\-,&#\s]/g;
      emoticons[Absolute.user.user_name] = '';

    // build a regex pattern for each defined property
    for (var i in emoticons) {
    if (emoticons.hasOwnProperty(i)){ // escape metacharacters
      patterns.push('('+i.replace(metachars, "\\$&")+')');
    }
    }

    // build the regular expression and replace
    return text.replace(new RegExp(patterns.join('|'),'g'), function (match) {
    return typeof emoticons[match] != 'undefined' ?
         '<span class="highlight">'+match+'</span>' :
         match;
    });
  }

  return replaceEmoticons(this);
}

String.prototype.userify = function(msg) {
  var text = this .split('%');

  var edited = '',last='';
  for(var i in text) {
    if (text.hasOwnProperty(i)) {
      var l = text[i].split('#');

      if (l.length == 3 && l[0] == 'user' && l[1].match(/^[0-9]+$/) != null && msg.users[l[1]].nick == l[2]) {
        edited += ChatMessage.user(msg.users[l[1]]);
        last = 'user'
      } else if (last == 'user' || i == 0) {
        last = 'no';
        edited += text[i];
      } else {
        edited += '%' + text[i];
      }
    }
  }

  return edited;
}