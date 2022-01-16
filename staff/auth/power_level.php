<?php
	if ( $User_Data['Power'] < $Current_Page['Power'] )
	{
    echo "
      <div class='panel content'>
        <div class='head'>Staff Panel</div>
        <div class='body' style='padding: 5px'>
          You aren't authorized to be here.
        </div>
      </div>
    ";

		exit;
	}
