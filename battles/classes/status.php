<?php
  class Status
  {
    public $Name = null;
    public $Turns_Left = null;
    public $Volatile = null;
    public $Stacks = null;

    public function __construct
    (
      PokemonHandler $Pokemon,
      string $Status_Name,
      int $Status_Turns = null
    )
    {
      $Status_Data = $this->AllStatuses()[$Status_Name];
      if ( !isset($Status_Data) )
        return false;

      if ( $Pokemon->HasStatus($Status_Name) )
        return false;

      if ( !$Status_Data['Volatile'] )
      {
        if ( in_array($Pokemon->Item->Name, ['Flame Orb', 'Toxic Orb']) )
          if ( in_array($Pokemon->Ability, ['Flower Veil']) )
            return false;

        if ( in_array($Pokemon->Ability, ['Leaf Guard', 'Comatose']) )
          return false;

        if ( $Pokemon->HasStatus('Safeguard') )
          return false;
      }

      if ( $Pokemon->Ability == 'Shields Down' )
        return false;

      if ( !isset($Status_Turns) )
        $Status_Turns = mt_rand($Status_Data['Min_Turns'], $Status_Data['Max_Turns']);

      if ( isset($Status_Data['Min_Stacks']) && isset($Status_Data['Max_Stacks']) )
        $this->Stacks = 1;

      $this->Name = $Status_Name;
      $this->Turns_Left = $Status_Turns;
      $this->Volatile = $Status_Data['Volatile'];
    }

    /**
     * Update the status.
     */
    public function UpdateStatus()
    {
      if ( $this->Turns_Left > 0 )
        $this->Turns_Left--;

      return $this;
    }

    /**
     * Increment stack count.
     */
    public function IncrementStacks
    (
      int $Amount = 1
    )
    {
      if ( isset($this->Stacks) )
        $this->Stacks++;

      return $this;
    }

    /**
     * An array of all statuses, volatile and otherwise.
     */
    public function AllStatuses()
    {
      return [
        'Burn' => [
          'Min_Turns' => -1,
          'Max_Turns' => -1,
          'Volatile' => false
        ],
        'Paralysis' => [
          'Min_Turns' => -1,
          'Max_Turns' => -1,
          'Volatile' => false
        ],
        'Poison' => [
          'Min_Turns' => -1,
          'Max_Turns' => -1,
          'Volatile' => false
        ],
        'Badly Poisoned' => [
          'Min_Turns' => -1,
          'Max_Turns' => -1,
          'Volatile' => false
        ],
        'Freeze' => [
          'Min_Turns' => 1,
          'Max_Turns' => 5,
          'Volatile' => false
        ],
        'Sleep' => [
          'Min_Turns' => 1,
          'Max_Turns' => 3,
          'Volatile' => false
        ],

        'Aiming' => [
          'Min_Turns' => 1,
          'Max_Turns' => 1,
          'Volatile' => true
        ],
        'Aqua Ring' => [
          'Min_Turns' => -1,
          'Max_Turns' => -1,
          'Volatile' => true
        ],
        'Bide' => [
          'Min_Turns' => 2,
          'Max_Turns' => 2,
          'Volatile' => true
        ],
        'Bound' => [
          'Min_Turns' => 4,
          'Max_Turns' => 5,
          'Volatile' => true
        ],
        'Braced' => [
          'Min_Turns' => 1,
          'Max_Turns' => 1,
          'Volatile' => true
        ],
        'Center Of Attention' => [
          'Min_Turns' => 1,
          'Max_Turns' => 1,
          'Volatile' => true
        ],
        'Charging' => [
          'Min_Turns' => 1,
          'Max_Turns' => 1,
          'Volatile' => true
        ],
        'Confusion' => [
          'Min_Turns' => 2,
          'Max_Turns' => 5,
          'Volatile' => true
        ],
        'Crafty Shield' => [
          'Min_Turns' => -1,
          'Max_Turns' => -1,
          'Volatile' => true
        ],
        'Curse' => [
          'Min_Turns' => -1,
          'Max_Turns' => -1,
          'Volatile' => true
        ],
        'Defense Curl' => [
          'Min_Turns' => -1,
          'Max_Turns' => -1,
          'Volatile' => true
        ],
        'Destiny Bond' => [
          'Min_Turns' => -1,
          'Max_Turns' => -1,
          'Volatile' => true,
        ],
        'Damage Over Time' => [
          'Min_Turns' => -1,
          'Max_Turns' => -1,
          'Volatile' => true,
        ],
        'Embargo' => [
          'Min_Turns' => 5,
          'Max_Turns' => 5,
          'Volatile' => true
        ],
        'Encore' => [
          'Min_Turns' => 3,
          'Max_Turns' => 3,
          'Volatile' => true
        ],
        'Flinch' => [
          'Min_Turns' => 1,
          'Max_Turns' => 1,
          'Volatile' => true
        ],
        'Focus Energy' => [
          'Min_Turns' => -1,
          'Max_Turns' => -1,
          'Volatile' => true
        ],
        'Foresight' => [
          'Min_Turns' => 2,
          'Max_Turns' => 2,
          'Volatile' => true
        ],
        'Heal Block' => [
          'Min_Turns' => 5,
          'Max_Turns' => 5,
          'Volatile' => true
        ],
        'Identified' => [
          'Min_Turns' => -1,
          'Max_Turns' => -1,
          'Volatile' => true
        ],
        'Infatuation' => [
          'Min_Turns' => -1,
          'Max_Turns' => -1,
          'Volatile' => true
        ],
        'Leech Seed' => [
          'Min_Turns' => -1,
          'Max_Turns' => -1,
          'Volatile' => true
        ],
        'Levitate' => [
          'Min_Turns' => 5,
          'Max_Turns' => 5,
          'Volatile' => true
        ],
        'Lock-On' => [
          'Min_Turns' => 1,
          'Max_Turns' => 5,
          'Volatile' => true
        ],
        'Magic Coat' => [
          'Min_Turns' => 1,
          'Max_Turns' => 1,
          'Volatile' => true
        ],
        'Mimic' => [
          'Min_Turns' => 1,
          'Max_Turns' => 1,
          'Volatile' => true
        ],
        'Minimize' => [
          'Min_Turns' => -1,
          'Max_Turns' => -1,
          'Volatile' => true
        ],
        'Move Locked' => [
          'Min_Turns' => 1,
          'Max_Turns' => 5,
          'Volatile' => true
        ],
        'Nightmare' => [
          'Min_Turns' => -1,
          'Max_Turns' => -1,
          'Volatile' => true
        ],
        'No Guard' => [
          'Min_Turns' => 1,
          'Max_Turns' => 5,
          'Volatile' => true
        ],
        'Perish Song' => [
          'Min_Turns' => 3,
          'Max_Turns' => 3,
          'Volatile' => true
        ],
        'Protect' => [
          'Min_Turns' => 1,
          'Max_Turns' => 1,
          'Volatile' => true
        ],
        'Semi-Invulnerability' => [
          'Min_Turns' => 1,
          'Max_Turns' => 5,
          'Volatile' => true
        ],
        'Stockpile' => [
          'Min_Turns' => -1,
          'Max_Turns' => -1,
          'Volatile' => true,
          'Min_Stacks' => 0,
          'Max_Stacks' => 3,
          'Stacks' => 0
        ],
        'Substitute' => [
          'Min_Turns' => 1,
          'Max_Turns' => -1,
          'Volatile' => true
        ],
        'Recharging' => [
          'Min_Turns' => 1,
          'Max_Turns' => 1,
          'Volatile' => true
        ],
        'Rooted' => [
          'Min_Turns' => -1,
          'Max_Turns' => -1,
          'Volatile' => true
        ],
        'Taunt' => [
          'Min_Turns' => 3,
          'Max_Turns' => 3,
          'Volatile' => true
        ],
        'Telekenisis' => [
          'Min_Turns' => -1,
          'Max_Turns' => -1,
          'Volatile' => true
        ],
        'Torment' => [
          'Min_Turns' => -1,
          'Max_Turns' => -1,
          'Volatile' => true
        ],
        'Transformed' => [
          'Min_Turns' => -1,
          'Max_Turns' => -1,
          'Volatile' => true
        ],
        'Trap' => [
          'Min_Turns' => -1,
          'Max_Turns' => -1,
          'Volatile' => true
        ],
        'Withdrawing' => [
          'Min_Turns' => 1,
          'Max_Turns' => 1,
          'Volatile' => true
        ],
      ];
    }
  }
