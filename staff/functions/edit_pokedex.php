<?php
  /**
   * Show an HTML dropdown of all possible pokedex entries.
   */
  function ShowPokedexDropdown()
  {
    global $PDO;

    try
    {
      $Get_Pokedex_Entries = $PDO->prepare("
        SELECT `ID`, `Pokedex_ID`, `Alt_ID`, `Pokemon`, `Forme`
        FROM `pokedex`
        ORDER BY `Pokedex_ID` ASC, `Alt_ID` ASC
      ");
      $Get_Pokedex_Entries->execute([ ]);
      $Get_Pokedex_Entries->setFetchMode(PDO::FETCH_ASSOC);
      $Pokedex_Entries = $Get_Pokedex_Entries->fetchAll();
    }
    catch ( PDOException $e )
    {
      HandleError($e);
    }

    if ( empty($Pokedex_Entries) )
    {
      return "
        <select name='pokedex_entries'>
          <option>There are no Pok&eacute;dex entries</option>
        </select>
      ";
    }

    $Dropdown_Entries = '';
    foreach ( $Pokedex_Entries as $Pokemon )
    {
      if ( $Pokemon['Forme'] !== null )
        $Display_Name = $Pokemon['Pokemon'] . " " . $Pokemon['Forme'];
      else
        $Display_Name = $Pokemon['Pokemon'];

      $Dropdown_Entries .= "
        <option value='{$Pokemon['ID']}'>
          {$Display_Name}
        </option>
      ";
    }

    return "
      <select name='pokedex_entries' onchange='ShowPokedexEntry();'>
        <option>Select A Pok&eacute;mon</option>
        {$Dropdown_Entries}
      </select>
    ";
  }
