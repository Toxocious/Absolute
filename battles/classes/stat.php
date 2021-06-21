<?php
  class Stat
  {
    public $Stat_Name = null;
    public $Base_Value = null;
    public $Current_Value = null;
    public $Stage = null;

    public function __construct
    (
      string $Stat_Name,
      int $Base_Value
    )
    {
      $this->Stat_Name = $Stat_Name;
      $this->Base_Value = $Base_Value;
      $this->Current_Value = $Base_Value;
      $this->Stage = 0;
    }

    public function SetValue()
    {
      $Modifier = $this->CalcModifier();

      $this->Current_Value *= $Modifier;
    }

    public function CalcModifier()
    {
      if ( in_array($this->Stat_Name, ['Attack', 'Defense', 'Sp_Attack', 'Sp_Defense', 'Speed']) )
        if ( $this->Stage >= 0 )
          return ($this->Stage + 2) / 2;
        else
          return 2 / (($this->Stage * 1) + 2);
      else
        if ( $this->Stage >= 0 )
          return ($this->Stage + 3) / 3;
        else
          return 3 / (($this->Stage * 1) + 3);
    }

    public function SetStage
    (
      int $Stage = 1
    )
    {
      $this->Stage += $Stage;

      if ( $this->Stage > 6 )
        $this->Stage = 6;
    }

    public function ResetStat()
    {
      $this->Current_Value = $this->Base_Value;
      $this->Stage = 0;
    }
  }
