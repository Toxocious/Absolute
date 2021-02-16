<?php
  require_once '../../required/session.php';

  $Clan_Data = $Clan_Class->FetchClanData($User_Data['Clan']);

  if ( !$Clan_Data )
    return false;

  $Output = [];

  $Clan_Upgrades = $Clan_Class->FetchUpgrades($Clan_Data['ID']);
  foreach ( $Clan_Upgrades as $Index => $Upgrade )
  {
    $Upgrade_Data = $Clan_Class->FetchUpgradeData($Upgrade['ID']);

    $Disable_Input = false;
    $Upgrade_Cost = '';
    
    foreach ( $Clan_Upgrades[$Upgrade_Data['ID'] - 1]['Cost'] as $Index => $Cost )
    {
      if ( $Cost['Quantity'] > 0 )
      {
        if ( $Cost['Quantity'] > $Clan_Data[$Index . '_Raw'])
          $Disable_Input = true;
  
        $Upgrade_Cost .= number_format($Cost['Quantity']) . " {$Cost['Name']}<br />";
      }
    }

    $Output[] = [
      'Upgrade_ID' => $Upgrade_Data['ID'],
      'Bonus' => "+{$Clan_Upgrades[$Upgrade_Data['ID'] - 1]['Current_Level']}{$Clan_Upgrades[$Upgrade_Data['ID'] - 1]['Suffix']}",
      'Level' => $Clan_Upgrades[$Upgrade_Data['ID'] - 1]['Current_Level'],
      'Cost' => $Upgrade_Cost,
      'Disabled' => $Disable_Input,
    ];
  }

  header('Content-Type: application/json');
  echo json_encode($Output);
