<?php
  class Status
  {
    public $Pokemon = null;

    public $Status = null;
    public $Turns_Left = null;

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

      if ( $Pokemon->HasStatus($Status_Data['Name']) )
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

      $this->Pokemon = $Pokemon;
      $this->Status = $Status_Data;
      $this->Turns_Left = $Status_Turns;
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
          'Max_Turns' => -1,
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
        'Withdrawing' => [
          'Min_Turns' => 1,
          'Max_Turns' => 1,
          'Volatile' => true
        ],
      ];
    }
  }
