<?php namespace TiledTmx;

/**
 * <map> tag
 */
class TiledMap extends TiledObject
{
    var $version;
    var $orientation;
    var $renderorder = 'right-down';
    var $width;
    var $height;
    var $tilewidth;
    var $tileheight;

    /** @var TiledProperty[] */
    var $properties = [];

    /** @var TiledTileSet[] */
    var $tileset = [];

    /** @var TiledLayer[] */
    var $layer = [];

    /** @var TiledObject[] */
    var $objects = [];

    // returns the tileset that is associated with that id for this map
    public function FetchTileset($id) 
    {
        foreach ($this->tileset as $key => $tileset)
        {
            if ($id >= $tileset->firstgid && $id < $tileset->firstgid + $tileset->tilecount) 
            {
                return $tileset;
            }
        }

        return false;
    }
}
