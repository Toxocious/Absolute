<?php
	Class Tile
	{
		public $Tile;

		public function __construct($Tile)
		{
			$this->Tile = $Tile;
			$this->generate();
		}

		public function FetchSurface()
		{
			return $this->surface;
		}

		public function Walkable()
		{
			return $this->walkable;
		}

		public function Slippery()
		{
			return isset($this->slippery);
		}

		public function Stairs()
		{
			return isset($this->stair);
		}

		public function isEncounter()
		{
			return isset($this->encounterSlot);
		}

		public function getEncounterSlot()
		{
			return $this->encounterSlot;
		}

		private function generate()
		{
			$Tile = $this->Tile;
			
			// A-Z
			if ($Tile >= 1 && $Tile <= 26)
			{
				$Letters = range('a', 'z');
				$this->walkable = true;
				$this->surface = "land";
				$this->encounterSlot = $Letters[$Tile -1];
			}
			// 0
			else if ($Tile == 119) 
			{
				$this->walkable = true;
				$this->surface = "land";
			}
			// 1
			else if ($Tile == 120) 
			{
				$this->walkable = false;
				$this->surface = "solid";
			}
			// 2-9
			else if ($Tile >= 121 && $Tile <= 128)
			{
				$this->walkable = true;
				$this->surface = "water";
				$this->encounterSlot = $Tile - 119;
			}
			// Triangle Down
			else if ($Tile == 111)
			{
				$this->walkable = true;
				$this->surface = "land";
				$this->stair = true;
			}
			// Poke dollar
			else if ($Tile == 113) 
			{
				$this->walkable = true;
				$this->surface = "land";
				$this->slippery = true;
			}
			// Crash
			else if ($Tile == 114) 
			{
				$this->walkable = true;
				$this->surface = "land";
				$this->crash = true;
			}
			// out of bounds
			else if ($Tile == -1) 
			{
				$this->walkable = true;
				$this->surface = "land";
			}
			// Default
			else 
			{
				$this->walkable = true;
				$this->surface = "land";
			}
		}
	}
