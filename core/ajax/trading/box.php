<?php
  require '../../required/session.php';

  if ( !isset($_POST['id']) )
  {
    echo "
      <tr>
        <td colspan='21'>
          <b style='color: #f00'>
            An error has occurred while fetching this trainer's Pok&eacute;mon.
        </td>
      </tr>
    ";

    return;
  }

  $User_ID = Purify($_POST['id']);
  $Page = (isset($_POST['Page'])) ? Purify($_POST['Page']) : 1;
  $Filter_Type = (isset($_POST['filter_type'])) ? Purify($_POST['filter_type']) : 0;
  $Filter_Gender = (isset($_POST['filter_gender'])) ? Purify($_POST['filter_gender']) : 0;
  $Filter_Dir = (isset($_POST['filter_search_order'])) ? Purify($_POST['filter_search_order']) : 'ASC';

  $Begin = ($Page - 1) * 35;
  if ( $Begin < 0 )
    $Begin = 1;

  $Query = "SELECT `ID` FROM `pokemon` WHERE `Owner_Current` = ? AND `Location` = 'Box' AND `Trade_Interest` != 'No'";
  $Inputs = [$User_ID];

  if ( $Filter_Type != '0' )
  {
    $Query .= " AND `Type` = ?";
    $Inputs[] = $Filter_Type;
  }

  switch ($Filter_Gender)
  {
		case 'm': 
			$Query .= " AND `Gender` = 'Male'";
			break;
		case 'f': 
			$Query .= " AND `Gender` = 'Female'";
			break;
		case 'g': 
			$Query .= " AND `Gender` = 'Genderless'";
			break;
		case '?': 
			$Query .= " AND `Gender` = '(?)'";
			break;
  }

  if ( $Filter_Dir != 'ASC' )
  {
    $Filter_Dir = 'DESC';
  }
  else
  {
    $Filter_Dir = 'ASC';
  }

  $Query .= " ORDER BY `Pokedex_ID`, `ID` ASC";

  try
  {
    $Box_Query = $PDO->prepare($Query . " LIMIT " . $Begin . ",35");
    $Box_Query->execute($Inputs);
    $Box_Query->setFetchMode(PDO::FETCH_ASSOC);
    $Box_Pokemon = $Box_Query->fetchAll();
  }
  catch ( PDOException $e )
  {
    HandleError( $e->getMessage() );
	}
  
  if ( count($Box_Pokemon) == 0 )
  {
    echo "
      <tr>
        <td colspan='21' style='height: 220px; padding: 10px;'>
          There are no Pok&eacute;mon in this trainer's box.
        </td>
      </tr>
    ";
  }
  else
  {
    Pagination(str_replace('SELECT `ID`', 'SELECT COUNT(*)', $Query), $Inputs, $User_ID, $Page, 35);

    echo "<tr>";
    $Total_Rendered = 0;
    foreach( $Box_Pokemon as $Index => $Pokemon )
    {
      $Index++;
      $Total_Rendered++;
      $Pokemon = $Poke_Class->FetchPokemonData($Pokemon['ID']);

      echo "
        <td colspan='3' data-poke-id='{$Pokemon['ID']}'>
          <img
            class='spricon'
            src='{$Pokemon['Icon']}'
            onclick='Add_To_Trade({$User_ID}, \"Add\", \"Pokemon\", {$Pokemon['ID']})'
          />
        </td>
      ";

      if ( $Index % 7 === 0 && $Index % 35 !== 0 )
        echo "</tr><tr>";
    }

    if ( $Total_Rendered <= 35 )
    {
      $Total_Rendered++;
      
      for ( $Total_Rendered; $Total_Rendered <= 35; $Total_Rendered++ )
      {
        echo "
          <td colspan='3' style='padding: 20.5px; width: 56px;'></td>
        ";

        if ( $Total_Rendered % 7 === 0 && $Total_Rendered % 35 !== 0 )
          echo "</tr><tr>";
      }
    }

    echo "</tr>";
  }
