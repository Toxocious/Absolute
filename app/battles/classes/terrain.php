<?php
  class Terrain
  {
    public $Name = null;
    public $Turns_Left = null;
    public $Dialogue = null;

    public function __construct
    (
      string $Terrain_Name,
      int $Terrain_Turns = null
    )
    {
      $Terrain_Data = $this->TerrainList()[$Terrain_Name];
      if ( !isset($Terrain_Data) )
        return false;

      if ( !isset($Terrain_Turns) )
        $Terrain_Turns = 5;
      else
        $Terrain_Turns = $Terrain_Turns;

      foreach (['Ally', 'Foe'] as $Side)
      {
        $Active_Pokemon = $_SESSION['EvoChroniclesRPG']['Battle'][$Side]->Active;

        switch ($this->Name)
        {
          case 'Electric':
            if ( $Active_Pokemon->Ability->Name == 'Surge Surfer' )
            {
              $Active_Pokemon->Stats['Speed']->Current_Value *= 2;
            }
            break;

          case 'Grassy':
            if ( $Active_Pokemon->Ability->Name == 'Grassy Pelt' )
            {
              $Active_Pokemon->Stats['Defense']->Current_Value *= 1.5;
            }
            break;
        }
      }

      $this->Name = $Terrain_Name;
      $this->Turns_Left = $Terrain_Turns;
      $this->Dialogue = $Terrain_Data['Dialogue'];

      unset($_SESSION['EvoChroniclesRPG']['Battle']['Terrain']);
      $_SESSION['EvoChroniclesRPG']['Battle']['Terrain'] = $this;

      return $this;
    }

    /**
     * Decrement the turn count.
     */
    public function TickTerrain()
    {
      if ( $this->Turns_Left > 0 )
        $this->Turns_Left--;

      return $this;
    }

    /**
     * End the current terrain.
     */
    public function EndTerrain()
    {
      foreach (['Ally', 'Foe'] as $Side)
      {
        $Active_Pokemon = $_SESSION['EvoChroniclesRPG']['Battle'][$Side]->Active;

        switch ($this->Name)
        {
          case 'Electric':
            if ( $Active_Pokemon->Ability->Name == 'Surge Surfer' )
            {
              $Active_Pokemon->Stats['Speed']->Current_Value /= 2;
            }
            break;

          case 'Grassy':
            if ( $Active_Pokemon->Ability->Name == 'Grassy Pelt' )
            {
              $Active_Pokemon->Stats['Defense']->Current_Value /= 1.5;
            }
            break;
        }

        if ( $Active_Pokemon->Ability->Name == 'Mimicry' )
        {
          $Active_Pokemon->ResetTyping();
        }
      }
    }

    /**
     * All possible types of terrain.
     */
    public function TerrainList()
    {
      return [
        'Electric' => [
          'Turns' => 5,
          'Dialogue' => 'The ground has been electrified!',
        ],
        'Grassy' => [
          'Turns' => 5,
          'Dialogue' => 'The ground has been turned into grass!',
        ],
        'Misty' => [
          'Turns' => 5,
          'Dialogue' => 'The ground has been covered in mist!',
        ],
        'Psychic' => [
          'Turns' => 5,
          'Dialogue' => 'Pok&eacute;mon on the ground are being protected!'
        ],
      ];
    }
  }
