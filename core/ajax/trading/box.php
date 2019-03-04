<?php
  require '../../required/session.php';

	if ( isset($_POST['id']) )
	{
		$User_ID = $_POST['id'];
	}

  $Page = (isset($_POST['Page'])) ? $_POST['Page'] : 1;
  $Filter_Type = (isset($_POST['filter_type'])) ? $_POST['filter_type'] : '0';
  $Filter_Gender = (isset($_POST['filter_gender'])) ? $_POST['filter_gender'] : '0';
  $Filter_Dir = (isset($_POST['filter_search_order'])) ? $_POST['filter_search_order'] : 'ASC';

  $Begin = ($Page - 1) * 50;
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

  $Query .= " ORDER BY `Pokedex_ID` ASC";

  try
  {
    $Box_Query = $PDO->prepare($Query . " LIMIT " . $Begin . ",50");
    $Box_Query->execute($Inputs);
    $Box_Query->setFetchMode(PDO::FETCH_ASSOC);
    $Box_Pokemon = $Box_Query->fetchAll();
  }
  catch ( PDOException $e )
  {
    HandleError( $e->getMessage() );
	}
  
  if ( isset($User_ID) )
  {
    if ( count($Box_Pokemon) == 0 )
		{
			echo "<div style='padding: 85px 5px;'>There are no Pokemon in this user's box.</div>";
    }
    else
    {
      echo "<div class='page_nav'>";
      Pagi(str_replace('SELECT `ID`', 'SELECT COUNT(*)', $Query), $User_ID, $Inputs, $Page, 'onclick="updateBox(' . $Page . ', ' . $User_ID . '); return false;"', 50);
      echo "</div>";

      echo "<div style='height: 160px; padding: 5px;'>";
      foreach ( $Box_Pokemon as $Index => $Pokemon )
      {
        $Pokemon = $PokeClass->FetchPokemonData($Pokemon['ID']);
        echo "<img class='spricon' src='{$Pokemon['Icon']}' onclick='Action({$User_ID}, \"Add\", \"Pokemon\", {$Pokemon['ID']})' />";
      }
      echo "</div>";
    }
  }

	exit();
?>