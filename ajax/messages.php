<?php
  require '../php/session.php';

  if ( $_SERVER['REQUEST_METHOD'] === 'GET' )
  {
    echo "
      <div class='msidebar'>
        <div>
          <input type='text' placeholder='Username/ID' style='text-align: center; width: 180px;' /><br />
          <input type='button' value='Start Message' style='margin-bottom: 0px; margin-top: -4px; width: 180px;' />
        </div>

        <div>
          Started Messages
        </div>
      </div>

      <div class='messageArea' style='padding-top: 45px;'>
        Please select a message to view.
      </div>
    ";
  }