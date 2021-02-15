<?php
  require_once '../core/required/layout_top.php';

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

    <div class='warning' id='AJAXRequest'>
      Awaiting the purchase of an upgrade.
    </div>

    <table class='border-gradient' style='flex-basis: 400px; margin-bottom: 5px; width: 400px;'>
      <thead>
        <tr>
          <th colspan='1' style='width: calc(100% / 3);'>
            <b>Clan Points</b>
          </th>
          <th colspan='1' style='width: calc(100% / 3);'>
            <b>Money</b>
          </th>
          <th colspan='1' style='width: calc(100% / 3);'>
            <b>Absolute Coins</b>
          </th>
        </tr>
      </thead>
      <tbody>
        <tr>
          <td colspan='1' id='Clan_Points'>
            <?= $Clan_Data['Clan_Points']; ?>
          </td>
          <td colspan='1' id='Money'>
            <?= $Clan_Data['Money']; ?>
          </td>
          <td colspan='1' id='Abso_Coins'>
            <?= $Clan_Data['Abso_Coins']; ?>
          </td>
        </tr>
      </tbody>
    </table>

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

<script type='text/javascript'>
  const PurchaseUpgrade = (Upgrade_ID) =>
  {
    const Upgrade_Data = new FormData();
    Upgrade_Data.append('Upgrade_ID', Upgrade_ID);

    document.querySelector('#AJAXRequest').innerHTML = 'Loading';

    return new Promise((resolve, reject) =>
    {
      const req = new XMLHttpRequest();
      req.open('POST', '<?= DOMAIN_ROOT; ?>/core/ajax/clan/purchase_upgrade.php');
      req.send(Upgrade_Data);
      req.onerror = (error) => reject(Error(`Network Error: ${error}`));
      req.onload = () =>
      {
        if ( req.status === 200 )
        {
          FetchCurrencies();
          document.querySelector('#AJAXRequest').innerHTML = req.responseText;
          resolve(req.response);
        }
        else
        {
          document.querySelector('#AJAXRequest').innerHTML = req.statusText;
          reject(Error(req.statusText))
        }
      };
    });
  }

  const FetchCurrencies = () =>
  {
    return new Promise((resolve, reject) =>
    {
      const req = new XMLHttpRequest();
      req.open('GET', '<?= DOMAIN_ROOT; ?>/core/ajax/clan/fetch_currencies.php');
      req.send();
      req.onerror = (error) => reject(Error(`Network Error: ${error}`));
      req.onload = () =>
      {
        if ( req.status === 200 )
        {
          let Currencies = JSON.parse(req.responseText);
          Object.keys(Currencies).map((Currency) =>
          {
            document.querySelector(`#${Currency}`).innerHTML = Currencies[Currency]
          });
          resolve(req.response);
        }
        else
        {
          reject(Error(req.statusText))
        }
      };
    });
  }
</script>

<?php
  require_once '../core/required/layout_bottom.php';
