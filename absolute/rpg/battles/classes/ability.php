<?php
  class Ability
  {
    public $Name;
    public $Procced;
    public $Suppressed;

    public function __construct
    (
      string $Ability_Name
    )
    {
      $this->Name = $Ability_Name;
      $this->Procced = false;
      $this->Suppressed = false;
    }

    /**
     * Once an ability has procced, it (sometimes) won't activate again.
     */
    public function SetProcStatus
    (
      bool $Value
    )
    {
      $this->Procced = $Value;
    }
  }
