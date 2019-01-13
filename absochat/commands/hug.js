/* ********************
 * hug.js
 *
 * Requested by Starscream
 *
 * Gives internet hugs
 */
 
module.exports = {
	minArgs: 0,
	helpText: [
		'Who needs help with hugs?'
	],
	command: function(args, bot, conn) {
		if (typeof args[1] !== "undefined") {
			bot.scyther(args, "hugs "+args[1]+".");
		} else {
			bot.scyther(args, "hugs "+args.nick+".");
		}
	}
};