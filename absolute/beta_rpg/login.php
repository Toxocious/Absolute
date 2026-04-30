<?php
    $Page_Metadata = [
        'Title' => 'Login',
        'Styles' => ['/themes/pages/login.css'],
        'Scripts' => [],
    ];

    ob_start();
?>

<div class='panel'>
    <div class='panel-header'>
        Login
    </div>

    <div class='panel-content login-page'>
        <h2>Welcome back, Trainer!</h2>
        <p>Sign in to continue your adventure.</p>

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
