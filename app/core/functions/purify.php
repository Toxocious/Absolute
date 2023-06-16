<?php
  /**
   * Filters user inputs.
   * Add more parameters later on for more diversity.
   *
   * @param {$Input}
   */
  function Purify($Input)
  {
    if ( !$Input )
      return false;

    $Input_Type = gettype($Input);
    $Input_As_Text = $Input;

    if ( is_array($Input_As_Text) )
    {
      foreach ( $Input_As_Text as $K => $V )
      {
        $V = htmlentities($V, ENT_NOQUOTES, "UTF-8");
        $V = nl2br($V, false);
        $Input_As_Text[$K] = $V;
      }
    }
    else
    {
      $Input_As_Text = htmlentities($Input_As_Text, ENT_NOQUOTES, "UTF-8");
      $Input_As_Text = nl2br($Input_As_Text, false);
    }

    /**
     * Return the variable as it's original type.
     */
    switch ( $Input_Type )
    {
      case 'boolean':
        return (bool) $Input_As_Text;
      case 'integer':
        return (integer) $Input_As_Text;
      case 'double':
        return (double) $Input_As_Text;
      case 'string':
        return (string) $Input_As_Text;
      case 'array':
        return (array) $Input_As_Text;
      case 'object':
        return (object) $Input_As_Text;
      case 'NULL':
        return null;
    }

    return false;
  }
