<?php
	require 'core/required/layout_top.php';

	if ( isset($_SESSION['abso_user']) )
	{
		echo "
			<div class='panel content'>
				<div class='head'>Register</div>
				<div class='body'>
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
			$Username = ( isset($_POST["username"]) ) ? $Purify->Cleanse($_POST['username']) : '';
			$Password = ( isset($_POST["password"]) ) ? $Purify->Cleanse($_POST["password"]) : '';
			$Password_Confirm = ( isset($_POST["password_confirm"]) ) ? $Purify->Cleanse($_POST["password_confirm"]) : '';
			$Gender = ( isset($_POST['gender']) ) ? $Purify->Cleanse($_POST["gender"]) : 'u';
			$Starter = ( isset($_POST['starter']) ) ? $Purify->Cleanse($_POST["starter"]) : '';
			$Avatar = ( isset($_POST['avatar']) ) ? $Purify->Cleanse($_POST["avatar"]) : '1';

			try {
				$Check_Username = $PDO->prepare("SELECT COUNT(*) FROM `users` WHERE LOWER(`Username`) = LOWER(?) LIMIT 1");
				$Check_Username->execute([$Username]);
				$Username_Available = $Check_Username->fetchColumn();
			} catch ( PDOException $e) {
				echo $e->getMessage();
			}
			
			// Field Check
			if ( $Username == '' || $Password == '' || $Password_Confirm == '' || $Gender == '' )
			{
				$Error = "<div style='border: 2px solid #7f0000; background: #190000; margin-bottom: 3px; width: 70%;'>You must fill in all fields.</div>";
			}
			else if ( $Username_Available == '1' )
			{
				$Error = "<div>The username that you have chosen is already taken.</div>";
			}
			else if ( $Password != $Password_Confirm )
			{
				$Error = "<div style='border: 2px solid #7f0000; background: #190000; margin-bottom: 3px; width: 70%;'>The passwords that you have entered do not match.</div>";
			}
			else if ( $Avatar > 352 || $Avatar < 1 )
			{
				$Error = "<div style='border: 2px solid #7f0000; background: #190000; margin-bottom: 3px; width: 70%;'>Please choose a valid avatar.</div>";
			}
			else if ( !in_array($Gender, ['m', 'f', 'u']) )
			{
				$Error = "<div style='border: 2px solid #7f0000; background: #190000; margin-bottom: 3px; width: 70%;'>Please choose a valid gender.</div>";
			}
			else if ( !in_array($Starter, ['1', '4', '7', '152', '155', '158', '252', '255', '258', '387', '390', '393', '495', '498', '501', '650', '653', '656', '722', '725', '728', '810', '813', '816']) )
			{
				$Error = "<div style='border: 2px solid #7f0000; background: #190000; margin-bottom: 3px; width: 70%;'>Please choose a valid starter Pokemon.</div>";
			}

			if ( !isset($Error) )
			{
				$Base_Salt = GAME_DEFAULT_SALT;
				$Base_Key = RandSalt(80);
				$Hashed_Password = hash_hmac('sha512', $Password . $Base_Key, $Base_Salt);
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

					$User_Currency_Create = $PDO->prepare("INSERT INTO `user_currency` ( `User_ID` ) VALUES ( ? )");
					$User_Currency_Create->execute([ $User_ID ]);

					$Nature_List = [
						'Lonely',	'Adamant', 'Naughty', 'Brave', 'Bold', 'Impish', 'Lax', 'Relaxed', 'Modest', 'Mild', 'Rash', 'Quiet', 'Calm', 'Gentle', 'Careful', 'Sassy', 'Timid', 'Hasty', 'Jolly', 'Naive', 'Bashful', 'Docile', 'Hardy', 'Quirky', 'Serious',	
					];
					$Nature_Random = array_rand($Nature_List, 1);
					$Nature = $Nature_List[$Nature_Random];

					$Poke_Gender = (mt_rand(1, 7) == 1) ? 'Female' : 'Male';
					$IVs = mt_rand(0, 31) . "," . mt_rand(0, 31) . "," . mt_rand(0, 31) . "," . mt_rand(0, 31) . "," . mt_rand(0, 31) . "," . mt_rand(0, 31);
					$Starter_Data = $Poke_Class->FetchPokedexData($Starter);

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
					$Starter_ID = $PDO->lastInsertId();

					$User_Roster_Update = $PDO->prepare("UPDATE `users` SET `Roster` = ? WHERE `Username` = ? LIMIT 1");
					$User_Roster_Update->execute([ $Starter_ID, $Username ]);
				}
				catch ( PDOException $e )
				{
					HandleError( $e->getMessage() );
				}

				$Error = "
					<div class='success' style='margin-bottom: -15px; width: 70%;'>
						You've successfully registered an account on The Pokemon Absolute!<br />
						<a href='login.php'><b>Click Here To Login</b></a>
					</div>
				";
			}
		}
