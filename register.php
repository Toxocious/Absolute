<?php
	/**
	 * $Starter_List = [
	 * 		[001: Bulbasaur], etc
	 * ];
	 * 
	 * foreach ( $Starter_List as $Index => $Key )
	 * {
	 * 		show sprite + radio input
	 * }
	 */


	require 'core/required/layout_top.php';

	if ( isset($_SESSION['abso_user']) )
	{
		echo "
			<div class='content'>
				<div class='head'>Register</div>
				<div class='box'>
					You may not access this page while you're logged in.
				</div>
			</div>
		";
		require 'core/required/layout_bottom.php';
		exit();
	}
	else
	{
		if ( isset($_POST["register"]) )
		{
			$Username = ( isset($_POST["username"]) ) ? Text($_POST['username'])->in() : '';
			$Password = ( isset($_POST["password"]) ) ? Text($_POST["password"])->in() : '';
			$Password_Confirm = ( isset($_POST["password_confirm"]) ) ? Text($_POST["password_confirm"])->in() : '';
			$Gender = ( isset($_POST['gender']) ) ? Text($_POST["gender"])->in() : 'u';
			$Starter = ( isset($_POST['starter']) ) ? Text($_POST["starter"])->in() : '';
			$Avatar = ( isset($_POST['avatar']) ) ? Text($_POST["avatar"])->in() : '1';

			try {
				$Check_Username = $PDO->prepare("SELECT COUNT(*) FROM `users` WHERE LOWER(`Username`) = LOWER(?) LIMIT 1");
				$Check_Username->execute([$Username]);
				$Username_Available = $Check_Username->fetchColumn();
			} catch ( PDOException $e) {
				echo $e->getMessage();
			}
			
			$Validate_Username = Text($Username)->validate('username');
			$Validate_Password = Text($Password)->validate('password');
			
			// Field Check
			if ( $Username == '' || $Password == '' || $Password_Confirm == '' || $Gender == '' )
			{
				$Oops = "<div style='border: 2px solid #7f0000; background: #190000; margin-bottom: 3px; width: 70%;'>You must fill in all fields.</div>";
			}
			else if ( $Username_Available == '1' )
			{
				$Oops = "<div>The username that you have chosen is already taken.</div>";
			}
			else if ($Validate_Username !== true)
			{
				$Oops = $Validate_Username;
			}
			else if ($Validate_Password !== true)
			{
				$Oops = $Validate_Password;
			}
			else if ( $Password != $Password_Confirm )
			{
				$Oops = "<div style='border: 2px solid #7f0000; background: #190000; margin-bottom: 3px; width: 70%;'>The passwords that you have entered do not match.</div>";
			}
			else if ( $Avatar > 352 || $Avatar < 1 )
			{
				$Oops = "<div style='border: 2px solid #7f0000; background: #190000; margin-bottom: 3px; width: 70%;'>Please choose a valid avatar.</div>";
			}
			else if ( !in_array($Gender, ['m', 'f', 'u']) )
			{
				$Oops = "<div style='border: 2px solid #7f0000; background: #190000; margin-bottom: 3px; width: 70%;'>Please choose a valid gender.</div>";
			}
			else if ( !in_array($Starter, ['1', '4', '7', '152', '155', '158', '252', '255', '258', '387', '390', '393', '495', '498', '501', '650', '653', '656', '722', '725', '728']) )
			{
				$Oops = "<div style='border: 2px solid #7f0000; background: #190000; margin-bottom: 3px; width: 70%;'>Please choose a valid starter Pokemon.</div>";
			}

			if ( !isset($Oops) )
			{
				$Base_Salt = GAME_DEFAULT_SALT;
				$Base_Key = RandomSalt(80);
				$Hashed_Password = hash_hmac('sha512', $Password . $Base_Key, $Base_Key);
				$Signed_Up_On = time();
				$Auth_Code = mt_rand(100000, 99999999);

				switch ($Gender)
				{
					case 'f':
						$Gender = 'Female';
						break;
					case 'm':
						$Gender = 'Male';
						break;
					case 'u':
						$Gender = 'Ungendered';
						break;
				}

				try
				{
					$User_Create = $PDO->prepare("
						INSERT INTO `users` (
							`Username`,
							`Password`,
							`Password_Salt`,
							`Gender`,
							`Date_Registered`,
							`Avatar`,
							`Auth_Code`
						)
						VALUES (?, ?, ?, ?, ?, ?, ?)
					");
					$User_Create->execute([ $Username, $Hashed_Password, $Base_Key, $Gender, time(), "images/Avatars/".$Avatar.".png", $Auth_Code ]);
					$User_ID = $PDO->lastInsertId();

					$Nature_List = [
						'Lonely',	'Adamant', 'Naughty', 'Brave', 'Bold', 'Impish', 'Lax', 'Relaxed', 'Modest', 'Mild', 'Rash', 'Quiet', 'Calm', 'Gentle', 'Careful', 'Sassy', 'Timid', 'Hasty', 'Jolly', 'Naive', 'Bashful', 'Docile', 'Hardy', 'Quirky', 'Serious',	
					];
					$Nature_Random = array_rand($Nature_List, 1);
					$Nature = $Nature_List[$Nature_Random];

					$Poke_Gender = (mt_rand(1, 7) == 1) ? 'Female' : 'Male';
					$IVs = mt_rand(0, 31) . "," . mt_rand(0, 31) . "," . mt_rand(0, 31) . "," . mt_rand(0, 31) . "," . mt_rand(0, 31) . "," . mt_rand(0, 31);
					$Starter_Data = $PokeClass->FetchPokedexData($Starter);

					$Starter_Create = $PDO->prepare("
						INSERT INTO `pokemon` (
							`Pokedex_ID`,
							`Alt_ID`,
							`Name`,
							`Location`,
							`Slot`,
							`Owner_Current`,
							`Owner_Original`,
							`Gender`,
							`IVs`,
							`Nature`,
							`Creation_Date`,
							`Creation_Location`
						) 
						VALUES
						(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
					");
					$Starter_Create->execute([ $Starter_Data['Pokedex_ID'], $Starter_Data['Alt_ID'], $Starter_Data['Name'], 'Roster', 1, $User_ID, $User_ID, $Poke_Gender, $IVs, $Nature, time(), 'Starter Pokemon' ]);
				}
				catch ( PDOException $e )
				{
					HandleError( $e->getMessage() );
				}

				$Oops = "
					<div class='success' style='margin-bottom: -15px; width: 70%;'>
						You've successfully registered an account on The Pokemon Absolute!<br />
						<a href='login.php'><b>Click Here To Login</b></a>
					</div>
				";
			}
		}
?>

<div class='content' style='margin: 5px; max-height: calc(100% - 135px); width: calc(100% - 10px)'>
	<div class='head'>Register</div>
	<div class='box pokecenter'>
		<div class='nav'>
			<div><a href='index.php' style='display: block;'>Home</a></div>
			<div><a href='login.php' style='display: block;'>Login</a></div>
			<div><a href='register.php' style='display: block;'>Register</a></div>
			<div><a href='discord.php' style='display: block;'>Discord</a></div>
		</div>

		<?php
			if ( isset($Oops) )
			{
				echo $Oops . "<br />";
			}
		?>
		
		<div class='description' style='background: #334364; margin-bottom: 5px; width: 70%;'>Please fill out the form below in order to begin your journey as a Pokemon Trainer.</div>

		<form action="/register.php" method="post">
			<div class='panel' style='margin-bottom: 5px;'>
				<div class='panel-heading'>User Details</div>
				<div class='panel-body' style='padding: 3px;'>
					<div style='float: left; width: calc(100% / 3);'>
						<b>Username</b><br />
						<input type='text' name='username' placeholder='Username' />
						<br />
						<b>Gender</b><br />
						<select name='gender' style='padding: 4px; text-align: center; width: 180px;'>
							<option>Select Your Gender</option>
							<option value='f'>Female</option>
							<option value='m'>Male</option>
							<option value='u'>Ungendered</option>
						</select>
					</div>

					<div style='float: left; width: calc(100% / 3);'>
						<b>Password</b><br />
						<input type='password' name='password'>
						<br />
						<b>Confirm Password</b><br />
						<input type='password' name='password_confirm' />
					</div>

					<div style='padding-top: 40px;'>
						<input type='submit' name='register' value='Register' style='width: 25%;' />
					</div>
				</div>
			</div>

			<div class='row'>
				<div class='panel' style='float: left; margin-left: 15px; width: calc(100% / 2 - 20px);'>
					<div class='panel-heading'>Select A Starter</div>
					<div class='panel-body'>
						<div class='row'>
							<div style='float: left; padding: 0px; width: calc(100% / 6);'>
								<img src='images/Pokemon/Normal/001.png' />
							</div>
							<div style='float: left; padding: 0px; width: calc(100% / 6);'>
								<img src='images/Pokemon/Normal/004.png' />
							</div>
							<div style='float: left; padding: 0px; width: calc(100% / 6);'>
								<img src='images/Pokemon/Normal/007.png' />
							</div>
							<div style='float: left; padding: 0px; width: calc(100% / 6);'>
								<img src='images/Pokemon/Normal/152.png' />
							</div>
							<div style='float: left; padding: 0px; width: calc(100% / 6);'>
								<img src='images/Pokemon/Normal/155.png' />
							</div>
							<div style='float: left; padding: 0px; width: calc(100% / 6);'>
								<img src='images/Pokemon/Normal/158.png' />
							</div>
						</div>
						<div class='row'>
							<div style='float: left; padding: 0px; width: calc(100% / 6);'>
								Bulbasaur<br />
								<input type='radio' name='starter' value='001' />
							</div>
							<div style='float: left; padding: 0px; width: calc(100% / 6);'>
								Charmander<br />
								<input type='radio' name='starter' value='004' />
							</div>
							<div style='float: left; padding: 0px; width: calc(100% / 6);'>
								Squirtle<br />
								<input type='radio' name='starter' value='007' />
							</div>
							<div style='float: left; padding: 0px; width: calc(100% / 6);'>
								Chikorita<br />
								<input type='radio' name='starter' value='152' />
							</div>
							<div style='float: left; padding: 0px; width: calc(100% / 6);'>
								Cyndaquil<br />
								<input type='radio' name='starter' value='155' />
							</div>
							<div style='float: left; padding: 0px; width: calc(100% / 6);'>
								Totodile<br />
								<input type='radio' name='starter' value='158' />
							</div>
						</div>

						<div class='row'>
							<div style='float: left; padding: 0px; width: calc(100% / 6);'>
								<img src='images/Pokemon/Normal/252.png' />
							</div>
							<div style='float: left; padding: 0px; width: calc(100% / 6);'>
								<img src='images/Pokemon/Normal/255.png' />
							</div>
							<div style='float: left; padding: 0px; width: calc(100% / 6);'>
								<img src='images/Pokemon/Normal/258.png' />
							</div>
							<div style='float: left; padding: 0px; width: calc(100% / 6);'>
								<img src='images/Pokemon/Normal/387.png' />
							</div>
							<div style='float: left; padding: 0px; width: calc(100% / 6);'>
								<img src='images/Pokemon/Normal/390.png' />
							</div>
							<div style='float: left; padding: 0px; width: calc(100% / 6);'>
								<img src='images/Pokemon/Normal/393.png' />
							</div>
						</div>
						<div class='row'>
							<div style='float: left; padding: 0px; width: calc(100% / 6);'>
								Treecko<br />
								<input type='radio' name='starter' value='252' />
							</div>
							<div style='float: left; padding: 0px; width: calc(100% / 6);'>
								Torchic<br />
								<input type='radio' name='starter' value='255' />
							</div>
							<div style='float: left; padding: 0px; width: calc(100% / 6);'>
								Mudkip<br />
								<input type='radio' name='starter' value='258' />
							</div>
							<div style='float: left; padding: 0px; width: calc(100% / 6);'>
								Turtwig<br />
								<input type='radio' name='starter' value='387' />
							</div>
							<div style='float: left; padding: 0px; width: calc(100% / 6);'>
								Chimchar<br />
								<input type='radio' name='starter' value='390' />
							</div>
							<div style='float: left; padding: 0px; width: calc(100% / 6);'>
								Piplup<br />
								<input type='radio' name='starter' value='393' />
							</div>
						</div>

						<div class='row'>
							<div style='float: left; padding: 0px; width: calc(100% / 6);'>
								<img src='images/Pokemon/Normal/495.png' />
							</div>
							<div style='float: left; padding: 0px; width: calc(100% / 6);'>
								<img src='images/Pokemon/Normal/498.png' />
							</div>
							<div style='float: left; padding: 0px; width: calc(100% / 6);'>
								<img src='images/Pokemon/Normal/501.png' />
							</div>
							<div style='float: left; padding: 0px; width: calc(100% / 6);'>
								<img src='images/Pokemon/Normal/650.png' />
							</div>
							<div style='float: left; padding: 0px; width: calc(100% / 6);'>
								<img src='images/Pokemon/Normal/653.png' />
							</div>
							<div style='float: left; padding: 0px; width: calc(100% / 6);'>
								<img src='images/Pokemon/Normal/656.png' />
							</div>
						</div>
						<div class='row'>
							<div style='float: left; padding: 0px; width: calc(100% / 6);'>
								Snivy<br />
								<input type='radio' name='starter' value='495' />
							</div>
							<div style='float: left; padding: 0px; width: calc(100% / 6);'>
								Tepig<br />
								<input type='radio' name='starter' value='498' />
							</div>
							<div style='float: left; padding: 0px; width: calc(100% / 6);'>
								Oshawott<br />
								<input type='radio' name='starter' value='501' />
							</div>
							<div style='float: left; padding: 0px; width: calc(100% / 6);'>
								Chespin<br />
								<input type='radio' name='starter' value='650' />
							</div>
							<div style='float: left; padding: 0px; width: calc(100% / 6);'>
								Fennekin<br />
								<input type='radio' name='starter' value='653' />
							</div>
							<div style='float: left; padding: 0px; width: calc(100% / 6);'>
								Froakie<br />
								<input type='radio' name='starter' value='656' />
							</div>
						</div>

						<div class='row'>
							<div style='float: left; padding: 0px; width: calc(100% / 3);'>
								<img src='images/Pokemon/Normal/722.png' />
							</div>
							<div style='float: left; padding: 0px; width: calc(100% / 3);'>
								<img src='images/Pokemon/Normal/725.png' />
							</div>
							<div style='float: left; padding: 0px; width: calc(100% / 3);'>
								<img src='images/Pokemon/Normal/728.png' />
							</div>
						</div>
						<div class='row'>
							<div style='float: left; padding: 0px; width: calc(100% / 3);'>
								Rowlet<br />
								<input type='radio' name='starter' value='722' />
							</div>
							<div style='float: left; padding: 0px; width: calc(100% / 3);'>
								Litten<br />
								<input type='radio' name='starter' value='725' />
							</div>
							<div style='float: left; padding: 0px; width: calc(100% / 3);'>
								Popplio<br />
								<input type='radio' name='starter' value='728' />
							</div>
						</div>
					</div>
				</div>

				<div class='panel' style='float: left; margin-left: 5px; width: calc(100% / 2 - 15px);'>
					<div class='panel-heading'>Select An Avatar</div>
					<div class='panel-body' style='max-height: 552px; overflow: auto;'>
						<div class='row'>
							<?php
								for ( $i = 1; $i <= 352; $i++ ) {
									if ( $i >= 349 )
									{
										$Set_Width = '4';
									}
									else
									{
										$Set_Width = '6';
									}

									echo "
										<div style='float: left; padding: 0px; width: calc(100% / " . $Set_Width . ");'>
											<img src='images/Avatars/Sprites/" . $i . ".png' /><br />
											<input type='radio' name='avatar' value='" . $i . "' />
										</div>
									";
									//echo "<option value='" . $i . "'>Trainer #" . $i . "</option>";
								}
							?>
						</div>
					</div>
				</div>
			</div>
		</form>
	</div>
</div>

<?php
	}
	require 'core/required/layout_bottom.php';
?>