<?php
  class Stat
  {
    public $Base_Value = null;
    public $Current_Value = null;
    public $Modifier = null;

    public function __construct
    (
      int $Base_Value
    )
    {
      $this->Base_Value = $Base_Value;
      $this->Current_Value = $Base_Value;
      $this->Modifier = 1;
    }

    public function SetModifier
    (
      int $Modifier = 1
    )
    {
      $this->Current_Value *= $Modifier;
    }

    public function ResetStat()
    {
      $this->Current_Value = $this->Base_Value;
      $this->Modifier = 1;
    }
  }
