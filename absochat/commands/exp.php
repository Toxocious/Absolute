<?php

/* * * * * * * *
 * level.js for Scyther
 *
 * Converts levels into exp
 *
 * !level (System) [Level] (Level to compare)
 *
 * Also works with trainers
 *
 * !level (System) [Username/ID] [Username/ID to compare]
 */

class Exp extends Command
{
  public $stat = 'exp';
  public $min_arguments = 1;
  public $help_text = [
    '!level (System) [Level] (Level to compare)',
    'The level command returns the Exp. required to reach a level. It can',
    'also compare to levels and return the diffrernce. If you specify a system',
    'it can use a different level formula. Supported Systems:',
    'Pokemon, Trainer, Mine, Map, Fishing, Clan',
  ];

  public function execute_command($args)
  {
    if (is_numeric($this->short_number_parser($args[1]))) {
      if (count($args) == 2) {
        $args = ['exp', 'pokemon', $args[1]];
      } elseif (count($args) == 3) {
        $args = ['exp', 'pokemon', $args[1], $args[2]];
      }
    }

    $args[2] = Text($this->short_number_parser($args[2]))->num();
    if ($args[2] > 10000000000000000 || (isset($args[3]) && $args[3] > 10000000000000000)) {
      $this->say("Whoops, too big!");

      return;
    }

    $exp = getLevelFromExp($args[2], $args[1]);
    if ($exp === false) {
      $this->say("This isn't a real system.");

      return;
    }

    $_SESSION['format'] = 'shortened';
    if (isset($args[3])) {
      $args[3] = Text($this->short_number_parser($args[3]))->num();
      $compare = getLevelFromExp($args[3], $args[1]);

      $this->say("Level: ".Format(abs($compare - $exp)));
    } else {
      $this->say("Level: ".Format($exp));
    }

    $_SESSION['format'] = 'normal';
  }
}
