<?php
	require '../core/required/session.php';
	require '../core/functions/staff.php';

	if ( isset($_POST['Toggle']) )
	{
		$Page = $Purify->Cleanse($_POST['Toggle']);

		try
		{
			$Fetch_Page = $PDO->prepare("SELECT * FROM `pages` WHERE `ID` = ? LIMIT 1");
			$Fetch_Page->execute([ $Page ]);
			$Fetch_Page->setFetchMode(PDO::FETCH_ASSOC);
			$Page_Data = $Fetch_Page->fetch();

			if ( $Page_Data['Maintenance'] == 'no' )
			{
				$Maintenance = 'yes';
			}
			else
			{
				$Maintenance = 'no';
			}

			$Update_Page = $PDO->prepare("UPDATE `pages` SET `Maintenance` = ? WHERE `ID` = ? LIMIT 1");
			$Update_Page->execute([ $Maintenance, $Page ]);
		}
		catch ( PDOException $e )
		{
			HandleError ( $e->getMessage() );
		}
?>

		<div class='success' style='margin-bottom: 5px;'>
			You have toggled the maintenance status of the <b><?= $Page_Data['Name']; ?></b> page.
		</div>

		<div class='panel'>
			<div class='panel-heading'>Page Index</div>
			<div class='panel-body'>
				<table class='box_cont' style='width: 100%;'>
					<?php
						try
						{
							$Query_Pages = $PDO->query("SELECT * FROM `pages` WHERE `Staff_Only` = 'No'")->fetchAll();
						}
						catch ( PDOException $e )
						{
							HandleError ( $e->getMessage() );
						}

						$Page_Quantity = 0;
						foreach ( $Query_Pages as $Key => $Page )
						{
							if ( $Page['Maintenance'] == 'no' )
							{
								$Display = "<span style='color: #00ff00;'>Online</span>";
							}
							else
							{
								$Display = "<span style='color: #ff0000;'>Offline</span>";
							}


							if ( $Page_Quantity % 3 == 0 )
							{
								echo "</tr><tr>";
							}
							
							echo "
								<td class='box_slot' style='width: calc(100% / 3);'>
									<a href='javascript:void(0);' onclick='ToggleMaintenance({$Page['ID']});' style='font-size: 18px;'>
										{$Page['Name']}
									</a>
									<br />
									{$Display}
								</td>
							";

							$Page_Quantity++;
						}

						if ( $Page_Quantity % 3 == 1 )
						{
							echo "<td class='box_slot'></td>";
							echo "<td class='box_slot'></td>";
						}

						if ( $Page_Quantity % 3 == 2 )
						{
							echo "<td class='box_slot'></td>";
						}
					?>
				</table>
			</div>
		</div>

<?php

		exit;
	}
?>

<div class='head'>Page Maintenance</div>
<div class='box'>
	<div class='description' style='margin-bottom: 5px;'>
		Here, you may put any given page in to or out of maintenance.
	</div>

	<div id='AJAX'>
		<div class='panel'>
			<div class='panel-heading'>Page Index</div>
			<div class='panel-body'>
				<table class='box_cont' style='width: 100%;'>
					<?php
						try
						{
							$Query_Pages = $PDO->query("SELECT * FROM `pages` WHERE `Staff_Only` = 'No'")->fetchAll();
						}
						catch ( PDOException $e )
						{
							HandleError ( $e->getMessage() );
						}

						$Page_Quantity = 0;
						foreach ( $Query_Pages as $Key => $Page )
						{
							if ( $Page['Maintenance'] == 'no' )
							{
								$Display = "<span style='color: #00ff00;'>Online</span>";
							}
							else
							{
								$Display = "<span style='color: #ff0000;'>Offline</span>";
							}


							if ( $Page_Quantity % 3 == 0 )
							{
								echo "</tr><tr>";
							}
							
							echo "
								<td class='box_slot' style='width: calc(100% / 3);'>
									<a href='javascript:void(0);' onclick='ToggleMaintenance({$Page['ID']});' style='font-size: 18px;'>
										{$Page['Name']}
									</a>
									<br />
									{$Display}
								</td>
							";

							$Page_Quantity++;
						}

						if ( $Page_Quantity % 3 == 1 )
						{
							echo "<td class='box_slot'></td>";
							echo "<td class='box_slot'></td>";
						}

						if ( $Page_Quantity % 3 == 2 )
						{
							echo "<td class='box_slot'></td>";
						}
					?>
				</table>
			</div>
		</div>
	</div>
</div>

<script type='text/javascript'>
	function ToggleMaintenance(Page)
	{
		$.ajax({
			type: 'post',
			url: 'maintenance.php',
			data: { Toggle: Page },
			success: function(data)
			{
				$('#AJAX').html(data);
			},
			error: function(data)
			{
				$('#AJAX').html(data);
			}
		});
	}
</script>