<?php
    require_once $_SERVER['DOCUMENT_ROOT'] . '/backend/session/domain.php';
    require_once $_SERVER['DOCUMENT_ROOT'] . '/backend/session/session.php';
    require_once $_SERVER['DOCUMENT_ROOT'] . '/backend/auth/register_service.php';

    function SafeString(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
    }

    $Page_Metadata = [
        'Title' => 'Register',
        'Styles' => ['/themes/pages/register.css'],
        'Scripts' => [],
    ];

    $Is_Logged_In = isset($_SESSION['Absolute_Beta']['Logged_In_As']);

    if (empty($_SESSION['Absolute_Beta']['CSRF']['Register'])) {
        echo "<script>console.log('Generating new CSRF token for registration form.');</script>";
        $_SESSION['Absolute_Beta']['CSRF']['Register'] = bin2hex(random_bytes(32));
    }

    $Form_State = [
        'username' => '',
        'gender' => '',
        'email' => '',
        'starter' => '',
    ];

    $Field_Errors = [];
    $General_Error = '';
    $Success_Message = '';

    ob_start();

    if ( $Is_Logged_In )
    {
        echo "
            <div class='panel'>
                <div class='panel-header'>
                    Already Registered?
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

    if ( $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['Register']) )
    {
        $Submitted_Csrf = (string)($_POST['csrf_token'] ?? '');
        $Expected_Csrf = (string)($_SESSION['Absolute_Beta']['CSRF']['Register'] ?? '');

        echo "<script>console.log('Submitted CSRF: " . SafeString($Submitted_Csrf) . "');</script>";
        echo "<script>console.log('Expected CSRF: " . SafeString($Expected_Csrf) . "');</script>";

        $Form_State['username'] = trim((string)($_POST['username'] ?? ''));
        $Form_State['gender'] = trim((string)($_POST['gender'] ?? ''));
        $Form_State['email'] = trim((string)($_POST['email'] ?? ''));
        $Form_State['starter'] = trim((string)($_POST['starter'] ?? ''));

        if ($Submitted_Csrf === '' || !hash_equals($Expected_Csrf, $Submitted_Csrf)) {
            $General_Error = 'Your session expired. Please refresh and try again.';
        } else {
            $Register_Result = RegisterService::register($_POST);

            if (!empty($Register_Result['ok'])) {
                $_SESSION['Absolute_Beta']['CSRF']['Register'] = bin2hex(random_bytes(32));

                echo "
                    <div class='panel'>
                        <div class='panel-header'>
                            Registration Successful!
                        </div>

                        <div class='panel-content login-page'>
                            <section class='login-form'>
                                <h2>Welcome to Pok&eacute;mon Absolute!</h2>
                                <p>Your account has been created. You can now log in and start your adventure.</p>

                                <form action='/login.php'>
                                    <section>
                                        <button type='submit'>Go to Login</button>
                                    </section>
                                </form>
                            </section>
                        </div>
                    </div>
                ";

                $Content = ob_get_clean();
                require_once $_SERVER['DOCUMENT_ROOT'] . '/layout/layout.php';

                exit;
            }

            $Field_Errors = is_array($Register_Result['errors'] ?? null)
                ? $Register_Result['errors']
                : [];

            $General_Error = (string)($Register_Result['message'] ?? 'Registration failed. Please try again.');

            var_dump($Register_Result);
        }
    }
?>

<div class='panel'>
    <div class='panel-header'>
        Register
    </div>

    <div class='panel-content login-page'>
        <h2>Begin Your Journey!</h2>
        <p>Create an account and become a legend.</p>
        <p><?= $_SESSION['Absolute_Beta']['CSRF']['Register']; ?></p>

        <?php if ($General_Error !== ''): ?>
            <div class="form-error"><?= SafeString($General_Error); ?></div>
        <?php endif; ?>

        <section class='login-form'>
            <form method='POST' action='/register.php'>
                <input type="hidden" name="csrf_token" value="<?= SafeString($_SESSION['Absolute_Beta']['CSRF']['Register']); ?>" />

                <section class='two-column'>
                    <section>
                        <label for='username'>Username</label>
                        <input type='text' id='username' name='username' placeholder='Enter your username' required />
                        <?php if (!empty($Field_Errors['username'])): ?>
                            <div class="form-error"><?= SafeString($Field_Errors['username']); ?></div>
                        <?php endif; ?>
                    </section>

                     <section>
                        <label for='gender'>Gender</label>
                        <select id='gender' name='gender' required>
                            <option value='' disabled selected>Select your gender</option>
                            <option value='female'>Female</option>
                            <option value='male'>Male</option>
                            <option value='genderless'>Genderless</option>
                        </select>
                        <?php if (!empty($Field_Errors['gender'])): ?>
                            <div class="form-error"><?= SafeString($Field_Errors['gender']); ?></div>
                        <?php endif; ?>
                    </section>
                </section>

                <section class='two-column'>
                    <section>
                        <label for='password'>Password</label>
                        <input type='password' id='password' name='password' placeholder='Enter your password' required />
                    </section>

                    <section>
                        <label for='confirm_password'>Confirm Password</label>
                        <input type='password' id='confirm_password' name='confirm_password' placeholder='Confirm your password' required />
                    </section>
                </section>

                <section>
                    <label for='email' class='tooltip' data-tooltip='Used for account recovery.'>Email <font style='color: var(--color-light-grey);'>(?)</font></label>
                    <input type='email' id='email' name='email' placeholder='Enter your email' required />
                    <?php if (!empty($Field_Errors['email'])): ?>
                        <div class="form-error"><?= SafeString($Field_Errors['email']); ?></div>
                    <?php endif; ?>
                </section>

                <h2>Select A Starter</h2>
                <section class='starter-selection'>
                    <?php
                        $Possible_Starters = [
                            001 => 'Bulbasaur', 004 => 'Charmander', 007 => 'Squirtle',
                            152 => 'Chikorita', 155 => 'Cyndaquil', 158 => 'Totodile',
                            252 => 'Treecko', 255 => 'Torchic', 258 => 'Mudkip',
                            387 => 'Turtwig', 390 => 'Chimchar', 393 => 'Piplup',
                            495 => 'Snivy', 498 => 'Tepig', 501 => 'Oshawott',
                            650 => 'Chespin', 653 => 'Fennekin', 656 => 'Froakie',
                            722 => 'Rowlet', 725 => 'Litten', 728 => 'Popplio',
                            // 810 => 'Grookey', 813 => 'Scorbunny', 816 => 'Sobble'
                        ];

                        foreach ( $Possible_Starters as $Starter_ID => $Starter_Name )
                        {
                            echo "
                                <div>
                                    <img src='" . DOMAIN_SPRITES . "/Pokemon/Icons/Normal/" . str_pad($Starter_ID, 3, '0', STR_PAD_LEFT) . ".png' alt='{$Starter_Name}' /><br />
                                    <input type='radio' name='starter' value='{$Starter_ID}' required />
                                </div>
                            ";
                        }
                    ?>
                </section>

                <section>
                    <button type='submit' name='Register'>Create Account</button>
                </section>
            </form>

            <p>
                Already have an account? <a href='/login.php'>Sign in</a>
            </p>
        </section>
    </div>
</div>

<?php
    $Content = ob_get_clean();
    require_once $_SERVER['DOCUMENT_ROOT'] . '/layout/layout.php';
