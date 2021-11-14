<?php
  namespace TMXParser;

  class Parser
  {
    /**
     * Parse the given .tmx map file.
     *
     * @param {string} $TMX_Filename
     */
    public function Parse
    (
      string $TMX_Filename
    )
    {
      $File_Content = file_get_contents($TMX_Filename);

      // Convert the file contents to a parsable XML element.
      $XML_Obj = new \SimpleXMLElement($File_Content);

      // Get the map's attributes.
      $Map = new TmxMap;
      $this->GetTiledObjectFromXmlElement($XML_Obj, $Map);

      /**
       * Parse .tmx <properties> data.
       */
      if ( !empty($XML_Obj->properties) )
      {
        foreach ( $XML_Obj->properties->property as $Property_Obj )
        {
          $Property = new TmxProperty;
          $this->GetTiledObjectFromXmlElement($Property_Obj, $Property);
          $Map->properties[$Property->Name] = $Property->Value;
        }
      }

      /**
       * Parse .tmx <tileset> data.
       * This will also grab each tile of the tileset, including its properties.
       */
      foreach ( $XML_Obj->tileset as $Tileset_Obj )
      {
        $Tileset = new TmxTileset;
        $this->GetTiledObjectFromXmlElement($Tileset_Obj, $Tileset);
        $Tileset->ProcessTileset();
        $Map->tilesets[$Tileset->name] = $Tileset;
      }

      /**
       * Parse .tmx <objectgroup> data.
       */
      if ( !empty($XML_Obj->objectgroup) )
      {
        /**
         * Iterate over every object group.
         */
        foreach ( $XML_Obj->objectgroup as $Object_Group )
        {
          /**
           * Iterate over every object within the object group.
           */
          foreach ( $Object_Group->object as $Current_Object )
          {
            $Object = new TmxMapObject($Current_Object);
            $Map->objects[] = $Object;
          }
        }
      }

      /**
       * Process layer data.
       */
      $Map = $this->ProcessLayers($XML_Obj, $Map);

      // Unset used vars that no longer need to be initialized.
      unset($XML_Obj);
      unset($File_Content);
      unset($TMX_Filename);
      unset($Property);
      unset($Property_Obj);
      unset($Tileset);
      unset($Tileset_Obj);
      unset($Object);
      unset($Current_Object);
      unset($Object_Group);

      /**
       * Dump current $Map object data.
       */
      return $Map;
    }

    /**
     * Parse and process layer data.
     * Will use cached map data if possible.
     */
    public function ProcessLayers
    (
      $XML_Obj,
      $Map_Data
    )
    {
      if ( empty($_SESSION['Absolute']['Maps']['Cache']['Layers']) )
      {
        foreach ( $XML_Obj->layer as $Current_Layer )
        {
          $Layer = new TmxLayer($Current_Layer);
          $this->GetTiledObjectFromXmlElement($Current_Layer, $Layer);

          $Map_Data->layers[] = $Layer;
        }
      }
      else
      {
        foreach ( $_SESSION['Absolute']['Maps']['Cache']['Layers'] as $Current_Layer )
        {
          $Layer = new TmxLayer($Current_Layer);
          $Map_Data->layers[] = $Layer;
        }
      }

      return $Map_Data;
    }

    /**
     * Convert XML element to a TiledObject.
     */
    public function GetTiledObjectFromXmlElement
    (
      \SimpleXMLElement $Element,
      $TmxObject
    )
    {
      foreach ( $Element->attributes() as $Name => $Value )
      {
        $Name = (string)$Name;
        $TmxObject->$Name = (string)$Value;
      }
    }
  }
