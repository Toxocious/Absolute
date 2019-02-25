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
	}