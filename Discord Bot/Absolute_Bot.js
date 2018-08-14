/* =====================================================================================
                            BOT COMMAND IDEAS AND STUFF
----------------------------------------------------------------------------------------
      -> ~user
      --> gather more data from the database about each user
      ---> member rank ( member, chat mod, game master, admin )
      ---> roster info ( pokemon in slots 1 through 6 )
      ---> clan info ( what clan they're in )

      ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

      -> ~trivia <start/stop>
      --> start the trivia bot
      ---> will have a bunch of preloaded questions about pokemon probs
      ---> upon the question being answered correctly, close the chat until
           the next question is ready
      ---> winner get's 'x' amount of discord currency
           this can be transfered to their rpg account to buy stuff with

      ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

      -> ~item <name> || ~equip <name>
      --> essentially lets you look up the stats, description, etc of a given item
          so that you can see everything about it
      --> won't be possible until items and equips are all in the database

      ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

      -> ~rarity <pokedex id/name>
      --> if you typo a pokemon name, have the script look for close matches 
          that it can suggest to you

      ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

      -> ~ban <ban type> <discord name> <unban time/date or something>
      --> allows a staff member to ban the user on discord AND the rpg

      --> ban type has three options
      ---> rpg      = bans the user on the rpg
      ---> discord  = bans the user from the discord server
      ---> both     = bans the user from both the rpg and the discord server

      --> allow time gated bans
      ---> give the staff member the option to ban until a certain date
           or for a specific given period of time:
           you can ban someone until next sunday
                        OR
           you can ban someone for the next 15 minutes
      ---> the bot will auto give the appropriate role(s) back to the banned user
           upon them being unbanned, if ever
                        OR
           if they leave and then rejoin the server, the correct roles will be
           given back to them
===================================================================================== */

const Discord = require('discord.js');
const client = new Discord.Client();
const TOKEN = 'MjY5MjU0ODI3OTg3NDM1NTIx.DVmdYA.KzqHQ7rwh89fF8cALFm12tDj1ws';
const PREFIX = '~';

const YTDL = require('ytdl-core');
const mysql = require('mysql');
const mysqli = require('mysqli');
const sequelize = require('sequelize');
const connection = mysql.createConnection({
  host     : 'localhost',
  port     : '3306',
  user     : 'root',
  password : '',
  database : 'absolute_original',
  charset : 'utf8mb4_general_ci'
});

var servers = {};
var welcome = 'Welcome to Absolute, ';
var whatdo = [ "yeah", "sure", "no", "negative", "AHHHHHHHHHHHHHHHHHHH", "what we gotta do", "HAHAHAHA", "you think i'm laying?", "GET THIS THROUGH YOUR HEAD", "maybe if i bust your head through that window, you'll get unconfused" ];

client.on('ready', () =>
{
  client.user.setActivity('Absolute RPG | ~help');
  console.log('Absolute Bot has started.');
  console.log('=========================');
});

