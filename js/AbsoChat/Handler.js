const Handler = {
  Messages: [],
  MaxMessages: 20,
  Message: '',

  /**
   * Clear the message array.
   */
  Clear: function()
  {
    this.Messages = [];
  },

  /**
   * Add a message to the messages array.
   */
  AddMessage: function(message)
  {
    /**
     * Only works if the socket and server are currently active.
     */
    if ( Absolute.active )
    {
      let Message_Data;

      Message_Data = {
        User: {
          ID: message.User.ID,
          Name: message.User.Name,
          Rank: message.User.Rank,
          Avatar: message.User.Avatar,
        },
        Message: {
          ID: this.Messages.length,
          Content: message.Message.Content,
          Timestamp: message.Message.Timestamp ? message.Message.Timestamp : new Date().toLocaleTimeString("en-US"),
          Private: message.Message.Private,
        },
      };

      this.Messages.push(Message_Data);
    }

    this.Display();
  },

  /**
   * Display messages in the chat window.
   */
  Display: function()
  {
    /**
     * Reset the currently stored message HTML so that we don't get repeated chat messages.
     */
    Handler.Message = '';

    /**
     * The client didn't connect to the chat for one reason or another.
     * Display that Absol is likely offline.
     */
    if ( typeof Absolute.user.connected === 'undefined' )
    {
      Handler.Message = `
        <table style="width: 100%; height: 100%;">
          <tr>
            <td style="width: 100%; height: 100%;" valign="middle">
              <img src='https://${location.hostname}/images/Pokemon/Sprites/Normal/359.png' />
              <br />
              <b style="color: #ff0000; font-size: 14px;">Absolute Chat is offline.</b>
              <br /><br />
              Absol is currently offline for one reason or another.
            </td>
          </tr>
        </table>
      `;
    }

    /**
     * Determine how many messages to loop through.
     */
    let Iterations = 0;
    if ( this.Messages.length < this.MaxMessages )
      Iterations = this.Messages.length;
    else
      Iterations = this.MaxMessages;

    /**
     * Loop through the messages array.
     */
    for ( let i = 0; i < Iterations; i++ )
      this.RenderMessage( this.Messages[i] );

    $('#AbsoChat').find('#chatContent').first().html(Handler.Message);
    $('#chatContent').scrollTop( $('#chatContent')[0].scrollHeight );
  },

  /**
   * Render a message.
   */
  RenderMessage: function(message)
  {
    if ( typeof message !== 'undefined' )
    {
      const Message_Data = {
        User: {
          ID: message.User.ID,
          Username: message.User.Name,
          Avatar: message.User.Avatar ? message.User.Avatar : `https://${location.hostname}/images/Pokemon/Sprites/0.png`,
          Rank: message.User.Rank,
        },
        Message: {
          ID: message.Message.ID,
          Content: message.Message.Content,
          Timestamp: message.Message.Timestamp,
          Private: message.Message.Private
        },
      };

      /**
       * Display all messages that the user should be able to see.
       * - Non-private messages.
       * - Private messages that were only sent to the user.
       */
      if (
        !Message_Data.Message.Private.isPrivate || 
        ( Message_Data.Message.Private.isPrivate && Message_Data.Message.Private.Private_To === Absolute.user.user_id )
      )
      {
        Handler.Message += `
          <div class="message${(Message_Data.Message.Private.isPrivate ? ' private' : '')}" data-msg-id="${Message_Data.Message.ID}">
            <div class="avatar">
              <a href='/profile.php?id=${Message_Data.User.ID}'>
                <img src="${Message_Data.User.Avatar}" />
              </a>
            </div>
            <div class="username">
              <a href='/profile.php?id=${Message_Data.User.ID}'>
                <b class='${Message_Data.User.Rank}'>${Message_Data.User.Username}</b>
              </a>
              <br />
              ${Message_Data.Message.Timestamp}
            </div>
            <div class='master-title'>
              <img src='https://${location.hostname}/images/Pokemon/Icons/Normal/359.1.png' />
            </div>
            <div class="text">
              <div>
                ${Message_Data.Message.Content}
              </div>
            </div>
          </div>
        `;
      }
    }
  },
};