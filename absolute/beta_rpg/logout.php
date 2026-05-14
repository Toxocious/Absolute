<?php
    if ( isset($_SESSION['Absolute_Beta']['Logged_In_As']) )
    {
        unset($_SESSION['Absolute_Beta']['Logged_In_As']);
        unset($_SESSION['Absolute_Beta']['playtime']);
        unset($_SESSION['Absolute_Beta']['last_activity']);

        header("Location: /");
        exit();
    }
    else
    {
        echo "You are not currently logged in.";
    }
