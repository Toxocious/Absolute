<?php
  /**
   * Get and return all releasable Pokemon that the user has.
   */
  function GetReleasablePokemon()
  {
    global $PDO, $User_Data;

    try
    {
      $Get_Releasable_Pokemon = $PDO->prepare("
        SELECT `ID`
        FROM `pokemon`
        WHERE `Owner_Current` = ? AND `Location` = 'Box' AND `Frozen` = 0
      ");
      $Get_Releasable_Pokemon->execute([
        $User_Data['ID']
      ]);
      $Get_Releasable_Pokemon->setFetchMode(PDO::FETCH_ASSOC);
      $Releasable_Pokemon = $Get_Releasable_Pokemon->fetchAll();
    }
    catch ( PDOException $e )
    {
      HandleError($e);
    }

    if ( !empty($Releasable_Pokemon) )
    {
      foreach ( $Releasable_Pokemon as $Index => $Pokemon )
      {
        $Pokemon_Info = GetPokemonData($Pokemon['ID']);

        $Releasable_Pokemon[$Index] = [
          'ID' => $Pokemon_Info['ID'],
          'Display_Name' => $Pokemon_Info['Display_Name'],
          'Gender' => $Pokemon_Info['Gender'],
          'Level' => $Pokemon_Info['Level']
        ];
      }
    }

    return [
      'Amount' => count($Releasable_Pokemon),
      'Pokemon' => $Releasable_Pokemon
    ];
  }

  /**
   * Process all of the Pokemon that were selected for release.
   *
   * @param $Selected_Pokemon
   */
  function ProcessSelectedPokemon
  (
    $Selected_Pokemon
  )
  {
    global $PDO, $User_Data;

    if ( empty($Selected_Pokemon) )
    {
      return [
        'Success' => false,
        'Message' => 'You did not select any Pok&esacute;mon to release.'
      ];
    }

    $Selected_Pokemon_Array = json_decode($Selected_Pokemon);

    $_SESSION['EvoChroniclesRPG']['Release']['Releasable_Pokemon'] = [];

    foreach ( $Selected_Pokemon_Array as $Pokemon )
    {
      try
      {
        $Check_Pokemon_Ownership = $PDO->prepare("
          SELECT `ID`
          FROM `pokemon`
          WHERE `ID` = ? AND `Owner_Current` = ?
          LIMIT 1
        ");
        $Check_Pokemon_Ownership->execute([
          $Pokemon,
          $User_Data['ID']
        ]);
        $Check_Pokemon_Ownership->setFetchMode(PDO::FETCH_ASSOC);
        $Pokemon_Ownership = $Check_Pokemon_Ownership->fetch();
      }
      catch ( PDOException $e )
      {
        HandleError($e);
      }

      if ( empty($Pokemon_Ownership) )
        continue;

      $Pokemon_Info = GetPokemonData($Pokemon);

      $_SESSION['EvoChroniclesRPG']['Release']['Releasable_Pokemon'][] = [
        'ID' => $Pokemon,
        'Display_Name' => $Pokemon_Info['Display_Name'],
        'Gender' => $Pokemon_Info['Gender'],
        'Level' => $Pokemon_Info['Level']
      ];
    }

    return [
      'Pokemon' => $_SESSION['EvoChroniclesRPG']['Release']['Releasable_Pokemon']
    ];
  }

  /**
   * Finalize the release process of the selected Pokemon.
   */
  function FinalizeRelease()
  {
    global $User_Data;

    foreach ( $_SESSION['EvoChroniclesRPG']['Release']['Releasable_Pokemon'] as $Pokemon )
    {
      ReleasePokemon($Pokemon['ID'], $User_Data['ID']);
    }

    return [
      'Success' => true,
      'Message' => 'You have released the selected Pok&eacute;mon'
    ];
  }
