<?php
	require_once $_SERVER['DOCUMENT_ROOT'] . '/core/required/session.php';

	/**
	 * Check for any AJAX requests.
	 */
	if ( isset($_POST['request']) )
	{
		$Request = Purify($_POST['request']);

		$Valid_Requests = ['attach', 'detach', 'detachall', 'item_data', 'item_tab'];

		/**
		 * Throw an error if the request is invalid.
		 */
		if ( !in_array($Request, $Valid_Requests) )
		{
			return [
				'Error' => 'The sent AJAX request is invalid.',
				'Info' => [
					'Request' => $Request,
					'Valid_Requests' => $Valid_Requests,
					'User_ID' => $User_Data['ID'],
					'Time' => time(),
				],
			];
		}

		/**
		 * Attach an item to a Pokemon.
		 */
		if ( $Request === 'attach' )
		{
			/**
			 * Return an error if any of the item category, item id, or pokemon id aren't set.
			 */
			if ( !isset($_POST['id']) || !isset($_POST['pokeid']) )
			{
				echo "
					<div class='error'>
						The sent AJAX request is missing a required data field.
					</div>
				";
			}

			/**
			 * Process attaching the item.
			 */
			else
			{
				$Item_ID = Purify($_POST['id']);
				$Pokemon_ID = Purify($_POST['pokeid']);

				$Item_Data = $Item_Class->FetchOwnedItem($User_Data['ID'], $Item_ID);
				$Poke_Data = GetPokemonData($Pokemon_ID);

				if ( $Item_Data['Quantity'] < 1 )
				{
					echo "
						<div class='error'>
							You do not own enough of this item to attach it to a Pokemon.
						</div>
					";
				}

				else if ( $Poke_Data['Owner_Current'] != $User_Data['ID'] )
				{
					echo "
						<div class='error'>
							You may not attach items to Pok&eacutemon; that do not belong to you.
						</div>
					";
				}

				else
				{
					$Attach_Item = $Item_Class->Attach($Item_ID, $Pokemon_ID, $User_Data['ID']);

					if ( $Attach_Item )
					{
						echo "
							<div class='success'>
								You have successfully attached your {$Item_Data['Name']} to your {$Poke_Data['Display_Name']}.
							</div>
						";
					}
					else
					{
						echo "
							<div class='error'>
								An error occurred while attempting to attach the item to your Pok&eacute;mon.
							</div>
						";
					}
				}
			}
		}
	}
?>

<div class='description'>
  Handle equipping and using your items here.
  <br />
  Clicking on an attached item will prompt you to unequip it from the Pok&eacute;mon.
</div>

<div id='Pokemon_Center_Moves_AJAX'></div>

<div style='display: flex; flex-wrap: wrap; gap: 10px;'>
  <table class='border-gradient' style='width: 400px;'>
    <thead>
      <tr>
        <th style='padding: 5px 5px 0px; width: 20%;' onclick="ShowInventoryTab('Battle Item');">
          <img src='images/Assets/bag_battle.png' />
        </th>
        <th style='padding: 5px 5px 0px; width: 20%;' onclick="ShowInventoryTab('General Item');">
          <img src='images/Assets/bag_general.png' />
        </th>
        <th style='padding: 5px 5px 0px; width: 20%;' onclick="ShowInventoryTab('Held Item');">
          <img src='images/Assets/bag_held.png' />
        </th>
        <th style='padding: 5px 5px 0px; width: 20%;' onclick="ShowInventoryTab('Medicine');">
          <img src='images/Assets/bag_medicine.png' />
        </th>
        <th style='padding: 5px 5px 0px; width: 20%;' onclick="ShowInventoryTab('Berries');">
          <img src='images/Assets/bag_berries.png' />
        </th>
      </tr>
    </thead>

    <tbody>
      <tr>
        <td colspan='5' id='Inventory_Items'>
          <div style='display: flex; align-items: center; justify-content: center; padding: 10px;'>
            <div class='loading-element'></div>
          </div>
        </td>
      </tr>
    </tbody>
  </table>

  <div style='display: flex; flex-direction: column; gap: 10px;'>
    <table class='border-gradient' style='width: 400px;'>
      <tbody id='Item_Preview'>
        <tr>
          <td style='padding: 10px;'>
            Click on an item to view more information.
          </td>
        </tr>
      </tbody>
    </table>

    <table class='border-gradient' style='width: 400px;'>
      <thead>
        <tr>
          <th colspan='3'>
            Equipped Items
          </th>
        </tr>
      </thead>

      <tbody>
        <tr>
          <td colspan='3' id='Equipped_Items'>
            <div style='display: flex; align-items: center; justify-content: center; padding: 10px;'>
              <div class='loading-element'></div>
            </div>
          </td>
        </tr>
      </tbody>
    </table>
  </div>
</div>
