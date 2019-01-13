/* ********************
 * okho.js
 *
 * Because the typos are real.
 */
module.exports = {
	minArgs: 0,
	hidden: true,
	helpText:[
		'!okho',
		'Command used to prevent mispelling. B0sh fails at spelling.'
	],
	command: function (args, bot, conn) {
		bot.scyther(args, 'Whoops! Did you mean !ohko?');
	}
}