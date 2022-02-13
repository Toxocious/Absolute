<?php
  /**
   * Check the user's power level in comparison to the page's power level.
   * Allow access to the page if they user's power level is not below the required power level.
   */
  function AuthorizeUser()
  {
    global $User_Data, $Current_Page;

  	if ( $User_Data['Power'] < $Current_Page['Power'] )
      return false;

    return true;
  }
