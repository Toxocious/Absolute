<?php
    /**
     * Return the user's boxed Pokemon as a string.
     *
     * @param $Profile_ID
     * @param $Category
     */
    function GetInventory
    (
        $Profile_ID,
        $Selected_Category
    )
    {
        global $PDO;

        $Category_Text = '';
		foreach ( ['Battle Item', 'General Item', 'Held Item', 'Key Item', 'Medicine', 'Pokeballs'] as $Category )
		{
			$Category_Image = DOMAIN_SPRITES . "/Assets/bag_" . strtolower(explode(' ', $Category)[0]) . '.png';

			$Category_Text .= "
				<td colspan='1' style='padding: 5px 5px 3px; height: 69px; max-height: 69px !important; width: calc(100% / 6);' onclick=\"GetInventory({$Profile_ID}, '{$Category}');\">
					<img src='{$Category_Image}' />
					<br />
					<b>{$Category}</b>
				</td>
			";
		}

		try
		{
			$Fetch_Items = $PDO->prepare("SELECT * FROM `items` WHERE `Owner_Current` = ? AND `Item_Type` = ? AND `Quantity` > 0 ORDER BY `Item_Name` ASC");
			$Fetch_Items->execute([ $Profile_ID, $Selected_Category ]);
			$Fetch_Items->setFetchMode(PDO::FETCH_ASSOC);
			$Items = $Fetch_Items->fetchAll();
		}
		catch ( PDOException $e )
		{
			HandleError($e);
		}

        return [
            'Category_Header' => $Category_Text,
            'Selected_Category' => $Selected_Category,
            'Items' => $Items
        ];
    }
