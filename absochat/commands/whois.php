<?php

/* **********************
 * !wtc
 *
 * Returns the current wtc rankings
 */

class Whois extends Command
{
  public $stat = 'whois';
  public $min_arguments = 1;
  public $help_text = [
    '!whatdo [next]',
    'Shows the WTC rankings. Argument "next" will tell if there is a WTC set.',
  ];

  public function execute_command($args)
  {
    global $PDO;
    try {
      $Select = $PDO->prepare("SELECT * FROM users WHERE `user_id`=? or `user_name`=? LIMIT 1");
      $Select->execute([$args[1], $args[1]]);
      $Select->setFetchMode(PDO::FETCH_ASSOC);
      $user = $Select->fetch();
    } catch (PDOException $e) {
      handleError($e);
    }

    if (strtolower($args[1]) == 'scyther') {
      $this->say("Scyther : aka : Beep boop.");

      return;
    }

    if (!isset($user['user_id'])) {
      $this->say("User not found.");

      return;
    }

    try {
      $SelectQuery = $PDO->prepare("SELECT * FROM user_name_changes WHERE `user_id`=? ORDER BY timestamp DESC LIMIT 3");
      $SelectQuery->execute([$user['user_id']]);
      $SelectQuery->setFetchMode(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
      handleError($e);
    }

    $names = '';
    while ($Name = $SelectQuery->fetch()) {
      $names .= $Name['old_user_name'].', ';
    }

    $this->say($user['user_name'].' : aka : '.trim($names, ', '));
  }
}
