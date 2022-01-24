<?php
  require_once $_SERVER['DOCUMENT_ROOT'] . '/battles/classes/log.php';
  $Log_Instance = new Log();

  try
  {
    $Get_Battle_Logs = $PDO->prepare("
      SELECT *
      FROM `battle_logs`
      WHERE `User_ID` = ?
      ORDER BY `ID` DESC
      LIMIT ?
    ");
    $Get_Battle_Logs->execute([
      $User_Info['ID'],
      $Log_Limit
    ]);
    $Get_Battle_Logs->setFetchMode(PDO::FETCH_ASSOC);
    $Battle_Logs = $Get_Battle_Logs->fetchAll();
  }
  catch ( PDOException $e )
  {
    HandleError($e);
  }

  if ( empty($Battle_Logs) )
  {
    echo '<h3>This user has no battle logs.</h3>';
    exit;
  }

  $Total_Logs = number_format(count($Battle_Logs));
  $Log_Limit = number_format($Total_Logs);

  $Battle_Image_Heatmap_URL = DOMAIN_ROOT . "/staff/ajax/logs/battle_image.php?User_ID_To_Show={$User_Info['ID']}";

  echo "
    <h3>Displaying {$Log_Limit} of {$Total_Logs} Logs</h3>
    <br />
    <button onclick=\"window.open('{$Battle_Image_Heatmap_URL}', 'Battle Log Heatmap', 'width=1600,height=900,scrollbars=no'); return false;\">
      Battle Image Heatmap
    </button>
    <br /><br />
    <hr class='faded' />
    <br />
  ";

  $Table_Text = '';
  $Last_User_Agent = null;
  foreach ( $Battle_Logs as $Log )
  {
    $User_Agent = $Log['Client_User_Agent'];

    if ( $User_Agent != $Last_User_Agent )
    {
      $Last_User_Agent = $User_Agent;

      $Table_Text .= "
          </tbody>
        </table>
        <h3>{$User_Agent}</h3>

        <table class='border-gradient' style='width: 700px;'>
          <thead>
            <tr>
              <th colspan='2' style='width: 150px;'>
                Logs
              </th>
            </tr>
          </thead>
          <tbody>
      ";
    }

    if ( empty($Log['Actions_Performed']) )
    {
      $Table_Text .= "
        <tr>
          <td colspan='1'>
            <div>
              <b>{$Log['Battle_Type']} &bull; {$Log['Foe_ID']}</b>
            </div>
            <div>
              " . date("n/j/Y h:i:s A", $Log['Time_Battle_Started']) . "
            </div>
          </td>

          <td colspan='1'>
            <h3 style='color: red;'>UNFINISHED BATTLE</h3>
          </td>
        </tr>
      ";
    }
    else
    {
      $Is_Battle_Trusted_Text = '';

      if ( !$Log['All_Inputs_Trusted'] )
        $Is_Battle_Trusted_Text .= '<div style="color: red;"><b>INPUTS UNTRUSTED</b></div>';
      if ( !$Log['Window_In_Focus'] )
        $Is_Battle_Trusted_Text .= '<div style="color: red;"><b>TAB NOT IN FOCUS</b></div>';
      if ( !$Log['All_Postcodes_Matched'] )
        $Is_Battle_Trusted_Text .= '<div style="color: red;"><b>ALL POSTCODES NOT MATCHED</b></div>';

      $Table_Text .= "
        <tr>
          <td colspan='1'>
            <div>
              <b>{$Log['Battle_Type']} &bull; {$Log['Foe_ID']}</b>
            </div>
            <div>
              " . number_format($Log['Battle_Duration']) . "ms &mdash; {$Log['Turn_Count']} Turns
            </div>
            <div>
              " . date('m/d/y&\nb\sp;&\nb\sp;h:i A', $Log['Time_Battle_Started']) . "
            </div>
            {$Is_Battle_Trusted_Text}
          </td>
          <td>
      ";

      $Actions = unpack('l*', $Log['Actions_Performed']);
      $Total_Actions = 0;

      foreach ( $Actions as $Action )
      {
        $Action_Data = $Log_Instance->Parse($Action);

        switch ( $Action_Data['Action'] )
        {
          case 'Unknown':
            $Table_Text .= ' <b>??</b> ';
            break;

          default:
            $Table_Text .= "
              {$Action_Data['Action']} ({$Action_Data['Coords']['x']},{$Action_Data['Coords']['x']})
            ";
            break;
        }
      }

      $Table_Text .= "</td></tr>";
    }
  }

  echo $Table_Text;
