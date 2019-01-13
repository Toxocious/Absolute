/* ************************
 * confirm.js
 *
 * Confirms send money requests
 */

var numeral = require('numeral');
var fn = require('./functions');

module.exports = {
	minArgs: 1,
	hidden: true,
	socketRequired: true,
	helpText: [
		'!confirm [code]',
		'This command is used to confirm commands that affect your TPK account.'
	],
	command: function (args, bot, conn) {
		data = bot.getConfirm(args[1]);
		Clients = bot.getClients();
		bot.deleteConfirm(args[1]);
		if (typeof data === "undefined" || data['sender'] != args.socket['tpk_clientID']){
			bot.scyther(args, "There is nothing to confirm here.");
			return
		}

		var user = Clients["user"+args.socket['tpk_clientID']]
		
		//Get user information again, because your money could've changed since the the user was set
		conn.query({
			sql: "SELECT * FROM users WHERE user_id=? LIMIT 1",
			values: [user.user_id]
		}, function (error, results, fields) {
			if (results.length == 0) {
				bot.scyther(args, "An error has occurred.");
			} else {
				var user = results[0];
				switch(data.command) {
					case 'sendmoney':
						conn.query({
							sql: "SELECT * FROM users WHERE user_id=? LIMIT 1",
							values: [data.reciever]
						}, function (error, results, fields) {
							if (results.length == 0) {
								bot.scyther(args, "An error has occurred.");
							} else if (user['tpk_money'] < data.moneyAmt) {
								bot.scyther(args, "There isn't enough money to send.");
							} else if (user['banned'] != 'no') {
								bot.scyther(args, "This trainer is banned.");
							} else if (user['type'] == 'spd') {
								bot.scyther(args, "You cannot send money to a Speedrun Account");
							} else {
								reciever = results[0];
								
								conn.query({
									sql: "UPDATE users SET tpk_money=tpk_money+? WHERE user_id=? LIMIT 1",
									values: [data.moneyAmt, data.reciever]
								}, function (error, results, fields) {
									conn.query({
										sql: "UPDATE users SET tpk_money=tpk_money-? WHERE user_id=? LIMIT 1",
										values: [data.moneyAmt, user['user_id']]
									}, function (error, results, fields) {
										args.hidden_command = false;
										bot.scyther(args, user['user_name'] +' sent $'+numeral(data.moneyAmt).format('0,0')+' to '+reciever['user_name']+'.');
									});
								});
							}
						});
						break;
				}
				
			}
		});
		
		
	}
}