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
  public $min_arguments = 1;
  public $help_text = [
    '!battle [Level] [Level Compare] #(Trainer ID) (exp)xp (exp boost)x',
  ];

  public function execute_command($args)
  {
    global $PDO;
    global $userClass;
    global $pokeClass;

    $Battle = [
      'TrainerID' => 2,
      'Level' => [Text($args[1])->num(), 0],
      'Exp' => 0,
      'BattleCount' => 0,
      'ExpMod' => 1,
    ];

    if (is_numeric($args[2])) {
      $Battle['Level'][1] = Text($args[2])->num();
    } else {
      foreach ($args as $key => $arg) {
        if (strpos($arg, 'xp') !== false) {
          $Battle['Exp'] = Text($arg)->num();
        } elseif (strpos($arg, 'x') !== false) {
          $Battle['ExpMod'] *= $arg;
        } elseif (strpos($arg, '#') !== false) {
          $Battle['TrainerID'] = Text($arg)->num();
        } elseif ($arg != $this->short_number_parser($arg)) {
          $Battle['Exp'] = Text($this->short_number_parser($arg))->num();
        }
      }
    }

    if ($Battle['Level'][0] > 100000 || $Battle['Level'][1] > 100000) {
      $this->say("Whoops, too big!");

      return;
    }

    if ($Battle['Exp'] == 0) {
      $exp = abs(getExpFromLevel($Battle['Level'][0], 'pokemon') - getExpFromLevel($Battle['Level'][1], 'pokemon'));
    } else {
      $exp = $Battle['Exp'];
    }

    try {
      $Select = $PDO->prepare("SELECT id FROM pokemon WHERE `trainer_id`=? AND `location`='party' LIMIT 6");
      $Select->execute([$Battle['TrainerID']]);
      $Select->setFetchMode(PDO::FETCH_ASSOC);
      $Roster = $Select->fetchAll();
    } catch (PDOException $e) {
      handleError($e);
    }

    $ExpTotal = 0;
    foreach ($Roster as $key => $Poke) {
      $PokeInfo = $pokeClass->GetPokeInfo($Poke['id']);
      $ExpTotal += battleExperienceFormula($PokeInfo) * $Battle['ExpMod'];

      if ($key == 0) {
        $image = [$PokeInfo['PokeID'], $PokeInfo['AltID'], $PokeInfo['Type']];
      }
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
    if ($Battle['ExpMod'] != 1) {
      $string .= ", ".Format($Battle['ExpMod'])."x";
    }

    $this->say($string, $image);
  }
}
