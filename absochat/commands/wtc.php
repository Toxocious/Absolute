<?php

/* **********************
 * !wtc
 *
 * Returns the current wtc rankings
 */

class Wtc extends Command
{
  public $stat = 'wtc';
  public $min_arguments = 0;
  public $help_text = [
    '!wtc [next]',
    'Shows the WTC rankings. Argument "next" will tell if there is a WTC set.',
  ];

  public function execute_command($args)
  {
    global $PDO;
    global $pokeClass;

    if (isset($args[1]) && $args[1] == 'next') {
      try {
        $Select = $PDO->prepare("SELECT COUNT(`id`) FROM `crons` WHERE `type` = 'wtc' AND `used` != 'yes'");
        $Select->execute();
        $Select->setFetchMode(PDO::FETCH_ASSOC);
        $Results = $Select->fetchColumn();
      } catch (PDOException $e) {
        handleError($e);
      }

      if ($Results == 0) {
        $this->say("B0sh! The next WTC is not set.");
      } else {
        $this->say("WTC is set for next week.");
      }

      return;
    }

    try {
      $GetAdminOptions = $PDO->query("SELECT * FROM admin_options");
      $GetAdminOptions->setFetchMode(PDO::FETCH_ASSOC);
      $AdminOptions = $GetAdminOptions->fetch();

      $PokeData = $pokeClass->GetPokeData($AdminOptions['wtc_id'], $AdminOptions['wtc_alt'], $AdminOptions['wtc_type'], $AdminOptions['wtc_subtype']);

      if ($PokeData == false) {
        $this->say("B0sh! The WTC is not active.");

        return;
      }

      $GetPokemon = $PDO->prepare("SELECT po.experience, po.trainer_id, po.id, u.user_name FROM `pokemon` AS po LEFT JOIN `users` AS u ON po.trainer_id=u.user_id WHERE u.banned = 'no' AND po.wtc='yes' ORDER BY experience DESC LIMIT 3");
      $GetPokemon->execute();
      $GetPokemon->setFetchMode(PDO::FETCH_ASSOC);
      $Pokemon = $GetPokemon->fetchAll();
    } catch (PDOException $e) {
      handleError($e);
    }

    if (count($Pokemon) == 0) {
      $this->say("Nobody has entered the contest yet.", [$AdminOptions['wtc_id'], $AdminOptions['wtc_alt'], $AdminOptions['wtc_type'], $AdminOptions['wtc_subtype']]);

      return;
    }

    $Response = '';
    foreach ($Pokemon as $key => $p) {
      $PokeInfo = $pokeClass->GetPokeInfo($p['id']);
      switch ($key) {
        case 0: $Response .= '1st '; break;
        case 1: $Response .= ' 2nd '; break;
        case 2: $Response .= ' 3rd '; break;
      }

      $Response .= $p['user_name'].' (Level: '.Format($PokeInfo['Level']).'),';
    }

    $Response = trim($Response, ",");
    $this->say($Response, [$AdminOptions['wtc_id'], $AdminOptions['wtc_alt'], $AdminOptions['wtc_type'], $AdminOptions['wtc_subtype']]);
  }
}
