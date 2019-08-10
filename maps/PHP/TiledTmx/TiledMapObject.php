<?php namespace TiledTmx;

/**
 * <object> tag
 */
class TiledMapObject extends TiledObject
{
    /** @var int */
    var $id;

    /** @var string */
    var $name;

    /** @var int */
    var $x;

    /** @var int */
    var $y;

    /** @var int */
    var $width;

    /** @var int */
    var $height;

    /** @var TiledProperty[] */
    var $properties = [];
}
