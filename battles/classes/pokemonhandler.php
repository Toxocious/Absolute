<?php
  class PokemonHandler
  {
    public $Pokemon_ID = null;

    public $Display_Name = null;

    public $Side = null;
    public $Active = null;
    public $Slot = null;

    public $Shiny = null;
    public $Gender = null;
    public $Level = null;

    public $HP = null;
    public $Max_HP = null;

    public $Primary_Type = null;
    public $Secondary_Type = null;

    public $Stats = null;

    public $IVs = null;
    public $EVs = null;

    public $Moves = null;
    public $Ability = null;

    public $Item = null;

    /**
     * Should contain an array of arrays, if not null.
     * Each child array containing data of the status.
     *
     * [
     *  'Type' => 'Burn',
     *  'Turns_Left' => 3,
     * ],
     * [
     *  'Type' => 'Confusion',
     *  'Turns_Left' => 1
     * ],
     */
    public $Statuses = null;

    public $Last_Move = null;

    public $Sprite = null;
    public $Icon = null;

    public $Participated = false;
    public $Fainted = false;

    public $Dialogue = null;

    public function __construct
    (
      $Pokemon_ID,
      $Side,
      $Slot
    )
    {
      $this->Pokemon_ID = $Pokemon_ID;
      $this->Side = $Side;
      $this->Slot = $Slot;
      $this->Active = ($Slot == 1 ? true : false);

      $this->SetupPokemon();
    }

    /**
     * Setup the specified Pokemon for the battle.
     * Sets the base data of the Pokemon.
     * Called once per Pokemon per battle.
     */
    public function SetupPokemon()
    {
      global $Poke_Class;

      $Pokemon = $Poke_Class->FetchPokemonData($this->Pokemon_ID);
      if ( !$Pokemon )
        return false;

      $this->Pokedex_ID = $Pokemon['Pokedex_ID'];
      $this->Alt_ID = $Pokemon['Alt_ID'];
      $this->Sprite = $Pokemon['Sprite'];
      $this->Icon = $Pokemon['Icon'];
      $this->Display_Name = $Pokemon['Display_Name'];
      $this->Shiny = ($Pokemon['Type'] == 'Shiny' ? true : false);
      $this->Ability = $Pokemon['Ability'];
      $this->Gender = $Pokemon['Gender'];
      $this->Level = $Pokemon['Level_Raw'];
      $this->HP = $Pokemon['Stats'][0];
      $this->Max_HP = $Pokemon['Stats'][0];
      $this->Primary_Type = $Pokemon['Type_Primary'];
      $this->Secondary_Type = $Pokemon['Type_Secondary'];
      $this->Exp = $Pokemon['Experience_Raw'];
      $this->Exp_Needed = FetchExpToNextLevel($Pokemon['Experience_Raw'], 'Pokemon', true);
      $this->Exp_Yield = $Pokemon['Exp_Yield'];
      $this->Owner_Original = $Pokemon['Owner_Original'];
      $this->Owner_Current = $Pokemon['Owner_Current'];
      $this->Stats = [
        'Base' => [
          'Attack' => $Pokemon['Stats'][1],
          'Defense' => $Pokemon['Stats'][2],
          'SpAttack' => $Pokemon['Stats'][3],
          'SpDefense' => $Pokemon['Stats'][4],
          'Speed' => $Pokemon['Stats'][5],
          'Accuracy' => 100,
          'Evasion' => 100,
        ],
        'Current' => [
          'Attack' => $Pokemon['Stats'][1],
          'Defense' => $Pokemon['Stats'][2],
          'SpAttack' => $Pokemon['Stats'][3],
          'SpDefense' => $Pokemon['Stats'][4],
          'Speed' => $Pokemon['Stats'][5],
          'Accuracy' => 100,
          'Evasion' => 100,
        ],
      ];
      $this->IVs = $Pokemon['IVs'];
      $this->EVs = $Pokemon['EVs'];
      $this->Moves = [
        new Move($Pokemon['Move_1'], 0),
        new Move($Pokemon['Move_2'], 1),
        new Move($Pokemon['Move_3'], 2),
        new Move($Pokemon['Move_4'], 3),
      ];
      $this->Item = new HeldItem($Pokemon['Item_ID']);

      return $this;
    }

    /**
     * Perform an attack via the client's active Pokemon.
     * @param $Move
     */
    public function Attack
    (
      $Move
    )
    {
      if ( !isset($Move) )
      {
        return [
          'Type' => 'Error',
          'Text' => 'Select a valid move to attack with.<br />'
        ];
      }

      if ( $this->Fainted )
      {
        return [
          'Type' => 'Error',
          'Text' => "{$this->Display_Name} is fainted and can not attack.<br />"
        ];
      }

      if ( $this->HP == 0 )
      {
        return [
          'Type' => 'Error',
          'Text' => "{$this->Display_Name} is currently fainted, and may not attack.<br />"
        ];
      }

      return $this->Moves[$Move->Slot]->ProcessAttack($this->Side);
    }

    /**
     * Switch into the desired Pokemon.
     */
    public function SwitchInto()
    {
      if ( $this->Active )
      {
        return [
          'Type' => 'Error',
          'Text' => 'The Pok&eacute;mon you\'re switching into is already active!'
        ];
      }

      foreach ($_SESSION['Battle']['Ally']['Roster'] as $Roster_Pokemon)
        $Roster_Pokemon->Active = false;
        if ( $Roster_Pokemon->Pokemon_ID == $this->Pokemon_ID )
          $Roster_Pokemon->Active = true;

      $this->Participated = true;
      $_SESSION['Battle']['Ally']['Active'] = $this;

      if ( $this->HP == 0 )
      {
        return [
          'Type' => 'Error',
          'Text' => 'The Pok&eacute;mon that you\'re switching into is fainted.',
        ];
      }

      return [
        'Type' => 'Success',
        'Text' => "You have sent {$this->Display_Name} into battle!"
      ];
    }

    /**
     * Increase the Pokemon's current HP.
     */
    public function IncreaseHP
    (
      int $Heal
    )
    {
      $this->HP -= $Heal;

      if ( $this->HP > $this->Max_HP )
        $this->HP = 0;

      return $this->HP;
    }

    /**
     * Decrease the Pokemon's current HP.
     */
    public function DecreaseHP
    (
      int $Damage
    )
    {
      $this->HP -= $Damage;

      if ( $this->HP < 0 )
        $this->HP = 0;

      return $this->HP;
    }

    /**
     * Fetch the desired move object.
     */
    public function FetchMove
    (
      $Move
    )
    {
      return $this->Moves[$Move];
    }

    /**
     * Fetch a random move from the selected Pokemon.
     */
    public function FetchRandomMove()
    {
      $Fetch_Move = mt_rand(0, (count($this->Moves) - 1));

      return $this->Moves[$Fetch_Move];
    }

    /**
     * Determine if the Pokemon has a given status ailment.
     */
    public function HasStatus
    (
      $Status
    )
    {
      if ( isset($this->Statuses[$Status]) )
        return true;

      return false;
    }
  }
