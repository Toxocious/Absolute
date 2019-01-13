/* *****************
 * battle.js
 * 
 * Determines the number of battles you have to do to get from level x to level x or an experience.
 * Also calculates time if you give it battle length in seconds
 */
var numeral = require('numeral');
var fn = require('./functions');

var NATURES = ['Lonely','Adamant','Naughty','Brave','Bold','Impish','Lax','Relaxed','Modest','Mild','Rash','Quiet','Calm','Gentle','Careful','Sassy','Timid','Hasty','Jolly','Naive','Bashful','Docile','Hardy','Quirky','Serious' ];

module.exports = {
	minArgs: 1,
	helpText:[
		'!nature [Nature]',
		'Tells the bonuses of a nature.'
	],
	command: function(args, bot, conn) {
		Nature = fn.ucfirst(args[1].toLowerCase());
		NatureKey = NATURES.indexOf(Nature);
		if (NatureKey == -1) {
			bot.scyther(args, "There is no such nature!");
			return false;
		} 
		
		if (Nature == 'Naughty') {
			Nature = 'Naughty (^_~)';
		}
		
		output = "Neutral";
		if (NatureKey >= 0 && NatureKey <= 3) output = "+10% Attack";
		if (NatureKey >= 4 && NatureKey <= 7) output = "+10% Defense";
		if (NatureKey >= 8 && NatureKey <= 11) output = "+10% Sp. Atk.";
		if (NatureKey >= 12 && NatureKey <= 15) output = "+10% Sp. Def.";
		if (NatureKey >= 16 && NatureKey <= 19) output = "+10% Speed";
		
		if (NatureKey == 4 || NatureKey == 8 || NatureKey == 12 || NatureKey == 16) output += ", -10% Attack";
		if (NatureKey == 0 || NatureKey == 9 || NatureKey == 13 || NatureKey == 17) output += ", -10% Defense";
		if (NatureKey == 1 || NatureKey == 5 || NatureKey == 14 || NatureKey == 18) output += ", -10% Sp. Atk.";
		if (NatureKey == 2 || NatureKey == 6 || NatureKey == 10 || NatureKey == 19) output += ", -10% Sp. Def.";
		if (NatureKey == 3 || NatureKey == 7 || NatureKey == 11 || NatureKey == 15) output += ", -10% Speed";
		
		bot.scyther(args, Nature+": "+output);
	}
};