/**
 * Message handler for Absol.
 */
 
var fn = require('./commands/functions.js');
var fs = require('fs');
var os = require("os");

// Setting the spam limit to 5 messages in 5 seconds.
let SPAM = [ 5, 5 ];

//Send all messages
var LogMessages = true;

exports.messageHandler = messageHandler;

function messageHandler()
{
	this.MessageLog = [];
}    

String.prototype.repeat = function(num)
{
  return new Array(num + 1).join(this);
}

function filter(message)
{
	let Filter_List =
	[
		'fuck',
		'fuckin',
		'fucking',
		'faggot',
		'fgt',
		'shit',
		'cunt',
		'pussy',
		'bitch',
		'dick',
		'ass',
		'asshole',
		'slut',
		'whore',
		'fag',
		'nig',
		'nigga',
		'nigger',
		'queer',
		'dildo',
		'porn',
		'hentai',
	];
	
	let text = message;

	// iterate over all words
	for ( let i = 0; i < Filter_List.length; i++ )
	{
		// Create a regular expression and make it global
		let pattern = new RegExp('\\b' + Filter_List[i] + '\\b', 'gi');

		// Create a new string filled with '*'
		let replacement = '*' . repeat(Filter_List[i].length);

		text = text.replace(pattern, replacement);
  }

	// returning text will set the new text value for the current element
	return text;
}

messageHandler.prototype.clear = function(users, message, chaterpie)
{
	this.MessageLog = [];
}

messageHandler.prototype.add = function(users, message, chaterpie, logfile)
{
	if ( typeof users[0] === "undefined" )
	{
		users = [users];
	}

	if ( typeof chaterpie === "undefined" )
	{
		chaterpie = {};
	}
	
	if ( users[0].rank == 'bot' )
	{
		message = fn.decodeHTML(message);
	}
	
	var timestamp = Math.round( Date.now() );
	msg = {
		users: users,
		text: filter(message),
		timestamp: timestamp,
		id: this.MessageLog.length,
		info: chaterpie,
	}
	
	if ( LogMessages )
	{
		var date = new Date(timestamp);
		
		var year = date.getYear();
		var month = date.getMonth() + 1;
		var day = date.getDate();
		var hours = date.getHours();
		var minutes = date.getMinutes();
		var seconds = date.getSeconds();

		var log = "m >> "+('0'+month).slice(-2) + "/" + ('0'+day).slice(-2) + "/" + ('0'+year).slice(-2) + " " + ('0'+hours).slice(-2) + ":" + ('0'+minutes).slice(-2) + ":" + ('0'+seconds).slice(-2)+" .. "+users[0].nick +": "+filter(message)+"";
		
		fn.log(log, logfile);
	}
	
	this.MessageLog.push(msg);
	
	return msg;
}

messageHandler.prototype.self = function(users, message, chaterpie, logfile)
{
	if ( typeof users[0] === "undefined" )
	{
		users = [users];
	}
	if ( typeof chaterpie === "undefined" )
	{
		chaterpie = {};
	}
		
	let timestamp = Math.round(Date.now());
	msg = {
		users: users,
		text: message,
		timestamp: timestamp,
		id: this.MessageLog.length,
		info: chaterpie,
	}

	if ( LogMessages && typeof chaterpie.do_not_log === "undefined" )
	{
		let date = new Date(timestamp);
		let year = date.getYear();
		let month = date.getMonth() + 1;
		let day = date.getDate();
		let hours = date.getHours();
		let minutes = date.getMinutes();
		let seconds = date.getSeconds();

		let log = "m >> "+('0'+month).slice(-2) + "/" + ('0'+day).slice(-2) + "/" + ('0'+year).slice(-2) + " " + ('0'+hours).slice(-2) + ":" + ('0'+minutes).slice(-2) + ":" + ('0'+seconds).slice(-2)+" .. "+users[0].nick +": "+filter(message)+"";
		
		fn.log(log, logfile);
	}
	
	return msg;
}

messageHandler.prototype.isSpam = function(user)
{
	if (this.MessageLog.length < 40)
	{
		var l = 0;
	}
	else
	{
		var l = this.MessageLog.length - 40;
	}
	
	var time = Math.round(Date.now() / 1000),
		yourMessageCount = 0;
	
	for ( let i = this.MessageLog.length; i > l; --i )
	{
		if ( typeof this.MessageLog[i] !== "undefined" )
		{
			var msg = this.MessageLog[i];

			if ( time - Math.round(msg.timestamp / 1000) <= SPAM[1] && msg.users[0].userID == user.user_id )
			{
				yourMessageCount++;
			}
		}
	}
		
	if ( yourMessageCount >= SPAM[0] )
	{
		console.log("SPAM POST BLOCKED");
		return true;
	}
	else
	{
		return false;
	}
}