/* *****************************
 * !OHKO a long awaited command
 
 8:23:52 PM <~xmex> !Ohko SoullessMachamp 255EV 31IV 150Power Fighting 
8:24:05 PM <~xmex> I guess from there I could actually use those stats on the live blisseys in Jim
8:24:36 PM <~xmex> Then you'd lose prediction abilities
 
 
 */
var numeral = require('numeral');
var fn = require('./functions');

var MoveTypes = ["normal", "fighting", "flying", "poison", "ground", "rock", "bug", "ghost", "steel", "fire", "water", "grass", "electric", "psychic", "ice", "dragon", "dark", "fairy", "none"];
var NATURES = ['Lonely','Adamant','Naughty','Brave','Bold','Impish','Lax','Relaxed','Modest','Mild','Rash','Quiet','Calm','Gentle','Careful','Sassy','Timid','Hasty','Jolly','Naive','Bashful','Docile','Hardy','Quirky','Serious' ];

function parse(args, bot) {
	var OHKO = {
		id: '294',
		pokeName: args[1],
		alt: '',
		attackEV: 0,
		attackIV: 16,
		movePower: 90,
		moveType: 'Fighting',
		nature: 'Quirky',
		attackBonus: 0
	};
	
	var arrayLength = args.length;
	for (var i = 1; i < arrayLength; i++) {
		args[i] = args[i].toLowerCase();
		if (args[i].indexOf('(') !== -1 && args[i].indexOf(')') !== -1) {
			OHKO.alt = args[i].replace(' ', '').replace('(', '').replace(')', '');
		}
		
		if (args[i].indexOf('ev') !== -1 && fn.isNumeric(args[i].replace('ev', ''))) {
			if (args[i].replace('ev', '') <= 252)
				OHKO.attackEV = args[i].replace('ev', '');
			else {
				bot.scyther(args, 'The maximum EV is 252.');
				OHKO.attackEV = 252;
				
			}
		}
		if (args[i].indexOf('iv') !== -1 && fn.isNumeric(args[i].replace('iv', ''))) {
			if (args[i].replace('iv', '') <= 31)
				OHKO.attackIV = args[i].replace('iv', '');
			else {
				bot.scyther(args, 'The maximum IV is 31.');
				OHKO.attackIV = 31;
			}
		}
		
		if (args[i].indexOf('+') !== -1 && fn.isNumeric(args[i].replace('+', ''))) {
			if (args[i].replace('+', '') <= 20)
				OHKO.attackBonus = parseInt(args[i].replace('+', ''));
			else {
				bot.scyther(args, 'The maximum attack bonus is 20.');
				OHKO.attackBonus = 20;
			}
		}
		
		if (args[i].indexOf('power') !== -1 && fn.isNumeric(args[i].replace('power', '')))
			OHKO.movePower = args[i].replace('power', '');
		if ((args[i].indexOf('p') !== -1 && fn.isNumeric(args[i].replace('p', ''))))
			OHKO.movePower = args[i].replace('p', '');
		
		if (MoveTypes.indexOf(args[i]) !== -1)
			OHKO.moveType = args[i];
			
		if (NATURES.indexOf(fn.ucfirst(args[i])) !== -1)
			OHKO.nature = args[i];
			
			
		if (i != 1 && args[i].indexOf('#') !== -1 && fn.isNumeric(args[i].replace('#', '').replace(',', '')))
			OHKO.id = args[i].replace('#', '').replace(',','');
	}

	return OHKO;
}


