/* **********************
 * wtc.js
 * 
 * Returns the current WTC Status
 */
 
var numeral = require('numeral');
var fn = require('./functions');

module.exports = {
	minArgs: 0,
	helpText: [
		'!wtc [next]',
		'Returns the current Pokemon of the WTC, and the top 3 Trainers. If "next" is the argument, it will say whether there is a WTC for next week or not.'
	],
	command: function (args, bot, conn) {
		var Response = "";
		
		if (args[1] == "next") { 
			conn.query({
				sql: "SELECT COUNT(`id`) FROM `crons` WHERE `type` = 'wtc' AND `used` != 'yes'",
				values: []
			}, function (error, results, fields) {
				if (results.length == 0) {
					bot.scyther(args, "B0sh! The next WTC is not set.");
				} else {
					bot.scyther(args, "There is a WTC for next week.");
				}
			});
			return;
		}
		
		
		conn.query({
			sql: "SELECT * FROM `admin_options` LIMIT 1",
			values: []
		}, function (error, results, fields) {
			var AdminOptions = results[0];
			
		conn.query({
			sql: "SELECT * FROM `poke_data` WHERE `poke_id` = ? AND `alt_id` = ? LIMIT 1",
			values: [AdminOptions['wtc_id'], AdminOptions['wtc_alt']]
		}, function (error, results, fields) {
			if (results.length == 0) {
				bot.scyther(args, "There is no WTC set.");
				return;
			}
		
			var WTCData = results[0];
			
			args.image = [WTCData['poke_id'], WTCData['alt_id'], AdminOptions['wtc_type']];
			
			var Fullname = WTCData['poke_name'];
			if (AdminOptions['wtc_type'] != 'normal')
				Fullname = fn.ucfirst(AdminOptions['wtc_type']) + Fullname
			if (WTCData['alter_poke_name'] != null)
				Fullname += WTCData['alter_poke_name']
			
			Response = Fullname +": ";
		conn.query({
			sql: "SELECT po.experience, po.trainer_id, po.id, u.user_name FROM `pokemon` AS po LEFT JOIN `users` AS u ON po.trainer_id=u.user_id WHERE u.banned = 'no' AND po.wtc='yes' ORDER BY experience DESC LIMIT 3",
			values: []	
		}, function (error, results, fields) {
			if (results.length == 0) {
				bot.scyther(args, Response + "Nobody has entered the contest.");
				return;
			}
			
			for (var i in results) {
				if (results.hasOwnProperty(i)) {
					switch (i) {
						case '0': Response += '1st '; break;
						case '1': Response += '2nd '; break;
						case '2': Response += '3rd '; break;
					}
					
					if (i != results.length-1) 
						Response += results[i]['user_name'] + ' (Level: '+(numeral(fn.getLevel(results[i]['experience']+1, 'pokemon')).format('0,0'))+'), ';
					else 
						Response += results[i]['user_name'] + ' (Level: '+(numeral(fn.getLevel(results[i]['experience']+1, 'pokemon')).format('0,0'))+')';
				}
			}
			
			bot.scyther(args, Response);
		}); //WTC Results
		}); //WTC Pokemon Data
		}); //Admin Options
	}
};