<?php namespace TiledTmx;

abstract class TiledObject
{
    public function __get($name)
    {
        $name = strtolower($name);
        return $this->$name;
    }

    public function __set($name, $value)
    {
        $name = strtolower($name);
        $this->$name = $value;
    }
}