module.exports = {
	minArgs: 1,
	helpText:[
		'!ohko [PokemonName] #(TrainerID) (Atk EV)EV (Atk IV)IV (Move Power)Power (Move Type) (Your Nature) +(Attack Boost)',
		'This returns the OHKO level of a Pokemon fighting Jim. All arguments after the',
		'Pokemons name can be in any order. Defaults: #294 90Power 0EV 16IV Quirky Fighting +0'
	],
	command: function(args, bot, conn) {
		if (args.length < 2) {
			bot.scyther(args, 'Command not entered properly.');
			return;
		}
		
		//Parse the string for given commands into the OHKO object
		var OHKO = parse(args, bot);
		
		if (args[1].indexOf('#') !== -1 && fn.isNumeric(args[1].replace('#', '').replace(',', ''))) {
			conn.query({
				sql: "SELECT * FROM `pokemon` WHERE `id` = ?",
				values: [''+args[1].replace('#', '').replace(',','')]
			}, function  (error, results, fields) {
				if (typeof results === "undefined" || results.length == 0) {
					bot.scyther(args, "This Pokemon ID does not exist.");
					return ;
				}
				
				Poke = results[0];
				Poke['EVs'] = Poke['ev'].split(',');
				Poke['IVs'] = Poke['iv'].split(',');	
					OHKO.attackEV = Poke['EVs'][1];
					OHKO.attackIV = Poke['IVs'][1];
					OHKO.nature   = Poke['nature']; 
				conn.query({
					sql: 'SELECT id FROM `poke_data` WHERE `poke_id` = ? AND  alt_id=? LIMIT 1',
					values: [ Poke['poke_id'], Poke['alt_id'] ]
				}, function (error2, results2, fields2) {
					OHKO.pokeName = Poke['type']+''+results2[0]['id']+'';
					goOnAgain();
				});
			});
		} else goOnAgain();
		
		function goOnAgain() {
		//Remove types from the pokemon name
		if (OHKO.pokeName.substring(0,5).toLowerCase()  == 'shiny')
		{
			var PokeName = OHKO.pokeName.substring(5);			OHKO.statMod = 3;  OHKO.Type = 'Shiny'; 
		}
		else if (OHKO.pokeName.substring(0,5).toLowerCase() == 'cloud')
		{
			var PokeName = OHKO.pokeName.substring(5);			OHKO.statMod = 12; OHKO.Type = 'Cloud'; 
		}
		else if (OHKO.pokeName.substring(0,6).toLowerCase()  == 'forest')
		{
			var PokeName = OHKO.pokeName.substring(6);			OHKO.statMod = 9; OHKO.Type = 'Forest'; 
		}
		else if (OHKO.pokeName.substring(0,8).toLowerCase()  == 'soulless')
		{
			var PokeName = OHKO.pokeName.substring(8);			OHKO.statMod = 6; OHKO.Type = 'Soulless'; 
		}
		else if (OHKO.pokeName.substring(0,6).toLowerCase()  == 'spirit')
		{
			var PokeName = OHKO.pokeName.substring(6);			OHKO.statMod = 15; OHKO.Type = 'Spirit'; 
		}
		else if (OHKO.pokeName.substring(0,6).toLowerCase() == 'normal')
		{
			var PokeName = OHKO.pokeName.substring(6);			OHKO.statMod = 0; OHKO.Type = ''; 
		}
		else
		{
			var PokeName = OHKO.pokeName;						OHKO.statMod = 0; OHKO.Type = ''; 
		}
		
		if (PokeName.toLowerCase() == "farfetch'd") {
			PokeName = "83";
		}
		
		PokeName = fn.encodeHTML(PokeName);
		OHKO.alt = fn.encodeHTML(OHKO.alt);
		
		if (OHKO.alt == '') {
			var sql = 'SELECT * FROM `poke_data` WHERE `poke_name` = ? ORDER BY alt_id ASC LIMIT 1';
			var input = [PokeName];
		} else {
			var sql = 'SELECT * FROM `poke_data` WHERE `poke_name` = ? AND `alter_poke_name` LIKE ? LIMIT 1';
			var input = [PokeName, '%'+fn.ucfirst(OHKO.alt)+'%'];
		}
		//Query for the Pokemon Data
		conn.query({
			sql: sql,
			values: input
		}, function (error, results, fields) {
			if (results.length == 0) {
				conn.query({
					sql: 'SELECT * FROM `poke_data` WHERE `poke_name` = ? AND `alter_poke_name` = ? LIMIT 1',
					values: [PokeName, args[2]]
				}, function (error, results, fields) {
					if (results.length == 0) {
						conn.query({
							sql: 'SELECT * FROM `poke_data` WHERE `alter_poke_name` = ? LIMIT 1',
							values: [PokeName]
						}, function (error, results, fields) {
							if (results.length == 0) {
								conn.query({
									sql: 'SELECT * FROM `poke_data` WHERE `alter_poke_name` = ? LIMIT 1',
									values: [PokeName.replace('Unown', '')]
								}, function (error, results, fields) {
									if (results.length == 0) {
										conn.query({
											sql: 'SELECT * FROM `poke_data` WHERE `id` = ? LIMIT 1',
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
			} else 
				callback(results[0]);
		});
		
		
		function callback(db) {
			OHKO.AttackerInfo = db;
			
			args.image = [db['poke_id'], db['alt_id'], OHKO.Type];
				
			Pokemon = fn.battleRoster(OHKO, conn, function(OHKO, Party) {
				var eff = 1,
					OHKOLevels = [],
					STAB = 1,
					AttackerInfo = OHKO.AttackerInfo;
				
				if (OHKO.moveType.toLowerCase() == 'fighting') eff = 2;
				if (OHKO.moveType.toLowerCase() == 'ghost') eff = 0.000001;
				if (AttackerInfo['type_1'].toLowerCase() == OHKO.moveType.toLowerCase() || AttackerInfo['type_2'].toLowerCase() == OHKO.moveType.toLowerCase())
					STAB = 1.5
					
				for(i=0; i<Party.length; i++) {
					var BlisseyHP = Party[i]['Stats'][0],
						DefenseStat = Party[i]['Stats'][2],
						AttackerLevel = 0;

						
					while (fn.damageFormula(AttackerLevel, OHKO.movePower, fn.GetStat('Attack', AttackerInfo['attack'] + OHKO.statMod + OHKO.attackBonus, AttackerLevel, OHKO.attackIV, OHKO.attackEV, OHKO.nature), DefenseStat, eff, STAB, 1, 1, 185/200) < BlisseyHP) {
						AttackerLevel += 100;
					}
					
					AttackerLevel -= 100;
					while (fn.damageFormula(AttackerLevel, OHKO.movePower, fn.GetStat('Attack', AttackerInfo['attack'] + OHKO.statMod + OHKO.attackBonus, AttackerLevel, OHKO.attackIV, OHKO.attackEV, OHKO.nature), DefenseStat, eff, STAB, 1, 1, 185/200) < BlisseyHP) {
						AttackerLevel++;
					}
				
					OHKOLevels[i] = AttackerLevel;
				}
				
				var trueOHKO = OHKOLevels.reduce(function(previous,current){ 
				  return previous > current ? previous:current
			   });
								
				var strongestBlissey = Party[OHKOLevels.indexOf(trueOHKO)]
				
				PokeName = db['poke_name'];
				
				bot.scyther(args, fn.ucfirst(OHKO.Type)+fn.sayPokeName(PokeName) + ' OHKOs level '+numeral(Math.floor(Math.pow(strongestBlissey['experience']+1, 1/3))).format('0,0')+' '+ fn.ucfirst(strongestBlissey['type'])+ fn.ucfirst(strongestBlissey['poke_name'])+' at level  '+numeral(trueOHKO).format('0,0')+'. Exp.: ('+numeral(Math.pow(trueOHKO, 3)).format('0,0.[00]a')+')');
			});
		}
	}
	}
};





