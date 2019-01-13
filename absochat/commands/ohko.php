<?php

/* *****************************
 * !OHKO a long awaited command

 8:23:52 PM <~xmex> !Ohko SoullessMachamp 255EV 31IV 150Power Fighting
8:24:05 PM <~xmex> I guess from there I could actually use those stats on the live blisseys in Jim
8:24:36 PM <~xmex> Then you'd lose prediction abilities


 */

global $root;
require $root."/core/battles/battle.class.inc.php";

class Ohko extends Command
{
  public $stat = 'ohko';
  public $min_arguments = 1;
  public $help_text = [
    '!ohko [PokemonName] #(TrainerID) (Atk EV)EV (Atk IV)IV (Move Power)Power (Move Type) (Your Nature) +(Attack Boost) (other mod)x',
    'Calculates the OHKO level of a Pokemon. All arguments after the Pokemons name can be in any order. Defaults: #294 90Power 0EV 16IV Quirky Fighting +0',
  ];

  public function execute_command($args)
  {
    global $PDO;
    global $pokeClass;
    $battleClass = new Battle();

    $Battle = [
      'ClanID' => 0,
      'TrainerID' => 2,
      'PokeData' => $PokeData,
      'AttackEV' => 0,
      'AttackIV' => 20,
      'AttackBase' => 0,
      'MovePower' => 90,
      'OtherMod' => 1,
      'MoveType' => "Fighting",
      'Nature' => 'Quirky',
    ];

    foreach ($args as $key => $arg) {
      $arg = strtolower($arg);
//      $this->l($arg);
      if (strpos($arg, '#') !== false && $key == 1) {
        $Battle['PokemonID'] = Text($arg)->num();
      } elseif ($key == 1) {
        $dontunset = true;
      } //the first argument is special
      elseif (strpos($arg, '(') !== false or strpos($arg, ')') !== false) {
        $dontunset = true;
      } // Charizard (mega y) handling
      elseif (strpos($arg, '##') !== false) {
        $Battle['ClanID'] = Text($arg)->num();
      } elseif (strpos($arg, '#') !== false) {
        $Battle['TrainerID'] = Text($arg)->num();
      } elseif (strpos($arg, 'ev') !== false) {
        $Battle['AttackEV'] = Text($arg)->num();
      } elseif (strpos($arg, 'iv') !== false) {
        $Battle['AttackIV'] = Text($arg)->num();
      } elseif (strpos($arg, '+') !== false) {
        $Battle['AttackBase'] = Text($arg)->num();
      } elseif (strpos($arg, 'x') !== false) {
        $Battle['OtherMod'] = Text($arg)->in();
      } elseif (strpos($arg, 'p') !== false) {
        $Battle['MovePower'] = Text($arg)->num();
      } elseif (strpos($arg, 'power') !== false) {
        $Battle['MovePower'] = Text($arg)->num();
      } elseif (in_array(ucfirst($arg), $pokeClass->Natures())) {
        $Battle['Nature'] = ucfirst(Text($arg)->in());
      } elseif (in_array($arg, ["normal", "fighting", "flying", "poison", "ground", "rock", "bug", "ghost", "steel", "fire", "water", "grass", "electric", "psychic", "ice", "dragon", "dark", "fairy", "none"])) {
        $Battle['MoveType'] = ucfirst(Text($arg)->in());
      } else {
        $dontunset = true;
      }

      if (isset($dontunset)) {
        unset($dontunset);
      } else {
        unset($args[$key]);
      }
    }

    if ($Battle['AttackEV'] > 255) {
      $this->say("Attack EV lowered to 255.");
      $Battle['AttackEV'] = 255;
    }
    if ($Battle['AttackIV'] > 31) {
      $this->say("Attack IV lowered to 31.");
      $Battle['AttackIV'] = 31;
    }

    if ($Battle['PokemonID'] > 0) {
      $PokeInfo = $pokeClass->GetPokeInfo($Battle['PokemonID']);
      if ($PokeInfo == "Error") {
        $this->say("This Pokemon ID does not exist.");

        return;
      }
      $PokeData = $pokeClass->GetPokeData($PokeInfo['PokeID'], $PokeInfo['AltID'], $PokeInfo['Type'], $PokeInfo['Subtype']);
      $Battle['Nature'] = $PokeInfo['Nature'];
      $Battle['AttackBase'] = $PokeInfo['Base'][1];
      $Battle['AttackEV'] = $PokeInfo['EVs'][1];
      $Battle['AttackIV'] = $PokeInfo['IVs'][1];
      $Battle['Type'] = $PokeInfo['Type'];
    } else {
      unset($args[0]);
      $type = 'normal';
      $input = strtolower($args[1]);

      foreach ($pokeClass->Type() as $key => $t) {
        $t = strtolower($t);
        if (str_replace($t, '', $input) != $input) {
          $input = (str_replace($t, '', $input));
          $type = $t;
          break;
        }
      }

      $extra = '';
      foreach ($args as $key => $arg) {
        if ($arg == 'all') {
          $all = 'all';
          $type = 'normal';
          unset($args[$key]);
        } elseif ($key >= 2) {
          $extra .= $arg.' ';
        }
      }

      $this->l(print_r([$input, trim($extra)], true));

      $Battle['Type'] = $type;

      $SelectPokeData = $GlobalPDO->prepare("SELECT * FROM `poke_data` WHERE (`poke_name`=? AND `alter_poke_name`=?) LIMIT 1");
      $SelectPokeData->execute([$input, trim($extra)]);
      $SelectPokeData->setFetchMode(PDO::FETCH_ASSOC);
      $PokeData = $SelectPokeData->fetch();
      $PokeData = $pokeClass->GetPokeData($PokeData['poke_id'], $PokeData['alt_id'], $type);

      if (!isset($PokeData['id'])) {
        $SelectPokeData = $GlobalPDO->prepare("SELECT * FROM `poke_data` WHERE (`poke_name`=? or `scyther_name`=?) LIMIT 1");
        $SelectPokeData->execute([$input, $input]);
        $SelectPokeData->setFetchMode(PDO::FETCH_ASSOC);
        $PokeData = $SelectPokeData->fetch();
        $PokeData = $pokeClass->GetPokeData($PokeData['poke_id'], $PokeData['alt_id'], $type);
      }

      if ($PokeData == false) {
        $this->say("The Pokemon could not be found.");

        return true;
      }

      $Battle['AttackBase'] += $PokeData['attack'];
      if (isset($Constants->POKEMON_COLOR_STAT_MOD[$Battle['Type']])) {
        $Battle['AttackBase'] += $Constants->POKEMON_COLOR_STAT_MOD[$Battle['Type']];
      }
    }

    //  $this->l(print_r($Battle, true));

    try {
      if ($Battle['ClanID'] != 0) {
        $Select = $PDO->prepare("SELECT id FROM pokemon WHERE `clan_id`=? AND `location`='party' LIMIT 6");
        $Select->execute([$Battle['ClanID']]);
      } else {
        $Select = $PDO->prepare("SELECT id FROM pokemon WHERE `trainer_id`=? AND `location`='party' LIMIT 6");
        $Select->execute([$Battle['TrainerID']]);
      }
      $Select->setFetchMode(PDO::FETCH_ASSOC);
      $Roster = $Select->fetchAll();
    } catch (PDOException $e) {
      handleError($e);
    }

    // no available Pokes to run with
    if (count($Roster) == 0) {
      if ($Battle['ClanID'] != 0) {
        $this->say("There are no Pokemon in this clan's vault roster.");
      } else {
        $this->say("There are no Pokemon in this trainer's party.");
      }

      return;
    }

    if ($Battle['MoveType'] == $PokeData['type_1'] or $Battle['MoveType'] == $PokeData['type_2']) {
      $STAB = 1.5;
    } else {
      $STAB = 1;
    }

    $Other = $Battle['OtherMod'];

    // loop through all of the pokemon in the enemy roster and find ohko level
    $HighestOHKO = 0;
    $HighestTxt = "";

    foreach ($Roster as $key => $Poke) {
      $PokeInfo = $pokeClass->GetPokeInfo($Poke['id']);
      $Eff = $battleClass->TypeEffectiveness($Battle['MoveType'], $PokeInfo['Type1'], $PokeInfo['Type2'], 'none');

      $Level = 0;
      do {
        $Level += 10;
        $Atk = $pokeClass->GetStat("Attack", $Battle['AttackBase'], $Level, $Battle['AttackIV'], $Battle['AttackEV'], $Battle['Nature']);
        $Damage = $battleClass->damageFormula($Level, $Battle['MovePower'], $Atk, $PokeInfo['Stats'][2], $Eff, $STAB, 1, $Other);
      } while ($PokeInfo['Stats'][0] > $Damage);

      if ($HighestOHKO < $Level) {
        $HighestOHKO = $Level;
        $HighestTxt = $PokeInfo['DisplayName'].' (Level: '.Format($PokeInfo['Level']).')';
//        $this->l (print_r(Array("Attack", $Battle['AttackBase'], $Level, $Battle['AttackIV'], $Battle['AttackEV'], $Battle['Nature']), true));
//        $this->l (print_r(Array($Battle['MoveType'], $PokeInfo['Type1'], $PokeInfo['Type2'], 'none'), true));
//        $this->l (print_r(Array($Level, $Battle['MovePower'], $Atk, $PokeInfo['Stats'][2], $Eff, $STAB, 1, $Other), true));
      }
    }

    $heigh = Format($HighestOHKO);
    $_SESSION['format'] = 'shortened';
    $this->say("Level: ".$heigh." (".Format(getExpFromLevel($HighestOHKO, 'pokemon'))." xp) :: ".$PokeData['Fullname']." vs. ".$HighestTxt, [$PokeData['poke_id'], $PokeData['alt_id'], $Battle['Type']]);
    $_SESSION['format'] = 'normal';
  }
}
