let socket;

const Absolute = {
  port: '3001',
  active: false,
  user: {},
  messages: {},

  handleInputBox: function()
  {
    if ( !Absolute.user.connected )
      $('#chatMessage').css('background', '#666').attr('disabled', true);
    else
      $('#chatMessage').css('background', '').attr('disabled', false);
  },

  /**
   * Sending a message.
   */
  Enable: function()
  {
    /**
     * Structure the socket commands.
     */
    if ( typeof socket != 'object' )
    {
      /**
       * Set the active prop to true.
       */
      Absolute.active = true;
      Absolute.user.connected = true;

      /**
       * Start the connection to the server.
       */
      socket = io('localhost:' + Absolute.port,
      {
        reconnection: true,
        secure: true
      });

      /**
       * Handle the connection of the user.
       */
      socket.on("connect", function()
      {
        Handler.Clear();
        Absolute.handleInputBox();

        socket.emit('auth',
        {
          user: Absolute.user.user_id,
          postcode: Absolute.user.postcode
        });
      });

      /**
       * Handle the user upon disconnecting.
       */
      socket.on("disconnect", function()
      {
        Absolute.handleInputBox();
        Handler.AddMessage(
          {
            User: {
              ID: 3,
              Name: 'Absol',
              Rank: 'bot',
              Avatar: '../images/Avatars/Custom/3.png',
            },
            Message: {
              Content: 'You have been disconnected from Absolute Chat. Please refresh the page.',
              Private: {
                isPrivate: true,
                Private_To: Absolute.user.user_id,
              },
            }
          },
        );
      });

      /**
       * Handle sent messages.
       */
      socket.on('chat-message', function(data)
      {
        Handler.AddMessage(data);
      });
    }
  },
};