/* ************************
 * whatdo.js
 * 
 * Helps b0sh figure out what do
 */
 
var fn = require('./functions');

module.exports = {
	minArgs: 1,
	helpText: [
		'!whatdo [option 1], [option 2], [option 3]...',
		'Randomly selects a item from a list to help figure out what do.',
		'By using commas inbetween the options, you can have spaces inside whatdo text.'
	],
	command: function (args, bot, conn) {
		var Do = args.join(' ').replace('whatdo ', '').split(',');

		if (Do.length == 0)
			return;
		
		if (Do.length == 1) {
			Do = Do[0].split(' ');
		}
		
		var Option = Do[fn.randomIntInc(0, Do.length-1)];
		
		bot.scyther(args, Option.trim() +' is what do.');
	}
};