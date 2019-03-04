<?php
	Class Item
	{
		public $PDO;

		/**
		 * Construct and initialize the class.
		 */
		public function __construct()
		{
			global $PDO;
			$this->PDO = $PDO;
		}

		/**
		 * Fetch the data of any item via the `item_dex` table.
		 */
		public function FetchItemData($Item_ID)
		{
			global $PDO;

			try
			{
				$Fetch_Item = $PDO->prepare("SELECT * FROM `item_dex` WHERE `Item_ID` = ?");
				$Fetch_Item->execute([$Item_ID]);
				$Fetch_Item->setFetchMode(PDO::FETCH_ASSOC);
				$Item = $Fetch_Item->fetch();
			}
			catch ( PDOException $e )
			{
				HandleError( $e->getMessage() );
			}

			return [
				"ID" => $Item['Item_ID'],
				"Name" => $Item['Item_Name'],
				"Category" => $Item['Item_Type'],
				"Description" => $Item['Item_Description'],
			];
		}

		/**
		 * Fetch the item data of the item that a Pokemon is holding.
		 * ?? Possibly pass the ID of the Pokemon that's holding the item as well?
		 */
		public function FetchOwnedItem($Owner_ID, $Item_ID = null, $Limit = 1)
		{
			global $PDO;

			try
			{
				if ( $Item_ID == null )
				{
					$Fetch_Item = $PDO->prepare("SELECT * FROM `items` WHERE `Owner_Current` = ? LIMIT $Limit");
					$Fetch_Item->execute([$Owner_ID]);
				}
				else
				{
					$Fetch_Item = $PDO->prepare("SELECT * FROM `items` WHERE `Owner_Current` = ? AND `Item_ID` = ? LIMIT $Limit");
					$Fetch_Item->execute([$Owner_ID, $Item_ID]);
				}
				
				$Fetch_Item->setFetchMode(PDO::FETCH_ASSOC);
				$Item = $Fetch_Item->fetch();
			}
			catch ( PDOException $e )
			{
				HandleError( $e->getMessage() );
			}

			return [
				"Row" => $Item['id'],
				"ID" => $Item['Item_ID'],
				"Name" => $Item['Item_Name'],
				"Category" => $Item['Item_Type'],
				"Owner" => $Item['Owner_Current'],
				"Quantity" => $Item['Quantity'],
			];
		}

		/**
		 * Add an item to the `items` database table.
		 * If the user already has the item, update the quantity.
		 * Else, create a new row.
		 */
		public function SpawnItem($User_ID, $Item_ID, $Quantity, $Subtract = false)
		{
			global $PDO;

			if ( !isset($User_ID) || !isset($Item_ID) || !isset($Quantity) )
			{
				die("Please specify the receiver's User ID, the Item ID, and Quantity of the item.");
			}
			else if ( $User_ID < 1 || $Item_ID < 1 || $Quantity < 1 )
			{
				die("Please specify a User ID, Item ID, and Quantity that are greater than 0.");
			}
			else
			{
				try
				{
					$Query_Row = $PDO->prepare("SELECT * FROM `items` WHERE `Item_ID` = ? AND `Owner_Current` = ?");
					$Query_Row->execute([ $Item_ID, $User_ID ]);
					$Query_Row->setFetchMode(PDO::FETCH_ASSOC);
					$Row = $Query_Row->fetchAll();

					$Item_Data = $this->FetchItemData($Item_ID);

					if ( count($Row) == 0 )
					{
						$Create_Row = $PDO->prepare("INSERT INTO `items` (`Item_ID`, `Item_Name`, `Item_Type`, `Owner_Current`, `Quantity`) VALUES (?, ?, ?, ?, ?)");
						$Create_Row->execute([ $Item_ID, $Item_Data['Name'], $Item_Data['Category'], $User_ID, $Quantity ]);
					}
					else
					{
						if ( $Subtract )
						{
							$Update_Row = $PDO->prepare("UPDATE `items` SET `Quantity` = `Quantity` - ? WHERE `Item_ID` = ? AND `Owner_Current` = ?");
						}
						else
						{
							$Update_Row = $PDO->prepare("UPDATE `items` SET `Quantity` = `Quantity` + ? WHERE `Item_ID` = ? AND `Owner_Current` = ?");
						}
						$Update_Row->execute([ $Quantity, $Item_ID, $User_ID ]);
					}
				}
				catch( PDOException $e )
				{
					HandleError( $e->getMessage() );
				}
			}
		}
	}