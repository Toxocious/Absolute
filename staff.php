<?php
	require 'core/required/layout_top.php';

	try
	{
		$Fetch_Staff = $PDO->prepare("SELECT `id`, `Username`, `Avatar`, `Rank`, `Last_Active`, `Staff_Message` FROM `users` WHERE `Power` >= 3 ORDER BY `Power` DESC");
		$Fetch_Staff->execute();
		$Fetch_Staff->setFetchMode(PDO::FETCH_ASSOC);
		$Staff = $Fetch_Staff->fetchAll();
	}
	catch( PDOException $e )
	{
		HandleError( $e->getMessage() );
	}
?>

<style>
	.trainer_card
	{
		flex-grow: 1;
		margin-top: 10px;
		max-width: calc(100% / 3);
		width: 290px;
	}

	.trainer_card:nth-child(3n+2)
	{
    margin-left: 5px;
    margin-right: 5px;
	}

	.trainer_card > .trainer_avatar
	{
		background: linear-gradient(180deg, #8090b0 0, #334364);
    border-radius: 50%;
		box-shadow: 0px 1px 5px #000;
    height: 104px;
    padding: 2px;
    width: 104px;
    position: relative;
    margin: 0px 93px;
    margin-bottom: -53px;
	}

	.trainer_card > .trainer_avatar > div
	{
		background: #253147;
    border-radius: 50%;
    height: 100px;
    width: 100px;
	}

	.trainer_card > .trainer_avatar > div > a > img
	{
		border-radius: 50%;
		height: 100px;
    width: 100px;
	}

	.trainer_card > .trainer_url
	{
		background: #2c3a55;
    border: 2px solid #4a618f;
    border-top-left-radius: 6px;
    border-top-right-radius: 6px;
		padding-top: 55px;
	}

	.trainer_card > .trainer_roster
	{
		background: #1d2639;
    border: 2px solid #4a618f;
    border-bottom: none;
    border-top: none;
		height: 50px;
		padding: 5px 0px;
	}

	.trainer_card > .trainer_roster > .slot
	{
		background: linear-gradient(180deg, #8090b0 0, #334364);
		border-radius: 50%;
		box-shadow: 0px 1px 5px #000;
    height: 40px;
    padding: 2px;
		float: left;
    width: 40px;
	}

	.trainer_card > .trainer_roster > .slot > div
	{
		background: #253147;
    border-radius: 50%;
    height: 36px;
    padding: 3px 0px 0px 0px;
    width: 36px;
	}

	.trainer_card > .trainer_roster > .slot > div:hover {
    background: #3b4d72;
    cursor: pointer;
	}

	.trainer_card > .trainer_roster > .slot > div > img
	{
		border-radius: 50%;
    margin-left: -2px;
	}

	.trainer_card > .trainer_roster > .slot:nth-child(1) { margin-left: 7px; }
	.trainer_card > .trainer_roster > .slot:nth-child(2) { margin-left: 7px; }
	.trainer_card > .trainer_roster > .slot:nth-child(3) { margin-left: 7px; }
	.trainer_card > .trainer_roster > .slot:nth-child(4) { margin-left: 7px; }
	.trainer_card > .trainer_roster > .slot:nth-child(5) { margin-left: 7px; }
	.trainer_card > .trainer_roster > .slot:nth-child(6) { margin-left: 7px; }

	.trainer_card > .trainer_page
	{
		background: #2c3a55;
    border: 2px solid #4a618f;
    border-bottom-left-radius: 6px;
    border-bottom-right-radius: 6px;
	}

	.trainer_card > .trainer_page > div:nth-child(1)
	{
		display: inline-block;
    padding: 5px;
    text-align: left;
    width: calc(50% - 5px);
	}

	.trainer_card > .trainer_page > div:nth-child(2)
	{
		display: inline-block;
    padding: 5px;
    width: calc(50% - 5px);
    text-align: right;
	}
</style>

<div class='panel content'>
	<div class='head'>Staff List</div>
	<div class='body'>
		<div class='description' style='margin: 0px auto 5px'>
			All members of Absolute's staff team are listed below.<br />
			If you require assistance with something, don't hesitate to contact one of them.
		</div>

		<div class='row' style='display: flex; flex-wrap: wrap;'>
			<?php
				foreach ( $Staff as $User_Key => $User_Val )
				{
					$Online_Username = $User_Class->DisplayUsername($User_Val['id'], true, true, true);

					echo "
						<div class='trainer_card'>
							<div class='trainer_avatar'>
								<div>
									<a href='/profile.php?id={$User_Val['id']}'>
										<img src='{$User_Val['Avatar']}' />
									</a>
								</div>
							</div>

							<div class='trainer_url'>
								<b>{$Online_Username}</b>
							</div>

							<div class='trainer_roster'>
								<b>{$User_Val['Rank']}</b><br />
								<a href=''>Send Me A Message</a>
							</div>

							<div class='trainer_page'>
								<div style='text-align: center; width: 293px;'><b>Last Online</b>: " . lastseen($User_Val['Last_Active'], 'week') . "</div>
							</div>
						</div>
					";
				}
			?>
		</div>
	</div>
</div>

<?php
	require 'core/required/layout_bottom.php';