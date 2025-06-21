<?php
	require_once '../../required/session.php';

	if ( !isset($_POST['ID']) )
	{
		echo "
			<div class='error'>
				Please specify the User ID or Username of the trainer that you would like to trade with.
			</div>
		";

		return;
	}

	$Recipient_ID = Purify($_POST['ID']);
	$Recipient = $User_Class->FetchUserData($Recipient_ID);

	if ( $Recipient_ID == 0 || !$Recipient )
	{
		echo "<div class='error'>The user that you're attempting to trade with does not exist.</div>";

		return;
	}
	else if ( $User_Data['ID'] !== $Recipient['ID'] )
	{
		echo "<div class='error'>You may not trade with yourself.</div>";

		return;
	}
	else if ( $Recipient['RPG_Ban'] )
	{
		echo "<div class='error'>The user that you're attempting to trade with is currently banned.</div>";

		return;
	}
	else
	{
		$_SESSION['EvoChroniclesRPG']['Trade'] = [
			'Sender' => [
				'User' => $User_Data['ID'],
				'Pokemon' => [],
				'Currency' => [],
				'Items' => []
			],
			'Recipient' => [
				'User' => $Recipient['ID'],
				'Pokemon' => [],
				'Currency' => [],
				'Items' => []
			],
		];
	}
?>

<div class='description' style='flex-basis: 85%;'>
	Choose the pokemon, items, and/or currency to be added to the trade.
</div>

<div style='flex-basis: 85%;'>
	<button onclick='TradeCreate();' style='width: 200px;'>
		Create Trade
	</button>
</div>

