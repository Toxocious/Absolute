<?php

class Weighter
{
  private $events = [];
  private $total_weight = 0;

  public function __construct()
  {
  }

  public function get($Unlock = -1)
  {
    if (count($this->events) == 0) {
      return false;
    }

    if ($Unlock != -1) {
      $events = [];
      $weight = 0;

      foreach ($this->events as $k => $Event) {
        if ($Event[3] == -1 || $Event[3] < $Unlock) {
          $weight += $Event[2];
          $events[] = [$Event[0], $weight];
        }
      }
    } else {
      $events = $this->events;
      $weight = $this->total_weight;
    }

    $Random = mt_rand(1, $weight);
    foreach ($events as $k => $Event) {
      if ($Random <= $Event[1]) {
        return $Event[0];
      }
    }
  }

  public function add($Desc, $Weight = 1, $Unlock = -1)
  {
    $this->total_weight += $Weight;
    $this->events[] = [
      $Desc, $this->total_weight, $Weight, $Unlock,
    ];

    return true;
  }

  public function resetEvent()
  {
    $this->total_weight = 0;
    $this->events = [];

    return true;
  }

  public function out()
  {
    return [
      'events' => $this->events,
      'total_weight' => $this->total_weight,
    ];
  }

  public function in($in)
  {
    if (!isset($in['events']) || !isset($in['total_weight'])) {
      return false;
    }

    $this->events = $in['events'];
    $this->total_weight = $in['total_weight'];

    return true;
  }
}
