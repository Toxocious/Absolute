/* **********
 * signals.js
 * s
 * Takes in messages from TPK sent via PHP and converts sends them as IRC messages
 */
var fn = require('./functions');

module.exports = {
	input: function (message, bot) {
		//For now limit to #TPK, later possible a second channel for this stuff?
		bot.scyther({from:'#TPK'}, message);
	}
}