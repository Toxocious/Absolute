<?php
  class Ability
  {
    public $Name = null;
    public $Procced = null;

    public function __construct
    (
      string $Ability_Name
    )
    {
      $this->Name = $Ability_Name;
      $this->Procced = false;
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
