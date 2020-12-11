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
				"Icon" => DOMAIN_SPRITES . "/Items/{$Item['Item_Name']}.png"
			];
		}

		/**
		 * Fetch the item data of the item that a Pokemon is holding.
		 * ?? Possibly pass the ID of the Pokemon that's holding the item as well?
		 */
		public function FetchOwnedItem($Owner_ID, $Item_ID = null, $Limit = 1)
		{
			if ( !isset($Owner_ID) || !$Owner_ID )
				return false;

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
				"Icon" => DOMAIN_SPRITES . "/Items/{$Item['Item_Name']}.png"
			];
		}

		/**
		 * Attach an item to a given Pokemon.
		 * @param $Item_ID :: The `Items`.`ID` of the given item.
		 * @param $Pokemon_ID :: The `Pokemon`.`ID` of the given Pokemon.
		 * @param $Owner_ID :: The `User`.`ID` of the given item's owner.
		 */
		public function Attach($Item_ID, $Pokemon_ID, $Owner_ID)
		{
			if ( !isset($Item_ID) || !isset($Pokemon_ID) )
				return false;

			global $PDO, $Poke_Class, $User_Class;

			$Item_Data = $this->FetchOwnedItem($Owner_ID, $Item_ID);
			$Owner_Data = $User_Class->FetchUserData($Owner_ID);
			$Pokemon_Data = $Poke_Class->FetchPokemonData($Pokemon_ID);

			if ( $Item_Data['Quantity'] < 1 )
			{
				return false;
			}
			else if ( $Item_Data['Owner'] !== $Owner_Data['ID'] )
			{
				return false;
			}
			else if ( $Pokemon_Data['Owner_Current'] !== $Owner_Data['ID'] )
			{
				return false;
			}
			else
			{
				try
				{
					$Update_Pokemon = $PDO->prepare("UPDATE `pokemon` SET `Item` = ? WHERE `ID` = ?");
					$Update_Pokemon->execute([ $Item_Data['ID'], $Pokemon_ID ]);

					$Update_Item = $PDO->prepare("UPDATE `items` SET `Quantity` = `Quantity` - 1 WHERE `Owner_Current` = ? AND `Item_ID` = ?");
					$Update_Item->execute([ $Owner_Data['ID'], $Item_Data['ID'] ]);
				}
				catch ( PDOException $e )
				{
					HandleError($e);
				}

				return true;
			}
		}

		/**
		 * Unequip an item from a Pokemon.
		 * @param $Poke_ID :: The `Pokemon`.`ID` of the given Pokemon.
		 * @param $User_ID :: The `Users`.`ID` of the given Pokemon's owner.
		 */
		public function Unequip($Poke_ID, $User_ID)
		{
			global $PDO;
			global $Poke_Class;
			
			$Owner_ID = Purify($User_ID);
			$Pokemon_ID = Purify($Poke_ID);

			$Pokemon = $Poke_Class->FetchPokemonData($Pokemon_ID);
			$Item_Data = $this->FetchOwnedItem($Owner_ID, $Pokemon['Item_ID']);

			if ( !$Pokemon )
			{
				return [
					'Message' => 'This Pok&eacute;mon could not be found.',
					'Type' => 'error',
				];
			}
			else if ( $Pokemon['Owner_Current'] != $Owner_ID )
			{
				return [
					'Message' => 'You don\'t own this Pok&eacute;mon.',
					'Type' => 'error',
				];
			}
			else if ( $Pokemon['Item_ID'] == null || $Pokemon['Item_ID'] == 0 )
			{
				return [
					'Message' => 'This Pok&eacute;mon doesn\'t have an item equipped.',
					'Type' => 'error',
				];
			}
			else if ( $Pokemon['Location'] == "Trade" )
			{
				return [
					'Message' => 'This Pok&eacute;mon is in a trade.',
					'Type' => 'error',
				];
			}
			else
			{
				try
				{
					$Update_Pokemon = $PDO->prepare("UPDATE `pokemon` SET `Item` = 0 WHERE `ID` = ?");
					$Update_Pokemon->execute([ $Pokemon['ID'] ]);

					$Update_Item = $PDO->prepare("UPDATE `items` SET `Quantity` = `Quantity` + 1 WHERE `Owner_Current` = ? AND `Item_ID` = ?");
					$Update_Item->execute([ $Owner_ID, $Item_Data['ID'] ]);
				}
				catch ( PDOException $e )
				{
					HandleError( $e->getMessage() );
				}

				return [
					'Message' => "You have detached your <b>{$Item_Data['Name']}</b> from <b>{$Pokemon['Display_Name']}</b>.",
					'Type' => 'success',
				]; 
			}
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