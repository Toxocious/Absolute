<?php
  /**
   * Show a table of pages that can have maintenance mode toggled for them.
   */
  function ShowMaintenanceTable()
  {
    global $PDO;

    try
    {
      $Get_Player_Maintenance_Pages = $PDO->prepare("
        SELECT `ID`, `Maintenance`, `Name`
        FROM `pages`
        WHERE `Staff_Only` = 'No'
      ");
      $Get_Player_Maintenance_Pages->execute([ ]);
      $Get_Player_Maintenance_Pages->setFetchMode(PDO::FETCH_ASSOC);
      $Player_Maintenance_Pages = $Get_Player_Maintenance_Pages->fetchAll();
    }
    catch ( PDOException $e )
    {
      HandleError($e);
    }

    if ( empty($Get_Player_Maintenance_Pages) )
      return 'There are no pages that can be displayed.';

    $Maintenance_Table = '
      <table class="border-gradient" style="width: 700px;">
        <tbody>
          <tr>
    ';

    $Total_Slots = count($Player_Maintenance_Pages);
    $Total_Remaining_Slots = Abs(($Total_Slots % 4) - 4);

    foreach ( $Player_Maintenance_Pages as $Index => $Page )
    {
      switch ( $Page['Maintenance'] )
      {
        case 'no':
          $Maintenance_Status = '<b style="color: green;">Online</b>';
          break;

        case 'yes':
          $Maintenance_Status = '<b style="color: red;">Offline</b>';
          break;
      }

      $Maintenance_Table .= "
        <td colspan='1' style='width: calc(100% / 4);' onclick='TogglePageMaintenance({$Page['ID']});'>
          <h3>{$Page['Name']}</h3>
          {$Maintenance_Status}
        </td>
      ";

      if ( $Index % 4 == 3 )
        $Maintenance_Table .= '</tr>';
    }

    if ( $Total_Slots % 4 != 0 && $Index == $Total_Slots - 1 )
        for ( $Remaining_Slots = $Total_Remaining_Slots; $Remaining_Slots > 0; $Remaining_Slots-- )
          $Maintenance_Table .= '<td></td>';

    $Maintenance_Table .= '
        </tbody>
      </table>
    ';

    return $Maintenance_Table;
  }

  /**
   * Toggle the specified page into or out of maintenance.
   *
   * @param $Page_ID
   */
  function TogglePageMaintenance
  (
    $Page_ID
  )
  {
    global $PDO;

    try
    {
      $PDO->beginTransaction();

      $Toggle_Page_Maintenance = $PDO->prepare("
        UPDATE `pages`
        SET `Maintenance` = IF(`Maintenance` = 'yes', 'no', 'yes')
        WHERE `ID` = ?
        LIMIT 1
      ");
      $Toggle_Page_Maintenance->execute([
        $Page_ID
      ]);

      $PDO->commit();
    }
    catch ( PDOException $e )
    {
      $PDO->rollBack();

      HandleError($e);
    }

    return [
      'Success' => true,
      'Message' => 'You have toggled maintenance mode for this page.',
      'Maintenance_Table' => ShowMaintenanceTable()
    ];
  }
