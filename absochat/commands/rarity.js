/* **********************
 * rarity.js
 * 
 * Returns the rarity of a Pokemon
 */
 
var numeral = require('numeral');
var fn = require('./functions');

module.exports = {
	minArgs: 1,
	helpText: [
		'!rarity [PokemonName]',
		'Returns the rarity of a Pokemon as given in the Amount Viewer in the Pokedex of TPK.'
	],
	command: function (args, bot, conn) {
			
		conn.query({
			sql: 'SELECT * FROM `admin_options` LIMIT 1',
			values: []
		}, function (error, admim, fields) {
			if (admim[0]['crons'] == 1) {
				bot.scyther(args, 'Rarity information is unavailable due to daily maintenance.');
				return  false;
			}
		
		if (typeof args[2] !== "undefined" && args[2].replace('mega', '') != args[2]) {
			args[2].replace("mega", "Mega");
		}
		
			
		if (typeof args[7] !== "undefined") 
			var pokeName = args[1] + ' ' +args[2] + ' ' + args[3] + ' ' + args[4] + ' ' + args[5] + ' ' + args[6] + ' ' + args[7];
		else if (typeof args[6] !== "undefined") 
			var pokeName = args[1] + ' ' +args[2] + ' ' + args[3] + ' ' + args[4] + ' ' + args[5] + ' ' + args[6];
		else if (typeof args[5] !== "undefined") 
			var pokeName = args[1] + ' ' +args[2] + ' ' + args[3] + ' ' + args[4] + ' ' + args[5];
		else if (typeof args[4] !== "undefined") 
			var pokeName = args[1] + ' ' +args[2] + ' ' + args[3] + ' ' + args[4];
		else if (typeof args[3] !== "undefined") 
			var pokeName = args[1] + ' ' +args[2] + ' ' + args[3];
		else if (typeof args[2] !== "undefined") 
			var pokeName = args[1] + ' ' +args[2];
		else
			var pokeName = args[1];
		
		if (pokeName.substring(0,5).toLowerCase()  == 'shiny')
		{
			var PokeName = pokeName.substring(5);
			var Type = 'shiny';
		}
		else if (pokeName.substring(0,5).toLowerCase() == 'cloud')
		{
			var PokeName = pokeName.substring(5);
			var Type = 'cloud';
		}
		else if (pokeName.substring(0,6).toLowerCase()  == 'forest')
		{
			var PokeName = pokeName.substring(6);
			var Type = 'forest';
		}
		else if (pokeName.substring(0,8).toLowerCase()  == 'soulless')
		{
			var PokeName = pokeName.substring(8);
			var Type = 'soulless';
		}
		else if (pokeName.substring(0,6).toLowerCase()  == 'spirit')
		{
			var PokeName = pokeName.substring(6);
			var Type = 'spirit';
		}
		else if (pokeName.substring(0,6).toLowerCase() == 'normal')
		{
			var PokeName = pokeName.substring(6);
			var Type = 'normal';
		}
		else
		{
			var PokeName = pokeName;
			var Type = 'normal';
		}
		
		PokeName = fn.encodeHTML(PokeName);
		
		
		
		conn.query({
			sql: 'SELECT * FROM `poke_data` WHERE `poke_name` = ? LIMIT 1',
			values: [PokeName]
		}, function (error, results, fields) {
			if (results.length == 0) {
				conn.query({
					sql: 'SELECT * FROM `poke_data` WHERE `scyther_name` = ? LIMIT 1',
					values: [PokeName]
				}, function (error, results, fields) {
					if (results.length == 0) {
						conn.query({
							sql: 'SELECT * FROM `poke_data` WHERE `alter_poke_name` = ? LIMIT 1',
							values: [PokeName.replace('Unown', '')]
						}, function (error, results, fields) {
							if (results.length == 0) {
							conn.query({
								sql: 'SELECT * FROM `users` WHERE `user_name` = ? LIMIT 1',
								values: [PokeName]
							}, function (error, results, fields) {
								if (results.length == 0) {
									bot.scyther(args, args[1]+' does not exist.');
									return ;
								} else 
					callback(results[0]);
							});
						} else 
					callback(results[0]);
					});
				} else 
					callback(results[0]);
				});
			} else 
					callback(results[0]);
		});
		
		
		function callback(PokeData) {
			args.image = [PokeData['poke_id'], PokeData['alt_id'], Type];

			conn.query({
				sql: "SELECT * FROM `rarity` WHERE `type` = ? AND `poke_id` = ? AND `alt_id` = ?",
				values: [Type, PokeData['poke_id'], PokeData['alt_id']]
			}, function (error, results, fields) {
				//Handle User inputs to rarity
				//Easterr Eggs
				if (results.length == 0) {
					delete args.image;
					bot.scyther(args, args[1] + ' is not owned by anyone in TPK.');
				} else if (typeof results[0]['user_id'] !== "undefined") {
					var User = results[0];
					switch (User['gender']) {
						case 'm': bot.scyther(args, User['user_name']+' :: Total:1, M:1, F:0, G:0, (?):0'); break;
						case 'f': bot.scyther(args, User['user_name']+' :: Total:1, M:0, F:1, G:0, (?):0'); break;
						case 'u': bot.scyther(args, User['user_name']+' :: Total:1, M:0, F:0, G:1, (?):0'); break;
					}
				} else {
					var Rarity = results[0];
					
					if (Type == 'normal' || Type == '') var t = '';
						else t = fn.ucfirst(Type);
					
					var G = Rarity['rarity'].split(',');
					
					bot.scyther(args, t+ fn.ucfirst(PokeName) +' :: Total:'+numeral(G[4]).format('0,0')+' M:'+numeral(G[0]).format('0,0')+' F:'+numeral(G[1]).format('0,0')+' G:'+numeral(G[2]).format('0,0')+' (?):'+numeral(G[3]).format('0,0'));
				}
			});
		}
	});
	}
};