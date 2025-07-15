<?php
	require_once $_SERVER['DOCUMENT_ROOT'] . '/core/required/session.php';
?>

<!DOCTYPE html>
<html>
	<head>
		<title>Staff Panel :: The Pok&eacute;mon Absolute</title>
		<link href='<?= DOMAIN_SPRITES; ?>/Pokemon/Icons/Normal/359-mega.png' rel='shortcut icon'>

		<link type='text/css' rel='stylesheet' href='/themes/css/styles/<?= (isset($User_Data['Theme']) ? $User_Data['Theme'] : 'absol'); ?>.css' />
		<link type='text/css' rel='stylesheet' href='/themes/css/root.css' />
		<link type='text/css' rel='stylesheet' href='/themes/css/structure.css' />
		<link type='text/css' rel='stylesheet' href='/themes/css/theme.css' />
	</head>

	<body style='align-content: flex-start; display: flex; flex-wrap: wrap; gap: 5px; justify-content: center; padding: 5px;'>
    <?php
      if ( !isset($_GET['Trade_ID']) )
      {
        echo "
          <div class='error'>
            The trade that you are trying to view doesn't exist.
          </div>
        ";

        exit;
      }

      $Trade_ID = Purify($_GET['Trade_ID']);

      try
      {
        $Get_Trade_Data = $PDO->prepare("SELECT * FROM `trades` WHERE `ID` = ?");
        $Get_Trade_Data->execute([ $Trade_ID ]);
        $Get_Trade_Data->setFetchMode(PDO::FETCH_ASSOC);
        $Trade_Data = $Get_Trade_Data->fetch();
      }
      catch( PDOException $e )
      {
        HandleError($e);
      }

      if ( count($Trade_Data) === 0 )
      {
        echo "
          <div class='error'>
            You may not view trades that you did not take part in, or that do not exist.
          </div>
        ";

        exit;
      }

      $Sender = $User_Class->FetchUserData($Trade_Data['Sender']);
      $Recipient = $User_Class->FetchUserData($Trade_Data['Recipient']);

      switch ( $Trade_Data['Status'] )
      {
        case 'Accepted':
          $Color = "#00ff00";
          break;
        case 'Declined':
          $Color = "#ff0000";
          break;
        case 'Deleted':
          $Color = "#999";
          break;
      }

      $Trade_Status = '';
      if ( $Trade_Data['Status'] != 'Pending' )
      {
        $Trade_Status = "
          <br />
          This trade was <b style='color: {$Color}'>" . strtolower($Trade_Data['Status']) . "</b>.
        ";
      }
    ?>

    <div class='border-gradient' style='height: 50px; width: 80%;'>
      <div>
        Viewing the trade contents of Trade #<?= number_format($Trade_ID); ?>.
        <?= $Trade_Status; ?>
      </div>
    </div>

    <div style='display: flex; width: 100%;'>
      <table class='border-gradient' style='margin: 5px; width: 50%;'>
        <thead>
          <tr>
            <th colspan='3'>
              <b><?= $Sender['Username']; ?>'s Offer</b>
            </th>
          </tr>
        </thead>
        <tbody>
          <?php
            try
            {
              $Sender_Query = $PDO->prepare("SELECT `Sender`, `Sender_Pokemon`, `Sender_Currency`, `Sender_Items` FROM `trades` WHERE `ID` = ?");
              $Sender_Query->execute([ $Trade_ID ]);
              $Sender_Query->setFetchMode(PDO::FETCH_ASSOC);
              $Sender_Content = $Sender_Query->fetch();
            }
            catch( PDOException $e )
            {
              HandleError($e);
            }

            if
            (
              empty($Sender_Content['Sender_Pokemon']) &&
              empty($Sender_Content['Sender_Items']) &&
              empty($Sender_Content['Sender_Currency'])
            )
            {
              echo "
                <tr>
                  <td colspan='3' style='padding: 12px;'>
                    <b>This user has nothing included in their side of the trade.</b>
                  </td>
                </tr>
              ";
            }
            else
            {
              if ( !empty($Sender_Content['Sender_Pokemon']) )
              {
                $Sender_Pokemon = explode(',', $Sender_Content['Sender_Pokemon']);
                foreach ( $Sender_Pokemon as $Key => $Pokemon )
                {
                  $Pokemon_Data = GetPokemonData($Pokemon);

                  if ( !$Pokemon_Data )
                  {
                    echo '<tr><td colspan="3">This Pok&eacute;mon no longer exists.</td></tr>';
                  }
                  else
                  {
                    echo "
                      <tr>
                        <td colspan='1' style='width: 76px;'>
                          <img src='{$Pokemon_Data['Icon']}' />
                          " . ( $Pokemon_Data['Item'] ? "<img src='{$Pokemon_Data['Item_Icon']}' />" : '' ) . "
                        </td>
                        <td colspan='1' style='width: 34px;'>
                          <img src='{$Pokemon_Data['Gender_Icon']}' style='height: 20px; width: 20px;' />
                        </td>
                        <td colspan='1'>
                          {$Pokemon_Data['Display_Name']} (Level: " . number_format($Pokemon_Data['Level']) . ")
                          " . ($Pokemon_Data['Nickname'] ? "<br /><i>{$Pokemon_Data['Nickname']}</i>" : '')  . "
                        </td>
                      </tr>
                    ";
                  }
                }
              }

              if ( !empty($Sender_Content['Sender_Items']) )
              {
                $Sender_Items = explode(',', $Sender_Content['Sender_Items']);
                foreach ( $Sender_Items as $Key => $Item )
                {
                  $Item_Params = explode('-', $Item);
                  $Item_Data = $Item_Class->FetchOwnedItem($Sender_Content['Sender'], $Item_Params[1]);

                  echo "
                    <tr>
                      <td colspan='2' style='width: 76px;'>
                        <img src='{$Item_Data['Icon']}' />
                      </td>
                      <td colspan='1'>
                        {$Item_Data['Name']}<br />
                        x" . number_format($Item_Params[2]) . "
                      </td>
                    </tr>
                  ";
                }
              }

              if ( !empty($Sender_Content['Sender_Currency']) )
              {
                $Sender_Currency = explode(',', $Sender_Content['Sender_Currency']);
                foreach ( $Sender_Currency as $Key => $Currency )
                {
                  $Currency_Info = explode('-', $Currency);
                  $Currency_Data = $Constants->Currency[$Currency_Info[0]];

                  echo "
                    <tr>
                      <td colspan='2' style='width: 76px;'>
                        <img src='{$Currency_Data['Icon']}' />
                      </td>
                      <td colspan='1'>
                        {$Currency_Data['Name']}<br />
                        " . number_format($Currency_Info[1]) . "
                      </td>
                    </tr>
                  ";
                }
              }
            }
          ?>
        </tbody>
      </table>

      <table class='border-gradient' style='margin: 5px; width: 50%;'>
        <thead>
          <tr>
            <th colspan='3'>
              <b><?= $Recipient['Username']; ?>'s Offer</b>
            </th>
          </tr>
        </thead>
        <tbody>
        <?php
            try
            {
              $Recipient_Query = $PDO->prepare("SELECT `Recipient`, `Recipient_Pokemon`, `Recipient_Currency`, `Recipient_Items` FROM `trades` WHERE `ID` = ?");
              $Recipient_Query->execute([ $Trade_ID ]);
              $Recipient_Query->setFetchMode(PDO::FETCH_ASSOC);
              $Recipient_Content = $Recipient_Query->fetch();
            }
            catch( PDOException $e )
            {
              HandleError($e);
            }

            if
            (
              empty($Recipient_Content['Recipient_Pokemon']) &&
              empty($Recipient_Content['Recipient_Items']) &&
              empty($Recipient_Content['Recipient_Currency'])
            )
            {
              echo "
                <tr>
                  <td colspan='3' style='padding: 12px;'>
                    <b>This user has nothing included in their side of the trade.</b>
                  </td>
                </tr>
              ";
            }
            else
            {
              if ( !empty($Recipient_Content['Recipient_Pokemon']) )
              {
                $Recipient_Pokemon = explode(',', $Recipient_Content['Recipient_Pokemon']);
                foreach ( $Recipient_Pokemon as $Key => $Pokemon )
                {
                  $Pokemon_Data = GetPokemonData($Pokemon);

                  if ( !$Pokemon_Data )
                  {
                    echo '<tr><td colspan="3" style="padding: 5px;">This Pok&eacute;mon no longer exists.</td></tr>';
                  }
                  else
                  {
                    echo "
                      <tr>
                        <td colspan='1' style='width: 76px;'>
                          <img src='{$Pokemon_Data['Icon']}' />
                          " . ( $Pokemon_Data['Item'] ? "<img src='{$Pokemon_Data['Item_Icon']}' />" : '' ) . "
                        </td>
                        <td colspan='1' style='width: 34px;'>
                          <img src='{$Pokemon_Data['Gender_Icon']}' style='height: 20px; width: 20px;' />
                        </td>
                        <td colspan='1'>
                          {$Pokemon_Data['Display_Name']} (Level: " . number_format($Pokemon_Data['Level']) . ")
                          " . ($Pokemon_Data['Nickname'] ? "<br /><i>{$Pokemon_Data['Nickname']}</i>" : '')  . "
                        </td>
                      </tr>
                    ";
                  }
                }
              }

              if ( !empty($Recipient_Content['Recipient_Items']) )
              {
                $Recipient_Items = explode(',', $Recipient_Content['Recipient_Items']);
                foreach ( $Recipient_Items as $Key => $Item )
                {
                  // row-id-quantity-owner
                  $Item_Params = explode('-', $Item);
                  $Item_Data = $Item_Class->FetchOwnedItem($Recipient_Content['Recipient'], $Item_Params[1]);

                  echo "
                    <tr>
                      <td colspan='2' style='width: 76px;'>
                        <img src='{$Item_Data['Icon']}' />
                      </td>
                      <td colspan='1'>
                        {$Item_Data['Name']}<br />
                        x" . number_format($Item_Params[2]) . "
                      </td>
                    </tr>
                  ";
                }
              }

              if ( !empty($Recipient_Content['Recipient_Currency']) )
              {
                $Recipient_Currency = explode(',', $Recipient_Content['Recipient_Currency']);
                foreach ( $Recipient_Currency as $Key => $Currency )
                {
                  $Currency_Info = explode('-', $Currency);
                  $Currency_Data = $Constants->Currency[$Currency_Info[0]];

                  echo "
                    <tr>
                      <td colspan='2' style='width: 76px;'>
                        <img src='{$Currency_Data['Icon']}' />
                      </td>
                      <td colspan='1'>
                        {$Currency_Data['Name']}<br />
                        " . number_format($Currency_Info[1]) . "
                      </td>
                    </tr>
                  ";
                }
              }
            }
          ?>
        </tbody>
      </table>
    </div>
  </body>
</html>
