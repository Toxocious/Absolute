<?php
	require_once '../../required/session.php';

	if ( !isset($_POST['id']) )
  {
    echo "
      <tr>
        <td colspan='21'>
          <b style='color: #f00'>
            An error has occurred while fetching this trainer's currencies.
        </td>
      </tr>
    ";

    return;
  }
	
	$User_ID = $Purify->Cleanse($_POST['id']);
	$User = $User_Class->FetchUserData($User_ID);

	foreach ( $Constants->Currency as $Key => $Value )
	{
		if ( $Value['Tradeable'] )
		{
			echo "
				<tr>
					<td colspan='7'>
						<img src='{$Value['Icon']}' />
						<br />
						" . number_format($User[$Value['Value']]) . "
					</td>
					<td colspan='7'>
						<input type='text' id='User-{$User_ID}-{$Value['Value']}' placeholder='0' style='width: 50%;' />
					</td>
					<td colspan='7'>
						<button style='width: 50px;' onclick='Add_To_Trade($User_ID, \"Add\", \"Currency\", \"User-{$User_ID}-{$Value['Value']}\")'>Add</button>
					</td>
				</tr>
			";
		}
	}