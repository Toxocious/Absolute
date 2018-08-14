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
};