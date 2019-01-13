/* *****************
 * battle.js
 * 
 * Determines the number of battles you have to do to get from level x to level x or an experience.
 * Also calculates time if you give it battle length in seconds
 */
var numeral = require('numeral');
var fn = require('./functions');

function parse(args, bot) {
	var Battle = {
		id: 294,
		level:0,
		xp: 0,
		levelFinish:0,
		battleLength: 0,
		battleCount: 0,
		expMod: 1
	};
	
	
	var arrayLength = args.length;
	for (var i = 1; i < arrayLength; i++) {
		if (args[i].indexOf('s') !== -1 && fn.isNumeric(args[i].replace('s', ''))) {
			Battle.battleLength = args[i].replace('s', '');
		}
		else if (args[i].indexOf('+') !== -1 && fn.isNumeric(args[i].replace('+', '').replace(',', ''))) {
			Battle.battleCount = args[i].replace('+', '').replace(',','');
		}
		else if (args[i].indexOf('battles') !== -1 && fn.isNumeric(args[i].replace('battles', '').replace(',', ''))) {
			Battle.battleCount = args[i].replace('battles', '').replace(',','');
		}
		else if (args[i].indexOf('battle') !== -1 && fn.isNumeric(args[i].replace('battle', '').replace(',', ''))) {
			Battle.battleCount = args[i].replace('battle', '').replace(',','');
		}
		else if (args[i].indexOf('#') !== -1 && fn.isNumeric(args[i].replace('#', '').replace(',', ''))) {
			Battle.id = args[i].replace('#', '').replace(',','');
		}
		else if (args[i].indexOf('xp') !== -1 && fn.isNumeric(args[i].replace('xp', ''))) {
			Battle.xp = parseInt(args[i].replace('xp', ''));
		} 
		else if (args[i].indexOf('b') !== -1 && fn.isNumeric(args[i].replace('b', ''))) {
			Battle.xp = 1000000000*parseInt(args[i].replace('b', ''));
		} 
		else if (args[i].indexOf('x') !== -1 && fn.isNumeric(args[i].replace('x', '').replace('.',''))) {
			Battle.expMod *= (args[i].replace('x', ''));
		} else if (fn.isNumeric(args[i])) {
			if (Battle.level == 0)
				Battle.level = args[i];
			else
				Battle.levelFinish = args[i];
		}
	}
	
	return Battle;
}

module.exports = {
	minArgs: 0,
	helpText:[
		'!battle [Level] [Level Compare] #(Trainer ID) (exp)xp (exp boost)x'
	],
	command: function(args, bot, conn) {
		if (args.length < 1) {
			bot.scyther(args, 'Command not entered properly.');
			return;
		}
		
		//Parse the string for given commands into the OHKO object
		var Battle = parse(args, bot);

		//Query for the Pokemon Data
		Battle.id = fn.encodeHTML(""+Battle.id);
		conn.query({
			sql: 'SELECT * FROM `users` WHERE `user_id` = ? LIMIT 1',
			values: [Battle.id]
		}, function (error, results, fields) {
			if (results.length != 0) {
				fn.battleRoster(Battle, conn, function(Battle, Party) {
					TotalExp = 0;
					for(i=0; i<Party.length; i++) {
						TotalExp += Party[i]['Exp'];
					}
					
					if (Battle.xp != 0)
						var Exp = Battle.xp;
					else if (Battle.level != 0) {
						if (Battle.levelFinish != 0) {
							var Exp = fn.getExp(Battle.level, 'pokemon') - fn.getExp(Battle.levelFinish, 'pokemon');
						} else {
							var Exp = fn.getExp(Battle.level, 'pokemon');
						}
					} else {
						bot.scyther(args, "Trainer ID: #"+Battle.id+", Battle Exp.: "+numeral(TotalExp).format('0,0'));
						return;
					}
					
					if (Battle.battleCount != 0) {
					
					
						if (Battle.battleCount <= 0) Battle.battleCount *= -1;
					
						if (Battle.level != 0) {
							var EXP = fn.getExp(Battle.level, 'pokemon') + Battle.battleCount*TotalExp;
							var newLevel = fn.getLevel(EXP, 'pokemon');
							var EXP = Battle.battleCount*TotalExp;

							if (Battle.expMod != 1)
								extraMod = "; Exp. Boost: "+Battle.expMod+"x";
							else
								extraMod = '';
							
							bot.scyther(args, "Level: "+numeral(Math.floor(newLevel)).format('0,0')+"; Exp. Earned: "+numeral(Math.floor(EXP)).format('0,0.[00]a')+"; Trainer ID: #"+Battle.id+"; Battle Exp.: "+numeral(TotalExp).format('0,0') + extraMod);
						} else {
							var EXP = Battle.battleCount*TotalExp;
							var newLevel = fn.getLevel(EXP, 'pokemon');
							var EXP = Battle.battleCount*TotalExp;
				
							if (Battle.expMod != 1)
								extraMod = "; Exp. Boost: "+Battle.expMod+"x";
							else
								extraMod = '';
							
							bot.scyther(args, "Level: "+numeral(Math.floor(newLevel)).format('0,0')+"; Exp. Earned: "+numeral(Math.floor(EXP)).format('0,0.[00]a')+"; Trainer ID: #"+Battle.id+"; Battle Exp.: "+numeral(TotalExp).format('0,0') + extraMod);
						}
						return;
					}
					
					if (Exp <= 0) Exp *= -1;
					var BattleCount = Exp / TotalExp;
				
					if (Math.floor(BattleCount) != BattleCount) {
						var diff = TotalExp * Math.floor(BattleCount);
						var extraKill = 0;
						for(i=0; i<Party.length; i++) {
							extraKill++;
							diff += Party[i]['Exp'];
							if (diff >= Exp)
								break;
						}
						
						if (extraKill == 6)
							extraKill = '';
						else 
							extraKill = " + "+extraKill+"/"+Party.length;
					} else 
							extraKill = '';
			
			
					if (Battle.expMod != 1)
						extraMod = "; Exp. Boost: "+Battle.expMod+"x";
					else
						extraMod = '';
					
					bot.scyther(args, "Battles: "+numeral(Math.floor(BattleCount)).format('0,0')+extraKill+"; Exp. Earned: "+numeral(Math.floor(Exp)).format('0,0.[00]a')+"; Trainer ID: #"+Battle.id+"; Battle Exp.: "+numeral(TotalExp).format('0,0') + extraMod);
					return;
				});
			} else {
				bot.scyther(args, "This user does not exist.");
			}
		});
	}
};
