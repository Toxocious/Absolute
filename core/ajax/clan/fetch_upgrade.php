<?php
  require_once '../../required/session.php';

  $Clan_Data = $Clan_Class->FetchClanData($User_Data['Clan']);

  if ( !$Clan_Data )
    return false;

  if ( !isset($_GET['Upgrade_ID']) )
    return false;

  $Upgrade_ID = Purify($_GET['Upgrade_ID']);
  
  $Clan_Upgrades = $Clan_Class->FetchUpgrades($Clan_Data['ID']);
  $Upgrade_Data = $Clan_Class->FetchUpgradeData($Upgrade_ID);

  $Upgrade_Cost = '';
  if ( $Clan_Upgrades[$Upgrade_Data['ID'] - 1]['Cost']['Clan_Points']['Quantity'] > 0 )
    $Upgrade_Cost .= number_format($Clan_Upgrades[$Upgrade_Data['ID'] - 1]['Cost']['Clan_Points']['Quantity']) . ' Clan Points<br />';
  if ( $Clan_Upgrades[$Upgrade_Data['ID'] - 1]['Cost']['Money']['Quantity'] > 0 )
    $Upgrade_Cost .= number_format($Clan_Upgrades[$Upgrade_Data['ID'] - 1]['Cost']['Money']['Quantity']) . ' Money<br />';
  if ( $Clan_Upgrades[$Upgrade_Data['ID'] - 1]['Cost']['Abso_Coin']['Quantity'] > 0 )
    $Upgrade_Cost .= number_format($Clan_Upgrades[$Upgrade_Data['ID'] - 1]['Cost']['Abso_Coin']['Quantity']) . ' Absolute Coins<br />';

  $Output = [
    'Upgrade_ID' => $Upgrade_ID,
    'Bonus' => "+{$Clan_Upgrades[$Upgrade_Data['ID'] - 1]['Current_Level']}{$Clan_Upgrades[$Upgrade_Data['ID'] - 1]['Suffix']}",
    'Level' => $Clan_Upgrades[$Upgrade_Data['ID'] - 1]['Current_Level'],
    'Cost' => $Upgrade_Cost,
  ];

  header('Content-Type: application/json');
  echo json_encode($Output);
