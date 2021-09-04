<?php
  class Terrain
  {
    public $Name = null;
    public $Turns_Left = null;

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

      $this->Name = $Terrain_Name;
      $this->Turns_Left = $Terrain_Turns;

      unset($_SESSION['Battle']['Terrain']);
      $_SESSION['Battle']['Terrain'] = $this;

      return $this;
    }

    /**
     * Decrement the turn count.
     */
    public function DecrementTurnCount()
    {
      if ( $this->Turns_Left > 0 )
        $this->Turns_Left--;

      return $this;
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
