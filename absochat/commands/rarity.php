<?php

/* **********************
 * !rarity
 *
 * Returns the rarity of a Pokemon
 */

class Rarity extends Command
{
  public $stat = 'rarity';
  public $min_arguments = 0;
  public $help_text = [
  ];

  public function execute_command($args)
  {
    global $PDO;
    global $GlobalPDO;
    global $pokeClass;

    unset($args[0]);
    $type = 'normal';
    $subtype = 'normal';
    $input = strtolower($args[1]);

    foreach ($pokeClass->Type() as $key => $t) {
      $t = strtolower($t);
      if (str_replace($t, '', $input) != $input) {
        $input = (str_replace($t, '', $input));
        $type = $t;
        break;
      }
    }

    foreach (['event'] as $key => $t) {
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

    if (strpos(strtolower($extra), '(retro)') !== false) {
      $subtype = 'retro';
      $extra = str_ireplace('(retro)', '', $extra);
    }

    try {
      $SelectPokeData = $GlobalPDO->prepare("SELECT * FROM `poke_data` WHERE (`poke_name`=? AND `alter_poke_name`=?) LIMIT 1");
      $SelectPokeData->execute([$input, trim($extra)]);
      $SelectPokeData->setFetchMode(PDO::FETCH_ASSOC);
      $PokeData = $SelectPokeData->fetch();
      $PokeData = $pokeClass->GetPokeData($PokeData['poke_id'], $PokeData['alt_id'], $type, $subtype);

      if (isset($PokeData['id'])) {
        if (isset($all)) {
          return $this->pokeAllRarity($PokeData);
        } else {
          return $this->pokeRarity($PokeData, $type, $subtype);
        }
      }

      $SelectPokeData = $GlobalPDO->prepare("SELECT * FROM `poke_data` WHERE (`poke_name`=? or `scyther_name`=?) LIMIT 1");
      $SelectPokeData->execute([$input, $input]);
      $SelectPokeData->setFetchMode(PDO::FETCH_ASSOC);
      $PokeData = $SelectPokeData->fetch();
      $PokeData = $pokeClass->GetPokeData($PokeData['poke_id'], $PokeData['alt_id'], $type, $subtype);

      if (isset($PokeData['id'])) {
        if (isset($all)) {
          return $this->pokeAllRarity($PokeData);
        } else {
          return $this->pokeRarity($PokeData, $type, $subtype);
        }
      }

      $SelectItemData = $GlobalPDO->prepare("SELECT * FROM `item_data` WHERE (`item_name`=?) LIMIT 1");
      $SelectItemData->execute([implode(' ', $args)]);
      $SelectItemData->setFetchMode(PDO::FETCH_ASSOC);
      $ItemData = $SelectItemData->fetch();

      if (isset($ItemData['id'])) {
        return $this->itemRarity($ItemData);
      }

      $SelectUserData = $PDO->prepare("SELECT * FROM `users` WHERE (`user_name`=?) LIMIT 1");
      $SelectUserData->execute([$args[1]]);
      $SelectUserData->setFetchMode(PDO::FETCH_ASSOC);
      $UserData = $SelectUserData->fetch();

      if (isset($UserData['user_id'])) {
        return $this->userRarity($UserData);
      }
    } catch (PDOException $e) {
      handleError($e);
    }

    $this->say("Are you sure you spelled that right?");
  }

  private function pokeRarity($PokeData, $type, $subtype)
  {
    global $PDO;

    try {
      $GetRarity = $PDO->prepare("SELECT count(*) AS total, gender FROM pokemon WHERE poke_id=? AND alt_id=? AND type=? AND subtype=? AND banned='no' GROUP BY gender");
      $GetRarity->execute([$PokeData['poke_id'], $PokeData['alt_id'], $type, $subtype]);
      $GetRarity->setFetchMode(PDO::FETCH_ASSOC);
      $rarities = $GetRarity->fetchAll();
    } catch (PDOException $e) {
      handleError($e);
    }

    if (count($rarities) == 0) {
      $this->say("There are no ".$PokeData['Fullname']." in TPK.", [$PokeData['poke_id'], $PokeData['alt_id'], $type, $subtype]);
    } else {
      $rarity = ['total' => 0, 'm' => 0, 'f' => 0, 'g' => 0, '?' => 0];
      foreach ($rarities as $key => $poke) {
        $rarity['total'] += $poke['total'];
        $rarity[$poke['gender']] = $poke['total'];
      }
      $this->say($PokeData['Fullname'].' :: '.Format($rarity['total']).' :: M: '.Format($rarity['m']).', F: '.Format($rarity['f']).', G: '.Format($rarity['g']).', (?): '.Format($rarity['?']), [$PokeData['poke_id'], $PokeData['alt_id'], $type, $subtype]);
    }
  }

  // will show all types of pokemon obtainable
  private function pokeAllRarity($PokeData, $type)
  {
    global $PDO;
    global $pokeClass;

    try {
      $GetRarity = $PDO->prepare("SELECT count(*) AS total, type FROM pokemon WHERE poke_id=? AND alt_id=? AND banned='no' GROUP BY type");
      $GetRarity->execute([$PokeData['poke_id'], $PokeData['alt_id']]);
      $GetRarity->setFetchMode(PDO::FETCH_ASSOC);
      $rarities = $GetRarity->fetchAll();
    } catch (PDOException $e) {
      handleError($e);
    }

    if (count($rarities) == 0) {
      $this->say("There are no ".$PokeData['Fullname']." in TPK.");
    } else {
      $out = $PokeData['Fullname'].' :: ';
      foreach ($pokeClass->Type() as $key2 => $t) {
        foreach ($rarities as $key => $value) {
          if ($t == $value['type']) {
            $out .= ucfirst($t).':'.Format($value['total']).', ';
          }
        }
      }

      $out = trim($out, ", ");
      $this->say($out, [$PokeData['poke_id'], $PokeData['alt_id'], 'normal']);
    }
  }

  // finds the raity of an item
  // does not include currently held items
  private function itemRarity($ItemData)
  {
    global $PDO;

    try {
      $GetAllItems = $PDO->prepare("SELECT amount FROM items WHERE item_id=?");
      $GetAllItems->execute([$ItemData['id']]);
      $GetAllItems->setFetchMode(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
      handleError($e);
    }

    $count = 0;
    while ($Item = $GetAllItems->fetch()) {
      $count += $Item['amount'];
    }

    $this->say($ItemData['item_name'].' :: '.Format($count));
  }

  // easter egg rarity for users
  private function userRarity($UserData)
  {
    if ($UserData['gender'] == 'm') {
      $m = 1;
      $f = 0;
      $u = 0;
    }
    if ($UserData['gender'] == 'f') {
      $m = 0;
      $f = 1;
      $u = 0;
    }
    if ($UserData['gender'] == 'u') {
      $m = 0;
      $f = 0;
      $u = 1;
    }

    $this->say($UserData['user_name'].' :: 1 :: M:'.$m.', F:'.$f.', G:0, (?):'.$u);
  }
}
