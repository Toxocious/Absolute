<?php
	require '../core/required/session.php';
	require '../core/functions/staff.php';

	try
	{
		$Query_Reports = $PDO->prepare("SELECT * FROM `reports`");
		$Query_Reports->execute([ ]);
		$Query_Reports->setFetchMode(PDO::FETCH_ASSOC);
		$Reports = $Query_Reports->fetchAll();
	}
	catch ( PDOException $e )
	{
		HandleError ( $e->getMessage() );
	}
?>

<div class='head'>Reported Users</div>
<div class='body'>
	<?php
		if ( count($Reports) == 0 )
		{
			echo "
				<div class='panel'>
					<div class='head'>User Reports</div>
					<div class='body' style='padding: 5px;'>
						There are currently no reports.
					</div>
				</div>
			";
		}
		else
		{
			foreach ( $Reports as $Key => $Value )
			{
				$Reported_User = $User_Class->FetchUserData($Value['Reported_ID']);
				$Reported_By = $User_Class->FetchUserData($Value['Reported_By']);

				echo "
					<div class='panel' style='margin-bottom: 5px;'>
						<div class='head'>
							Report On {$Reported_User['Username']}
							<div style='float: right;'>(Report #" . number_format($Value['id']) . ")</div>
						</div>
						<div class='body navi'>

							<div>
								<div style='float: left; padding: 5px; width: 100%;'>
									<b>Reported On:</b> " . date("F j, Y (g:i A)", $Value['Report_Date']) . "
								</div>
							</div>

							<hr />

							<div>
								<div style='float: left; height: 130px; padding: 5px; width: 150px;'>
									<a href='../profile.php?id={$Reported_User['ID']}'>
										{$Reported_User['Username']} (#" . number_format($Reported_User['ID']) . ")
									</a>
									<img src='../{$Reported_User['Avatar']}' /><br />
								</div>
								
								<div style='float: left; height: 130px; padding: 5px; width: calc(100% - 300px);'>
									<b>Reason</b><hr />
									" . nl2br($Value['Reported_Reason']) . "
								</div>

								<div style='float: left; height: 130px; padding: 5px; width: 150px;'>
									<a href='../profile.php?id={$Reported_By['ID']}'>
										{$Reported_By['Username']} (#" . number_format($Reported_By['ID']) . ")
									</a>
									<img src='../{$Reported_By['Avatar']}' /><br />
								</div>
							</div>

						</div>
					</div>
				";
			}
		}
	?>
</div>