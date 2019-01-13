/* ************************
 * pokemon.js
 *
 * Returns a random pokemon of a given type
 */

var fn = require('./functions');
var numeral = require('numeral');

module.exports = {
	minArgs: 0, 
	helpText: [
		'!pokemon [type]'
	],
	command: function (args, bot, conn) {
		
		if (args[1] == "glitch") {
			conn.query({
				sql: 'SELECT * FROM `poke_data` WHERE poke_id >= 1000',
				values: []
			}, function (error, results, fields) {
				
				if (results.length == 0) {
					bot.scyther(args, "There are no Pokemon of that type.");
				} else {
					var random = results[fn.randomIntInc(0,results.length-1)];
					
					args.image = [random['poke_id'], random['alt_id'], 'normal'];
					
					if (random.alter_poke_name != null)
						random.poke_name += random.alter_poke_name;
					
					bot.scyther(args, random.poke_name);
				}
				
			});
		} else if (typeof args[1] !== "undefined") {
			args[1] = fn.encodeHTML(args[1].toLowerCase());
			
			conn.query({
				sql: 'SELECT * FROM `poke_data` WHERE `type_1` = ? OR `type_2`=?',
				values: [args[1], args[1]]
			}, function (error, results, fields) {
				
				if (results.length == 0) {
					bot.scyther(args, "There are no Pokemon of that type.");
				} else {
					var random = results[fn.randomIntInc(0,results.length-1)];
					
					args.image = [random['poke_id'], random['alt_id'],'normal'];
					
					if (random.alter_poke_name != null)
						random.poke_name += random.alter_poke_name;
					
					bot.scyther(args, random.poke_name);
				}
				
			});
		} else  {
			conn.query({
				sql: 'SELECT * FROM `poke_data`',
				values: []
			}, function (error, results, fields) {
				
				if (results.length == 0) {
					bot.scyther(args, "There are no Pokemon of that type.");
				} else {
					var random = results[fn.randomIntInc(0,results.length-1)];
					
					args.image = [random['poke_id'], random['alt_id'], 'normal'];
					
					if (random.alter_poke_name != null)
						random.poke_name += random.alter_poke_name;
					
					bot.scyther(args, random.poke_name);
				}
				
			});
		}
	}
}