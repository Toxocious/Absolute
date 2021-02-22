<?php
  require_once '../../required/session.php';

  if ( isset($_GET['User_ID']) )
  {
		$User_ID = Purify($_GET['User_ID']);

		if ( isset($_GET['Category']) )
			$Item_Type = Purify($_GET['Category']);
		else
			$Item_Type = 'Held Item';

		/**
		 * Display each of the available item categories.
		 */
		$Category_Text = '';
		foreach ( ['Battle Item', 'General Item', 'Held Item', 'Key Item', 'Medicine', 'Pokeballs'] as $Category )
		{
			$Category_Image = DOMAIN_SPRITES . "/Assets/bag_" . strtolower(explode(' ', $Category)[0]) . '.png';

			$Category_Text .= "
				<td colspan='1' style='padding: 5px 5px 3px; width: calc(100% / 6);' onclick=\"UpdateInventory({$User_ID}, '{$Category}');\">
					<img src='{$Category_Image}' />
					<br />
					<b>{$Category}</b>
				</td>
			";
		}

		try
		{
			$Fetch_Items = $PDO->prepare("SELECT * FROM `items` WHERE `Owner_Current` = ? AND `Item_Type` = ? AND `Quantity` > 0 ORDER BY `Item_Name` ASC");
			$Fetch_Items->execute([ $User_ID, $Item_Type ]);
			$Fetch_Items->setFetchMode(PDO::FETCH_ASSOC);
			$Items = $Fetch_Items->fetchAll();
		}
		catch ( PDOException $e )
		{
			HandleError( $e->getMessage() );
		}

		if ( $Items )
		{
			$Item_List = '';

			foreach ($Items as $Index => $Item)
			{
				$Item_Data = $Item_Class->FetchItemData($Item['Item_ID']);

				if ( $Index % 3 == 0 )
					$Item_List .= "<tr>";

				$Item_List .= "
					<td colspan='2'>
						<div style='float: left;'>
							<img src='" . DOMAIN_SPRITES . "/Items/{$Item['Item_Name']}.png' />
						</div>
						<b>{$Item_Data['Name']}</b>
						<br />
						Owned: " . number_format($Item['Quantity']) . "
					</td>
				";

				if ( $Index % 3 == 2 )
					$Item_List .= "</tr>";
			}

			if ( count($Items) % 3 != 0 )
			{
				$Slots = count($Items) - (count($Items) % 3);
				for ( $i = 0; $i < 3 - (count($Items) % 3); $i++ )
					$Item_List .= "<td colspan='2'></td>";
			}

			echo "
				<tbody>
					{$Category_Text}
				</tbody>
				<tbody>
					{$Item_List}
				</tbody>
			";
		}
		else
		{
			echo "
				<tbody>
					{$Category_Text}
				</tbody>
				<tbody>
					<tr>
						<td colspan='6' style='padding: 5px;'>
							This section of the user's inventory is empty.
						</td>
					</tr>
				</tbody>
			";
		}
	}
	else
	{
		echo "
      <tbody>
        <tr>
          <td>
            An invalid user has been selected.
          </td>
        </tr>
      </tbody>
    ";
	}