?>

<div class="panel content" style="margin: 5px; width: calc(100% - 14px);">
	<div class='head'>Register</div>
	<div class='body pokecenter'>
		<div class='nav'>
			<div><a href='index.php' style='display: block;'>Home</a></div>
			<div><a href='login.php' style='display: block;'>Login</a></div>
			<div><a href='register.php' style='display: block;'>Register</a></div>
			<div><a href='discord.php' style='display: block;'>Discord</a></div>
		</div>

		<?php
			if ( isset($Error) )
			{
				echo $Error . "<br />";
			}
		?>
		
		<div class='description' style='background: #334364; margin-bottom: 5px; width: 70%;'>Please fill out the form below in order to begin your journey as a Pokemon Trainer.</div>

		<form action="/register.php" method="post">
			<div class='panel' style='margin: 5px auto; width: calc(100% - 14px);'>
				<div class='head'>User Details</div>
				<div class='body' style='padding: 3px;'>
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

					<div style='height: 101px;'>
						<input type='submit' name='register' value='Register' style='width: 25%; position: relative; top: 46%;' />
					</div>
				</div>
			</div>

			<div class='row' style="min-height: 620px; position: relative;">
				<div class='panel' style='float: right; width: calc(100% / 2 - 20px); margin-right: 6px;'>
					<div class='head'>Select A Starter</div>
					<div class='body'>
						<div class='row' style='height: 145px;'>
							<div style='float: left; padding: 0px; width: calc(100% / 6);'>
								<img src='images/Pokemon/Normal/001.png' /><br />
								Bulbasaur<br />
								<input type='radio' name='starter' value='001' />
							</div>
							<div style='float: left; padding: 0px; width: calc(100% / 6);'>
								<img src='images/Pokemon/Normal/004.png' /><br />
								Charmander<br />
								<input type='radio' name='starter' value='004' />
							</div>
							<div style='float: left; padding: 0px; width: calc(100% / 6);'>
								<img src='images/Pokemon/Normal/007.png' /><br />
								Squirtle<br />
								<input type='radio' name='starter' value='007' />
							</div>
							<div style='float: left; padding: 0px; width: calc(100% / 6);'>
								<img src='images/Pokemon/Normal/152.png' /><br />
								Chikorita<br />
								<input type='radio' name='starter' value='152' />
							</div>
							<div style='float: left; padding: 0px; width: calc(100% / 6);'>
								<img src='images/Pokemon/Normal/155.png' /><br />
								Cyndaquil<br />
								<input type='radio' name='starter' value='155' />
							</div>
							<div style='float: left; padding: 0px; width: calc(100% / 6);'>
								<img src='images/Pokemon/Normal/158.png' /><br />
								Totodile<br />
								<input type='radio' name='starter' value='158' />
							</div>
						</div>

						<div class='row' style='height: 145px;'>
							<div style='float: left; padding: 0px; width: calc(100% / 6);'>
								<img src='images/Pokemon/Normal/252.png' /><br />
								Treecko<br />
								<input type='radio' name='starter' value='252' />
							</div>
							<div style='float: left; padding: 0px; width: calc(100% / 6);'>
								<img src='images/Pokemon/Normal/255.png' /><br />
								Torchic<br />
								<input type='radio' name='starter' value='255' />
							</div>
							<div style='float: left; padding: 0px; width: calc(100% / 6);'>
								<img src='images/Pokemon/Normal/258.png' /><br />
								Mudkip<br />
								<input type='radio' name='starter' value='258' />
							</div>
							<div style='float: left; padding: 0px; width: calc(100% / 6);'>
								<img src='images/Pokemon/Normal/387.png' /><br />
								Turtwig<br />
								<input type='radio' name='starter' value='387' />
							</div>
							<div style='float: left; padding: 0px; width: calc(100% / 6);'>
								<img src='images/Pokemon/Normal/390.png' /><br />
								Chimchar<br />
								<input type='radio' name='starter' value='390' />
							</div>
							<div style='float: left; padding: 0px; width: calc(100% / 6);'>
								<img src='images/Pokemon/Normal/393.png' /><br />
								Piplup<br />
								<input type='radio' name='starter' value='393' />
							</div>
						</div>

						<div class='row' style='height: 145px;'>
							<div style='float: left; padding: 0px; width: calc(100% / 6);'>
								<img src='images/Pokemon/Normal/495.png' /><br />
								Snivy<br />
								<input type='radio' name='starter' value='495' />
							</div>
							<div style='float: left; padding: 0px; width: calc(100% / 6);'>
								<img src='images/Pokemon/Normal/498.png' /><br />
								Tepig<br />
								<input type='radio' name='starter' value='498' />
							</div>
							<div style='float: left; padding: 0px; width: calc(100% / 6);'>
								<img src='images/Pokemon/Normal/501.png' /><br />
								Oshawott<br />
								<input type='radio' name='starter' value='501' />
							</div>
							<div style='float: left; padding: 0px; width: calc(100% / 6);'>
								<img src='images/Pokemon/Normal/650.png' /><br />
								Chespin<br />
								<input type='radio' name='starter' value='650' />
							</div>
							<div style='float: left; padding: 0px; width: calc(100% / 6);'>
								<img src='images/Pokemon/Normal/653.png' /><br />
								Fennekin<br />
								<input type='radio' name='starter' value='653' />
							</div>
							<div style='float: left; padding: 0px; width: calc(100% / 6);'>
								<img src='images/Pokemon/Normal/656.png' /><br />
								Froakie<br />
								<input type='radio' name='starter' value='656' />
							</div>
						</div>

						<div class="row" style='height: 145px;'>
							<div style="float: left; padding: 0px; width: calc(100% / 6);">
								<img src="images/Pokemon/Normal/722.png"><br />
								Rowlet<br>
								<input type="radio" name="starter" value="722">
							</div>
							<div style="float: left; padding: 0px; width: calc(100% / 6);">
								<img src="images/Pokemon/Normal/725.png"><br />
								Litten<br>
								<input type="radio" name="starter" value="725">
							</div>
							<div style="float: left; padding: 0px; width: calc(100% / 6);">
								<img src="images/Pokemon/Normal/728.png"><br />
								Popplio<br>
								<input type="radio" name="starter" value="728">
							</div>
							<div style="float: left; padding: 0px; width: calc(100% / 6);">
								<img src="images/Pokemon/Normal/810.png"><br />
								Grookey<br>
								<input type="radio" name="starter" value="810">
							</div>
							<div style="float: left; padding: 0px; width: calc(100% / 6);">
								<img src="images/Pokemon/Normal/813.png"><br />
								Scorbunny<br>
								<input type="radio" name="starter" value="813">
							</div>
							<div style="float: left; padding: 0px; width: calc(100% / 6);">
								<img src="images/Pokemon/Normal/816.png"><br />
								Sobble<br>
								<input type="radio" name="starter" value="816">
							</div>
						</div>
					</div>
				</div>

				<div class='panel' style='float: left; width: calc(100% / 2 - 15px); margin-left: 6px;'>
					<div class='head'>Select An Avatar</div>
					<div class='body' style='max-height: 552px; overflow: auto;'>
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