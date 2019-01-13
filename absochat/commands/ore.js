/* * * * * * * *
 * ore.js for Scyther
 * 
 * Tells you how much ores someone has
 */
 
var numeral = require('numeral');
var fn = require('./functions');

function getOre(ores, ore) {
	ores = ores.split(',');

	switch(ore) {
		case 'Copper': return ores[0];
		case 'Iron': return ores[1];
		case 'Nickel': return ores[2];
		case 'Tin': return ores[3];
		case 'Coal': return ores[4];
		case 'Chromium': return ores[5];
		case 'Silver': return ores[6];
		case 'Cobalt': return ores[7];
		case 'Iridium': return ores[8];
		case 'Gold': return ores[9];
		case 'Titanium': return ores[10];
		case 'Platinum': return ores[11];
	}
	return 0;
}


module.exports = {
	minArgs: 1,
	helpText: [
		'!ore [oreType] [Username/ID]',
		'Returns how much of an ore someone has'
	],
	command: function (args, bot, conn) {	
		oreType = fn.ucfirst(args[1].toLowerCase());
		if (getOre('1,1,1,1,1,1,1,1,1,1,1,1', oreType) == 0) {
			thing = ['a muffin', 'a cord', 'a large sticker', 'a ratchet', 'a printer', 'a fruit', 'a drink', 'a person', 'an animal', 'a computer', 'a bed', 'a cinder block', 'a remote', 'a wagon', 'an outlet', 'a supermall', 'a tape', 'a pen', 'an album', 'a mp3 player', 'a lost cause', 'a hole', 'a table', 'a Pokemon', 'a shovel', 'a piece of black bread, which has molded for far to long, and is too beyond repair to consider eating it', 'a orange baseplate']
			bot.scyther(args, "This is not an ore. Maybe its "+thing[fn.randomIntInc(0, thing.length-1)]+"?");
			return;
		}
	
		if (args.length == 2) {
			conn.query({
				sql: 'SELECT ores FROM users WHERE ores != ? AND banned != ?',
				values: ['0,0,0,0,0,0,0,0,0,0,0,0', 'yes']
			}, function(error, results, fields) {
				sum = 0
				for(i in results) {
					if (results.hasOwnProperty(i) ){
						sum += parseInt(getOre(results[i]['ores'], oreType));
					}
				}
				
				bot.scyther(args, 'Total '+oreType+': '+numeral(sum).format('0,0'));
			});
			return;
		}
		
		
		conn.query({
			sql: 'SELECT * FROM `users` WHERE `user_name` = ? LIMIT 1',
			values: [args[2]]
		}, function (error, results, fields) {
			if (results.length == 0) {
				conn.query({
					sql: 'SELECT * FROM `users` WHERE `user_name` = ? LIMIT 1',
					values: [args[2]]
				}, function (error, results, fields) {
					if (results.length == 0) {
						bot.scyther(args, "This user does not exist.");
					} else {
						callback(results[0], oreType);
					}
				});
			} else {
				callback(results[0], oreType);
			}
		});
		
		function callback(user, oreType) {
			var r = getOre(user['ores'], oreType);
			bot.scyther(args, user['user_name'] +"'s "+oreType+': '+numeral(r).format('0,0'));
		}
	}
};
