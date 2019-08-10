<?php namespace TiledTmx;

/**
 * Renders a compact JSON object representing a Tiled map
 */
class JsonMapWriter
{
    /**
     * @param TiledMap $map
     * @return string json data
     */
    public static function render(TiledMap $map)
    {
        // exclude some properties from json output: version, encoding, compression
        unset($map->version);
        foreach ($map->layer as $idx => $layer) {
            unset($map->layer[$idx]->encoding);
            unset($map->layer[$idx]->compression);
        }

        return json_encode($map);
    }
}
