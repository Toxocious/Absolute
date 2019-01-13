<?php

/* ************************
 * !pokemon
 *
 * Returns a random pokemon of a given type
 */

class PokemonCommand extends Command
{
  public $stat = 'pokemon';
  public $min_arguments = 0;
  public $help_text = [
    '!pokemon [type]',
  ];

  public function execute_command($args)
  {
    global $pokeClass;
    global $GlobalPDO;

    if (isset($args[1]) && $args[1] == 'glitch') {
      $Query = "SELECT `id` FROM `poke_data` WHERE poke_id >= 1000 ORDER BY RAND() LIMIT 1";
      $Input = [];
    } elseif (isset($args[1]) && $args[1] == 'user') {
      try {
        $SelectUserData = $GlobalPDO->prepare("SELECT * FROM users WHERE user_id=? OR user_name=? LIMIT 1");
        $SelectUserData->execute([$args[2], $args[2]]);
        $SelectUserData->setFetchMode(PDO::FETCH_ASSOC);
        $user = $SelectUserData->fetch();

        if (!isset($user['user_id'])) {
          $this->say('User does not exist');
        }

        $SelectUserPokeID = $PDO->prepare("SELECT DISTINCT poke_id FROM pokemon WHERE trainer_id=? ORDER BY RAND() LIMIT 1");
        $SelectUserPokeID->execute([$user['user_id']]);
        $SelectUserPokeID->setFetchMode(PDO::FETCH_ASSOC);
        $UserPokeID = $SelectUserPokeID->fetch();

        $Query = "SELECT `id` FROM `poke_data` WHERE poke_id=? LIMIT 1";
        $Input = [$UserPokeID['poke_id']];
      } catch (PDOException $e) {
        handleError($e);
      }
    } elseif (isset($args[1])) {
      $args[1] = ucfirst(strtolower($args[1]));
      $Query = "SELECT `id` FROM `poke_data` WHERE `type_1` = ? OR `type_2`=? ORDER BY RAND() LIMIT 1";
      $Input = [$args[1], $args[1]];
    } else {
      $Query = "SELECT `id` FROM `poke_data` ORDER BY RAND() LIMIT 1";
      $Input = [];
    }

    try {
      $Select = $GlobalPDO->prepare($Query);
      $Select->execute($Input);
      $Select->setFetchMode(PDO::FETCH_ASSOC);
      $Random = $Select->fetch();
    } catch (PDOException $e) {
      handleError($e);
    }

    if (!isset($Random['id'])) {
      $this->say("There are no Pokemon of that type.");

      return;
    }

    $PokeData = $pokeClass->GetPokeData($Random['id'], 'id', 'normal');
    $this->say($PokeData['Fullname'], [$PokeData['poke_id'], $PokeData['alt_id'], 'normal']);
  }
}
