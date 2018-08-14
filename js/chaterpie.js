/*!
 * AbsoChat.js (c) B0sh 2015-2017
 */

var socket;
AbsoChat = {
  version: '2.0',
  lastupdated: '2/1/2017',
  active:'',
  isStaff: false,
  window: 'chat',
  user: {},

  enable: function() {
    AbsoChat.active = true;
    $('#AbsoChat').css('display', 'block');

    // If the Socket is not created We need to set all the commands
    if (typeof socket != "object") {
      ChatMessage.MSGS = new Array();

      socket = io('localhost:3000', {
        reconnection: true,
        secure: true
      });

      socket.emit('auth', {
        user: AbsoChat.user.user_id,
        postcode: AbsoChat.user.postcode
      });

      socket.on("disconnect", function() {
        // AbsoChat.active = false;
        AbsoChat.addErrorMessage('You have been disconnected from AbsoChat. Please refresh the page.');
      });

      socket.on("nick-list", function(data) {
        AbsoChat.NickList = data;
        AbsoChat.changeWindow('settings');
      });
      socket.on("AbsoChat-user-info", function(data) {
        AbsoChat.UserInfo = data;
        AbsoChat.changeWindow('userinfo');
      });
      socket.on("irc-message", function(data) {
        ChatMessage.add(data);
      });
      socket.on("irc-kick", function(data) {
        ChatMessage.add(data);

        if (typeof data.users[0].userID !== 'undefined' && data.users[0].userID == AbsoChat.user.user_id && AbsoChat.user.user_id != 2460) {
          AbsoChat.kickinfo = data;
          AbsoChat.changeWindow('kick');
        }
      });
      socket.on("irc-ban", function(data) {
        ChatMessage.add(data);

        if (typeof data.users[0].userID !== 'undefined' && data.users[0].userID == AbsoChat.user.user_id) {
          AbsoChat.baninfo = data;
          AbsoChat.changeWindow('ban');
        }
      });
      socket.on("irc-fail", function(data) {
        if (data == 'auth_fail') {
          AbsoChat.active = false;
          AbsoChat.addErrorMessage("An error has occurred. Please refresh the page.");
        }
      });
    }
  },

  changeWindow: function(window) {
    AbsoChat.window = window;
    switch(window) {
      /* Settings Window, can probably be compacted */
      case 'settings':
        TEXT = '<div class="msg1"><b>AbsoChat Settings</b><br>&nbsp;&nbsp;<a href="/options.php" target="_blank">AbsoChat Settings</a>';
        TEXT += '<br>&nbsp;&nbsp;<a href="" onclick="AbsoChat.help(); return false;" id="chat_length_toggle">Scyther Chat Commands</a>';
        TEXT += '<br>&nbsp;&nbsp;<a href="/title.php" target="_blank">Change AbsoChat Icon</a>';

        TEXT += '</div>';

        var nicks = AbsoChat.NickList[0],
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

        var nicks = AbsoChat.NickList[1];

        TEXT += '<div class="msg0"><B>Users Online:</b> ('+RPGOnline.length+')<br>';
        for (var prop in RPGOnline) {
          if (RPGOnline.hasOwnProperty(prop)) {
            u = RPGOnline[prop];
            TEXT += '&nbsp;&nbsp;<a href="javascript:void;" onclick="AbsoChat.userinfo(\''+u.user_id+'\'); return false;">'+u.user_name+'</a><br>';
          }
        }

        TEXT += '</div>';

        TEXT += '<div class="msg0">AbsoChat is TPK\'s in-game chat. For more information <a href="'+DOMAIN+'irc.php">click</a>.';

        TEXT += '<div class="msg1"><div class="userSprite" style="margin:-5px;"> <img src="http://sprites.tpkrpg.net/pokemon/icons/normal/010.png">  </div> Scyther & AbsoChat<br>&nbsp;&nbsp;&nbsp;&nbsp;&copy; <b>B0sh</b> August 5 2018</div>';

        $('#chatContent').html(TEXT).scrollTop(0);

        break;
      /* User Info */
      case 'userinfo':
        var u =AbsoChat.UserInfo.user;

        if (u.lastactive < 60)
          lastactive = u.lastactive + " Second(s) Ago";
        else if (u.lastactive < 3600)
          lastactive = Math.floor(u.lastactive/60) + " Minute(s) Ago";
        else
          lastactive = Math.floor(u.lastactive/3600) + " Hour(s) Ago";

        TEXT = '<div class="msg0" style="min-height:95%;"><div style="width:95%;text-align:center;"><a class="userLink" id="'+u.rank+'" href="'+DOMAIN+'user/'+u.userID+'">'+u.username+'</a> (ID #'+u.userID+')<br>';

        if (u.position == 'Bot') {
          TEXT += '<img src="http://sprites.tpkrpg.net/avatars/'+u.avatar+'.png" style="margin:0 auto;"><br><br><a href="" onclick="AbsoChat.changeWindow(\'chat\'); return false;"> Return To Chat </a>';
          TEXT += '<div style="width:100%;text-align:left;padding:5px;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Scyther is the official TPK Bot. Scyther runs AbsoChat, and has many useful commands. All Scyther commands begin with an exclamation mark (Example: !level). Using commands is as simple as typing them in AbsoChat.</div><br><div style="width:100%;text-align:left;padding:5px;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;For a list of commands use "!scyther list". You can find out help information about all commands by typing "!scyther [CommandName]".';

        } else {
          if (u.position != "Member") {
            TEXT += '<br><b id="'+u.rank+'">'+u.position+'</b><br><br>';
          }
          TEXT += '<img src="http://sprites.tpkrpg.net/avatars/'+u.avatar+'.png" style="margin:0 auto;"><br><b>Last Active:</b><Br>'+lastactive+'<br>';

          TEXT += '<br><div style="width:100%;text-align:center;"><a href="" onclick="AbsoChat.changeWindow(\'chat\'); jumpToPageBottom(); return false;"> Return To Chat </a></div><br><b>Interact</b></div>';

          TEXT += '&nbsp;&nbsp;<a target="_blank" href="'+DOMAIN+'user/'+u.userID+'">View Profile</a><br>';

          TEXT += '&nbsp;&nbsp;<a target="_blank" href="'+DOMAIN+'private_chat.php?d=Start&user_id='+u.userID+'">Message '+u.username+'</a><br>';
          TEXT += '&nbsp;&nbsp;<a target="_blank" href="'+DOMAIN+'trade_create.php?UserID='+u.userID+'">Trade with '+u.username+'</a><br>';
          TEXT += '&nbsp;&nbsp;<a target="_blank" href="'+DOMAIN+'friends.php?userID='+u.userID+'">Friend '+u.username+'</a><br>';
          TEXT += '&nbsp;&nbsp;<a target="_blank" href="'+DOMAIN+'battle_create.php?Battle=Trainer&Trainer='+u.userID+'">Battle '+u.username+'</a><br><br>';

          if (AbsoChat.isStaff == true) {
            TEXT += '&nbsp;&nbsp;<a href="javascript:void" onclick="AbsoChat.staff.kick(\''+u.userID+'\');">Kick '+u.username+'</a><br>';
            TEXT += '&nbsp;&nbsp;<a href="javascript:void" onclick="AbsoChat.staff.ban(\''+u.userID+'\');">Ban '+u.username+'</a><br><i>Textbox is reason</i><br><br>';
            TEXT += '&nbsp;&nbsp;<b>Ban Length (sec.):</b><br><input type="text" size="6" id="messageBanTimeStaff" value="300"><br><br>';
          }
        }
        TEXT += "</div>";
        $('#chatContent').html(TEXT).scrollTop(0);
        break;
      /* Kick screen. Has a rejoin button */
      case 'kick':
        reason = AbsoChat.kickinfo;

        TEXT = '<table style="width:100%;height:100%;"><tr><td style="width:100%; height:100%;" valign="middle"><b class="error_text" style="font-size:14px;">You have been kicked from the chatroom.</b><br><br><b>Kicked By:</b><br>'+reason.users[1].nick+'<br><br><b>Reason:</b><br>'+reason.info.reason+' <br><br> <a href="javascript:void" onclick="AbsoChat.changeWindow(\'chat\');">Rejoin Chat</a></td></tr></table>'

        $('#chatContent').html(TEXT).scrollTop(0);
      /* Ban screen. Displays Time remaining */
      case 'ban':
        reason = AbsoChat.baninfo;
        if (reason.info.banlength > 3600) {
          t = Math.round(reason.info.banlength/360)/10 + " hours"
        } else if (reason.info.banlength > 60) {
          t = Math.round(reason.info.banlength/6)/10 + " minutes"
        } else {
          t = reason.info.banlength + " seconds"
        }

        TEXT = '<table style="width:100%;height:100%;"><tr><td style="width:100%; height:100%;" valign="middle"><b class="error_text" style="font-size:14px;">You have been banned from the chatroom.</b><br><br><b>Banned By:</b><br>'+reason.users[1].nick+'<br><br><b>Reason:</b><br>'+reason.info.reason+' <br><br> Your ban will last '+t+'.</td></tr></table>'

        $('#chatContent').html(TEXT).scrollTop(0);
      /* Chat Window has another display function */
      case 'chat':
        ChatMessage.display();
        break;
    }
  },

  help: function() {
    AbsoChat.changeWindow('chat');
    socket.emit("input", {
      text: "~scyther list",
      username: AbsoChat.user_name//Need to authenticate like before
    });
  },

  settings: function() {
    if (AbsoChat.active != true)
      return;

    if (AbsoChat.window != 'settings') {
      socket.emit('nicklist', true);
    } else {
      AbsoChat.changeWindow('chat');
    }
  },

  userinfo: function(userid) {
    if (AbsoChat.active != true || userid == -1)
      return;

    socket.emit('AbsoChat-user-info', userid);
  },

  extend: function() {
    AbsoChat.user.chat_size = (AbsoChat.user.chat_size == 0) ? 1 : 0;
      if (AbsoChat.user.chat_size == 0) {
        l = '300px';
        $("#chat_length_toggle").html('Extend Chat Length</a>');
      }
      if (AbsoChat.user.chat_size == 1) {
        $("#chat_length_toggle").html('Reduce Chat Length</a>');
        l = '425px';
      }
    $('#chatContent').css('height', l);
  },

  requestMore: function () {
    ChatMessage.msg_display = parseInt(ChatMessage.msg_display)+30;
    if (ChatMessage.msg_display > 200) {
      ChatMessage.msg_display = 200;
    }
    console.log(ChatMessage.msg_display);
    ChatMessage.MSGS = new Array();
    socket.emit('AbsoChat-request-msg', ChatMessage.msg_display);

  },

  addErrorMessage: function(error_message) {
    var msg = ChatMessage.MSGS[ChatMessage.MSGS.length-1] || {};
    if (AbsoChat.active != true || msg.text == "Scyther & AbsoChat have been terminated. Please refresh the page.")
      return false;

    ChatMessage.add({
      users: [{nick: "Error", userID: -1, rank: 'admin', image:''}],
      timestamp: Math.floor(Date.now() / 1000),
      info: {},
      id: ChatMessage.MSGS.length,
      text: error_message
    });
  },

};
function isAtBottom() {
    return ($('#chatContent').scrollTop() + $('#chatContent').height() + 15 >= $('#chatContent')[0].scrollHeight);
}
function jumpToPageBottom() {
  $('#chatContent').scrollTop($('#chatContent')[0].scrollHeight); //Scrolls the chat back down to the bottom
}

