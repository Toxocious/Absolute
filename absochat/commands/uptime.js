/* *****************
 * uptime.js
 *
 * Command that tells how long Scyther has been active
 */
 
var numeral = require('numeral');

module.exports = {
	minArgs: 0,
	helpText: ['!uptime', 'Responds with how long Scyther has been running since it started.'],
	command: function (args, bot, conn) {
		var Seconds = Math.floor(Date.now() / 1000) - bot.startTime;
		
		if(Seconds <= 120)
			lastseen = ""+Seconds+" seconds";
		else if(Seconds >= 120 && Seconds <= 3599*2+1)
			lastseen = Math.floor(Seconds / 60)+" minutes";
		else if(Seconds >= 3600*2 && Seconds <= 86399*2+1)
			lastseen = numeral(Seconds / 3600).format('0.[00]')+" hours";
		else if(Seconds >= 86400*2 && Seconds <= 604799*999999)
			lastseen = numeral(Seconds / 86400).format('0.[00]')+" days";

				
		bot.scyther(args, 'Scyther has been online for '+lastseen+'.');
	}
}