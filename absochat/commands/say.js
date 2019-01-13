/* ********************
 * say.js
 */
 
module.exports = {
	minArgs: 2,
	helpText: '!say [Channel] [Text...]',
	hidden: true,
	command: function (args, bot, conn) {
		var responseChannel = args[1];
		var response = args.shift().shift().replace(',', ' ');
			
		bot.scyther (responseChannel, response);
	}
}