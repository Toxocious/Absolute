const Handler = {
    Messages: [],
    MaxMessages: 20,
    Message: '',

    /**
     * Clear the message array.
     */
    Clear: function () {
        this.Messages = [];
    },

    /**
     * Add a message to the messages array.
     */
    AddMessage: function (message) {
        /**
         * Only works if the socket and server are currently active.
         */
        if (ECRPGClient.active) {
            let Message_Data;

            Message_Data = {
                User: {
                    ID: message.userID,
                    Name: message.userName,
                    Rank: message.userRank,
                    Avatar: message.userAvatar,
                },
                Message: {
                    ID: this.Messages.length,
                    Content: message.messageText,
                    Timestamp: new Date(message.sentOn).toLocaleTimeString('en-US'),
                    Private: message.isPrivate,
                },
            };

            this.Messages.push(Message_Data);
        }

        this.Display();
    },

    /**
     * Display messages in the chat window.
     */
    Display: function () {
        console.log(ECRPGClient);
        console.log(ECRPGClient.user);

        /**
         * Reset the currently stored message HTML so that we don't get repeated chat messages.
         */
        Handler.Message = '';

        /**
         * The client didn't connect to the chat for one reason or another.
         * Display that Absol is likely offline.
         */

        if (typeof ECRPGClient.user.Connected === 'undefined') {
            Handler.Message = `
                <table style="width: 100%; height: 100%;">
                  <tr>
                    <td style="width: 100%; height: 100%;" valign="middle">
                      <img src='https://${location.hostname}/images/Pokemon/Sprites/Normal/359.png' />
                      <br />
                      <b style="color: #ff0000; font-size: 14px;">Evo-Chronicles RPG Chat is offline.</b>
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
        if (this.Messages.length < this.MaxMessages) Iterations = this.Messages.length;
        else Iterations = this.MaxMessages;

        /**
         * Loop through the messages array.
         */
        for (let i = 0; i < Iterations; i++) this.RenderMessage(this.Messages[i]);

        $('#AbsoChat').find('#chatContent').first().html(Handler.Message);
        $('#chatContent').scrollTop($('#chatContent')[0].scrollHeight);
    },

    /**
     * Render a message.
     */
    RenderMessage: function (message) {
        if (typeof message !== 'undefined') {
            const Message_Data = {
                User: {
                    ID: message.User.ID,
                    Username: message.User.Name,
                    Avatar: message.User.Avatar
                        ? message.User.Avatar
                        : `https://${location.hostname}/images/Pokemon/Sprites/0.png`,
                    Rank: message.User.Rank,
                },
                Message: {
                    ID: message.Message.ID,
                    Content: message.Message.Content,
                    Timestamp: message.Message.Timestamp,
                    Private: message.Message.Private,
                },
            };

            /**
             * Display all messages that the user should be able to see.
             * - Non-private messages.
             * - Private messages that were only sent to the user.
             */
            if (
                !Message_Data.Message.Private.isPrivate ||
                (Message_Data.Message.Private.isPrivate &&
                    Message_Data.Message.Private.Private_To === ECRPGClient.user.UserID)
            ) {
                Handler.Message += `
          <div class="message${
              Message_Data.Message.Private.isPrivate ? ' private' : ''
          }" data-msg-id="${Message_Data.Message.ID}">
            <div class="avatar">
              <a href='/profile.php?id=${Message_Data.User.ID}'>
                <img src="/images/${Message_Data.User.Avatar}" />
              </a>
            </div>
            <div class="username">
              <a href='/profile.php?id=${Message_Data.User.ID}'>
                <b class='${Message_Data.User.Rank}'>${Message_Data.User.Username}</b>
              </a>
              <br />
              ${Message_Data.Message.Timestamp}
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
