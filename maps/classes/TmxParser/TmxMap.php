<?php
  namespace TmxParser;

  class TmxMap
  {
    /**
     * Default .tmx file attributes.
     */
    public $version;
    public $tiledversion;
    public $orientation;
    public $renderorder;
    public $width;
    public $height;
    public $tilewidth;
    public $tileheight;
    public $infinite;
    public $nextlayerid;
    public $nextobjectid;

    /**
     * Store the .tmx file's
     *  -> Layers
     *  -> Objects
     *  -> Properties
     *  -> Tilesets
     */
    public $layers;
    public $objects;
    public $properties;
    public $tilesets;
  }
