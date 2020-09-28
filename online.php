<?php
	require 'core/required/layout_top.php';

	$Last_Active = time() - 60 * 15;

	try
	{
		$Fetch_Online_Users = $PDO->prepare("SELECT `id`, `Avatar`, `Last_Page`, `Last_Active` FROM `users` WHERE `Last_Active` > ?");
		$Fetch_Online_Users->execute([ $Last_Active ]);
		$Fetch_Online_Users->setFetchMode(PDO::FETCH_ASSOC);
		$Online_Users = $Fetch_Online_Users->fetchAll();
	}
	catch( PDOException $e )
	{
		HandleError( $e->getMessage() );
	}
?>

<style>
	.trainer_card
	{
		/*float: left;*/
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
	<div class='head'>Online Users</div>
	<div class='body'>
		<div class='description' style='margin: 0px auto 5px'>
			All users that have been online in the past fifteen minutes are displayed below.
		</div>

		<div class='row' style='display: flex; flex-wrap: wrap;'>
			<?php
				foreach ( $Online_Users as $User_Key => $User_Val )
				{
					$Online_Username = $User_Class->DisplayUsername($User_Val['id'], true, true, true);

					try
					{
						$Fetch_Pokemon = $PDO->prepare("SELECT `ID` FROM `pokemon` WHERE `Owner_Current` = ? AND `Location` = 'Roster' ORDER BY `Slot` ASC LIMIT 6");
						$Fetch_Pokemon->execute([ $User_Val['id'] ]);
						$Fetch_Pokemon->setFetchMode(PDO::FETCH_ASSOC);
						$Fetch_Roster = $Fetch_Pokemon->fetchAll();
					}
					catch ( PDOException $e )
					{
						HandleError( $e->getMessage() );
					}

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
					";

					for ( $i = 0; $i <= 5; $i++ )
					{
						if ( isset($Fetch_Roster[$i]['ID']) )
						{
							$RosterPoke[$i] = $Poke_Class->FetchPokemonData($Fetch_Roster[$i]['ID']);

							echo "
								<div class='slot popup cboxElement' href='" . Domain(1) . "/core/ajax/pokemon.php?id={$RosterPoke[$i]['ID']}'>
									<div>
										<img src='{$RosterPoke[$i]['Icon']}' />
									</div>
								</div>
							";
						}
					}

					echo "
							</div>

							<div class='trainer_page'>
								<div>{$User_Val['Last_Page']}</div>
								<div>" . lastseen($User_Val['Last_Active'], 'week') . "</div>
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
?>