<?php

/* ********************
 * whatdo
 *
 * The most important Scyther Command. Making lives work better.
 */

class Whatdo extends Command
{
  public $stat = 'whatdo';
  public $min_arguments = 1;
  public $help_text = [
    '!whatdo [option 1], [option 2], [option 3]...',
    'Randomly selects a item from a list to help figure out what do.',
    'By using commas inbetween the options, you can have spaces inside whatdo text.',
  ];

  public function execute_command($args)
  {
    unset($args[0]); // remove the command
    $argumentString = implode(' ', $args);
    $choices = explode(',', $argumentString);
    if (count($choices) == 1) {
      $choices = explode(' ', $argumentString);
    }
    shuffle($choices);
    if (isset($choices[0])) {
      $this->say($choices[0]." is whatdo.");
    }
  }
}
