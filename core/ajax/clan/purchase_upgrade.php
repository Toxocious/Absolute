<?php
  require_once '../../required/session.php';

  $Clan_Data = $Clan_Class->FetchClanData($User_Data['Clan']);

  if ( !$Clan_Data )
  {
    echo "
      <tr>
        <td colspan='4'>
          <b style='color: #ff0000;'>
            You're not currently in a clan.
          </b>
        </td>
      </tr>
    ";

    return;
  }

  if ( isset($_POST['Upgrade_ID']) )
  {
    $Upgrade_ID = Purify($_POST['Upgrade_ID']);

    if ( $Upgrade_ID )
    {
      $Upgrade_Data = $Clan_Class->FetchUpgradeData($Upgrade_ID);
      
      if ( $Upgrade_Data )
      {
        $Clan_Upgrades = $Clan_Class->FetchUpgrades($Clan_Data['ID']);

        if ( $Clan_Upgrades )
        {
          $Upgrade_Cost = [
            'Money' => $Clan_Upgrades[$Upgrade_Data['ID'] - 1]['Cost']['Money']['Quantity'],
            'Abso_Coin' => $Clan_Upgrades[$Upgrade_Data['ID'] - 1]['Cost']['Abso_Coin']['Quantity'],
            'Clan_Points' => $Clan_Upgrades[$Upgrade_Data['ID'] - 1]['Cost']['Clan_Points']['Quantity'],
          ];

          if
          (
            $Clan_Data['Money_Raw'] >= $Upgrade_Cost['Money'] &&
            $Clan_Data['Abso_Coins_Raw'] >= $Upgrade_Cost['Abso_Coin'] &&
            $Clan_Data['Clan_Points_Raw'] >= $Upgrade_Cost['Clan_Points']
          )
          {
            $Purchase_Upgrade = $Clan_Class->PurchaseUpgrade($Clan_Data['ID'], $Upgrade_Data['ID']);

            if ( $Purchase_Upgrade )
            {
              $Update_Currencies = $Clan_Class->UpdateCurrencies(
                $Clan_Data['ID'],
                [
                  'Money' => $Clan_Data['Money_Raw'] - $Upgrade_Cost['Money'],
                  'Abso_Coins' => $Clan_Data['Abso_Coins_Raw'] - $Upgrade_Cost['Abso_Coin'],
                  'Clan_Points' => $Clan_Data['Clan_Points_Raw'] - $Upgrade_Cost['Clan_Points'],
                ]
              );

              echo "
                You have successfully upgraded your clan's {$Upgrade_Data['Name']} to +{$Purchase_Upgrade['Current_Level']}.
              ";
            }
            else
            {
              echo "
                An error occurred while attempting to purchase the clan upgrade.
              ";
            }
          }
          else
          {
            echo "
              Your clan does not have the sufficient currencies to purchase this upgrade.
            ";
          }
        }
        else
        {
          echo "
            An error occurred while attempting to fetch your clan's upgrades.
          ";
        }
      }
      else
      {
        echo "
          An error occurred while attempting to fetch the data for this upgrade.
        ";
      }
    }
  }
  else
  {
    echo "
      You may not purchase an upgrade that doesn't exist.
    ";
  }
