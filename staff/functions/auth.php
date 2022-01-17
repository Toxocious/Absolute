<?php
  function AuthorizeUser()
  {
    global $User_Data, $Current_Page;

  	if ( $User_Data['Power'] < $Current_Page['Power'] )
      return false;

    return true;
  }
