<?php 
	require("db.php");
	
	ini_set("display_errors", 0);
	
	session_start();
	
	// Destroy the user's cookies.
	$past = time() - 100; 
	setcookie(save_user, gone, $past); 
	setcookie(save_pass, gone, $past); 
	
	session_destroy();
	
	# Fetch Promo Data
	$Promo_Data = mysqli_fetch_assoc(mysqli_query($con, "SELECT * FROM promo WHERE Promo_Active = 'True'"));
	$Promo_Type = mysqli_fetch_assoc(mysqli_query($con, "SELECT * FROM types WHERE id = '" . $Promo_Data['Promo_Type'] . "'"));
	
	# Logging in~
	if (isset($_POST['login'])) {
		session_start();
		if (!$_POST['login_username'] | !$_POST['login_password']) {
			$error = "<div class='error'>You did not fill in a required field.</div>";
		} else {
			$check = mysqli_query($con, "SELECT * FROM members WHERE Username = '" . mysqli_real_escape_string($con, $_POST['login_username']) . "'") or die();
			$check2 = mysqli_num_rows($check);

			if ($check2 == 0) {
				$error = "<div class='error'>That user does not exist in our database.</div>";
			}

			else {
				$info = mysqli_fetch_array($check);
				$_POST['login_password'] = stripslashes($_POST['login_password']);
				$info['Password'] = stripslashes($info['Password']);
				$_POST['login_password'] = sha1(md5($_POST['login_password']));

				if ($_POST['login_password'] != $info['Password']) {
					$error = "<div class='error'>You entered an incorrect password. Please try again.</div>";
				} else {
					$_POST['login_username'] = stripslashes($_POST['login_username']);
					$month = time() + 3600 * 24 * 30;
					$_SESSION['login_user'] = $_POST['login_username'];

					setcookie("save_user", stripslashes(htmlentities($_POST['login_username'])), $hour);
					setcookie("save_pass", stripslashes(htmlentities($_POST['login_password'])), $month);
					header("location: news.php");
				}
			}
		}
	}
	
	$memCount  = mysqli_num_rows(mysqli_query($con, "SELECT id FROM members"));
?>

