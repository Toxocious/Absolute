<?php
  class Field
  {
    public $Name = null;
    public $Side = null;
    public $Turns_Left = null;

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
        $Field_Turns = mt_rand($Field_Data['Min_Turns'], $Field_Data['Max_Turns']);

      $this->Name = $Field_Name;
      $this->Side = $Side;
      $this->Turns_Left = $Field_Turns;
    }

    /**
     * All possible field effects.
     */
    public function FieldEffects()
    {
      return [
        'Mist' => [
          'Min_Turns' => 5,
          'Max_Turns' => 5,
          'Volatile' => false
        ],
        'Trick Room' => [
          'Min_Turns' => 5,
          'Max_Turns' => 5,
          'Volatile' => false
        ],
      ];
    }
  }