client.on('message', async message =>
{
  console.log(message.author.tag + ": " + message.content);

  if ( message.author.bot )
    return;

  if ( !message.content.startsWith(PREFIX) )
    return;

  var args = message.content.substring(PREFIX.length).split(" ");

  switch ( args[0].toLowerCase() )
  {
    case "help":
      const embed = new Discord.RichEmbed()
        .setAuthor(`Absolute Bot's Command List`)
        .setColor("#4A618F")
        .setThumbnail('https://www.smogon.com/dex/media/sprites/xy/Absol.gif')
        .setTimestamp()
        .addBlankField(true)
        .addField("~help", `List all of the possible commands that Absolute Bot has.`)
        .addField("~shelp", `List all available staff member commands.`)
        .addField("~link <code>", `Link your Discord account to your Absolute RPG account.`)
        .addField("~user <discord user>", `Display the appropriate Discord user's RPG information.`)
        .addField("~rarity <pokemon id/name>", `Display the rarity information of a given Pokemon.`)
        .addField("~equip <id/name>", `Display the stats, etc of a given piece of equipment.`)
        .addField("~8ball <thingy>", `Responds 'Yes', 'No', or 'Maybe' to the given parameter.`)
        .addField("~whatdo <option1, option2, etc>", `Have Absolute Bot randomly choose a provided option for you.`)
        .addField("~yplay <youtube url>", `Add a song/video to Absolute Bot's music queue.`)
        .addField("~yskip", `Skip the current song in Absolute Bot's music queue.`)
        .addField("~ystop", `Stops the current music queue.`)
        .addBlankField(true);
  
      message.reply({ embed });
      break;

    case "shelp":
      message.reply("needs fixed up later nigga");
      break;

    case "whatdo":
      message.reply("ok this doesnt work yet but dw about it");

      break;

    case "8ball":
      if ( args[1] ) message.reply( whatdo[Math.floor(Math.random() * whatdo.length)] );
      else message.reply("I expect at least one parameter to execute this command.");
      
      break;
    
    case "link":
      var linkCode = args[1];
      connection.query(`SELECT Discord_UserID, Discord_User FROM members WHERE Discord_Token = ${linkCode}`, function(err, result)
      {
        if ( err )
        {
          message.reply('an error has occurred when attempting to link your account.');
        }
        else
        {
          var user_id = message.author.id;
          var user_tag = message.author.tag;
    
          connection.query(`UPDATE members SET Discord_UserID = '` + user_id + `', Discord_User = '` + user_tag + `' WHERE Discord_Token = ${linkCode}`);
          message.reply('you have successfully linked your Discord account to your Absolute RPG account.');
        }
      });
      break;

    case "user":
      var getUserID1 = args[1];
      var getUserID2 = getUserID1.split("<@")[1].split(">")[0];

      connection.query(`SELECT id, Username, Rank, Clan, Money, Last_Online, Last_Page, Avatar FROM members WHERE Discord_UserID = ${getUserID2}`, function (err, result)
      {
        if ( err )
        {
          message.reply('the specified user has not been found.');
        }
        else
        {
          var string = JSON.stringify(result);
          var json = JSON.parse(string);
          
          if ( json[0].id == null )           json[0].id          = 'Unknown';
          if ( json[0].Username == null )     json[0].Username    = 'Unknown';
          if ( json[0].Avatar == null )       json[0].Avatar      = 'images/Avatars/Sprites/1.png';
          if ( json[0].Money == null )        json[0].Money       = 'Unknown';
          if ( json[0].Clan == null )         json[0].Clan        = 'Unknown';
          if ( json[0].Rank == null )         json[0].Rank        = 'Unknown';
          if ( json[0].Last_Page == null )    json[0].Last_Page   = 'Unknown';
          if ( json[0].Last_Online == null )  json[0].Last_Online = 'Unknown';

          connection.query(`SELECT Pokedex_ID, Type, Slot, Owner_Current, Level, Experience FROM pokemon WHERE Slot < 7 AND Owner_Current = ${json[0].id}`, function(err, result1)
          {
            if ( err )
            {
              message.reply("the specified Pokemon have not been found.");
            }
            else
            {
              var string1 = JSON.stringify(result1);
              var json1 = JSON.parse(string1);
              var i = 0;
              var end = json1.length;
              console.log("JSON1");
              console.log("===============================");
              console.log(json1);
              console.log("===============================");

              for ( i = 0; i < json1.length; i++ )
              {
                if ( json1[i].Pokedex_ID === undefined )      return json1[i].Pokedex_ID    = '1';
                if ( json1[i].Type === undefined )            return json1[i].Type          = 'Unknown';
                if ( json1[i].Slot === undefined )            return json1[i].Slot          = '0';
                if ( json1[i].Owner_Current === undefined )   return json1[i].Owner_Current = '0';
                if ( json1[i].Level === undefined )           return json1[i].Level         = '0';
                if ( json1[i].Experience === undefined )      return json1[i].Experience    = '0';
              } 

              if ( json[0].Rank === '420' ) json[0].Rank === "Administrator";
              else if ( json[0].Rank === '69' ) json[0].Rank === "Game Master";
              else if ( json[0].Rank === '420' ) json[0].Rank === "Chat Moderator";
              else json[0].Rank === "Member";

              if ( json[0].Clan < 1 ) json[0].Clan = "Clanless";
              else json[0].Clan = "Unknown Clan";

              const embed = new Discord.RichEmbed()
                .setAuthor(`${json[0].Username}'s RPG Overview`)
                .setColor("#4A618F")
                .setThumbnail(`https://absobeta.gwiddle.co.uk/${json[0].Avatar}`)
                .setTimestamp()
                .addField("Account ID", `${addCommas(json[0].id)}`, true)
                .addField("Account Username", `${json[0].Username}`, true)
                .addBlankField(true)
                .addField("Rank", `${json[0].Rank}`)
                .addField("Account Money", `$${addCommas(json[0].Money)}`, true)
                .addBlankField(true)
                .addField("Clan", `${json[0].Clan}`)
                .addBlankField(true)
                .addField("Last Online", `${json[0].Last_Online}`, true)
                .addField("Last Page", `${json[0].Last_Page}`, true)
                .addBlankField(true)
                .addBlankField(true)
                .addField("Roster", '~~')
                .addField("#1", `https://www.serebii.net/pokedex-xy/icon/${json1[0].Pokedex_ID}.png`)
                .addField("#2", `https://www.serebii.net/pokedex-xy/icon/${json1[1].Pokedex_ID}.png`)
                .addField("#3", `https://www.serebii.net/pokedex-xy/icon/${json1[2].Pokedex_ID}.png`)
                .addField("#4", `https://www.serebii.net/pokedex-xy/icon/${json1[3].Pokedex_ID}.png`)
                .addField("#5", `https://www.serebii.net/pokedex-xy/icon/${json1[4].Pokedex_ID}.png`)
                .addField("#6", `https://www.serebii.net/pokedex-xy/icon/${json1[5].Pokedex_ID}.png`);

              message.reply({ embed });
            }
          });
        }
      });
      break;
    
    case "rarity":
      var pokemon = args[1];
      var rarities = {
        "total": 0,
        "normal": 0,
        "shiny": 0,
        "sunset": 0,
        "shinysunset": 0,
      };

      pokemon = pokemon.substr(0,1).toUpperCase()+pokemon.substr(1);

      if ( pokemonArray.indexOf(pokemon) == -1 ) return message.reply("this is not a valid Pokemon.");

      connection.query(`SELECT ID, Name FROM Pokedex WHERE ID = '${pokemon}' OR Name = '${pokemon}'`, function (err, result)
      {
        if ( err ) 
        {
          message.reply('the specified Pokemon is unable to be found. (Err #1)');
        }
        else
        {
          var string = JSON.stringify(result);
          var json = JSON.parse(string);
          console.log(json);
          
          connection.query(`SELECT Pokedex_ID, Type FROM Pokemon WHERE Pokedex_ID = ${json[0].ID}`, function (err, result1)
          {
            if ( err ) 
            {
              message.reply('the specified Pokemon is unable to be found. (Err #2)');
            }
            else
            {
              var string1 = JSON.stringify(result1);
              var json1 = JSON.parse(string1);
              console.log( json1 );

              var getDate = new Date();

              for ( i = 0; i < json1.length; i++ )
              {
                if ( json1[i].Type === 'Normal' )         rarities.normal++;
                if ( json1[i].Type === 'Shiny' )          rarities.shiny++;
                if ( json1[i].Type === 'Sunset' )         rarities.sunset++;
                if ( json1[i].Type === 'Shiny Sunset' )   rarities.shinysunset++;
              }
              rarities.total = rarities.normal + rarities.shiny + rarities.sunset + rarities.shinysunset;

              if ( rarities.total == null )                 rarities.total           = 'Unknown';
              if ( rarities.normal == null )                rarities.normal          = 'Unknown';
              if ( rarities.shiny == null )                 rarities.shiny           = 'Unknown';
              if ( rarities.sunset == null )                rarities.sunset          = 'Unknown';
              if ( rarities.shinysunset == null )           rarities.shinysunset     = 'Unknown';

              var pokemonName = json[0].Name.toLowerCase();

              const embed = new Discord.RichEmbed()
              .setAuthor(`${json[0].Name}'s Rarity Data`, `https://www.smogon.com/dex/media/sprites/xy/${pokemonName}.gif`)
              .setColor("#4A618F")
              .setImage(`https://www.smogon.com/dex/media/sprites/xy/${pokemonName}.gif`)
              .setThumbnail(`https://www.smogon.com/dex/media/sprites/xy/${pokemonName}.gif`)
              .setTimestamp()
              .addField("Total", `${addCommas(rarities.total)}`)
              .addField("Normal", `${addCommas(rarities.normal)}`, true)
              .addField("Shiny", `${addCommas(rarities.shiny)}`, true)
              .addField("Sunset", `${addCommas(rarities.sunset)}`, true)
              .addField("Shiny Sunset", `${addCommas(rarities.shinysunset)}`, true)
              .addBlankField(true);
          
              message.reply({ embed });
            }
          });
        }
      });
      break;

    case "cwelcome":
      if (
        message.member.roles.find('name', 'Chat Moderator') || 
        message.member.roles.find('name', 'Game Master') || 
        message.member.roles.find('name', 'Developer')
      )
      {
        welcome = message.content.slice(10);
        message.reply("you've successfully changed the welcome message to: **'" + welcome + "'**.");
      }
      else
      {
        message.reply("you lack the sufficient power to use this command.");
      }
      break;

    case "yplay":
      if ( !args[1] ) return message.reply("please provide a proper link.");
      //if ( !message.member.voiceChannel ) return message.reply("you must be in a voice channel to use this command.");
      if ( !servers[message.guild.id] ) servers[message.guild.id] = { queue: [] };
      
      var server = servers[message.guild.id];

      server.queue.push( args[1] );

      if ( !message.guild.voiceConnection ) message.member.voiceChannel.join().then(function(connection) { play(connection, message) });

      break;

    case "yskip":
      var server = servers[message.guild.id];
      if ( server.dispatcher ) server.dispatcher.end();
      message.reply("the current song in the queue has been skipped.");

      break;

    case "ystop":
      var server = servers[message.guild.id];
      if ( message.guild.voiceConnection ) message.guild.voiceConnection.disconnect();
      if ( message.guild.voiceConnection ) servers[message.guild.id] = { queue: [] };
      message.reply("you have successfully stopped the current music queue.");

      break;

    case "yqueue":
      var server = servers[message.guild.id];
      
      console.log( servers[message.guild.id] );
      break;

    default:
      message.reply("you have tried to use an invalid command.");
  }
});

