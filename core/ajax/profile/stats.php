<?php
  require '../../required/db.php';
  require '../../functions/global_functions.php';

  if ( isset($_GET['id']) )
  {
		echo "
      <div class='panel'>
        <div class='panel-heading'>Statistics</div>
        <div class='panel-body'>

        <div style='float: left; padding: 5px; width: 50%;'>
          <b style='font-size: 18px;'>Trainer Level</b>
          <div class='progress green'>
            <span class='progress-left'>
              <span class='progress-bar'></span>
            </span>
            <span class='progress-right'>
              <span class='progress-bar'></span>
            </span>
            <div class='progress-value'>
              <div style='font-size: 16px; font-weight: bold; margin-top: -7px;'>10,000</div>
              <div style='font-size: 12px; margin-top: -85px;'>1,000,000,000,000</div>
            </div>
          </div>
        </div>

        <div style='float: left; padding: 5px; width: 50%;'>
          <b style='font-size: 18px;'>Map Level</b>
          <div class='progress green'>
            <span class='progress-left'>
              <span class='progress-bar'></span>
            </span>
            <span class='progress-right'>
              <span class='progress-bar'></span>
            </span>
            <div class='progress-value'>
              <div style='font-size: 16px; font-weight: bold; margin-top: -7px;'>1,000</div>
              <div style='font-size: 12px; margin-top: -85px;'>1,000,000,000</div>
            </div>
          </div>
        </div>

        </div>
      </div>
    ";
	}

	exit();
?>