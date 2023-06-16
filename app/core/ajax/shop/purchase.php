<?php
  require_once '../../required/session.php';
  require_once '../../classes/shop.php';

  if ( isset($_GET['Shop']) )
    $Shop_ID = Purify($_GET['Shop']);
  else
    $Shop_ID = 1;

  if ( isset($_GET['Object_ID']) )
    $Object_ID = Purify($_GET['Object_ID']);
  else
    $Object_ID = null;

  if ( isset($_GET['Object_Type']) )
    $Object_Type = Purify($_GET['Object_Type']);
  else
    $Object_Type = null;

  $Shop = $Shop_Class->FetchShopData($Shop_ID);

  if ( !$Shop || !$Object_ID || !$Object_Type )
  {
    echo "
      <div class='error'>
        An error occurred while processing your purchase.
      </div>
    ";
  }
  else if ( !in_array($Object_Type, ['Item', 'Pokemon']) )
  {
    echo "
      <div class='error'>
        An error occurred while processing your purchase.
      </div>
    ";
  }
  else
  {
    $Object_Data = $Shop_Class->FetchObjectData($Object_ID, $Object_Type);

    if ( !$Object_Data )
    {
      echo "
        <div class='error'>
          An error occurred while processing your purchase.
        </div>
      ";
    }
    else
    {
      $Purchase_Object = $Shop_Class->PurchaseObject($Object_ID, $Object_Type);

      if ( !$Purchase_Object )
      {
        echo "
          <div class='error'>
            An error occurred while processing your purchase.
          </div>
        ";
      }
      else
      {
        if ( $Purchase_Object['Shiny_Alert'] )
        {
          echo '
            <script type="text/javascript">
              setTimeout(() => {
                alert("The Pokemon that you purchased was Shiny!");
              }, 1);
            </script>
          ';
        }

        if ( $Purchase_Object['Ungendered_Alert'] )
        {
          echo '
            <script type="text/javascript">
              setTimeout(() => {
                alert("The Pokemon that you purchased was Ungendered!");
              }, 1);
            </script>
          ';
        }

        $Total_Stat = array_sum($Purchase_Object['Stats']);
        $Total_IV = array_sum($Purchase_Object['IVs']);

        echo "
          <table class='border-gradient' style='width: 475px;'>
            <tbody>
              <tr>
                <td colspan='2' rowspan='3'>
                  <img src='{$Purchase_Object['Sprite']}' />
                </td>
                <td></td>
                <td style='width: 39px;'>
                  <b>HP</b>
                </td>
                <td style='width: 39px;'>
                  <b>Att</b>
                </td>
                <td style='width: 39px;'>
                  <b>Def</b>
                </td>
                <td style='width: 39px;'>
                  <b>Sp.A</b>
                </td>
                <td style='width: 39px;'>
                  <b>Sp.D</b>
                </td>
                <td style='width: 39px;'>
                  <b>Spe</b>
                </td>
                <td style='width: 39px;'>
                  <b>Total</b>
                </td>
              </tr>
              <tr>
                <td><b>Base</b></td>
                <td>" . number_format($Purchase_Object['Stats'][0]) . "</td>
                <td>" . number_format($Purchase_Object['Stats'][1]) . "</td>
                <td>" . number_format($Purchase_Object['Stats'][2]) . "</td>
                <td>" . number_format($Purchase_Object['Stats'][3]) . "</td>
                <td>" . number_format($Purchase_Object['Stats'][4]) . "</td>
                <td>" . number_format($Purchase_Object['Stats'][5]) . "</td>
                <td>" . number_format($Total_Stat) . "</td>
              </tr>
              <tr>
                <td><b>IVs</b></td>
                <td>" . number_format($Purchase_Object['IVs'][0]) . "</td>
                <td>" . number_format($Purchase_Object['IVs'][1]) . "</td>
                <td>" . number_format($Purchase_Object['IVs'][2]) . "</td>
                <td>" . number_format($Purchase_Object['IVs'][3]) . "</td>
                <td>" . number_format($Purchase_Object['IVs'][4]) . "</td>
                <td>" . number_format($Purchase_Object['IVs'][5]) . "</td>
                <td>" . number_format($Total_IV) . "</td>
              </tr>
              <tr>
                <td colspan='10' style='padding: 5px;'>
                  <b>You have successfully purchased a(n) {$Purchase_Object['Display_Name']}.</b>
                </td>
              </tr>
            <tbody>
          </table>
        ";
      }
    }
  }
