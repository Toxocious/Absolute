<?php
  require '../../required/session.php';

  /**
   * Determine the current state of the user's release process.
   * Defaults to one.
   */
  $Release_State = (isset($_POST['Release_Stage']) ? $_POST['Release_Stage'] : 1);

  /**
   * Process releasing the user's Pokemon.
   */
  if ( $Release_State == 3 )
  {
    $Release_List = (isset($_POST['Release_List']) ? $_POST['Release_List'] : null);

    if ( count($Release_List) === 0 )
    {
      $Release_Type = "error";
      $Release_Message = "
        You may not release 0 Pok&eacute;mon.
      ";
    }
    else
    {
      $Owner_Of_Pokemon = 0;

      foreach ( $Release_List as $Poke_ID )
      {
        $Poke_ID = Purify($Poke_ID);

        $Release_Pokemon = $Poke_Class->FetchPokemonData($Poke_ID);
  
        if ( $Release_Pokemon['Owner_Current'] === $User_Data['id'] )
        {
          $Owner_Of_Pokemon++;

          $Poke_Class->ReleasePokemon($Release_Pokemon['ID'], $User_Data['id']);
        }
      }

      $Release_Type = "success";
      $Release_Message = "You have successfully released <b>{$Owner_Of_Pokemon}</b> Pok&eacute;mon.";

      // The user attempted to release at least one Pokemon that didn't belong to them.
      if ( $Owner_Of_Pokemon !== count($Release_List) )
      {
        $Unowned_Count = count($Release_List) - $Owner_Of_Pokemon;

        $Release_Message .= "
          <br />
          You attempted to release at least <b>{$Unowned_Count}</b> Pok&eacute;mon that didn't belong to you.
        ";
      }
    }

    // Set the $Release_State back to 1, so that the user's Pokemon will again be displayed.
    $Release_State = 1;

    echo "
      <div class='{$Release_Type}'>
        {$Release_Message}
      </div>
    ";
  }

  /**
   * The user has chosen the Pokemon that they want to release.
   * Let them double-check what they have chosen to release.
   */
  if ( $Release_State == 2 )
  {
    $Release_List = (isset($_POST['Release_List']) ? $_POST['Release_List'] : null);

    if ( count($Release_List) === 0 )
    {
      $Release_Text = "
        <tr>
          <td colspan='3'>
            You are trying to release 0 Pok&eacute;mon. Please try again.
          </td>
        </tr>
      ";
    }
    else
    {
      $Release_Text = "
        <tr>
          <td colspan='3'>
            <button onclick='ReleasePokemon(3);'>
              Release Pok&eacute;mon
            </button>
          </tr>
        </tr>
      ";

      foreach ( $Release_List as $Poke_ID )
      {
        $Poke_ID = Purify($Poke_ID);

        $Release_Pokemon = $Poke_Class->FetchPokemonData($Poke_ID);

        if ( $Release_Pokemon['Owner_Current'] === $User_Data['id'] )
        {
          $Release_Text .= "
            <tr>
              <td colspan='1'>
                <img src='{$Release_Pokemon['Icon']}' />
              </td>
              <td colspan='2'>
                <b>{$Release_Pokemon['Display_Name']}</b>
                <br />
                " . ($Release_Pokemon['Nickname'] ? $Release_Pokemon['Nickname'] . '<br />' : '') . "
                (Level: {$Release_Pokemon['Level']})
              </td>
            </tr>
          ";
        }
      }
    }

    echo "
      <div class='description'>
        You have chosen to release the following Pok&eacute;mon.
        <br />
        Double check, and confirm that you would like to release each of them.
      </div>

      <table class='border-gradient' style='margin: 0 auto; width: 300px;'>
        <thead>
          <tr>
            <th colspan='3'>
              Release List
            </th>
          </tr>
        </thead>

        <tbody>
          {$Release_Text}
        </tbody>
      </table>
    ";
  }

  /**
   * The user needs to pick which Pokemon that they want to release.
   */
  if ( $Release_State == 1 )
  {
    try
    {
      $Select_Query = $PDO->prepare("SELECT `ID` FROM `pokemon` WHERE `Location` = 'Box' AND `Slot` = 7 AND `Owner_Current` = ? ORDER BY `Pokedex_ID` ASC, `Alt_ID` ASC, `ID` ASC");
      $Select_Query->execute([ $User_Data['id'] ]);
      $Select_Query->setFetchMode(PDO::FETCH_ASSOC);
      $Result = $Select_Query->fetchAll();
      $Result_Count = $Select_Query->rowCount();
    }
    catch ( PDOException $e )
    {
      HandleError($e);
    }
?>

<div class='description'>
  Any Pok&eacute;mon that you no longer want to keep are able to be released here.
</div>

<div class='panel' style='margin: 0 auto;'>
  <div class='head'>Release Pok&eacute;mon</div>
  <div class='body' style='padding: 5px;'>
    You own <b><?= number_format($Result_Count); ?></b> Pok&eacute;mon.
    <br />
    
    <select id='ReleaseList' name='Release[]' multiple='multiple' style='height: 400px; width: 300px;'>
      <?php
        foreach ( $Result as $Pokemon )
        {
          $Pokemon = $Poke_Class->FetchPokemonData($Pokemon['ID']);

          $Pokemon['Gender'] = substr($Pokemon['Gender'], 0, 1);

          echo "
            <option value='{$Pokemon['ID']}'>
              {$Pokemon['Display_Name']} 
              {$Pokemon['Gender']} 
              (Level: {$Pokemon['Level']})
            </option>
          ";
        }
      ?>
    </select>
    <br />

    <button onclick='ReleasePokemon(2);'>
      Release Pok&eacute;mon
    </button>
  </div>
</div>

<?php
  }