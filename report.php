<?php
	require_once 'core/required/layout_top.php';
	require_once 'core/functions/report.php';
?>

<div class='panel content'>
	<div class='head'>Report A User</div>
	<div class='body' style='padding: 5px;'>
		<div class='description'>
			If a user has done something that you believe is against the rules, or that you believe <i>should</i> be against the rules, you may report them here.
      <br /><br />
      Please specify a valid reason, and if possible, include proof of the accusation(s).
		</div>

    <?php
      if ( !empty($_POST['Reported_User_ID']) && !empty($_POST['Reported_Reason']) )
      {
        $Reported_User_ID = Purify($_POST['Reported_User_ID']);
        $Reported_Reason = Purify($_POST['Reported_Reason']);

        AddNewReport($Reported_User_ID, $Reported_Reason);

        echo "
          <div class='success'>
            Your report has been sent to the Absolute staff team.
          </div>
        ";
      }
    ?>

    <form method='POST'>
      <table class='border-gradient' style='width: 500px;'>
        <tbody>
          <tr>
            <td colspan='1' style='width: 50%;'>
              <h3>User ID</h3>
            </td>
            <td colspan='1' style='padding: 5px;'>
              <input
                type='number'
                name='Reported_User_ID'
                value='<?= !empty($_GET['Reporting_User']) ? Purify($_GET['Reporting_User']) : ''; ?>'
                onkeydown='return event.keyCode !== 69'
              />
            </td>
          </tr>
        </tbody>

        <tbody>
          <tr>
            <td colspan='2'>
              <textarea cols='70' rows='20' name='Reported_Reason'></textarea>
            </td>
          </tr>
        </tbody>

        <tbody>
          <tr>
            <td colspan='2' style='padding: 5px;'>
              <input type='submit' name='Report_User' value='Report User' />
            </td>
          </tr>
        </tbody>
      </table>
    </form>
	</div>
</div>
