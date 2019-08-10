<?php
	class Sync
	{
		public function __construct($Map) 
		{
			$this->Map = $Map;
		}

		private function FetchObjects()
		{
			$objects = [];

			foreach ( $this->Map->Objects as $map_object )
			{
				$object = $map_object->Object;
				if ( isset($map_object->hidden) )
				{
					continue;
				}

				$objects[] =
				[
					'object_id' => $object->properties['object_id'],
					'active' 		=> $map_object->isActiveOnMap(),
				];
			}
			
			return $objects;
		}

		public function RequiredTilesets() 
		{
			$tilesets = [];
			foreach ($this->Map->TiledMap->tileset as $tileset) 
			{
				$tilesets[] = $tileset->name;
			}

			return $tilesets;
		}

		public function Load() 
		{
			return
			[
				'map_name'	=> $this->Map->Player->FetchMap(),
				'objects'		=> $this->FetchObjects(),
				'position'	=> $this->Map->Player->FetchPosition(),
				'tilesets'	=> $this->RequiredTilesets(),
			];
		}
	}