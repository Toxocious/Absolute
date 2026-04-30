<?php
    $Page_Metadata = [
        'Title' => 'Register',
        'Styles' => ['/themes/pages/register.css'],
        'Scripts' => [],
    ];

    ob_start();
?>

<div class='panel'>
    <div class='panel-header'>
        Register
    </div>

    <div class='panel-content login-page'>
        <h2>Begin Your Journey!</h2>
        <p>Create an account and become a legend.</p>

        <section class='login-form'>
            <form method='POST' action='/register.php'>
                <section class='two-column'>
                    <section>
                        <label for='username'>Username</label>
                        <input type='text' id='username' name='username' placeholder='Enter your username' required />
                    </section>

                     <section>
                        <label for='gender'>Gender</label>
                        <select id='gender' name='gender' required>
                            <option value='' disabled selected>Select your gender</option>
                            <option value='female'>Female</option>
                            <option value='male'>Male</option>
                            <option value='genderless'>Genderless</option>
                        </select>
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
                </section>

                <section>
                    <button type='submit'>Create Account</button>
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
