<?php namespace TiledTmx;

/**
 * <tileset> tag
 */
class TiledTileSet extends TiledObject
{
    var $firstgid;
    var $name;
    var $tilewidth;
    var $tileheight;

    /** @var TiledTileOffset */
    var $tileoffset;

    /** @var TiledImage[] */
    var $image = [];

    /** @var TiledTerrain[] holds the <terraintypes> content tags (<terrain>) */
    var $terraintypes = [];

    /** @var TiledTile[] */
    var $tile = [];
}
