<?php

/* ********************
 * hug
 *
 * Requested by Starscream
 *
 * Gives internet hugs
 */

class Hug extends Command
{
  public $stat = 'hug';
  public $min_arguments = 1;
  public $help_text = [
    'Who needs help with hugs?',
  ];

  public function execute_command($args)
  {
    if (isset($args[1])) {
      $this->say("hugs ".$args[1]);
    } else {
      $this->say("hugs you");
    }
  }
}
