<?php
  class Timer
  {
    private $Time_Started = null;

    public function __construct()
    {
      $this->Time_Started = time();
    }

    public function __destruct()
    {
      echo "Timer finished in " . (time() - $this->Time_Started) . " seconds";
    }
  }
