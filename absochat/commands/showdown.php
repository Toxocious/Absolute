<?php

/* **********************
 * !showdown
 *
 * Returns the current Showdown rankings
 */

class Showdown extends Command
{
  public $stat = 'showdown';
  public $min_arguments = 0;
  public $help_text = [
    '!showdown',
    'Returns the rankings of the Clan Showdown.',
  ];

  public function execute_command($args)
  {
    global $PDO;

    try {
      $Select = $PDO->prepare("SELECT * FROM clans WHERE `clan_showdown` != 0 ORDER BY `clan_showdown` DESC LIMIT 3");
      $Select->execute();
      $Select->setFetchMode(PDO::FETCH_ASSOC);
      $Resutls = $Select->fetchAll();
    } catch (PDOException $e) {
      handleError($e);
    }

    $Response = '';
    foreach ($Resutls as $key => $Clan) {
      switch ($key) {
        case 0: $Response .= '1st '; break;
        case 1: $Response .= '2nd '; break;
        case 2: $Response .= '3rd '; break;
      }

      $Response .= $Clan['clan_name'].' '.Format(floor($Clan['clan_showdown'] / 1000)).',';
    }

    if ($Response == '') {
      $Response = 'Showdown was just reset.';
    }

    $Response = trim($Response, ",");
    $this->say($Response);
  }
}