client.on('guildMemberAdd', member =>
{
  const channel = member.guild.channels.find('name', 'welcome');
  if (!channel) return;
  channel.send( welcome + member );
});

function play(connection, message)
{
  var server = servers[message.guild.id];

  server.dispatcher = connection.playStream( YTDL( server.queue[0], {filter: "audioonly"} ) );

  server.queue.shift();

  server.dispatcher.on('end', function() {
    if ( server.queue[0] ) play(connection, message);
    else setTimeout(function() { connection.disconnect(); }, 300000);
  });
}

function addCommas(x)
{
	return x.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
}

var pokemonArray = ["Bulbasaur",  "Ivysaur",  "Venusaur",  "Charmander",  "Charmeleon",  "Charizard",  "Squirtle",  "Wartortle",  "Blastoise",  "Caterpie",  "Metapod",  "Butterfree",  "Weedle",  "Kakuna",  "Beedrill",  "Pidgey",  "Pidgeotto",  "Pidgeot",  "Rattata",  "Raticate",  "Spearow",  "Fearow",  "Ekans",  "Arbok",  "Pikachu",  "Raichu",  "Sandshrew",  "Sandslash",  "Nidoran(F)",  "Nidorina",  "Nidoqueen",  "Nidoran(M)",  "Nidorino",  "Nidoking",  "Clefairy",  "Clefable",  "Vulpix",  "Ninetales",  "Jigglypuff",  "Wigglytuff",  "Zubat",  "Golbat",  "Oddish",  "Gloom",  "Vileplume",  "Paras",  "Parasect",  "Venonat",  "Venomoth",  "Diglett",  "Dugtrio",  "Meowth",  "Persian",  "Psyduck",  "Golduck",  "Mankey",  "Primeape",  "Growlithe",  "Arcanine",  "Poliwag",  "Poliwhirl",  "Poliwrath",  "Abra",  "Kadabra",  "Alakazam",  "Machop",  "Machoke",  "Machamp",  "Bellsprout",  "Weepinbell",  "Victreebel",  "Tentacool",  "Tentacruel",  "Geodude",  "Graveler",  "Golem",  "Ponyta",  "Rapidash",  "Slowpoke",  "Slowbro",  "Magnemite",  "Magneton",  "Farfetch'd",  "Doduo",  "Dodrio",  "Seel",  "Dewgong",  "Grimer",  "Muk",  "Shellder",  "Cloyster",  "Gastly",  "Haunter",  "Gengar",  "Onix",  "Drowzee",  "Hypno",  "Krabby",  "Kingler",  "Voltorb",  "Electrode",  "Exeggcute",  "Exeggutor",  "Cubone",  "Marowak",  "Hitmonlee",  "Hitmonchan",  "Lickitung",  "Koffing",  "Weezing",  "Rhyhorn",  "Rhydon",  "Chansey",  "Tangela",  "Kangaskhan",  "Horsea",  "Seadra",  "Goldeen",  "Seaking",  "Staryu",  "Starmie",  "Mr. Mime",  "Scyther",  "Jynx",  "Electabuzz",  "Magmar",  "Pinsir",  "Tauros",  "Magikarp",  "Gyarados",  "Lapras",  "Ditto",  "Eevee",  "Vaporeon",  "Jolteon",  "Flareon",  "Porygon",  "Omanyte",  "Omastar",  "Kabuto",  "Kabutops",  "Aerodactyl",  "Snorlax",  "Articuno",  "Zapdos",  "Moltres",  "Dratini",  "Dragonair",  "Dragonite",  "Mewtwo",  "Mew",  "Chikorita",  "Bayleef",  "Meganium",  "Cyndaquil",  "Quilava",  "Typhlosion",  "Totodile",  "Croconaw",  "Feraligatr",  "Sentret",  "Furret",  "Hoothoot",  "Noctowl",  "Ledyba",  "Ledian",  "Spinarak",  "Ariados",  "Crobat",  "Chinchou",  "Lanturn",  "Pichu",  "Cleffa",  "Igglybuff",  "Togepi",  "Togetic",  "Natu",  "Xatu",  "Mareep",  "Flaaffy",  "Ampharos",  "Bellossom",  "Marill",  "Azumarill",  "Sudowoodo",  "Politoed",  "Hoppip",  "Skiploom",  "Jumpluff",  "Aipom",  "Sunkern",  "Sunflora",  "Yanma",  "Wooper",  "Quagsire",  "Espeon",  "Umbreon",  "Murkrow",  "Slowking",  "Misdreavus",  "Unown",  "Wobbuffet",  "Girafarig",  "Pineco",  "Forretress",  "Dunsparce",  "Gligar",  "Steelix",  "Snubbull",  "Granbull",  "Qwilfish",  "Scizor",  "Shuckle",  "Heracross",  "Sneasel",  "Teddiursa",  "Ursaring",  "Slugma",  "Magcargo",  "Swinub",  "Piloswine",  "Corsola",  "Remoraid",  "Octillery",  "Delibird",  "Mantine",  "Skarmory",  "Houndour",  "Houndoom",  "Kingdra",  "Phanpy",  "Donphan",  "Porygon 2",  "Stantler",  "Smeargle",  "Tyrogue",  "Hitmontop",  "Smoochum",  "Elekid",  "Magby",  "Miltank",  "Blissey",  "Raikou",  "Entei",  "Suicune",  "Larvitar",  "Pupitar",  "Tyranitar",  "Lugia",  "Ho-Oh",  "Celebi",  "Treecko",  "Grovyle",  "Sceptile",  "Torchic",  "Combusken",  "Blaziken",  "Mudkip",  "Marshtomp",  "Swampert",  "Poochyena",  "Mightyena",  "Zigzagoon",  "Linoone",  "Wurmple",  "Silcoon",  "Beautifly",  "Cascoon",  "Dustox",  "Lotad",  "Lombre",  "Ludicolo",  "Seedot",  "Nuzleaf",  "Shiftry",  "Taillow",  "Swellow",  "Wingull",  "Pelipper",  "Ralts",  "Kirlia",  "Gardevoir",  "Surskit",  "Masquerain",  "Shroomish",  "Breloom",  "Slakoth",  "Vigoroth",  "Slaking",  "Nincada",  "Ninjask",  "Shedinja",  "Whismur",  "Loudred",  "Exploud",  "Makuhita",  "Hariyama",  "Azurill",  "Nosepass",  "Skitty",  "Delcatty",  "Sableye",  "Mawile",  "Aron",  "Lairon",  "Aggron",  "Meditite",  "Medicham",  "Electrike",  "Manectric",  "Plusle",  "Minun",  "Volbeat",  "Illumise",  "Roselia",  "Gulpin",  "Swalot",  "Carvanha",  "Sharpedo",  "Wailmer",  "Wailord",  "Numel",  "Camerupt",  "Torkoal",  "Spoink",  "Grumpig",  "Spinda",  "Trapinch",  "Vibrava",  "Flygon",  "Cacnea",  "Cacturne",  "Swablu",  "Altaria",  "Zangoose",  "Seviper",  "Lunatone",  "Solrock",  "Barboach",  "Whiscash",  "Corphish",  "Crawdaunt",  "Baltoy",  "Claydol",  "Lileep",  "Cradily",  "Anorith",  "Armaldo",  "Feebas",  "Milotic",  "Castform",  "Kecleon",  "Shuppet",  "Banette",  "Duskull",  "Dusclops",  "Tropius",  "Chimecho",  "Absol",  "Wynaut",  "Snorunt",  "Glalie",  "Spheal",  "Sealeo",  "Walrein",  "Clamperl",  "Huntail",  "Gorebyss",  "Relicanth",  "Luvdisc",  "Bagon",  "Shelgon",  "Salamence",  "Beldum",  "Metang",  "Metagross",  "Regirock",  "Regice",  "Registeel",  "Latias",  "Latios",  "Kyogre",  "Groudon",  "Rayquaza",  "Jirachi",  "Deoxys",  "Turtwig",  "Grotle",  "Torterra",  "Chimchar",  "Monferno",  "Infernape",  "Piplup",  "Prinplup",  "Empoleon",  "Starly",  "Staravia",  "Staraptor",  "Bidoof",  "Bibarel",  "Kricketot",  "Kricketune",  "Shinx",  "Luxio",  "Luxray",  "Budew",  "Roserade",  "Cranidos",  "Rampardos",  "Shieldon",  "Bastiodon",  "Burmy",  "Wormadam (Plant Cloak)",  "Mothim",  "Combee",  "Vespiquen",  "Pachirisu",  "Buizel",  "Floatzel",  "Cherubi",  "Cherrim",  "Shellos",  "Gastrodon",  "Ambipom",  "Drifloon",  "Drifblim",  "Buneary",  "Lopunny",  "Mismagius",  "Honchkrow",  "Glameow",  "Purugly",  "Chingling",  "Stunky",  "Skuntank",  "Bronzor",  "Bronzong",  "Bonsly",  "Mime Jr.",  "Happiny",  "Chatot",  "Spiritomb",  "Gible",  "Gabite",  "Garchomp",  "Munchlax",  "Riolu",  "Lucario",  "Hippopotas",  "Hippowdon",  "Skorupi",  "Drapion",  "Croagunk",  "Toxicroak",  "Carnivine",  "Finneon",  "Lumineon",  "Mantyke",  "Snover",  "Abomasnow",  "Weavile",  "Magnezone",  "Lickilicky",  "Rhyperior",  "Tangrowth",  "Electivire",  "Magmortar",  "Togekiss",  "Yanmega",  "Leafeon",  "Glaceon",  "Gliscor",  "Mamoswine",  "Porygon-Z",  "Gallade",  "Probopass",  "Dusknoir",  "Froslass",  "Rotom",  "Uxie",  "Mesprit",  "Azelf",  "Dialga",  "Palkia",  "Heatran",  "Regigigas",  "Giratina",  "Cresselia",  "Phione",  "Manaphy",  "Darkrai",  "Shaymin",  "Arceus",  "Victini",  "Snivy",  "Servine",  "Serperior",  "Tepig",  "Pignite",  "Emboar",  "Oshawott",  "Dewott",  "Samurott",  "Patrat",  "Watchog",  "Lillipup",  "Herdier",  "Stoutland",  "Purrloin",  "Liepard",  "Pansage",  "Simisage",  "Pansear",  "Simisear",  "Panpour",  "Simipour",  "Munna",  "Musharna",  "Pidove",  "Tranquill",  "Unfezant",  "Blitzle",  "Zebstrika",  "Roggenrola",  "Boldore",  "Gigalith",  "Woobat",  "Swoobat",  "Drilbur",  "Excadrill",  "Audino",  "Timburr",  "Gurdurr",  "Conkeldurr",  "Tympole",  "Palpitoad",  "Seismitoad",  "Throh",  "Sawk",  "Sewaddle",  "Swadloon",  "Leavanny",  "Venipede",  "Whirlipede",  "Scolipede",  "Cottonee",  "Whimsicott",  "Petilil",  "Lilligant",  "Basculin",  "Sandile",  "Krokorok",  "Krookodile",  "Darumaka",  "Darmanitan",  "Maractus",  "Dwebble",  "Crustle",  "Scraggy",  "Scrafty",  "Sigilyph",  "Yamask",  "Cofagrigus",  "Tirtouga",  "Carracosta",  "Archen",  "Archeops",  "Trubbish",  "Garbodor",  "Zorua",  "Zoroark",  "Minccino",  "Cinccino",  "Gothita",  "Gothorita",  "Gothitelle",  "Solosis",  "Duosion",  "Reuniclus",  "Ducklett",  "Swanna",  "Vanillite",  "Vanillish",  "Vanilluxe",  "Deerling",  "Sawsbuck",  "Emolga",  "Karrablast",  "Escavalier",  "Foongus",  "Amoonguss",  "Frillish",  "Jellicent",  "Alomomola",  "Joltik",  "Galvantula",  "Ferroseed",  "Ferrothorn",  "Klink",  "Klang",  "Klinklang",  "Tynamo",  "Eelektrik",  "Eelektross",  "Elgyem",  "Beheeyem",  "Litwick",  "Lampent",  "Chandelure",  "Axew",  "Fraxure",  "Haxorus",  "Cubchoo",  "Beartic",  "Cryogonal",  "Shelmet",  "Accelgor",  "Stunfisk",  "Mienfoo",  "Mienshao",  "Druddigon",  "Golett",  "Golurk",  "Pawniard",  "Bisharp",  "Bouffalant",  "Rufflet",  "Braviary",  "Vullaby",  "Mandibuzz",  "Heatmor",  "Durant",  "Deino",  "Zweilous",  "Hydreigon",  "Larvesta",  "Volcarona",  "Cobalion",  "Terrakion",  "Virizion",  "Tornadus",  "Thundurus",  "Reshiram",  "Zekrom",  "Landorus",  "Kyurem",  "Keldeo",  "Meloetta",  "Genesect",  "Chespin",  "Quilladin",  "Chesnaught",  "Fennekin",  "Braixen",  "Delphox",  "Froakie",  "Frogadier",  "Greninja",  "Bunnelby",  "Diggersby",  "Fletchling",  "Fletchinder",  "Talonflame",  "Scatterbug",  "Spewpa",  "Vivillon",  "Litleo",  "Pyroar",  "Flabébé",  "Floette",  "Florges",  "Skiddo",  "Gogoat",  "Pancham",  "Pangoro",  "Furfrou",  "Espurr",  "Meowstic",  "Honedge",  "Doublade",  "Aegislash",  "Spritzee",  "Aromatisse",  "Swirlix",  "Slurpuff",  "Inkay",  "Malamar",  "Binacle",  "Barbaracle",  "Skrelp",  "Dragalge",  "Clauncher",  "Clawitzer",  "Helioptile",  "Heliolisk",  "Tyrunt",  "Tyrantrum",  "Amaura",  "Aurorus",  "Sylveon",  "Hawlucha",  "Dedenne",  "Carbink",  "Goomy",  "Sliggoo",  "Goodra",  "Klefki",  "Phantump",  "Trevenant",  "Pumpkaboo",  "Gourgeist",  "Bergmite",  "Avalugg",  "Noibat",  "Noivern",  "Xerneas",  "Yveltal",  "Zygarde",  "Diancie",  "Hoopa",  "Volcanion",  "Rowlet",  "Dartrix",  "Decidueye",  "Litten",  "Torracat",  "Incineroar",  "Popplio",  "Brionne",  "Primarina",  "Pikipek",  "Trumbeak",  "Toucannon",  "Yungoos",  "Gumshoos",  "Grubbin",  "Charjabug",  "Vikavolt",  "Crabrawler",  "Crabominable",  "Oricorio",  "Cutiefly",  "Ribombee",  "Rockruff",  "Lycanroc",  "Wishiwashi",  "Mareanie",  "Toxapex",  "Mudbray",  "Mudsdale",  "Dewpider",  "Araquanid",  "Fomantis",  "Lurantis",  "Morelull",  "Shiinotic",  "Salandit",  "Salazzle",  "Stufful",  "Bewear",  "Bounsweet",  "Steenee",  "Tsareena",  "Comfey",  "Oranguru",  "Passimian",  "Wimpod",  "Golisopod",  "Sandygast",  "Palossand",  "Pyukumuku",  "Type: Null",  "Silvally",  "Minior",  "Komala",  "Turtonator",  "Togedemaru",  "Mimikyu",  "Bruxish",  "Drampa",  "Dhelmise",  "Jangmo-o",  "Hakamo-o",  "Kommo-o",  "Tapu Koko",  "Tapu Lele",  "Tapu Bulu",  "Tapu Fini",  "Cosmog",  "Cosmoem",  "Solgaleo",  "Lunala",  "Nihilego",  "Buzzwole",  "Pheromosa",  "Xurkitree",  "Celesteela",  "Kartana",  "Guzzlord",  "Necrozma",  "Magearna",  "Marshadow"];

client.login(TOKEN);