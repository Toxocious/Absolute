<?php
  require_once '../../required/layout_top.php';

  $Clan_Data = $Clan_Class->FetchClanData($User_Data['Clan']);
?>

<div class='panel content'>
  <div class='head'>Clan Upgrades</div>
  <div class='body' style='padding: 5px;'>
    <?php
      if ( !$Clan_Data )
      {
        echo "
          <div class='error' style='margin-bottom: 0px;'>
            To access this page, you must currently be in a clan.
          </div>
        ";
    
        return;
      }

      if ( !$User_Data['Clan_Rank'] == 'Member' )
      {
        echo "
          <div class='error' style='margin-bottom: 0px;'>
            To access this page, you must be at least a Clan Moderator.
          </div>
        ";
    
        return;
      }
    ?>

    <div class='description'>
      Here, you may upgrade various aspects of your clan that will help to further your progression as a clan.
    </div>

    <table class='border-gradient' style='flex-basis: 600px; width: 600px;'>
      <?php
        $Upgrades_List = $Clan_Class->FetchUpgrades($Clan_Data['ID']);

        if ( !$Upgrades_List )
        {
          echo "
            <div class='error'>
              An error occurred while attempting to fetch the available upgrades for your clan.
            </div>
          ";
        }
        else
        {
          foreach ( $Upgrades_List as $Upgrade )
          {
            $Upgrade_Cost_Text = '';

            foreach ( $Upgrade['Cost'] as $Cost )
            {
              if ( $Cost['Quantity'] > 0 )
                $Upgrade_Cost_Text .= number_format($Cost['Quantity']) . " " . $Cost['Name'] . "<br />";
            }

            echo "
              <thead>
                <tr>
                  <th colspan='4'>
                    {$Upgrade['Name']}
                  </th>
                </tr>
              </thead>
              <tbody>
                <tr>
                  <td colspan='4' style='padding: 5px;'>
                    {$Upgrade['Description']}
                  </td>
                </tr>
                <tr>
                  <td colspan='1' rowspan='2' style='padding: 5px; width: 150px;'>
                    <b>Upgrade Cost</b>
                  </td>
                  <td colspan='1' rowspan='2' style='padding: 5px; width: 150px;'>
                    {$Upgrade_Cost_Text}
                  </td>
                  <td colspan='1' style='padding: 5px; width: 150px;'>
                    <b>Current Level</b>
                  </td>
                  <td colspan='1' style='padding: 5px; width: 150px;'>
                    {$Upgrade['Current_Level']}
                  </td>
                </tr>
                <tr>
                  <td colspan='1'>
                    <b>Current Bonus</b>
                  </td>
                  <td colspan='1'>
                    +{$Upgrade['Current_Level']}{$Upgrade['Suffix']}
                  </td>
                </tr>
                <tr>
                  <td colspan='4'>
                    <button onclick='PurchaseUpgrade({$Upgrade['ID']});'>
                      Purchase Upgrade
                    </button>
                  </td>
                </tr>
              </tbody>
            ";
          }
        }
      ?>
    </table>
  </div>
</div>

<?php
  require_once '../../required/layout_bottom.php';
