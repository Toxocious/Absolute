/* * * * * * * *
 * money.js for Scyther
 * 
 * Tells you how much money someone has
 */
 
var numeral = require('numeral');
var fn = require('./functions');


module.exports = {
	minArgs: 0,
	helpText: [
		'!money [Username/ID]',
		'Returns how much money someone has'
	],
	command: function (args, bot, conn) {	
		if (args.length == 1) {
			conn.query({
				sql: 'SELECT SUM(tpk_money) as sum FROM users WHERE banned != ?',
				values: ['yes']
			}, function(error, results, fields) {
				bot.scyther(args, 'Total Money: $'+numeral(results[0]['sum']).format('0,0'));
			});
			return;
		}
		
		
		conn.query({
			sql: 'SELECT * FROM `users` WHERE `user_name` = ? LIMIT 1',
			values: [args[1]]
		}, function (error, results, fields) {
			if (results.length == 0) {
				conn.query({
					sql: 'SELECT * FROM `users` WHERE `user_name` = ? LIMIT 1',
					values: [args[1]]
				}, function (error, results, fields) {
					if (results.length == 0) {
						bot.scyther(args, "This user does not exist.");
					} else {
						callback(results[0]);
					}
				});
			} else {
				callback(results[0]);
			}
		});
		
		function callback(user) {
			bot.scyther(args, user['user_name'] +': $'+numeral(user['tpk_money']).format('0,0'));
		}
	}
};
