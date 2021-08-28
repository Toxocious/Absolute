<?php
  class Status
  {
    public $Name = null;
    public $Turns_Left = null;
    public $Volatile = null;
    public $Stacks = null;
    public $Max_HP = null;
    public $HP = null;

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
        if ( $Status_Name == 'Burn' && $Pokemon->HasTyping(['Fire']) )
          return false;

        if ( $Status_Name == 'Paralysis' && $Pokemon->HasTyping(['Electric']) )
          return false;

        if ( $Status_Name == 'Poison' && $Pokemon->HasTyping(['Poison']) )
          return false;
      }
      else
      {
        if ( in_array($Pokemon->Item->Name, ['Flame Orb', 'Toxic Orb']) )
          if ( in_array($Pokemon->Ability, ['Flower Veil']) )
            return false;

        if ( in_array($Pokemon->Ability, ['Leaf Guard', 'Comatose']) )
          return false;

        if ( $Pokemon->HasStatus('Safeguard') )
          return false;

        if ( in_array($Status_Name, ['Encore', 'Heal Block', 'Infatuation', 'Taunt', 'Torment']) && $Pokemon->Ability == 'Aroma Veil' )
          return false;
      }

      if ( $Pokemon->Ability == 'Shields Down' )
        return false;

      if ( !isset($Status_Turns) )
        $Status_Turns = mt_rand($Status_Data['Min_Turns'], $Status_Data['Max_Turns']);

      if ( isset($Status_Data['Min_Stacks']) && isset($Status_Data['Max_Stacks']) )
        $this->Stacks = 1;

      if ( isset($Status_Data['Dialogue']) )
        $this->Dialogue = $Status_Data['Dialogue'];

      $this->Name = $Status_Name;
      $this->Turns_Left = $Status_Turns;
      $this->Volatile = $Status_Data['Volatile'];

      if ( $Status_Name == 'Substitute' )
      {
        $this->Max_HP = $Pokemon->Max_HP / 4;
        $this->HP = $Pokemon->Max_HP / 4;
      }
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
          'Volatile' => false,
          'Dialogue' => 'has been burnt!',
        ],
        'Paralysis' => [
          'Min_Turns' => -1,
          'Max_Turns' => -1,
          'Volatile' => false,
          'Dialogue' => 'has been paralyzed!',
        ],
        'Poison' => [
          'Min_Turns' => -1,
          'Max_Turns' => -1,
          'Volatile' => false,
          'Dialogue' => 'has been poisoned!',
        ],
        'Badly Poisoned' => [
          'Min_Turns' => -1,
          'Max_Turns' => -1,
          'Volatile' => false,
          'Dialogue' => 'has been badly poisoned!',
        ],
        'Freeze' => [
          'Min_Turns' => 1,
          'Max_Turns' => 5,
          'Volatile' => false,
          'Dialogue' => 'has been frozen!',
        ],
        'Sleep' => [
          'Min_Turns' => 1,
          'Max_Turns' => 3,
          'Volatile' => false,
          'Dialogue' => 'has been put to sleep!',
        ],

        'Aiming' => [
          'Min_Turns' => 1,
          'Max_Turns' => 1,
          'Volatile' => true,
          'Dialogue' => 'is taking aim!',
        ],
        'Aqua Ring' => [
          'Min_Turns' => -1,
          'Max_Turns' => -1,
          'Volatile' => true,
          'Dialogue' => 'No Dialogue Has Been Set For This Move',
        ],
        'Bide' => [
          'Min_Turns' => 2,
          'Max_Turns' => 2,
          'Volatile' => true,
          'Dialogue' => 'No Dialogue Has Been Set For This Move',
        ],
        'Bind' => [
          'Min_Turns' => 2,
          'Max_Turns' => 2,
          'Volatile' => true,
          'Dialogue' => 'No Dialogue Has Been Set For This Move',
        ],
        'Bound' => [
          'Min_Turns' => 4,
          'Max_Turns' => 5,
          'Volatile' => true,
          'Dialogue' => 'No Dialogue Has Been Set For This Move',
        ],
        'Braced' => [
          'Min_Turns' => 1,
          'Max_Turns' => 1,
          'Volatile' => true,
          'Dialogue' => 'No Dialogue Has Been Set For This Move',
        ],
        'Center Of Attention' => [
          'Min_Turns' => 1,
          'Max_Turns' => 1,
          'Volatile' => true,
          'Dialogue' => 'No Dialogue Has Been Set For This Move',
        ],
        'Charging' => [
          'Min_Turns' => 1,
          'Max_Turns' => 1,
          'Volatile' => true,
          'Dialogue' => 'No Dialogue Has Been Set For This Move',
        ],
        'Confusion' => [
          'Min_Turns' => 2,
          'Max_Turns' => 5,
          'Volatile' => true,
          'Dialogue' => 'No Dialogue Has Been Set For This Move',
        ],
        'Crafty Shield' => [
          'Min_Turns' => -1,
          'Max_Turns' => -1,
          'Volatile' => true,
          'Dialogue' => 'No Dialogue Has Been Set For This Move',
        ],
        'Curse' => [
          'Min_Turns' => -1,
          'Max_Turns' => -1,
          'Volatile' => true,
          'Dialogue' => 'No Dialogue Has Been Set For This Move',
        ],
        'Defense Curl' => [
          'Min_Turns' => -1,
          'Max_Turns' => -1,
          'Volatile' => true,
          'Dialogue' => 'No Dialogue Has Been Set For This Move',
        ],
        'Destiny Bond' => [
          'Min_Turns' => -1,
          'Max_Turns' => -1,
          'Volatile' => true,
          'Dialogue' => 'No Dialogue Has Been Set For This Move',
        ],
        'Damage Over Time' => [
          'Min_Turns' => -1,
          'Max_Turns' => -1,
          'Volatile' => true,
          'Dialogue' => 'No Dialogue Has Been Set For This Move',
        ],
        'Embargo' => [
          'Min_Turns' => 5,
          'Max_Turns' => 5,
          'Volatile' => true,
          'Dialogue' => 'No Dialogue Has Been Set For This Move',
        ],
        'Encore' => [
          'Min_Turns' => 3,
          'Max_Turns' => 3,
          'Volatile' => true,
          'Dialogue' => 'No Dialogue Has Been Set For This Move',
        ],
        'Flinch' => [
          'Min_Turns' => 1,
          'Max_Turns' => 1,
          'Volatile' => true,
          'Dialogue' => 'No Dialogue Has Been Set For This Move',
        ],
        'Focus Energy' => [
          'Min_Turns' => -1,
          'Max_Turns' => -1,
          'Volatile' => true,
          'Dialogue' => 'No Dialogue Has Been Set For This Move',
        ],
        'Foresight' => [
          'Min_Turns' => 2,
          'Max_Turns' => 2,
          'Volatile' => true,
          'Dialogue' => 'No Dialogue Has Been Set For This Move',
        ],
        'Heal Block' => [
          'Min_Turns' => 5,
          'Max_Turns' => 5,
          'Volatile' => true,
          'Dialogue' => 'No Dialogue Has Been Set For This Move',
        ],
        'Identified' => [
          'Min_Turns' => -1,
          'Max_Turns' => -1,
          'Volatile' => true,
          'Dialogue' => 'has been identified!',
        ],
        'Infatuation' => [
          'Min_Turns' => -1,
          'Max_Turns' => -1,
          'Volatile' => true,
          'Dialogue' => 'has become infatuated!',
        ],
        'Leech Seed' => [
          'Min_Turns' => -1,
          'Max_Turns' => -1,
          'Volatile' => true,
        ],
        'Levitate' => [
          'Min_Turns' => 5,
          'Max_Turns' => 5,
          'Volatile' => true,
          'Dialogue' => 'No Dialogue Has Been Set For This Move',
        ],
        'Lock-On' => [
          'Min_Turns' => 1,
          'Max_Turns' => 5,
          'Volatile' => true,
          'Dialogue' => 'has locked on!',
        ],
        'Magic Coat' => [
          'Min_Turns' => 1,
          'Max_Turns' => 1,
          'Volatile' => true,
          'Dialogue' => 'No Dialogue Has Been Set For This Move',
        ],
        'Mimic' => [
          'Min_Turns' => 1,
          'Max_Turns' => 1,
          'Volatile' => true,
          'Dialogue' => 'No Dialogue Has Been Set For This Move',
        ],
        'Minimize' => [
          'Min_Turns' => -1,
          'Max_Turns' => -1,
          'Volatile' => true,
          'Dialogue' => 'No Dialogue Has Been Set For This Move',
        ],
        'Move Locked' => [
          'Min_Turns' => 1,
          'Max_Turns' => 5,
          'Volatile' => true,
          'Dialogue' => 'No Dialogue Has Been Set For This Move',
        ],
        'Nightmare' => [
          'Min_Turns' => -1,
          'Max_Turns' => -1,
          'Volatile' => true,
          'Dialogue' => 'No Dialogue Has Been Set For This Move',
        ],
        'No Guard' => [
          'Min_Turns' => 1,
          'Max_Turns' => 5,
          'Volatile' => true,
          'Dialogue' => 'No Dialogue Has Been Set For This Move',
        ],
        'Perish Song' => [
          'Min_Turns' => 3,
          'Max_Turns' => 3,
          'Volatile' => true,
          'Dialogue' => 'No Dialogue Has Been Set For This Move',
        ],
        'Protect' => [
          'Min_Turns' => 1,
          'Max_Turns' => 1,
          'Volatile' => true,
          'Dialogue' => 'No Dialogue Has Been Set For This Move',
        ],
        'Semi-Invulnerability' => [
          'Min_Turns' => 1,
          'Max_Turns' => 5,
          'Volatile' => true,
          'Dialogue' => 'No Dialogue Has Been Set For This Move',
        ],
        'Stockpile' => [
          'Min_Turns' => -1,
          'Max_Turns' => -1,
          'Volatile' => true,
          'Dialogue' => 'No Dialogue Has Been Set For This Move',
          'Min_Stacks' => 0,
          'Max_Stacks' => 3,
          'Stacks' => 0
        ],
        'Substitute' => [
          'Min_Turns' => 1,
          'Max_Turns' => -1,
          'Volatile' => true,
          'Dialogue' => 'No Dialogue Has Been Set For This Move',
        ],
        'Recharging' => [
          'Min_Turns' => 1,
          'Max_Turns' => 1,
          'Volatile' => true,
          'Dialogue' => 'No Dialogue Has Been Set For This Move',
        ],
        'Rooted' => [
          'Min_Turns' => -1,
          'Max_Turns' => -1,
          'Volatile' => true,
          'Dialogue' => 'No Dialogue Has Been Set For This Move',
        ],
        'Taunt' => [
          'Min_Turns' => 3,
          'Max_Turns' => 3,
          'Volatile' => true,
          'Dialogue' => 'No Dialogue Has Been Set For This Move',
        ],
        'Telekenisis' => [
          'Min_Turns' => -1,
          'Max_Turns' => -1,
          'Volatile' => true,
          'Dialogue' => 'No Dialogue Has Been Set For This Move',
        ],
        'Torment' => [
          'Min_Turns' => -1,
          'Max_Turns' => -1,
          'Volatile' => true,
          'Dialogue' => 'No Dialogue Has Been Set For This Move',
        ],
        'Transformed' => [
          'Min_Turns' => -1,
          'Max_Turns' => -1,
          'Volatile' => true,
          'Dialogue' => 'No Dialogue Has Been Set For This Move',
        ],
        'Trap' => [
          'Min_Turns' => -1,
          'Max_Turns' => -1,
          'Volatile' => true,
          'Dialogue' => 'is being trapped!',
        ],
        'Withdrawing' => [
          'Min_Turns' => 1,
          'Max_Turns' => 1,
          'Volatile' => true,
          'Dialogue' => 'No Dialogue Has Been Set For This Move',
        ],
      ];
    }
  }
