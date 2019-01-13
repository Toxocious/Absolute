<?php
	if ( isset($_GET['tab']) )
	{
		require '../../functions/main_functions.php';
		global $PDO;

		if ( $_GET['tab'] === 'Home' )
		{

			$Last_Active = strtotime("-24 hours", time());
			try
			{
				$Online_Query = $PDO->prepare("SELECT `id` FROM `members` WHERE `last_active` > ? ORDER BY `last_active` DESC");
				$Online_Query->execute([$Last_Active]);
				$Online_Query->setFetchMode(PDO::FETCH_ASSOC);
				$Online_Count = count($Online_Query->fetchAll());

				$Fetch_User_Count = $PDO->query("SELECT COUNT(`id`) FROM `members`");
				$User_Count = $Fetch_User_Count->fetchColumn();
		
				$Fetch_Pokemon_Count = $PDO->query("SELECT COUNT(`ID`) FROM `pokemon`");
				$Pokemon_Count = $Fetch_Pokemon_Count->fetchColumn();
			} 
			catch ( PDOException $e )
			{
				HandleError( $e->getMessage() );
			}
?>

			<div class='description' style='background: #334364; margin-bottom: 3px; width: 70%;'>
				Of the <b><?= number_format($User_Count); ?></b> registered users on Absolute, <b><?= number_format($Online_Count); ?></b> of them have been online today!<br />
				The Pokemon Absolute is home to <b><?= number_format($Pokemon_Count); ?></b> Pokemon!
			</div>

			<div style='text-align: left; width: 100%;'>
				<img src='https://vignette.wikia.nocookie.net/nintendo/images/b/b2/Professor_Sycamore_%28Pok%C3%A9mon_X_and_Y%29.png/revision/latest?cb=20131102213329&path-prefix=en' style='height: 345px; transform: scaleX(-1); width: 230px;' />

				<div style='float: right; text-align: center; width: calc(100% - 300px);'>
					Welcome to Absolute text blurb goes here.<br />
					<br />
					What is Lorem Ipsum?<br />
					Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged. It was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum.
					<br /><br />
					Why do we use it?<br />
					It is a long established fact that a reader will be distracted by the readable content of a page when looking at its layout. The point of using Lorem Ipsum is that it has a more-or-less normal distribution of letters, as opposed to using 'Content here, content here', making it look like readable English. Many desktop publishing packages and web page editors now use Lorem Ipsum as their default model text, and a search for 'lorem ipsum' will uncover many web sites still in their infancy. Various versions have evolved over the years, sometimes by accident, sometimes on purpose (injected humour and the like).
				</div>
			</div>

<?php
		}
		else if ( $_GET['tab'] == 'Login' )
		{
?>

			<div class='description' style='background: #334364; margin-bottom: 3px; width: 70%;'>
				Fill in the form below if you wish to login to Absolute.
			</div>

			<div class='description' style='background: #334364; width: 50%;'>
				<form>
					<b>Username/ID</b><br />
					<input type='text' name='username' placeholder='Username/ID' style='text-align: center;' />
					<br />
					<b>Password</b><br />
					<input type='password' name='password' placeholder='Password' style='text-align: center;' />
					<br />
					<input type='submit' name='login' value='Login to Absolute' style='margin-left: -3px; width: 180px;' />
				</form>
			</div>

			<script type='text/javascript'>
				$('form').submit(function(e) {
					e.preventDefault();
					$.ajax({
						type: "POST",
						url: 'core/ajax/index/login.php',
						data: { username: $('input[name="username"]').val(), password: $('input[name="password"]').val() },
						success: function(data)
						{
							$('#index').html(data);
						},
						error: function(data)
						{
							$('#index').html(data);
						}
					});
				});
			</script>

<?php
		}
		else if ( $_GET['tab'] == 'Register' )
		{
?>
			<div class='description' style='background: #334364; margin-bottom: 3px; width: 70%;'>
				Fill in the form below if you wish to register an account to Absolute.
			</div>

			<div id='result'></div>
			
			<form id='registration_form' action="#" method="post">
				<div class='row'>
					<div class='col-xs-6'>
						<div class='panel panel-default' style='margin-bottom: 5px;'>
							<div class='panel-heading'>Username</div>
							<div class='panel-body' style='padding: 5px'>
								<input type='text' name='Username' placeholder='Username' style='margin-bottom: 0px; text-align: center;' />
							</div>
						</div>
					</div>

					<div class='col-xs-6'>
						<div class='panel panel-default' style='margin-bottom: 5px;'>
							<div class='panel-heading'>Gender</div>
							<div class='panel-body' style='padding: 6px'>
								<select name='Gender' style='width: 180px'>
									<option>Select A Gender</option>
									<option value='1'>Female</option>
									<option value='2'>Male</option>
									<option value='3'>Ungendered</option>
								</select>
							</div>
						</div>
					</div>
				</div>

				<div class='row'>
					<div class='col-xs-6'>
						<div class='panel panel-default' style='margin-bottom: 5px;'>
							<div class='panel-heading'>Password</div>
							<div class='panel-body' style='padding: 5px'>
								<input type='password' name='Password' placeholder='Password' style='margin-bottom: 0px; text-align: center;' />
							</div>
						</div>
					</div>

					<div class='col-xs-6'>
						<div class='panel panel-default' style='margin-bottom: 5px;'>
							<div class='panel-heading'>Password Confirmation</div>
							<div class='panel-body' style='padding: 5px'>
								<input type='password' name='Password_Confirm' placeholder='Confirm Password' style='margin-bottom: 0px; text-align: center;' />
							</div>
						</div>
					</div>
				</div>

				<div class='row'>
					<div class='col-xs-6'>
						<div class='panel panel-default'>
							<div class='panel-heading'>Choose An Avatar</div>
							<div class='panel-body'>
								<div style='margin-bottom: 17.5px; margin-top: 17.5px;'>
									<img src='images/Avatars/Sprites/1.png' id='selectedAvatar' />
								</div>
								
								<select name='Avatar' id='Avatar' onchange='changeAvatar()'>
									<option>Select An Avatar</option>
									<?php
										for ( $i = 1; $i <= 352; $i++ ) {
											echo "<option value='" . $i . "'>Trainer #" . $i . "</option>";
										}
									?>
								</select>
							</div>
						</div>
					</div>

					<div class='col-xs-6'>
						<div class='panel panel-default'>
							<div class='panel-heading'>Choose A Starter</div>
							<div class='panel-body'>
								<div style='margin-bottom: 10px; margin-top: 10px;'>
									<img src='images/Pokemon/Normal/1.png' id='selectedStarter' />
								</div>

								<select name='Starter' id='Starter' onchange='changeStarter()'>
									<option>Select A Starter</option>
									<option value='1'>Bulbasaur</option>
									<option value='4'>Charmander</option>
									<option value='7'>Squirtle</option>
									<option value='152'>Chikorita</option>
									<option value='155'>Cyndaquil</option>
									<option value='158'>Totodile</option>
									<option value='252'>Treecko</option>
									<option value='255'>Torchic</option>
									<option value='258'>Mudkip</option>
									<option value='387'>Turtwig</option>
									<option value='390'>Chimchar</option>
									<option value='393'>Piplup</option>
									<option value='495'>Snivy</option>
									<option value='498'>Tepig</option>
									<option value='501'>Oshawott</option>
									<option value='650'>Chespin</option>
									<option value='653'>Fennekin</option>
									<option value='656'>Froakie</option>
									<option value='722'>Rowlet</option>
									<option value='725'>Litten</option>
									<option value='728'>Popplio</option>
								</select>
							</div>
						</div>
					</div>
				</div>

				<input type='submit' name='register' value='Register!' style='margin-bottom: 5px; margin-top: 8px; width: 50%;' />
			</form>

			<script type='text/javascript'>
				$('form#registration_form').on('submit', function (e)
				{
					e.preventDefault();

					$.ajax({
						type: 'post',
						url: 'core/ajax/index/register.php',
						data: $('form#registration_form').serialize(),
						success: function(data) {
							$('#index').html(data);
						}
					});
				});

				function changeAvatar()
				{
					var image = document.getElementById("selectedAvatar");
					var dropd = document.getElementById("Avatar");
					image.src = 'images/Avatars/Sprites/' + dropd.value + '.png';
				}

				function changeStarter()
				{
					var image = document.getElementById("selectedStarter");
					var dropd = document.getElementById("Starter");
					image.src = 'images/Pokemon/Normal/' + dropd.value + '.png';
				}
			</script>
<?php
		}
		else if ( $_GET['tab'] == 'Discord' )
		{
?>

			<div class='description' style='background: #334364; margin-bottom: 5px; padding: 5px 20px; width: 70%;'>
				While you're here, please take a look at Absolute's Discord server.
				A lot goes on here, including announcing updates to Absolute, as well as general conversation between it's users.
			</div>

			<iframe src="https://discordapp.com/widget?id=269182206621122560&theme=dark" width="500" height="500" allowtransparency="true" frameborder="0"></iframe>

<?php
		}
	}