<div style='display: flex; flex-wrap: wrap; gap: 10px; justify-content: center; width: 98%;'>
  <div style='flex-basis: 48%;'>
    <table class='border-gradient' style='margin: 5px auto; width: 100%;'>
      <thead>
        <tr>
          <th colspan='21'>
            <b><?= $User_Data['Username']; ?>'s Belongings</b>
          </th>
        </tr>
      </thead>
      <tbody>
        <tr>
          <td colspan='7' style='padding: 5px;'>
            <a href='javascript:void(0);' onclick="Change_Tab('box', <?= $User_Data['ID']; ?>)">
              <b>Pok&eacute;mon</b>
            </a>
          </td>
          <td colspan='7' style='padding: 5px;'>
            <a href='javascript:void(0);' onclick="Change_Tab('inventory', <?= $User_Data['ID']; ?>)">
              <b>Inventory</b>
            </a>
          </td>
          <td colspan='7' style='padding: 5px;'>
            <a href='javascript:void(0);' onclick="Change_Tab('currency', <?= $User_Data['ID']; ?>)">
              <b>Currency</b>
            </a>
          </td>
        </tr>
      </tbody>
      <tbody id='TabContent<?= $User_Data['ID']; ?>'>
        <?php
          try
          {
            $Sender_Query = $PDO->prepare("SELECT `ID` FROM `pokemon` WHERE `Owner_Current` = ? AND `Location` = 'Box' AND `Trade_Interest` != 'No' ORDER BY `Pokedex_ID` ASC LIMIT 35");
            $Sender_Query->execute([ $User_Data['ID'] ]);
            $Sender_Query->setFetchMode(PDO::FETCH_ASSOC);
            $Sender_Box = $Sender_Query->fetchAll();

            $Query = "SELECT `ID` FROM `pokemon` WHERE `Owner_Current` = {$User_Data['ID']} AND `Location` = 'Box' AND `Trade_Interest` != 'No' ORDER BY `Pokedex_ID` ASC LIMIT 35";
            $Inputs = [];
            $Page = 1;
          }
          catch ( PDOException $e )
          {
            HandleError($e);
          }

          if ( count($Sender_Box) == 0 )
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
            Pagination(str_replace('SELECT `ID`', 'SELECT COUNT(*)', $Query), $Inputs, $User_Data['ID'], $Page, 35);

            echo "<tr>";
            $Total_Rendered = 0;
            foreach( $Sender_Box as $Index => $Pokemon )
            {
              $Index++;
              $Total_Rendered++;
              $Pokemon = GetPokemonData($Pokemon['ID']);

              echo "
                <td colspan='3' data-poke-id='{$Pokemon['ID']}'>
                  <img
                    class='spricon'
                    src='{$Pokemon['Icon']}'
                    onclick='Add_To_Trade({$User_Data['ID']}, \"Add\", \"Pokemon\", {$Pokemon['ID']})'
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
        ?>
      </tbody>
    </table>

    <table class='border-gradient' style='margin: 5px auto; width: 100%;'>
      <thead>
        <tr>
          <th colspan='3'>
            <b>Included In Trade</b>
          </th>
        </tr>
      </thead>
      <tbody id='TradeIncluded<?= $User_Data['ID']; ?>'>
        <tr>
          <td colspan='3' style='padding: 10px;'>
            Nothing has been added to the trade yet.
          </td>
        </tr>
      </tbody>
    </table>
  </div>

  <div style='flex-basis: 48%;'>
    <table class='border-gradient' style='margin: 5px auto; width: 100%;'>
      <thead>
        <tr>
          <th colspan='21'>
            <b><?= $Recipient['Username']; ?>'s Belongings</b>
          </th>
        </tr>
      </thead>
      <tbody>
        <tr>
          <td colspan='7' style='padding: 5px;'>
            <a href='javascript:void(0);' onclick="Change_Tab('box', <?= $Recipient['ID']; ?>)">
              <b>Pok&eacute;mon</b>
            </a>
          </td>
          <td colspan='7' style='padding: 5px;'>
            <a href='javascript:void(0);' onclick="Change_Tab('inventory', <?= $Recipient['ID']; ?>)">
              <b>Inventory</b>
            </a>
          </td>
          <td colspan='7' style='padding: 5px;'>
            <a href='javascript:void(0);' onclick="Change_Tab('currency', <?= $Recipient['ID']; ?>)">
              <b>Currency</b>
            </a>
          </td>
        </tr>
      </tbody>
      <tbody id='TabContent<?= $Recipient['ID']; ?>'>
        <?php
          try
          {
            $Recipient_Query = $PDO->prepare("SELECT `ID` FROM `pokemon` WHERE `Owner_Current` = ? AND `Location` = 'Box' AND `Trade_Interest` != 'No' ORDER BY `Pokedex_ID` ASC LIMIT 35");
            $Recipient_Query->execute([ $Recipient['ID'] ]);
            $Recipient_Query->setFetchMode(PDO::FETCH_ASSOC);
            $Recipient_Box = $Recipient_Query->fetchAll();

            $Query = "SELECT `ID` FROM `pokemon` WHERE `Owner_Current` = {$Recipient['ID']} AND `Location` = 'Box' AND `Trade_Interest` != 'No' ORDER BY `Pokedex_ID` ASC LIMIT 35";
            $Inputs = [];
            $Page = 1;
          }
          catch ( PDOException $e )
          {
            HandleError($e);
          }

          if ( count($Recipient_Box) == 0 )
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
            Pagination(str_replace('SELECT `ID`', 'SELECT COUNT(*)', $Query), $Inputs, $Recipient['ID'], $Page, 35);

            echo "<tr>";
            $Total_Rendered = 0;
            foreach( $Recipient_Box as $Index => $Pokemon )
            {
              $Index++;
              $Total_Rendered++;
              $Pokemon = GetPokemonData($Pokemon['ID']);

              echo "
                <td colspan='3' data-poke-id='{$Pokemon['ID']}'>
                  <img
                    class='spricon'
                    src='{$Pokemon['Icon']}'
                    onclick='Add_To_Trade({$Recipient['ID']}, \"Add\", \"Pokemon\", {$Pokemon['ID']})'
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
        ?>
      </tbody>
    </table>

    <table class='border-gradient' style='margin: 5px auto; width: 100%;'>
      <thead>
        <tr>
          <th colspan='3'>
            <b>Included In Trade</b>
          </th>
        </tr>
      </thead>
      <tbody id='TradeIncluded<?= $Recipient['ID']; ?>'>
        <tr>
          <td colspan='3' style='padding: 10px;'>
            Nothing has been added to the trade yet.
          </td>
        </tr>
      </tbody>
    </table>
  </div>
</div>
