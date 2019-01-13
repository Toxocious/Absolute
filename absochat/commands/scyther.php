<?php

/************************
 * !scyther
 * scyther list will list all commands
 * scyther will just say hi
 * scyther command will give you a command
 */

class Scyther extends Command
{
  public $stat = 'scyther';
  public $min_arguments = 0;
  public $help_text = [
  ];

  public function execute_command($args)
  {
    if (!isset($args[1])) {
      return $this->say("Hi, my name is Scyther.");
    }

    $commands = [];
    global $root;
    $dir = scandir($root.'/scyther/commands/');
    $message = "Commands:";
    foreach ($dir as $key => $filename) {
      $f = str_replace('.php', '', $filename);
      if ($f == '.') {
        continue;
      }
      if ($f == '..') {
        continue;
      }
      if ($f == 'pokemoncommand') {
        $f = 'pokemon';
      }
      $message .= " !".$f.",";
      $commands[] = $f;
    }

    if ($args[1] == 'list') {
      return $this->say(trim($message, ' ,'));
    } elseif (in_array($args[1], $commands)) {
      try {
        $c = cmdName($args[1]);
        $c = new $c();
        foreach ($c->help_text as $key => $t) {
          $this->say($t, "null");
        }
      } catch (Exception $e) {
        $this->say("I don't have this command.");
      }
    }
  }
}
