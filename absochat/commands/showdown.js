/* **********************
 * wtc.js
 * 
 * Returns the current WTC Status
 */
 
var numeral = require('numeral');
var fn = require('./functions');

module.exports = {
	minArgs: 0,
	helpText: [
		'!showdown [next]',
		'Returns the status of the Clan Showdown.'
	],
	command: function (args, bot, conn) {
		var Response = "";
		
		conn.query({
			sql: "SELECT * FROM clans WHERE `clan_showdown`!=0 ORDER BY clan_showdown DESC LIMIT 3",
			values: []
		}, function (error, results, fields) {
			for (var i in results) {
				if (results.hasOwnProperty(i)) {
					switch (i) {
						case '0': Response += '1st '; break;
						case '1': Response += '2nd '; break;
						case '2': Response += '3rd '; break;
					}
					
					if (i != results.length-1) 
						Response += results[i]['clan_name'] + ' ('+(numeral(Math.floor(results[i]['clan_showdown']/1000)).format('0,0'))+'), ';
					else 						
						Response += results[i]['clan_name'] + ' ('+(numeral(Math.floor(results[i]['clan_showdown']/1000)).format('0,0'))+')';
				}
			}
			
			bot.scyther(args, Response);
		}); //Clan Results
	}
};