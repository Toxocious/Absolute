<?php
  namespace TmxParser;

  class TmxLayer extends Parser
  {
    public $name;
    public $width;
    public $height;
    public $encoding;
    public $compression;
    public $data;

    public function __construct
    (
      $Layer_Object
    )
    {
      $this->GetTiledObjectFromXmlElement($Layer_Object, $this);

      if ( !empty($Layer_Object->data) )
      {
        if ( !empty($Layer_Object->data->attributes()->encoding) )
          $this->encoding = $Layer_Object->data->attributes()->encoding;

        if ( !empty($Layer_Object->data->attributes()->compression) )
          $this->compression = $Layer_Object->data->attributes()->compression;

        $this->data = $this->DecodeLayer($Layer_Object->data);
      }
    }

    /**
     * Decode the layer.
     */
    public function DecodeLayer
    (
      $Layer_Data
    )
    {
      $Decoded_Layer = null;

      switch ( $this->encoding )
      {
        case 'csv':
          $Decoded_Layer = explode(',',
            str_replace(array("\r", "\n"), '', $Layer_Data)
          );
          break;

        case 'base64':
          $Decoded_Layer = base64_decode($Layer_Data);
          $Decoded_Layer = array_values(unpack("V*", $Decoded_Layer));
          break;

        case 'zlib':
          $Decoded_Layer = zlib_decode($Layer_Data);
          $Decoded_Layer = array_values(unpack("V*", $Decoded_Layer));
          break;

        default:
          throw new \Exception("Unable to process map layer. Encode: {$this->encoding} | Compression: {$this->compression}");
          break;
      }

      return $Decoded_Layer;
    }
  }