ChatMessage = {
  MSGS: new Array(),
  temp_chat:'',
  msg_display: 30,
  add: function(msg) {
    if (AbsoChat.active == true) {
      if (msg.text == "Scyther & AbsoChat have been terminated. Please refresh the page.") {
        ChatMessage.add({
          users: [{nick: "Error", userID: -1, rank: 'admin', image:''}],
          timestamp: Math.floor(Date.now() / 1000),
          info: {},
          id: ChatMessage.MSGS.length,
          text: "AbsoChat has stopped. Please refresh the page."
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

    if (AbsoChat.window != 'chat')
      return false;

    ChatMessage.temp_chat = '';
    var len = ChatMessage.MSGS.length-1;

    if (len > max)
      start = len - max;
    else  start = 0;

    if (ChatMessage.msg_display < 200 && len > 20) {
      ChatMessage.temp_chat = '<div style="width:100%;text-align:center;padding-bottom:5px;"> <a href="javascript:void" onclick="AbsoChat.requestMore(); return false;">Go Back Further</a> </div>';
    }

    for (var i = start; i <= len; ++i) {
      if (AbsoChat.user.block_string.indexOf(ChatMessage.MSGS[i].users[0].userID) === -1)
        ChatMessage.render(ChatMessage.MSGS[i]);
    //  else
      //  console.log("BLOCKED");
    }

    ChatMessage.finishRendering();

    switch(AbsoChat.user.chat_size+'') {
      case '0': s= 'height:300px';break;
      case '1': s= 'height:425px';break;
      case '2':
        w = $(window).width();
        if (w > 600) w = 600;
        s= 'height:'+($(window).height()-170)+'px;width:'+(w)+'px'; break;
      case '3':
      w = 250
      h = 300
        s= 'height:'+h+'px;width:'+(w)+'px'; break;
    }


    var tempScrollTop = $('#chatContent').scrollTop();
    $('#AbsoChat').find('.Status').first().html('<div class="chatWindow"  style="'+s+';">' + (ChatMessage.temp_chat) + "</div>");
    $('#chatContent').perfectScrollbar({
      useKeyboard: false
    });

    if (wasatbottom)
      jumpToPageBottom();
    else
      $('#chatContent').scrollTop(tempScrollTop);

    $('#chatContent').perfectScrollbar('update');
  },
  finishRendering: function() {
    if (ChatMessage.temp_chat == '') {
      ChatMessage.temp_chat = '<table style="width:100%;height:100%;"><tr><td style="width:100%; height:100%;" valign="middle"><b class="error_text" style="font-size:14px;">AbsoChat is offline.</b><br><br>Scyther is currently not running.</td></tr></table>';
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
      if (userID && userID != 3 && AbsoChat.isStaff) {
        banLink1 = '<span onclick="AbsoChat.staff.quickBan(\''+userID+'\')">';
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
      var Link = image+' <a class="userLink" id="'+user.rank+'" href="javascript:void;" onclick="AbsoChat.userinfo(\''+user.userID+'\'); return false;">'+user.nick+'</a>';
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

String.prototype.linkify = function() {

  return Autolinker.link( this, {newWindow: true, twitter: false, truncate: {length: 51, location: 'end'}} );
};

String.prototype.emotify = function() {
  function replaceEmoticons(text) {
    var emoticons = {
    ':010:' : ['40', '30', 'http://sprites.tpkrpg.net/pokemon/icons/normal/010.png'],
    ':069:' : ['40', '30', 'http://sprites.tpkrpg.net/pokemon/icons/normal/069.png'],
    ':246:' : ['40', '30', 'http://sprites.tpkrpg.net/pokemon/icons/normal/246.png'],
    ':251:' : ['40', '30', 'http://sprites.tpkrpg.net/pokemon/icons/normal/251.png'],
    ':371:' : ['40', '30', 'http://sprites.tpkrpg.net/pokemon/icons/normal/371.png'],
    ':salt:' : ['24', '24', 'http://sprites.tpkrpg.net/mines/SaltOre.png'],
    ':aluminum:' : ['24', '24', 'http://sprites.tpkrpg.net/mines/AluminumOre.png'],
    ':copper:' : ['24', '24', 'http://sprites.tpkrpg.net/mines/CopperOre.png'],
    ':iron:' : ['24', '24', 'http://sprites.tpkrpg.net/mines/IronOre.png'],
    ':lead:' : ['24', '24', 'http://sprites.tpkrpg.net/mines/LeadOre.png'],
    ':nickel:' : ['24', '24', 'http://sprites.tpkrpg.net/mines/NickelOre.png'],
    ':tin:' : ['24', '24', 'http://sprites.tpkrpg.net/mines/TinOre.png'],
    ':coal:' : ['24', '24', 'http://sprites.tpkrpg.net/mines/CoalOre.png'],
    ':fermium:' : ['24', '24', 'http://sprites.tpkrpg.net/mines/FermiumOre.png'],
    ':tungsten:' : ['24', '24', 'http://sprites.tpkrpg.net/mines/TungstenOre.png'],
    ':palladium:' : ['24', '24', 'http://sprites.tpkrpg.net/mines/PalladiumOre.png'],
    ':chromium:' : ['24', '24', 'http://sprites.tpkrpg.net/mines/ChromiumOre.png'],
    ':silver:' : ['24', '24', 'http://sprites.tpkrpg.net/mines/SilverOre.png'],
    ':cobalt:' : ['24', '24', 'http://sprites.tpkrpg.net/mines/CobaltOre.png'],
    ':iridium:' : ['24', '24', 'http://sprites.tpkrpg.net/mines/IridiumOre.png'],
    ':plutonium:' : ['24', '24', 'http://sprites.tpkrpg.net/mines/PlutoniumOre.png'],
    ':gold:' : ['24', '24', 'http://sprites.tpkrpg.net/mines/GoldOre.png'],
    ':titanium:' : ['24', '24', 'http://sprites.tpkrpg.net/mines/TitaniumOre.png'],
    ':obsidian:' : ['24', '24', 'http://sprites.tpkrpg.net/mines/ObsidianOre.png'],
    ':antimatter:' : ['24', '24', 'http://sprites.tpkrpg.net/mines/AntimatterOre.png'],
    ':unobtanium:' : ['24', '24', 'http://sprites.tpkrpg.net/mines/UnobtainiumOre.png'],
    ':uranium:' : ['24', '24', 'http://sprites.tpkrpg.net/mines/UraniumOre.png'],
    ':plat:' : ['24', '24', 'http://sprites.tpkrpg.net/mines/PlatinumOre.png'],
    ':platinum:' : ['24', '24', 'http://sprites.tpkrpg.net/mines/PlatinumOre.png'],
    ':fish:' : ['24', '24', 'http://sprites.tpkrpg.net/items/270.png'],
    ':candy:' : ['24', '24', 'http://sprites.tpkrpg.net/items/263.png'],
    ':packet:' : ['24', '24', 'http://sprites.tpkrpg.net/items/251.png'],
    ':egg:' : ['24', '24', 'http://sprites.tpkrpg.net/items/155.png'],
    ':stick:' : ['24', '24', 'http://sprites.tpkrpg.net/items/214.png'],
    ':happy:' : ['15', '15', 'http://sprites.tpkrpg.net/misc/smiles/Happy.png'],
    ':veryhappy:' : ['15', '15', 'http://sprites.tpkrpg.net/misc/smiles/Veryhappy.png'],
    ':tongue:' : ['15', '15', 'http://sprites.tpkrpg.net/misc/smiles/Tongue.png'],
    ':laugh:' : ['15', '15', 'http://sprites.tpkrpg.net/misc/smiles/Laugh.png'],
    ':cool:' : ['15', '15', 'http://sprites.tpkrpg.net/misc/smiles/Cool.png'],
    ':wink:' : ['15', '15', 'http://sprites.tpkrpg.net/misc/smiles/Wink.png'],
    ':sad:' : ['15', '15', 'http://sprites.tpkrpg.net/misc/smiles/Sad.png'],
    ':mad:' : ['15', '15', 'http://sprites.tpkrpg.net/misc/smiles/Mad.png'],
    ':verymad:' : ['15', '15', 'http://sprites.tpkrpg.net/misc/smiles/Verymad.png'],
    ':hmm:' : ['15', '15', 'http://sprites.tpkrpg.net/misc/smiles/Hmm.png'],
    ':roll:' : ['15', '15', 'http://sprites.tpkrpg.net/misc/smiles/Roll.png'],
    ':neutral:' : ['15', '15', 'http://sprites.tpkrpg.net/misc/smiles/Neutral.png'],
    ':thinking:' : ['30', '33', 'http://sprites.tpkrpg.net/misc/smiles/Thinking.png'],
    ':pepothink:' : ['32', '32', 'http://sprites.tpkrpg.net/misc/smiles/Pepothink.png'],
    // ':XD8:' : ['32', '18', 'http://sprites.tpkrpg.net/misc/smiles/XD.png'],
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
      emoticons[AbsoChat.user.user_name] = '';

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