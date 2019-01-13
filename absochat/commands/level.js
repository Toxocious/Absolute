/* * * * * * * *
 * level.js for Scyther
 * 
 * Converts levels into exp
 * 
 * !level (System) [Level] (Level to compare)
 *
 * Also works with trainers
 *
 * !level (System) [Username/ID] [Username/ID to compare]
 */
 
var numeral = require('numeral');
var fn = require('./functions');

var TYPES = [
	'pokemon', 'trainer', 'mining', 'mine', 'mines', 'smelting', 'map', 'maps', 'fishing', 'clan'
];
var USER_DB = [
	'','trainer_exp', 'mine_exp', 'mine_exp', 'mine_exp', '', 'map_exp', 'map_exp', 'fishing_exp', 'clan_exp_earn'
];
var TYPE_TEXT = [
	'','Trainer Exp.', 'Mining Exp.','Mining Exp.','Mining Exp.', '', 'Map Exp.', 'Map Exp.', 'Fishing Exp.', 'Clan Exp.'
];

module.exports = {
	minArgs: 1,
	helpText: [
		'!level (System) [Level] (Level to compare)',
		'The level command returns the Exp. required to reach a level. It can',
		'also compare to levels and return the diffrernce. If you specify a system',
		'it can use a different level formula. Supported Systems:',
		'Pokemon, Trainer, Mine, Map, Fishing, Clan'
	],
	command: function (args, bot, conn) {		
		var TypeID = TYPES.indexOf(args[1].toLowerCase());
		if (TypeID !== -1) {
			var type = args[1].toLowerCase();
			
			args.splice(1,1);
		} 
		
		if (fn.isNumeric(args[1])) {
			if (TypeID == -1) {
				var type = 'pokemon';
			}
			var Level = args[1];
			var Exp = fn.getExp(Level, type);

			if (args[2] !== "undefined" && fn.isNumeric(args[2]))
			{
				Exp = Exp - fn.getExp(args[2], type);
				if (Exp <= 0) 
					Exp *= -1;
			}

			bot.scyther(args, "Exp.: "+numeral(Exp).format('0,0.[00]a')); 
		} else {
			if (TypeID == -1) {
				
				var TypeID = TYPES.indexOf('trainer');
				var type = 'trainer';
				
			}
			args[1] = fn.encodeHTML(args[1]);
			conn.query({
				sql: 'SELECT * FROM `users` WHERE `user_name` = ? LIMIT 1',
				values: [args[1]]
			}, function (error, results, fields) {
				if (results.length == 0)
					bot.scyther(args, args[1]+' does not exist.');
				else if (typeof args[2] !== "undefined" && !fn.isNumeric(args[2])) {
					var UserOne = results[0];
					
			args[2] = fn.encodeHTML(args[2]);
					conn.query({
						sql: 'SELECT * FROM `users` WHERE `user_name` = ? LIMIT 1',
						values: [args[2]]
					}, function (error, results, fields) {
						if (results.length == 0) {
							bot.scyther(args, args[2]+' does not exist.');
						} else {
							var UserTwo = results[0];
							
							
							var U1 = UserOne[USER_DB[TypeID]];
							var U2 = UserTwo[USER_DB[TypeID]];
							if ( U1 > U2) {
								bot.scyther(args, UserOne['user_name'] + ' has '+numeral(U1 - U2).format('0,0')+' more '+TYPE_TEXT[TypeID]+' than '+UserTwo['user_name']);
							} else {
								bot.scyther(args, UserTwo['user_name'] + ' has '+numeral(U2 - U1).format('0,0')+' more '+TYPE_TEXT[TypeID]+' than '+UserOne['user_name']);
							}
						}
					});
				} else 
					bot.scyther(args, results[0]['user_name'] + " has "+numeral(results[0][USER_DB[TypeID]]).format('0,0')+" "+TYPE_TEXT[TypeID]);
			});

		}
	}
};
