<?php
  require '../../required/session.php';

	if ( isset($_POST['id']) )
	{
		$User_ID = $_POST['id'];
	}

  $Page = (isset($_POST['page'])) ? $_POST['page'] : 1;
  $Filter_Type = (isset($_POST['filter_type'])) ? $_POST['filter_type'] : '0';
  $Filter_Gender = (isset($_POST['filter_gender'])) ? $_POST['filter_gender'] : '0';
  $Filter_Dir = (isset($_POST['filter_search_order'])) ? $_POST['filter_search_order'] : 'ASC';

  $Begin = ($Page - 1) * 35;
  if ( $Begin < 0 )
    $Begin = 1;

  $Query = "SELECT `ID` FROM `pokemon` WHERE `Owner_Current` = ? AND `Location` = 'Box'";
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
  
  if ( isset($User_ID) )
  {
    if ( count($Box_Pokemon) == 0 )
    {
      echo "No Pokemon have been found given your search parameters.";
    }
    else
    {
      $Pagination = Pagi(str_replace('SELECT `ID`', 'SELECT COUNT(*)', $Query), $User_ID, $Inputs, $Page, 'onclick="updateBox(\'' . $Page . '\'); return false;"', 35);

      echo "
        {$Pagination}
        <div style='height: 172px; padding: 0px 0px 5px;'>
      ";

      foreach ( $Box_Pokemon as $Index => $Pokemon )
      {
        $Pokemon = $Poke_Class->FetchPokemonData($Pokemon['ID']);
        echo "<img class='spricon' src='{$Pokemon['Icon']}' onclick='displayPokeData({$Pokemon['ID']});'/>";
      }

      echo "
        </div>
      ";
    }
  }

	exit();
?>