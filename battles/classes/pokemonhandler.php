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

    public $Stats = null;

    public $IVs = null;
    public $EVs = null;

    public $Moves = null;
    public $Ability = null;

    public $Item = null;

    public $Status = null;
    public $Status_Stage = null;

    public $Last_Move = null;

    public $Sprite = null;
    public $Icon = null;

    public $Fainted = false;

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

      $this->Fainted = false;

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

      $this->Sprite = $Pokemon['Sprite'];
      $this->Icon = $Pokemon['Icon'];
      $this->Display_Name = $Pokemon['Display_Name'];
      $this->Shiny = ($Pokemon['Type'] == 'Shiny' ? true : false);
      $this->Ability = $Pokemon['Ability'];
      $this->Gender = $Pokemon['Gender'];
      $this->Level = $Pokemon['Level_Raw'];
      $this->HP = $Pokemon['Stats'][0];
      $this->Max_HP = $Pokemon['Stats'][0];
      $this->Exp = $Pokemon['Experience_Raw'];
      $this->Exp_Needed = FetchExpToNextLevel($Pokemon['Experience_Raw'], 'Pokemon', true);
      $this->Stats = [
        'Base' => [
          'Attack' => $Pokemon['Stats'][1],
          'Defense' => $Pokemon['Stats'][2],
          'SpAttack' => $Pokemon['Stats'][3],
          'SpDefense' => $Pokemon['Stats'][4],
          'Speed' => $Pokemon['Stats'][5],
        ],
        'Current' => [
          'Attack' => $Pokemon['Stats'][1],
          'Defense' => $Pokemon['Stats'][2],
          'SpAttack' => $Pokemon['Stats'][3],
          'SpDefense' => $Pokemon['Stats'][4],
          'Speed' => $Pokemon['Stats'][5],
        ],
      ];
      $this->IVs = $Pokemon['IVs'];
      $this->EVs = $Pokemon['EVs'];
      $this->Moves = [
        new Move($Pokemon['Move_1']),
        new Move($Pokemon['Move_2']),
        new Move($Pokemon['Move_3']),
        new Move($Pokemon['Move_4']),
      ];
      $this->Item = new HeldItem($Pokemon['Item_ID']);

      return $this;
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

      if ( $this->HP == 0 )
      {
        return [
          'Type' => 'Error',
          'Text' => 'The Pok&eacute;mon that you\'re switching into is fainted.',
        ];
      }

      foreach ($_SESSION['Battle']['Ally']['Roster'] as $Roster_Pokemon)
        if ( $Roster_Pokemon->Pokemon_ID != $this->Pokemon_ID )
          $Roster_Pokemon->Active = false;
        else
          $Roster_Pokemon->Active = true;

      $_SESSION['Battle']['Ally']['Active'] = $this;

      return [
        'Type' => 'Success',
        'Text' => "You have sent {$this->Display_Name} into battle!"
      ];
    }
  }
