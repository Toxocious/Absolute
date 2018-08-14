/* *********************
 * message.js
 *
 * Hopefully a way for Scyther to manage adding messages etc
 */
 
var fn = require('./commands/functions.js');
var fs = require('fs');
var os = require("os");

var SPAM = [
	5, //Limit is 5 messages in 
	5  //5 seconds
];

//Send all messages
var LogMessages = true;

 
exports.messageHandler = messageHandler;

function messageHandler() {
	this.MessageLog = [];
}    
String.prototype.repeat = function(num){
  return new Array(num + 1).join(this);
}

function filter (msg) {
	var FILTERED = [
		'fuck',
		'faggot',
		'fgt',
		'shit',
		'cunt',
		'pussy',
		'bitch',
		'dick',
		'asshole',
		'slut',
		'whore',
		'fag',
		'nigger',
		'nigga',
		'queer',
		'dildo'
	];
	
	txt = msg;

	// iterate over all words
	for(var i=0; i<FILTERED.length; i++){
		// Create a regular expression and make it global
		var pattern = new RegExp('\\b' + FILTERED[i] + '\\b', 'gi');

		// Create a new string filled with '*'
		var replacement = '*'.repeat(FILTERED[i].length);

		txt = txt.replace(pattern, replacement);
	}

	// returning txt will set the new text value for the current element
	return txt;
}

messageHandler.prototype.clear = function(users, message, chaterpie) {
	this.MessageLog = [];
}

messageHandler.prototype.add = function(users, message, chaterpie) {
	if (typeof users[0] === "undefined") 
		users = [users];
	if (typeof chaterpie === "undefined" )
		chaterpie = {};
	
	if (users[0].rank == 'bot')
		message = fn.decodeHTML(message);
	
	var timestamp = Math.round(Date.now());
	msg = {
		users: users,
		text: filter(message),
		timestamp: timestamp,
		id: this.MessageLog.length,
		info: chaterpie,
	}
	
	if (LogMessages) {
		var date = new Date(timestamp);
		
		var year = date.getYear();
		var month = date.getMonth() + 1;
		var day = date.getDate();
		var hours = date.getHours();
		var minutes = date.getMinutes();
		var seconds = date.getSeconds();

		var log = "m >> "+('0'+month).slice(-2) + "/" + ('0'+day).slice(-2) + "/" + ('0'+year).slice(-2) + " " + ('0'+hours).slice(-2) + ":" + ('0'+minutes).slice(-2) + ":" + ('0'+seconds).slice(-2)+" .. "+users[0].nick +": "+filter(message)+"";
		
		fn.log(log);
	}
	
	this.MessageLog.push(msg);
	
	return msg;
}

messageHandler.prototype.self = function(users, message, chaterpie) {
	if (typeof users[0] === "undefined") 
		users = [users];
	if (typeof chaterpie === "undefined" )
		chaterpie = {};
		
	var timestamp = Math.round(Date.now());
	msg = {
		users: users,
		text: message,
		timestamp: timestamp,
		id: this.MessageLog.length,
		info: chaterpie,
	}
	if (LogMessages && typeof chaterpie.do_not_log === "undefined") {
		var date = new Date(timestamp);

		var year = date.getYear();
		var month = date.getMonth() + 1;
		var day = date.getDate();
		var hours = date.getHours();
		var minutes = date.getMinutes();
		var seconds = date.getSeconds();

		var log = "m >> "+('0'+month).slice(-2) + "/" + ('0'+day).slice(-2) + "/" + ('0'+year).slice(-2) + " " + ('0'+hours).slice(-2) + ":" + ('0'+minutes).slice(-2) + ":" + ('0'+seconds).slice(-2)+" .. "+users[0].nick +": "+filter(message)+"";
		
		fn.log(log);
	}
	return msg;
}

messageHandler.prototype.isSpam = function(user) {
	if (this.MessageLog.length < 40) {
		var l = 0;
	} else {
		var l = this.MessageLog.length - 40;
	}
	
	var time = Math.round(Date.now() / 1000),
		yourMessageCount = 0;
	
	for (var i = this.MessageLog.length; i > l; --i) {
		if (typeof this.MessageLog[i] !== "undefined") {
			var msg = this.MessageLog[i];

			if (time-Math.round(msg.timestamp/1000) <= SPAM[1] && msg.users[0].userID == user.user_id) {
				yourMessageCount++;
			}
		}
	}
		
	if (yourMessageCount >= SPAM[0]) {
		console.log("SPAM POST BLOCKED");
		return true;
	} else {
		return false;
	}
}