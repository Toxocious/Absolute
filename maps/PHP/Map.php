<?php
	use TiledTmx\Parser;

	Class Map
	{
		public $Tiled_File;
		public $Objects = [];
		public $Player;
		public $Output;

		/**
		 * Constructor function.
		 */
		public function __construct()
		{
			$this->Player = Player::FetchInstance();

			$Tiled_File = 'maps/' . $this->Player->FetchMap() . '.tmx';

			/**
			 * If the map doesn't exist, set the player to the spawn.
			 */
			if ( !file_exists($Tiled_File) )
			{
				$this->Player->SetPosition(true);
				$Tiled_File = 'maps/' . $this->Player->FetchMap() . '.tmx';
			}

			/**
			 * Fetch the objects of the map that you're on.
			 */
			$this->TiledMap = (new Parser)->parseFile($Tiled_File);
			foreach ( $this->TiledMap->objects as $Object )
			{
				/**
				 * The object is set and is hidden.
				 */
				if ( isset($Object->properties['hidden']) )
				{
					continue;
				}

				/**
				 * The object is missing a required property.
				 */
				if ( !isset($Object->properties['object_class']) )
				{
					throw new Exception("An object is missing the 'object_class' property.");
				}

				$Class = 'Objects\\'. $Object->properties['object_class'] . 'Object';
				$this->Objects[] = new $Class($this, $Object);
			}
		}

		/**
		 * Random quips to output.
		 */
		public function Quip()
		{
			$Quips =
			[
				"Dun, dun, dun..",
				"Can't you find something?",
				"What are you doing out here?",
				"Nice weather, eh?",
				"Derp~",
			];

			$Quip_Chance = mt_rand(1, 7);
			if ( $Quip_Chance == 4 )
			{
				$Random_Quip = $Quips[mt_rand(1, count($Quips)) - 1];

				$this->TextAdd($Random_Quip);
				$_SESSION['Maps']['Quip'] = $Random_Quip;
			}
			else
			{
				$this->TextAdd($_SESSION['Maps']['Quip']);
			}
		}

		/**
		 * Move the player to the new position if there is no collision.
		 */
		public function Move()
		{
			global $User_Data;

			$Position = $this->Player->FetchPosition();
			$Tile_Current = $this->FetchTile( $Position['x'], $Position['y'], $Position['z'] );

			/**
			 * Check to see if you're able to encounter a Pokemon given the tile that you're standing on.
			 */
			if ( $Tile_Current->isEncounter() )
			{
				/**
				 * Chance to encounter something is 1 in MapProps['encounter_chance'].
				 */
				if ( mt_rand(1, 5) == 4 )
				{
					$this->OutputAdd( "Encounter", mt_rand(1, 5) );

					$Encounter = new Encounter($this);
					if ( $Encounter->GenerateEncounter() )
					{
						$Encounter->DisplayEncounter();
					}
				}
				else
				{
					$this->Quip();
				}
			}
			else
			{
				$this->Quip();
			}
		}

		/**
		 * Determine if the player can move.
		 */
		public function MoveCheck($x, $y, $z)
		{
			$Pos = $this->Player->FetchPosition();

			$Tile_Current = $this->FetchTile( $Pos['x'], $Pos['y'], $Pos['z'] );
			$Tile_Wanted	= $this->FetchTile( $x, $y, $z );

			//$this->TextAdd("My Pos: {$Pos['x']}, {$Pos['y']}, {$Pos['z']} => ");
			//$this->TextAdd("Moving To: {$x}, {$y}, {$z}");

			$Delta_X = $x - $Pos['x'];
			$Delta_Y = $y - $Pos['y'];

			/**
			 * If you're in an encounter, don't do anything.
			 */
			//if ( isset($_SESSION['Maps']['Encounter']) )
			//{
			//	$this->TextAdd("Can't move! Currently in an encounter.");
			//}
			//else
			//{
				/**
				 * Check if the user is on ice or not.
				 */
				if ( $Delta_X < -1 || $Delta_X > 1 || $Delta_Y < -1 || $Delta_Y > 1 )
				{
					if ( !$Tile_Wanted->Slippery() && !$Tile_Current->Slippery() )
					{
						return false;
					}
				}

				/**
				 * Determine if the surfaces are the same, are water and land, and is walkable.
				 * Also verify that the z pos is the same, or the current tile is a stair tile.
				 */
				if
				(
					( 
						$Tile_Current->FetchSurface() == $Tile_Wanted->FetchSurface() ||
						( $Tile_Current->FetchSurface() == "Water" && $Tile_Wanted->FetchSurface() == "Land" )
					) &&
					$Tile_Current->Walkable() &&
					( $Pos['z'] == $z || $Tile_Current->Stairs() )
				)
				{
					return true;
				}
				else
				{
					return true;
				}
			//}
		}

		/**
		 * Handle interactions from the user.
		 */
		public function Interact($x, $y, $z)
		{
			$Object = $this->FetchObject( $x, $y, $z );

			/**
			 * An object has been found at this location.
			 */
			if ( $Object && $Object->CanInteract() )
			{
				$Object->Interact();
				return true;
			}

			/**
			 * Fetch the player's position, as well as tile data for the current tile, and the desired tile.
			 */
			$Position = $this->Player->FetchPosition();
			$Tile_Current = $this->FetchTile( $Position['x'], $Position['y'], $Position['z'] );
			$Tile_Interact = $this->FetchTile( $x, $y, $z );

			/**
			 * Determine if the tile you're moving to is a water tile; if so, let the player surf.
			 * Update the player's position as well.
			 */
			if ( $Tile_Current->FetchSurface() == "land" && $Tile_Interact->FetchSurface() == "water" )
			{
				$this->OutputAdd("start_surfing", true);
				$this->Player->SetPosition(false, $x, $y, $z);
				
				return true;
			}

			return false;
		}

		/**
		 * Function to warp the player if they walk onto a warp tile.
		 */
		public function Warp($x, $y, $z)
		{
			/**
			 * Fetch the object and it's properties.
			 * Set the user's position to the object's x,y,z coordinates.
			 */
			$Object = $this->FetchObject( $x, $y, $z );
			$Object_Props = $Object->Object->properties;
			$this->Player->SetPosition( false, $Object_Props['Map_X'], $Object_Props['Map_Y'], $Object_Props['Map_Z'] );

			/**
			 * Warp the player to the correct map if necessary.
			 */
			if ( $this->Player->FetchMap() != $Object_Props['map_id'] )
			{
				$this->Player->SetMap($Object_Props['map_id']);
				$this->OutputAdd('warp_to_map', $Object_Props['map_id']);
			}

			$this->TextAdd("You've been warped!");
		}

		/**
		 * Fetch a particular tile.
		 */
		public function FetchTile($x, $y, $z, $Direction = false)
		{
			/**
			 * Update the x or y value depending on where the direction is moving towards.
			 */
			switch ( $Direction )
			{
				case false:
					break;
				case 'up':
					$y--;
					break;
				case 'down':
					$y++;
					break;
				case 'left':
					$x--;
					break;
				case 'right':
					$x++;
					break;
			}

			/**
			 * Verify that the direction is within the map dimensions.
			 */
			if
			(
				( $x < 0 || $x > $this->TiledMap->width ) ||
				( $y < 0 || $y > $this->TiledMap->height )
			)
			{
				return -1;
			}

			/**
			 * Fetch the layer of the map that the tile is on.
			 */
			$Layer = null;
			foreach ( $this->TiledMap->layer as $Key => $Value )
			{
				if ( $Value->name =='tileinfo_' . $z )
				{
					$Layer = $Value;
					break;
				}
			}

			/**
			 * If the layer hasn't been found, send an error, and crash the user.
			 */
			if ( $Layer == null )
			{
				throw new Exception("tileinfo_" . $z . " wasn't found.");
			}

			return $this->FetchTileInfo($x, $y, $Layer);
		}

		/**
		 * Fetch the tile info of a specific tile, given it's x, y, and z coordinates.
		 */
		public function FetchTileInfo($x, $y, $z)
		{
			$Position = $x + $y * $this->TiledMap->width;

			/**
			 * If the tile's position was found, fetch the tile via the Tile class.
			 * Else, throw an error.
			 */
			if ( isset($z->data[$Position]) )
			{
				$GID = $z->data[$Position];
				$Tileset = $this->TiledMap->FetchTileset($GID);
				$ID = $GID - $Tileset->firstgid + 1;

				return new Tile($ID);
			}
			else
			{
				throw new Exception("The tile ID wasn't found when fetching the tile's information.");
			}
		}

		/**
		 * Fetch an object, given it's x, y, and z coordinates.
		 */
		public function FetchObject($x, $y, $z)
		{
			foreach ( $this->Objects as $Objects )
			{
				$Object = $Objects->Object;
				$Object_X = $Object->x / $this->TiledMap->tilewidth;
				$Object_Y = $Object->y / $this->TiledMap->tileheight;

				/**
				 * If the object has a z coord prop, set it to a native variable.
				 */
				if ( isset($Object->properties['z']) )
				{
					$Object_Z = $Object->properties['z'];
				}

				return $Objects;
			}

			return false;
		}

		/**
		 * Fetch the map's name or ID.
		 */
		public function FetchMapData()
		{
			return $this->TiledMap->properties['map_id'];
		}

		/**
		 * Add text to the stack.
		 */
		public function TextAdd($Text)
		{
			$this->OutputAdd('Text', $Text);
		}

		/**
		 * Throw an error.
		 */
		public function ThrowError($Error, $Code)
		{
			$this->OutputAdd('Error', $Error);
			$this->OutputAdd('Code', $Code);
		}

		/**
		 * Add outputs, given the $Name.
		 */
		public function OutputAdd($Name, $Value)
		{
			if ( !isset($this->Output[$Name]) )
			{
				$this->Output[$Name] = $Value;
			}
			else
			{
				$this->Output[$Name] .= $Value;
			}
		}

		/**
		 * Fetch the given output.
		 */
		public function OutputFetch()
		{
			return $this->Output;
		}

		/**
		 * Debug printing.
		 */
		public function Print() 
    {
        echo '<pre>';
        print_r($this->TiledMap);
        echo '</pre>';
		}
		




		public function FetchSpawnData()
		{
			return [
				'x' => $this->TiledMap->properties['Spawn_X'],
				'y' => $this->TiledMap->properties['Spawn_Y'],
				'z' => $this->TiledMap->properties['Spawn_Z'],
			];
		}

		public function getPokemonLocation() 
    {
        return (string) $this->TiledMap->properties['pokemon_location'];
		}
		
		public function getEncounterRate() 
    {
        return (int) $this->TiledMap->properties['encounter_rate'];
		}
		
		public function tilesToNextEncounter() 
    {
        $encounter_rate = $this->getEncounterRate();

        if ($encounter_rate == 0) 
            return -1;
        
        // at least 2 tiles till encounter
        $tiles = 2;
        while ($encounter_rate < mt_rand(0, 255))
        {
            $tiles++;
        }

        return $tiles;
    }


	} // end of the class