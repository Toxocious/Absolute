/* ************************
 * activity.js
 *
 * Reports the activity of the user
 */

var numeral = require('numeral');
var fn = require('./functions');

module.exports = {
	minArgs: 1,
	helpText: [
		'!activity [Username/ID] (battles|mining)',
		'Returns a text blurb about the activity of the user.'
	],
	command: function (args, bot, conn) {
		if (typeof args[1] === "undefined") {
			bot.scyther(args, 'No Username/ID entered.');
			return;
		} else if (!fn.isNumeric(args[1])) {

			args[1] = fn.encodeHTML(args[1]);
			conn.query({
				sql: 'SELECT * FROM `users` WHERE `user_name` = ? LIMIT 1',
				values: [args[1]]
			}, function (error, results, fields) {
				if (results.length == 0) {
					bot.scyther(args, args[1]+' does not exist.');
				} else {
					activity(results[0]);
				}
			});
	    } 
	    else
	    {			
			args[1] = fn.encodeHTML(args[1]);

			conn.query({
				sql: 'SELECT * FROM `users` WHERE `user_name` = ? LIMIT 1',
				values: [args[1]]
			}, function (error, results, fields) {
				if (results.length == 0) {
					bot.scyther(args, args[1]+' does not exist.');
				} else {
					activity(results[0]);
				}
			});
	    }
			
		function activity(User) {
			var username = User['user_name'];
			Seconds = Math.floor(Date.now() / 1000) - User['online_time'];

			if(User['online_time'] == '')
				lastseen = "Never";
			else if(Seconds <= 59)
				lastseen = ""+Seconds+" Second(s)";
			else if(Seconds >= 60 && Seconds <= 3599)
				lastseen = Math.floor(Seconds / 60)+" Minute(s)";
			else if(Seconds >= 3600 && Seconds <= 86399)
				lastseen = Math.floor(Seconds / 3600)+" Hour(s)";
			else if(Seconds >= 86400 && Seconds <= 604799)
				lastseen = Math.floor(Seconds / 86400)+" Day(s)";
			else if(Seconds >= 604800 && Seconds <= 2419199)
				lastseen = Math.floor(Seconds / 604800)+" Week(s)";
			else if(Seconds >= 2419200 && Seconds <= 29030399)
				lastseen = Math.floor(Seconds / 2419200)+" Month(s)";
			else 
				lastseen = Math.floor(Seconds / 29030400)+" Year(s)";
				
			if (Seconds < 600)
				online = 'online';
			else online = 'offline';
			
			switch(User['gender']) {
				case 'm':
					G = 'He'; GG = 'his';
					break;
				case 'f':
					G = 'She'; GG = 'her';
					break;
				case 'u':
					G = 'It'; GG = 'it\'s';
					break;
			}
			
			if (Seconds < 60)
				firstSentence = username+' is actively playing.';
			else 
				firstSentence = username+' was last seen '+lastseen+' ago.';
			
			var date = new Date;
			if (date.getHours() < 2) {
				day = "already";
			} else if (date.getHours() < 9) {
				day = "this morning";
			} else {
				day = "today";
			}
			
			if (args[2] == 'mining') {
				if (User['mines_today'] == User['mines_today_record']) {
					secondSentence = G+' has broken '+GG+' mining record with '+numeral(User['mines_today']).format('0,0')+' mines '+day+'. Congratulations!';
				} else {
					secondSentence = G+' has completed '+numeral(User['mines_today']).format('0,0')+' mines '+day+'.';
				}
			} else {
				if (User['battle_day'] == User['battle_day_record']) {
					secondSentence = G+' has broken '+GG+' battle record with '+numeral(User['battle_day']).format('0,0')+' battles '+day+'. Congratulations!';
				} else {
					secondSentence = G+' has completed '+numeral(User['battle_day']).format('0,0')+' battles '+day+'.';
				}
			}
			
			if (User['battle_day'] == '69' || User['battle_day'] == '690' || User['battle_day'] == '6900') {
				args.image = ['069', '0', 'normal'];
			}
			
			bot.scyther(args, firstSentence+' '+secondSentence);
	
		}
	}
}//







