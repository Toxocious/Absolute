const FS = require('fs');
const OS = require('os');

/**
 * Handles messages that are emitted by the socket.
 */
module.exports = {
  MessageLog: [],

  /**
   * Clear the message log.
   */
  ClearMessages: function()
  {
    this.MessageLog = [];
  },

  /**
   * Add a message to the message log.
   */
  AddMessage: function(User, Message, Private)
  {
    const Timestamp = new Date().toLocaleTimeString("en-US");
    const Message_Data = {
      User: {
        ID: User.user_id,
        Name: User.username,
        Rank: User.rank,
        Avatar: User.avatar,
      },
      Message: {
        ID: this.MessageLog.length,
        Content: Message,
        Timestamp: Timestamp,
        Private: Private,
      },
    };

    /**
     * Parse date into a timestamp.
     */
    const date = new Date(Math.round(Date.now()));
    const year = date.getYear();
		const month = date.getMonth() + 1;
		const day = date.getDate();
		const hours = date.getHours();
		const minutes = date.getMinutes();
    const seconds = date.getSeconds();
    const Current_Date = `${('0' + month).slice(-2)}/${('0' + day).slice(-2)}/${('0' + year).slice(-2)} ${('0' + hours).slice(-2)}:${('0' + minutes).slice(-2)}:${('0' + seconds).slice(-2)}`;

    /**
     * Log the message to a log file.
     */
    const Log = (Private.isPrivate ? `[PRIVATE TO #${Private.Private_To} ] ` : '') + `[${Current_Date}] ${Message_Data.User.Name}: ${Message_Data.Message.Content}`;
    this.LogMessage(Log, 'chatlog.txt');

    /**
     * Push the message data to the message log.
     */
    this.MessageLog.push(Message_Data);

    return Message_Data;
  },

  LogMessage: function(text, file)
  {
    FS.appendFile(file, text + OS.EOL, encoding = 'utf8', function(error)
    {
      if (error)
      {
        throw error;
      }
    });
    
		console.log(text);
	},
};