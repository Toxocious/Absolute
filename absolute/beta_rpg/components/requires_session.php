<?php
    /**
     * Component: Requires Session
     * Description: Displays a message prompting the user to log in when trying to access a page that requires an active user session.
     *
     * Expected Variables:
     * - None
     *
     * Notes:
     * - This component is used in the layout to handle cases where a page requires a user session but none is present.
     * - It provides a consistent message and links for users to log in or register.
     */
?>

<div class='panel'>
    <div class='panel-header'>
        Login Required
    </div>

    <div class='panel-content text-center'>
        <div>You must be logged in to view this page.</div>
        <br />
        <div>Please <a href='/login.php'>log in</a> or <a href='/register.php'>register</a> for an account.</div>
    </div>
</div>
