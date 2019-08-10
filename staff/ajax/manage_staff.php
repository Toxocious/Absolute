<?php
	require '../../core/required/session.php';
	require '../../core/functions/staff.php';

	/**
	 * Fetching the appropriate data of the requested staff member.
	 */
	if ( isset($_POST['Fetch']) )
	{
		$User_ID = $Purify->Cleanse($_POST['Fetch']);
		$User = $User_Class->FetchUserData($User_ID);

		echo "
			<div class='description' style='margin-bottom: 5px;'>
				Editing the staff data of <b>{$User['Username']}</b>.
			</div>

			<form id='EditStaff' onsubmit='LoadContent(\"ajax/manage_staff.php\", \"AJAX\", $(\"#EditStaff\").serialize()); return false;'>
				<table>
					<tbody>
						<tr>
							<td rowspan='3' style='padding: 20px;'>
								<img src='/{$User['Avatar']}' />
							</td>
						</tr>

						<tr>
							<td style='padding-right: 10px;'><b>Rank</b></td>
							<td>
								<select name='Rank' style='padding: 4px; width: 180px;'>
								<option value='Member'>Member</option>
								<option value='ChatMod'>Chat Moderator</option>
								<option value='Mod'>Moderator</option>
								<option value='SuperMod'>Super Moderator</option>
								<option value='Developer'>Developer</option>
								<option value='Administrator'>Administrator</option>
								</select>
							</td>
						</tr>
						<tr>
							<td style='padding-right: 10px;'><b>Power</b></td>
							<td><input type='text' name='Power' value='{$User['Power']}' /></td>
						</tr>
					</tbody>
				</table>

				<input type='hidden' name='Edit' value='{$User['ID']}' />
				<input type='submit' value='Update Staff Member' />
			</form>
		";
	}

	/**
	 * Updating the staff member via the sent data.
	 */
	if ( isset($_POST['Edit']) )
	{
		$User_ID = $Purify->Cleanse($_POST['Edit']);
		$User = $User_Class->FetchUserData($User_ID);
		$Rank = $Purify->Cleanse($_POST['Rank']);
		$Power = $Purify->Cleanse($_POST['Power']);

		switch ( $Rank )
		{
			case 'Member':
				$Rank = 'Member';
				break;
			case 'ChatMod':
				$Rank = 'Chat Moderator';
				break;
			case 'Mod':
				$Rank = 'Moderator';
				break;
			case 'SuperMod':
				$Rank = 'Super Moderator';
				break;
			case 'Developer':
				$Rank = 'Developer';
				break;
			case 'Administrator':
				$Rank = 'Administrator';
				break;
		}

		try
		{
			$Update = $PDO->prepare("UPDATE `users` SET `Rank` = ?, `Power` = ? WHERE `id` = ?");
			$Update->execute([ $Rank, $Power, $User_ID ]);
		}
		catch ( PDOException $e )
		{
			HandleError( $e->getMessage() );
		}

		echo "<div class='success'>You have successfully updated the staff position and power of <b>{$User['Username']}</b>.</div>";
	}