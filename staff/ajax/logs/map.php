<?php
  require_once $_SERVER['DOCUMENT_ROOT'] . '/core/required/session.php';

  try
	{
		$Get_Map_Catches = $PDO->prepare("
      SELECT *
      FROM `map_logs`
      WHERE `Caught_By` = ?
      ORDER BY `ID` DESC
    ");
		$Get_Map_Catches->execute([
      $User_Data['ID']
    ]);
		$Get_Map_Catches->setFetchMode(PDO::FETCH_ASSOC);
		$Map_Catches = $Get_Map_Catches->fetchAll();
	}
	catch( PDOException $e )
	{
		HandleError($e);
	}

	if ( count($Map_Catches) === 0 )
	{
		echo "<h3>This user has not caught or released any map Pok&eacute;mon.</h3>";
    exit;
	}

  $Map_Catch_Text = '';
  foreach ( $Map_Catches as $Caught )
  {
    $Pokemon_Data = GetPokedexData($Caught['Pokemon_Pokedex_ID'], $Caught['Pokemon_Alt_ID'], $Caught['Pokemon_Type']);
    $Caught_Pokemon_Text = "
      <td>
        <img src='{$Pokemon_Data['Icon']}' />
      </td>
      <td>
        <b>{$Pokemon_Data['Display_Name']}</b>
      </td>
    ";

    $Map_Catch_Text .= "
      <tr>
        <td>
          {$Caught['Map_Name']}
        </td>
        {$Caught_Pokemon_Text}
        <td>
          " . date('m/d/y&\nb\sp;&\nb\sp;h:i A', $Caught['Encountered_On']) . "
        </td>
        <td>
          " . date('m/d/y&\nb\sp;&\nb\sp;h:i A', $Caught['Time_Caught']) . "
        </td>
      </tr>
    ";
  }
?>

<table class='border-gradient' style='width: 700px;'>
	<thead>
		<tr>
			<th colspan='5'>
				Map Catches
			</th>
		</tr>
	</thead>
	<tbody>
		<tr>
			<td style='padding: 5px; width: 20%;'>
				<b>
					Map
				</b>
			</td>
			<td colspan='2' style='width: 40%;'>
				<b>
					Pok&eacute;mon Caught
				</b>
			</td>
			<td style='width: 20%;'>
				<b>
					Encountered On
				</b>
			</td>
			<td style='width: 20%;'>
				<b>
					Caught On
				</b>
			</td>
		</tr>
	</tbody>
	<tbody>
		<?= $Map_Catch_Text; ?>
	</tbody>
</table>
