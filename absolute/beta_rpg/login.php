<?php
    require_once $_SERVER['DOCUMENT_ROOT'] . '/backend/session/session.php';
    require_once $_SERVER['DOCUMENT_ROOT'] . '/backend/auth/login_service.php';

    $Page_Metadata = [
        'Title' => 'Login',
        'Styles' => ['/themes/pages/login.css'],
        'Scripts' => [],
    ];

    ob_start();

    if ( isset($_SESSION['Absolute_Beta']['Logged_In_As']) )
    {
        echo "
            <div class='panel'>
                <div class='panel-header'>
                    Already Logged In?
                </div>

                <div class='panel-content login-page'>
                    <h2>It looks like you're already logged in!</h2>
                    <p>Continue your adventure or explore the world.</p>
                </div>
            </div>
        ";

        $Content = ob_get_clean();
        require_once $_SERVER['DOCUMENT_ROOT'] . '/layout/layout.php';

        exit;
    }

    if ( $_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['username']) && !empty($_POST['password']) )
    {
        $Username = (string)($_POST['username'] ?? '');
        $Password = (string)($_POST['password'] ?? '');

        $Login_Result = LoginService::attempt($Username, $Password);

        var_dump($Login_Result);

        if ( !$Login_Result['ok'] ) {
            $Login_Message = [
                'Type' => 'error',
                'Text' => $Login_Result['message'] ?? 'An unknown error occurred during login.',
            ];
        } else {
            header('Location: /news.php');
            exit;
        }
    }
?>

<div class='panel'>
    <div class='panel-header'>
        Login
    </div>

    <div class='panel-content login-page'>
        <h2>Welcome back, Trainer!</h2>
        <p>Sign in to continue your adventure.</p>

        <?php if ( isset($Login_Message) ): ?>
            <div class='login-message <?= $Login_Message['Type'] ?? ''; ?>'>
                <?= $Login_Message['Text'] ?? ''; ?>
            </div>
        <?php endif; ?>

        <section class='login-form'>
            <form method='POST' action='/login.php'>
                <section>
                    <label for='username'>Username</label>
                    <input type='text' id='username' name='username' placeholder='Enter your username' required />
                </section>

                <section>
                    <label for='password'>Password</label>
                    <input type='password' id='password' name='password' placeholder='Enter your password' required />
                </section>

                <section>
                    <a href='/forgot-password.php'>Forgot your password?</a>
                </section>

                <section>
                    <button type='submit'>Sign In</button>
                </section>
            </form>

            <h2 class='divider'>Or</h2>

            <form action='/register.php'>
                <section>
                    <button>Create an Account</button>
                </section>
            </form>
        </section>
    </div>
</div>

<?php
    $Content = ob_get_clean();
    require_once $_SERVER['DOCUMENT_ROOT'] . '/layout/layout.php';