<!DOCTYPE html>
<html>
	<head>
		<title>Logout :: The Pokemon Radiation RPG</title>
		<link href="images/Icons/Generation 3/385.gif" rel="shortcut icon"/>
		<link href="css/tipped.css" rel="stylesheet">
		<link href="css/bootstrap.min.css" rel="stylesheet">
		<link href="css/bootstrap-theme.min.css" rel="stylesheet">
		
		<script type="text/javascript" src="js/jquery-2.1.4.min.js"></script>
		<script type="text/javascript" src="js/bootstrap.min.js"></script>
		<script type="text/javascript" src="js/tipped.js"></script>
		<script type="text/javascript" src="js/ajax.js"></script>
	</head>
	
	<style>
		html { background: #000; height: 100%; }
		body { background: #111; border-left: 2px solid #4A618F; border-right: 2px solid #4A618F; color: #fff; height: 100%; margin: 0 auto; width: 1100px; }
		a:link { color: #4A618F; text-decoration: none; }
		a:hover { color: #669ac1 !important; text-decoration: none; }
		a:visited { color: #4A618F; text-decoration: none; }
		a, button, img, input { outline: none; }
		input[type='text'], input[type='password'] { background: #5c709a; border: 2px solid #000; border-radius: 4px; margin-bottom: 5px; padding: 3px; text-align: center; width: 200px; }
		input[type='submit'] { background: #5c709a; border: 2px solid #000; border-radius: 4px; margin-bottom: 5px; padding: 3px; width: 200px; }
		input[type='submit']:hover { background: #425780; }
		select { background: #5c709a; border: 2px solid #000; border-radius: 4px; padding: 3px; }
		::-webkit-input-placeholder { color: #000; }
	
		.banner { border-bottom: 2px solid #4A618F; }
		.banner div { background: rgba(0,0,0,0.5); border-bottom: 2px solid #4A618F; border-right: 2px solid #4A618F; padding: 3px; position: absolute; text-align: center; top: 0; width: 210px; }
		
		.success { background: #334364; border: 2px solid #00ff00; border-radius: 4px; margin: 5px auto; padding: 5px; width: 80%; }
		.error { background: #334364; border: 2px solid #ff0000; border-radius: 4px; margin: 5px auto; padding: 5px; width: 80%; }
		
		.cmod { color: #fcbc19 !important; }
		.gmod { color: #25e1e8 !important; }
		.admin { color: #bf00ff !important; }
		
		.left-col, .right-col { float: left; margin: 5px; }
		.left-col { width: 300px; }
		.left-col .panel { margin-bottom: 5px; }
		.right-col { width: 775px; }
		.right-col #content { border-top-left-radius: 0px; }
		
		.nav div {
			background: #4A618F; 
			background: -moz-linear-gradient(top, #4A618F 0%, #888 100%, #888 100%); 
			background: -webkit-linear-gradient(top, #4A618F 0%, #888 100%, #888 100%); 
			background: linear-gradient(to bottom, #4A618F 0%, #888 100%, #888 100%);
			border: 2px solid #4A618F;
			border-bottom: none;
			border-top-left-radius: 4px;
			border-top-right-radius: 4px;
			float: left;
			margin-right: 5px;
			padding: 3px;
			text-align: center;
			width: 125px;
		}
		.nav div:hover {
			background: #4A618F; 
			background: -moz-linear-gradient(top, #4A618F 0%, #777 100%, #777 100%); 
			background: -webkit-linear-gradient(top, #4A618F 0%, #777 100%, #777 100%); 
			background: linear-gradient(to bottom, #4A618F 0%, #777 100%, #777 100%);
			cursor: pointer;
		}
		
		.panel { background: #253047; border: 2px solid #4A618F; margin-bottom: 0px; text-align: center; }
		.panel .panel-heading { background: #333; border-color: #4A618F; color: #fff; font-weight: bold; padding: 3px; }
		
		.panel .panel-heading {
			background: #4A618F; 
			background: -moz-linear-gradient(top, #888 0%, #4A618F 100%, #4A618F 100%); 
			background: -webkit-linear-gradient(top, #888 0%, #4A618F 100%, #4A618F 100%); 
			background: linear-gradient(to bottom, #888 0%, #4A618F 100%, #4A618F 100%); 
		}
		
		.panel .panel-body { padding: 3px; }
		
		.panel .panel-subheading { background: #3b4d72; border-top: 1px solid #4A618F; font-weight: bold; padding: 3px; }
	</style>
	
	<body>
		<div class='banner' style='border-bottom-left-radius: 4px; border-bottom-right-radius: 4px;'>
			<img src='images/Assets/banner.png' />
		</div>
		
		<div class='success' style='margin: 5px 5px 0px 5px; text-align: center; width: calc(100% - 10px);'>
			You have successfully logged out.
		</div>
		
		<div class='left-col'>
			<div class='panel'>
				<div class='panel-heading'>Login</div>
				<div class='panel-body' style='padding-top: 8px;'>
					<form action='login.php' method='post'>
						<input type='text' name='login_username' placeholder='Username' />
						<input type='password' name='login_password' placeholder='Password' />
						
						<input type='submit' name='login' value='Login' />
					</form>
				</div>
			</div>
			
			<div class='panel'>
				<div class='panel-heading'>Current Promo</div>
				<div class='panel-body'>
					<?php
						echo	"<img src='images/Pokemon/" . $Promo_Type['Name'] . "/" . $Promo_Data['Promo_Dex'] . ".png' />";
					?>
				</div>
				<div class='panel-subheading'>
					<?php
						if ( $Promo_Type != "Normal" ) {
							echo	$Promo_Type['Name'] . $Promo_Data['Promo_Name'];
						}
						else {
							echo	$Promo_Data['Promo_Name'];
						}
					?>
				</div>
			</div>
			
			<div class='panel'>
				<div class='panel-heading'>Registered Players</div>
				<div class='panel-body'>
					<?php
						echo number_format($memCount);
					?>
				</div>
			</div>
		</div>
		
		<div class='right-col'>		
			<div class='nav'>
				<div onclick='showContent(1)'>News</div>
				<div onclick='showContent(3)'>Register</div>
			</div>
			
			<div class='panel' id='content'>
				<div class='panel-heading'>News/Register</div>
				<div class='panel-body'>
					zzz
				</div>
			</div>
		</div>
		
		<script type='text/javascript'>
			function showContent(id) {
				$('#content .panel-heading').html('Loading');
				$('#content .panel-body').html('Loading..');
				
				$.ajax({
					type: 'post',
					url: 'ajax_index.php',
					data: { id: id },
					success: function(data) {
						$('#content').html(data);
					},
					error: function(data) {
						$('#content').html(data);
					}
				});
			}
			
			$(function() {
				showContent(1);
			});
		</script>
	</body>
</html>