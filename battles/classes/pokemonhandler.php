<?php
  use BattleHandler\Battle;

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

      $this->ProcessPokemon();
    }

    public function ProcessPokemon()
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
      $_SESSION['Battle']['Ally']['Active'] = $this;

      return [
        'Type' => 'Success',
        'Text' => "You have switched your {$this->Display_Name} into battle!"
      ];
    }
  }
