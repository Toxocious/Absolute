<?php

/* *****************
 * !battle
 *
 * Determines the number of battles you have to do to get from level x to level x or an experience.
 * Also calculates time if you give it battle length in seconds
 */

class Battle extends Command
{
  public $stat = 'battle';
  public $min_arguments = 2;
  public $help_text = Array(
    '!tl [level] #(Trainer ID) (exp)xp (exp boost)x'
  );

  public function execute_command($args)
  {
    global $PDO;
    global $userClass;
    global $pokeClass;

    $Battle = Array(
      'TrainerID' => 2,
      'Level' => $args[1]
      'Exp' => 0,
      'BattleCount' => 0,
      'ExpMod' => 1
    );

    if (is_numeric($args[2]))
      $Battle['Level'][1] = Text($args[2])->num();

    foreach($args as $key => $arg)
    {
      if (strpos($arg, 'x') !== false)
        $Battle['ExpMod'] *= $arg;
      else if (strpos($arg, '#') !== false)
        $Battle['TrainerID'] = Text($arg)->num();
    }

    if ($Battle['Level'][0] > 20)
    {
      $this->say("Whoops, too big!");
      return;
    }

    $exp = abs(getExpFromLevel($Battle['Level'], 'trainer') );

    try {
      $Select = $PDO->prepare("SELECT id FROM pokemon WHERE `trainer_id`=? AND `location`='party' LIMIT 6");
      $Select->execute(array($Battle['TrainerID']));
      $Select->setFetchMode(PDO::FETCH_ASSOC);
      $Roster = $Select->fetchAll();
    }
    catch (PDOException $e) {
      handleError($e);
    }

    $ExpTotal = 0;
    foreach ($Roster as $key=>$Poke)
    {
      $PokeInfo = $pokeClass->GetPokeInfo($Poke['id']);
      $ExpTotal += battleExperienceFormula($PokeInfo) * $Battle['ExpMod'];

      if ($key == 0)
        $image = Array($PokeInfo['PokeID'], $PokeInfo['AltID'], $PokeInfo['Type']);
    }

    if ($ExpTotal == 0) {
      $this->say("The user doesn't exist.");
      return;
    }

    $Battles = ceil($exp / $ExpTotal);

    $_SESSION['format'] = 'shortened';
    $string = "Exp.: ".Format($exp);
    $_SESSION['format'] = 'normal';
    $string .= ", Battles: ".Format($Battles);
    $string .= ", Battle Exp.: ".Format($ExpTotal);
    if ($Battle['ExpMod'] != 1)
    $string .= ", ".Format($Battle['ExpMod'])."x";

    $this->say($string, $image);
  }
}






/* *****************
 * tl.js
 *
 * Determines the number of battles you have to do to get from level x to level x or an experience.
 * Also calculates time if you give it battle length in seconds
 */
var numeral = require('numeral');
var fn = require('./functions');

function parse(args, bot) {
  var Battle = {
    id: 294,
    expMod: 1
  };

  var arrayLength = args.length;
  for (var i = 1; i < arrayLength; i++) {
    if (args[i].indexOf('#') !== -1 && fn.isNumeric(args[i].replace('#', '').replace(',', ''))) {
      Battle.id = args[i].replace('#', '').replace(',','');
    }
    else if (args[i].indexOf('x') !== -1 && fn.isNumeric(args[i].replace('x', '').replace('.',''))) {
      Battle.expMod *= (args[i].replace('x', ''));
    }
  }

  return Battle;
}

module.exports = {
  minArgs: 1,
  helpText:[
    ''
  ],
  command: function(args, bot, conn) {
    if (typeof args[2] === "undefined" )
      args[2] = '`'; //not a valid username character

    var Battle = parse(args, bot);
    var user;
    conn.query({
      sql: 'SELECT * FROM `users` WHERE `user_name` = ? LIMIT 1',
      values: [fn.encodeHTML(args[2])]
    }, function (error, result, fields) {
      if (result.length == 0 && args[2] != '`') {
        bot.scyther(args, "This user does not exist.");
        return;
      }
      if (args[2] != '`')
        user = result[0];
      else
        user = {
          'user_name': 'Nobody',
          'trainer_exp': 0
        };

      if (args[1] <= 0) {
        bot.scyther (args, user['user_name']+" has already reached Trainer Level "+args[1]+".");
        return;
      }

      Battle.xp = (parseInt(fn.getExp(parseInt(args[1]), 'trainer')) - parseInt(user['trainer_exp']));
      console.log((parseInt(fn.getExp(parseInt(args[1]), 'trainer'))+' '+ parseInt(user['trainer_exp'])));

      if (Battle.xp+"" == "NaN") {
        bot.scyther(args, user['user_name']+" will never reach Trainer Level "+args[1]+".");
        return;
      }
      if (Battle.xp < 0) {
        bot.scyther (args, user['user_name']+" has already reached Trainer Level "+args[1]+".");
        return;
      }

      fn.battleRoster(Battle, conn, function(Battle, Party){
        TotalExp = 0;
        for(i=0; i<Party.length; i++) {
          TotalExp += Party[i]['Exp'];
        }


        if (Battle.xp != 0)
          var Exp = Battle.xp;
        else {
          bot.scyther(args, "Trainer ID: #"+Battle.id+", Battle Exp.: "+numeral(TotalExp).format('0,0'));
          return;
        }

        if (Exp <= 0) Exp *= -1;
        var BattleCount = Exp / TotalExp;

        if (Math.floor(BattleCount) != BattleCount) {
          var diff = TotalExp * Math.floor(BattleCount);
          var extraKill = 0;
          for(i=0; i<Party.length; i++) {
            extraKill++;
            diff += Party[i]['Exp'];
            if (diff >= Exp)
              break;
          }

          if (extraKill == 6)
            extraKill = '';
          else
            extraKill = " + "+extraKill+"/"+Party.length;
        } else
          extraKill = '';

        if (Battle.expMod != 1)
          extraMod = ", Exp. Boost: "+Battle.expMod+"x";
        else
          extraMod = '';

        bot.scyther(args, "Battles: "+numeral(Math.floor(BattleCount)).format('0,0')+extraKill+ "; Exp. Earned: "+numeral(Math.floor(Exp)).format('0,0.[00]a')+"; Trainer ID: #"+Battle.id+"; Battle Exp.: "+numeral(TotalExp).format('0,0') + extraMod);
        return;
      });

  });
  }
};
