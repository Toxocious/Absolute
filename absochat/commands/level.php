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

class Level extends Command
{
  public $stat = 'level';
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
    if (is_numeric($args[1])) {
      if (count($args) == 2) {
        $args = ['level', 'pokemon', $args[1]];
      } elseif (count($args) == 3) {
        $args = ['level', 'pokemon', $args[1], $args[2]];
      }
    }

    if (str_replace('.', '', $args[2]) != $args[2]) {
      $this->say("Decimal points are not supported");
    }

    $args[2] = Text($this->short_number_parser($args[2]))->num();
    if ($args[2] > 100000 || (isset($args[3]) && $args[3] > 100000)) {
      $this->say("Whoops, too big!");

      return;
    }

    $exp = getExpFromLevel($args[2], $args[1]);
    if ($exp === false) {
      $this->say("This isn't a real system.");

      return;
    }

    $_SESSION['format'] = 'shortened';
    if (isset($args[3])) {
      $args[3] = Text($this->short_number_parser($args[3]))->num();
      $compare = getExpFromLevel($args[3], $args[1]);

      $this->say("Exp.: ".Format(abs($compare - $exp)));
    } else {
      $this->say("Exp.: ".Format($exp));
    }

    $_SESSION['format'] = 'normal';
  }
}
