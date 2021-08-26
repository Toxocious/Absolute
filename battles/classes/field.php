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
      if ( !in_array($Side, ['Ally', 'Foe', 'Global']) )
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
     * Increment the field effect's stack count.
     */
    public function AddStack
    (
      int $Stacks = 1
    )
    {
      if ( !empty($Stacks) )
        $this->Stacks += $Stacks;
      else
        $this->Stacks++;

      return $this;
    }

    /**
     * All possible field effects.
     */
    public function FieldEffects()
    {
      return [
        'Gravity' => [
          'Min_Turns' => 5,
          'Max_Turns' => 5,
          'Volatile' => false,
          'Max_Stacks' => 1,
          'Stacks' => 1,
          'Dialogue' => 'Gravity intensified!',
        ],
        'Leech Seed' => [
          'Min_Turns' => -1,
          'Max_Turns' => -1,
          'Volatile' => false,
          'Max_Stacks' => 1,
          'Stacks' => 1,
          'Dialogue' => 'The foe was seeded!',
        ],
        'Light Screen' => [
          'Min_Turns' => 5,
          'Max_Turns' => 5,
          'Volatile' => false,
          'Max_Stacks' => 1,
          'Stacks' => 1,
          'Dialogue' => 'Light Screen raised your team\'s Special Defense!',
        ],
        'Mist' => [
          'Min_Turns' => 5,
          'Max_Turns' => 5,
          'Volatile' => false,
          'Max_Stacks' => 1,
          'Stacks' => 1,
          'Dialogue' => 'Your team became shrouded in mist!',
        ],
        'Spikes' => [
          'Min_Turns' => -1,
          'Max_Turns' => -1,
          'Volatile' => false,
          'Max_Stacks' => 3,
          'Stacks' => 1,
          'Dialogue' => 'Spikes were scattered all around the feet of the foe\'s team!',
        ],
        'Toxic Spikes' => [
          'Min_Turns' => -1,
          'Max_Turns' => -1,
          'Volatile' => false,
          'Max_Stacks' => 3,
          'Stacks' => 1,
          'Dialogue' => 'Toxic spikes were scattered all around the feet of the foe\'s team!',
        ],
        'Trick Room' => [
          'Min_Turns' => 5,
          'Max_Turns' => 5,
          'Volatile' => false,
          'Max_Stacks' => 1,
          'Stacks' => 1,
          'Dialogue' => 'The dimensions have been twisted!',
        ],
      ];
    }
  }
