<?php
	require '../../required/session.php';
	
	if ( isset($User_Data) )
	{
		if ( isset($_POST['id']) )
		{
			$User_ID = $Purify->Cleanse($_POST['id']);
			$User = $UserClass->FetchUserData($User_ID);

			foreach ( $Constants->Currency as $Key => $Value )
			{
				if ( $Value['Tradeable'] )
				{
					echo "
						<div>
							<div style='float: left; width: 120px;'>
								<img src='{$Value['Icon']}' style='height: 32px; width: 32px;' /><br />
								{$Value['Name']}
							</div>

							<div style='float: left; width: 182px;'>
								" . number_format($User[$Value['Value']]) . "<br />
								<input type='text' id='{$Value['Value']}' placeholder='0' style='border: none; border-radius: 0px; padding: 5px 0px; margin: 1px; text-align: center; width: 179px;' />
							</div>

							<div style='float: left; padding: 12px 15px;'>
								<!-- Action(User_ID, Action, Type, Data) -->
								<button onclick='Action($User_ID, \"Add\", \"Currency\", \"{$Value['Value']}\")'>Add To Trade</button>
							</div>
						</div>
						<hr />
					";
				}
			}
		}
		else
		{
			echo "An error has occurred.";
		}
	}
	else
	{
		echo "To use this feature, you must be logged in.";
	}