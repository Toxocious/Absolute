<?php
	require_once $_SERVER['DOCUMENT_ROOT'] . '/core/required/session.php';
    require_once $_SERVER['DOCUMENT_ROOT'] . '/core/functions/login.php';

    /**
     * The user is already logged in; don't process login logic.
     */
    if
    (
        session_status() === PHP_SESSION_ACTIVE &&
        !empty($_SESSION['Absolute'])
    )
    {
        echo "
            <div class='panel content'>
                <div class='head'>Login</div>
                <div class='body' style='padding: 5px;'>
                    You're already logged in to Absolute.
                </div>
            </div>
        ";

        require_once 'core/required/layout_bottom.php';
        exit;
    }

    /**
     * The user is attempting to log in.
     */
    if ( !empty($_POST['username']) && !empty($_POST['password']) )
    {
        $Username = Purify($_POST['username']);
        $Password = $_POST['password'];
        $IP = $_SERVER["REMOTE_ADDR"];

        $Login_Attempt = false;

        $User_Info = CheckUserExistence($Username);

        if ( empty($User_Info) )
        {
            TrackLoginAttempt($Username, $IP, false);

            $Login_Message = [
                'Type' => 'error',
                'Text' => "An account with that ID or Username does not exist."
            ];
        }

        if ( empty($Login_Message) )
        {
            $Password_Check = CheckUserPasswordMatch($User_Info['ID'], $Password);

            if ( !$Password_Check )
            {
                TrackLoginAttempt($User_Info['ID'], $IP, false);

                $Login_Message = [
                    'Type' => 'error',
                    'Text' => "You have entered an incorrect password."
                ];
            }
        }

        if ( empty($Login_Message) )
        {
            TrackLoginAttempt($User_Info['ID'], $IP, true);

            session_regenerate_id(true);
            $_SESSION['Absolute']['Logged_In_As'] = $User_Info['ID'];
            header('Location: /news.php');
            exit;
        }
	}

    require_once 'core/required/layout_top.php';
?>

<nav>
    <div class='nav-container'>
        <div class='button'>
            <a href='index.php'>Home</a>
        </div>
        <div class='button'>
            <a href='login.php'>Login</a>
        </div>
        <div class='button'>
            <a href='register.php'>Register</a>
        </div>
    </div>
</nav>

<div class='panel content' style='margin: 0 auto;'>
	<div class='head'>Login</div>
	<div class='body' style='padding: 10px 0;'>
        <?php
            if ( !empty($Login_Message) )
            {
                echo "
                    <div class='{$Login_Message['Type']}'>
                        {$Login_Message['Text']}
                    </div>
                ";
            }
        ?>

		<div style=' margin: 0 auto; width: 50%;'>
			<form method="POST">
				<b>Username/ID</b><br />
				<input autofocus type='text' name='username' placeholder='Username/ID' style='text-align: center;' />
				<br /><br />

				<b>Password</b><br />
				<input type='password' name='password' placeholder='Password' style='text-align: center;' />
				<br /><br />

				<input type='submit' name='action' value='Login to Absolute' style='margin-left: -3px; width: 180px;' />
			</form>
		</div>
	</div>
</div>

<?php
	require_once 'core/required/layout_bottom.php';
