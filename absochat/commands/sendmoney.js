/* ************************
 * sendmoney.js
 *
 * Allows users in Chaterpie to send money to another person in the real game.
 */

var numeral = require('numeral');
var fn = require('./functions');

module.exports = {
	minArgs: 2,
	socketRequired: true,
	helpText: [
		'!sendmoney [Username/ID] [Money Amount]',
		'This command allows you to send money to another user from within Chaterpie.'
	],
	command: function (args, bot, conn) {
		if (args[2] <= 0) {
			bot.scyther(args, "fixed.");
			return;
		}
		
		conn.query({
			sql: 'SELECT * FROM `users` WHERE `user_name` = ? AND banned = ? AND type != "spd" LIMIT 1',
			values: [args[1], 'no']
		}, function (error, results, fields) {
			if (results.length == 0) {
				conn.query({
					sql: 'SELECT * FROM `users` WHERE `user_name` = ? AND banned = ? AND type != "spd" LIMIT 1',
					values: [args[1], 'no']
				}, function (error, results, fields) {
					if (results.length == 0) {
						bot.scyther(args, "This user does not exist or is banned.");
					} else {
						callback(results[0]);
					}
				});
			} else {
				callback(results[0]);
			}
		});
		
		function callback(user) {

			var ConfirmString = bot.setConfirm({
				command: 'sendmoney', 
				sender: args['socket']['tpk_clientID'],
				reciever: user['user_id'],
				moneyAmt: args[2]
			});
			
			args.hidden_command = true; //Only sends the confirm to the same socket
			bot.scyther(args, "Confirm this action with ~confirm "+ConfirmString);
		}
	}
}