<?php
  require_once $_SERVER['DOCUMENT_ROOT'] . '/core/required/session.php';

  try
	{
		$Get_Trade_History = $PDO->prepare("
      SELECT `ID`, `Sender`, `Recipient`, `Status`
      FROM `trades`
      WHERE (`Sender` = ? OR `Recipient` = ?)
      ORDER BY `ID` DESC
    ");
		$Get_Trade_History->execute([
      $User_Data['ID'],
      $User_Data['ID']
    ]);
		$Get_Trade_History->setFetchMode(PDO::FETCH_ASSOC);
		$Trade_History = $Get_Trade_History->fetchAll();
	}
	catch( PDOException $e )
	{
		HandleError($e);
	}

	if ( count($Trade_History) === 0 )
	{
		echo "<h3>This user has no trade logs.</h3>";
    exit;
	}

  $Trade_Text = '';

  foreach ( $Trade_History as $Key => $Value )
  {
    $Sender = $User_Class->FetchUserData($Value['Sender']);
    $Sender_Username = $User_Class->DisplayUserName($Sender['ID']);
    $Recipient = $User_Class->FetchUserData($Value['Recipient']);
    $Recipient_Username = $User_Class->DisplayUserName($Recipient['ID']);

    switch( $Value['Status'] )
    {
      case 'Accepted':
        $Color = "#00ff00";
        break;

      case 'Declined':
        $Color = "#ff0000";
        break;

      case 'Deleted':
        $Color = "#999";
        break;

      default:
        $Color = '#fff;';
        break;
    }

    $Trade_Text .= "
      <tr>
        <td style='padding: 5px;'>
          <a href='javascript:void(0);' name='iFrame_Handler' data-src='" . DOMAIN_ROOT . "/staff/pages/trade_log_iframe.php?Trade_ID={$Value['ID']}'>
            #" . number_format($Value['ID']) . "
          </a>
        </td>
        <td>
          <a href='" . DOMAIN_ROOT . "/profile.php?id={$Sender['ID']}'>
            {$Sender_Username}
          </a>
        </td>
        <td>
          <a href='" . DOMAIN_ROOT . "/profile.php?id={$Recipient['ID']}'>
            {$Recipient_Username}
          </a>
        </td>
        <td style='color: {$Color};'>
          {$Value['Status']}
        </td>
      </tr>
    ";
  }

  $Trade_Text .= "
      </tbody>
    </table>
  ";
?>

<table class='border-gradient' style='width: 600px;'>
	<thead>
		<tr>
			<th colspan='4'>
				Trade History
			</th>
		</tr>
	</thead>
	<tbody>
		<tr>
			<td style='width: 25%;'>
				<b>
					Trade #
				</b>
			</td>
			<td style='width: 25%;'>
				<b>
					Sender
				</b>
			</td>
			<td style='width: 25%;'>
				<b>
					Recipient
				</b>
			</td>
			<td style='width: 25%;'>
				<b>
					Status
				</b>
			</td>
		</tr>
	</tbody>
	<tbody>
		<?= $Trade_Text; ?>
	</tbody>
</table>
