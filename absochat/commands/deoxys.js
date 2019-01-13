/* *************
 * deoxys.js
 *
 * B0sh says this is a joke
 */

module.exports = {
	minArgs: 0,
	helpText: [
		'!deoxys - Legacy puposes only.'
	],
	command: function (args, bot, conn) {
		var Day = (new Date().getDay());
		switch (Day) {
			case -1: var Deoxys = 'Normal'; break;
			case 1: var Deoxys = 'Attack'; break;
			case 2: var Deoxys = 'Defense'; break;
			case 3: var Deoxys = 'Speed'; break;
			case 4: var Deoxys = 'Attack'; break;
			case 5: var Deoxys = 'Speed'; break;
			case 6: var Deoxys = 'Defense'; break;
			case 0: var Deoxys = 'Speed'; break;
		}
		
		bot.scyther(args, "Today's Deoxys form is "+Day+Deoxys+" Form.");
	}
};