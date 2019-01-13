<?php

/* *****************
 * nature.php
 *
 * Determines the number of battles you have to do to get from level x to level x or an experience.
 * Also calculates time if you give it battle length in seconds
 */

class Nature extends Command
{
  public $stat = 'nature';
  public $min_arguments = 1;
  public $help_text = [
    '!nature [Nature|list]',
    'Tells the bonuses of a nature or lists all natures.',
  ];

  public function execute_command($args)
  {
    global $pokeClass;
    $NatureList = $pokeClass->Natures();

    $args[1] = ucfirst(strtolower($args[1]));
    if ($args[1] == 'List') {
      $output = '';
      foreach ($NatureList as $k => $n) {
        $output .= $n.', ';
      }
      $this->say(trim($output, ', '));

      return;
    }
    $Nature = array_search($args[1], $NatureList);

    if ($Nature === false) {
      $this->say("There is no such nature!");

      return;
    }

    if ($args[1] == 'Naughty') {
      $NatureList[$Nature] = 'Naughty (^_~)';
    }

    $output = 'Neutral';
    if ($Nature >= 0 && $Nature <= 3) {
      $output = "+10% Attack";
    }
    if ($Nature >= 4 && $Nature <= 7) {
      $output = "+10% Defense";
    }
    if ($Nature >= 8 && $Nature <= 11) {
      $output = "+10% Sp. Atk.";
    }
    if ($Nature >= 12 && $Nature <= 15) {
      $output = "+10% Sp. Def.";
    }
    if ($Nature >= 16 && $Nature <= 19) {
      $output = "+10% Speed";
    }

    if ($Nature == 4 || $Nature == 8 || $Nature == 12 || $Nature == 16) {
      $output .= ", -10% Attack";
    }
    if ($Nature == 0 || $Nature == 9 || $Nature == 13 || $Nature == 17) {
      $output .= ", -10% Defense";
    }
    if ($Nature == 1 || $Nature == 5 || $Nature == 14 || $Nature == 18) {
      $output .= ", -10% Sp. Atk.";
    }
    if ($Nature == 2 || $Nature == 6 || $Nature == 10 || $Nature == 19) {
      $output .= ", -10% Sp. Def.";
    }
    if ($Nature == 3 || $Nature == 7 || $Nature == 11 || $Nature == 15) {
      $output .= ", -10% Speed";
    }

    $this->say($NatureList[$Nature].": ".$output);
  }
}
