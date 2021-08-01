<?php
  class Field
  {
    public $Name = null;
    public $Side = null;
    public $Turns_Left = null;
    public $Stacks = null;

    public function __construct
    (
      string $Side,
      string $Field_Name,
      int $Field_Turns = null
    )
    {
      if ( !in_array($Side, ['Ally', 'Foe']) )
        return false;

      $Field_Data = $this->FieldEffects()[$Field_Name];
      if ( !isset($Field_Data) )
        return false;

      if ( !isset($Field_Turns) )
        if ( $Field_Data['Min_Turns'] > -1 && $Field_Data['Max_Turns'] > -1 )
          $Field_Turns = -1;
        else
          $Field_Turns = mt_rand($Field_Data['Min_Turns'], $Field_Data['Max_Turns']);
      else
        $Field_Turns = $Field_Turns;

      $Stack_Count = 1;

      if ( $Stack_Count > $Field_Data['Max_Stacks'] )
        $Stack_Count = $Field_Data['Max_Stacks'];

      $this->Name = $Field_Name;
      $this->Side = $Side;
      $this->Turns_Left = $Field_Turns;
      $this->Stacks = $Stack_Count;
    }

    /**
     * All possible field effects.
     */
    public function FieldEffects()
    {
      return [
        'Leech Seed' => [
          'Min_Turns' => -1,
          'Max_Turns' => -1,
          'Volatile' => false,
          'Max_Stacks' => 1,
          'Stacks' => 1,
        ],
        'Mist' => [
          'Min_Turns' => 5,
          'Max_Turns' => 5,
          'Volatile' => false,
          'Max_Stacks' => 1,
          'Stacks' => 1,
        ],
        'Spikes' => [
          'Min_Turns' => 5,
          'Max_Turns' => 5,
          'Volatile' => false,
          'Max_Stacks' => 3,
          'Stacks' => 1,
        ],
        'Trick Room' => [
          'Min_Turns' => 5,
          'Max_Turns' => 5,
          'Volatile' => false,
          'Max_Stacks' => 1,
          'Stacks' => 1,
        ],
      ];
    }
  }
