<?php

/* ********************
 * !tset
 *
 & tset
 */

class Tset extends Command
{
  public $stat = 'tset';
  public $min_arguments = 0;
  public $help_text = [
    'tset',
  ];

  public function execute_command($args)
  {
    $this->say("tset");
  }
}
