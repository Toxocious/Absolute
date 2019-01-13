/* * * * * * * *
 * level.js for Scyther
 * 
 * Converts levels into exp
 * 
 * !pickaxe (pickaxe Level) [Compare] [clan|personal]
 */
 
var numeral = require('numeral');
var fn = require('./functions');

function pickaxe (i,t) {
	if (t == 'clan') {
		var cost = 0;
		for (q = 1; q <= i;q++) {
			cost += (5000*q);
		}
	} else {
		if (i== 0) {
			var cost = 3500;
		} else {
			var cost = 0;
			for (q = 1; q <= i;q++) {
				cost += (350*q);
			}
		}
	}
	return cost;
}

module.exports = {
	minArgs: 1,
	helpText: [
		'!pickaxe (pickaxe Level) [Compare] [clan|personal]',
		'The pickaxe command returns the difference in ores between 2 pickaxe levels.',
		'By adding "clan" on the end it will calculate for clan pickaxe levels.'
	],
	command: function (args, bot, conn) {
		if (typeof args[args.length-1] !== "undefined" && args[args.length-1] == "clan") {
			t = 'clan';
		}  else t = 'personal';
		
		if (typeof args[2] === "undefined") {
			bot.scyther(args, "Ores: "+numeral(pickaxe(args[1], t)).format('0,0'));
		} else {
			var total = 0, l = 0;
			for(ql = args[1]; ql < args[2]; ql++) {
				total += pickaxe(ql, t); l ++;
			}
			
			bot.scyther(args, "Total Ores: "+numeral(total).format('0,0')+"; Last Upgrade: "+numeral(pickaxe(args[2], t)).format('0,0'));
		}
	}
};
