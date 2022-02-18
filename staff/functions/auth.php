<?php
  require_once $_SERVER['DOCUMENT_ROOT'] . '/staff/functions/permissions.php';

  /**
   * Check the user's power level in comparison to the page's power level.
   * Allow access to the page if they user's power level is not below the required power level.
   */
  function AuthorizeUser()
  {
    if ( !CheckUserPermission() )
      return false;

    return true;
  }
