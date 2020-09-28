<?php
  require '../../required/session.php';

  try
  {
    $Select_Query = $PDO->prepare("SELECT `ID`, `Name`, `Type`, `Gender`, `Experience` FROM `pokemon` WHERE `Location` = 'Box' AND `Slot` = 7 AND `Owner_Current` = ? ORDER BY `Pokedex_ID` ASC, `Alt_ID` ASC, `ID` ASC");
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

<div class='description' style='margin-bottom: 5px;'>
  Any Pok&eacute;mon that you no longer are able to be released here.
</div>

<div class='panel'>
  <div class='head'>Release Pok&eacute;mon</div>
  <div class='body' style='padding: 5px;'>
    You own <b><?= number_format($Result_Count); ?></b> Pok&eacute;mon.
    <br />
    <select id='ReleaseList' name='Release[]' multiple='multiple' onchange='CountSelected(this);' style='height: 400px;'>
      <?php
        foreach ( $Result as $Pokemon )
        {
          if ( $Pokemon['Type'] != 'Normal' )
            $Pokemon['Name'] = $Pokemon['Type'] . $Pokemon['Name'];

          $Pokemon['Gender'] = substr($Pokemon['Gender'], 0, 1);

          $Level = number_format(FetchLevel($Pokemon['Experience'], 'Pokemon'));

          echo "
            <option value='{$Pokemon['ID']}'>
              {$Pokemon['Name']} 
              {$Pokemon['Gender']} 
              (Level: {$Level})
            </option>
          ";
        }
      ?>
    </select>
  </div>
</div>