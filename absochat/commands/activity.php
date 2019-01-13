<?php

/* ************************
 * activity.js
 *
 * Reports the activity of the user
 */

class Activity extends Command
{
  public $stat = 'activity';
  public $min_arguments = 1;
  public $help_text = [
    '!activity [Username/ID] (battles|mining)',
    'Returns a text blurb about the activity of the user.',
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

    if (!isset($user['user_id'])) {
      $this->say("User not found.");

      return;
    }

    switch ($user['gender']) {
      case 'm': $G1 = 'He'; $G2 = 'his'; break;
      case 'f': $G1 = 'She'; $G2 = 'her'; break;
      case 'u': $G1 = 'It'; $G2 = 'it\'s'; break;
    }

    if (date('G') < 2) {
      $day = 'already';
    } elseif (date('G') < 9) {
      $day = 'this morning';
    } else {
      $day = 'today';
    }

    $lastseen = time() - $user['online_time'];
    if ($lastseen < 600) {
      $line = 'online';
    } else {
      $line = 'offline';
    }

    if ($user['pokes_defeated_day'] == 69 or $user['pokes_defeated_day'] == 690 or $user['pokes_defeated_day'] == 6900) {
      $image = ['069', 0, 'normal'];
    } else {
      $image = [123, 0, 'normal'];
    }
    $this->say($user['user_name'].": ".$line.", ".Format($user['pokes_defeated_day'])." Pokemon defeated", $image);
  }
}
