<?php

/* * * * * * * *
 * !pickaxe
 *
 * !pickaxe (pickaxe Level) [Compare] [clan|personal]
 */

class Pickaxe extends Command
{
  public $stat = 'pickaxe';
  public $min_arguments = 1;
  public $help_text = [
    '!pickaxe (pickaxe Level) [Compare] [ores|bars] [clan|personal]',
    'The pickaxe command returns the difference in ores/bars between 2 pickaxe levels.',
  ];

  public function execute_command($args)
  {
    $d = implode(',', $args);
    $isClan = strpos($d, 'clan') !== false;
    $isBars = strpos($d, 'bars') !== false;

    if (isset($args[2]) && is_numeric($args[2])) {
      $total = 0;
      $l = 0;
      for ($ql = $args[1]; $ql < $args[2]; ++$ql) {
        if ($isClan) {
          $ores = miningClanPickaxeOreCost($ql);
        } else {
          $ores = miningPickaxeOreCost($ql);
        }
        $total += $ores;
        ++$l;
      }

      if ($isBars) {
        $word = 'Bars';
        $total = miningPickaxeBarConversion($total);
      } else {
        $word = 'Ores';
      }

      $this->say("Total $word: ".Format($total));
    } else {
      if ($isClan) {
        $ores = miningClanPickaxeOreCost($args[1]);
      } else {
        $ores = miningPickaxeOreCost($args[1]);
      }

      if ($isBars) {
        $word = 'Bars';
        $ores = miningPickaxeBarConversion($ores);
      } else {
        $word = 'Ores';
      }

      $this->say("$word: ".Format($ores));
    }
  }
}
