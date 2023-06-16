<?php
  class Stat
  {
    public $Stat_Name = null;
    public $Base_Value = null;
    public $Current_Value = null;
    public $Stage = null;
    public $Mod = null;

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
      $this->Mod = 1;
    }

    /**
     * Set the current value of the stat.
     * @param int $Stage
     */
    public function SetValue
    (
      int $Stage = 0
    )
    {
      $this->SetStage($Stage);
      $Modifier = $this->CalcModifier();

      $this->Mod = $Modifier;
      $this->Current_Value *= $Modifier;
    }

    /**
     * Calculate the modifier of the stat, given it's given stage.
     */
    public function CalcModifier()
    {
      if ( in_array($this->Stat_Name, ['Attack', 'Defense', 'Sp_Attack', 'Sp_Defense', 'Speed']) )
        if ( $this->Stage >= 0 )
          return (($this->Stage + 2) / 2) * 1;
        else
          return 2 / ($this->Stage * -1 + 2) * 1;
      else
        if ( $this->Stage >= 0 )
          return (($this->Stage + 3) / 3) * 1;
        else
          return 2 / ($this->Stage * -1 + 3) * 1;
    }

    /**
     * Set the current stage of the stat.
     * @param int $Stage
     */
    public function SetStage
    (
      int $Stage = 0
    )
    {
      $this->Stage += $Stage;

      if ( $this->Stage > 6 )
        $this->Stage = 6;
    }

    /**
     * Reset the stat back to it's base properties.
     */
    public function ResetStat()
    {
      $this->Current_Value = $this->Base_Value;
      $this->Stage = 0;
    }
  }
