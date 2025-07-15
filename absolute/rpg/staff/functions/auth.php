<?php
    require_once $_SERVER['DOCUMENT_ROOT'] . '/core/required/ajax_header.php';


    /**
     * Check the user's power level in comparison to the page's power level.
     * Allow access to the page if they user's power level is not below the required power level.
     */
    function AuthorizeUser()
    {
        global $User_Data;

        if ( $User_Data['Rank'] == 'Member' )
            return false;

        return true;
    }
