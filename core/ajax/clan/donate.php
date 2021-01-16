<?php
  require_once '../../required/layout_top.php';

  $Clan_Data = $Clan_Class->FetchClanData($User_Data['Clan']);
?>

<div class='panel content'>
  <div class='head'>Donate to <?= $Clan_Data['Name']; ?></div>
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

      if ( isset($_POST['currency']) && isset($_POST['quantity']) )
      {
        $Currency_Value = Purify($_POST['currency']);
        $Currency_Amount = Purify($_POST['quantity']);

        if ( !is_numeric($Currency_Amount) )
        {
          echo "
            <div class='error'>
              You attempted to donate an invalid quantity of currency.
              <br />
              Please try again.
            </div>
          ";
        }
        else
        {
          $Currency_Data = $Constants->Currency[$Currency_Value];

          if ( $User_Data[$Currency_Data['Value']] < $Currency_Amount )
          {
            echo "
              <div class='error'>
                You do not have enough {$Currency_Data['Name']} to donate to the clan.
              </div>
            ";
          }
          else if ( !$Currency_Data['Tradeable'] )
          {
            echo "
              <div class='error'>
                Sorry, but {$Currency_Data['Name']} is not a tradable or donatable currency.
              </div>
            ";
          }
          else
          {
            // (int $User_ID, int $Clan_ID, string $Currency, int $Quantity)
            $Donation = $Clan_Class->DonateCurrency($User_Data['id'], $Clan_Data['ID'], $Currency_Data['Value'], $Currency_Amount);

            if ( $Donation )
            {
              echo "
                <div class='success'>
                  You have successfully donated " . number_format($Currency_Amount) . " {$Currency_Data['Name']}.
                </div>
              ";
            }
            else
            {
              echo "
                <div class='error'>
                  An error occurred while attempting to donate to your guild.
                </div>
              ";
            }
          }
        }
      }
    ?>

    <div class='description'>
      Here, you may donate various currencies to your clan.
      <br />
      Your clan benefits from having various currencies by being able to upgrade clan perks, which grant bonuses across the site.
    </div>

    <form method='POST'>
      <select name='currency'>
        <?php
          foreach ( $Constants->Currency as $Currency )
          {
            if ( $Currency['Tradeable'] )
            {
              echo "
                <option value='{$Currency['Value']}'>{$Currency['Name']}</option>
              ";
            }
          }
        ?>
      </select>

      <input type='text' name='quantity' placeholder='0' />
      <br />
      <input type='submit' value='Donate' style='margin-top: 5px;'/>
    </form>

    <br />

    <div class='description'>
      Below you can see all previous donations that your clan has received.
    </div>

    <table class='border-gradient' style='width: 600px;'>
      <thead>
        <tr>
          <th colspan='4'>
            Donations
          </th>
        </tr>
      </thead>
      <tbody>
        <tr>
          <td>
            <b>Donator</b>
          </td>
          <td>
            <b>Currency</b>
          </td>
          <td>
            <b>Quantity</b>
          </td>
          <td>
            <b>Timestamp</b>
          </td>
        </tr>
      </tbody>
      <tbody>
        <?php
          try
          {
            $Fetch_Donations = $PDO->prepare("SELECT * FROM `clan_donations` WHERE `Clan_ID` = ?");
            $Fetch_Donations->execute([ $Clan_Data['ID'] ]);
            $Fetch_Donations->setFetchMode(PDO::FETCH_ASSOC);
            $Donations = $Fetch_Donations->fetchAll();
          }
          catch (PDOException $e)
          {
            HandleError($e);
          }

          if ( count($Donations) === 0 )
          {
            echo "
              <tr>
                <td colspan='4' style='padding: 5px;'>
                  This clan has not yet received any donations.
                </td>
              </tr>
            ";
          }
          else
          {
            foreach ( $Donations as $Donation )
            {
              $Currency_Data = $Constants->Currency[$Donation['Currency']];
              $Donator_Username = $User_Class->DisplayUserName($Donation['Donator_ID']);
  
              echo "
                <tr>
                  <td colspan='1' style='width: 150px;'>
                    {$Donator_Username}
                  </td>
                  <td colspan='1' style='width: 150px;'>
                    {$Currency_Data['Name']}
                  </td>
                  <td colspan='1' style='width: 110px;'>
                    " . number_format($Donation['Quantity']) . "
                  </td>
                  <td colspan='1' style='width: 190px;'>
                    " . date("M j, Y (g:i A)", $Donation['Timestamp']) . "
                  </td>
                </tr>
              ";
            }
          }
        ?>
      </tbody>
    </table>
  </div>
</div>

<?php
  require_once '../../required/layout_bottom.php';
