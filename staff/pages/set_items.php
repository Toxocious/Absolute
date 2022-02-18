<?php
  require_once $_SERVER['DOCUMENT_ROOT'] . '/core/required/session.php';
  require_once $_SERVER['DOCUMENT_ROOT'] . '/staff/functions/auth.php';

  if ( !AuthorizeUser() )
  {
    echo "
      <div style='padding: 5px;'>
        You aren't authorized to be here.
      </div>
    ";

    exit;
  }
?>

<div style='display: flex; flex-direction: column; flex-wrap: wrap; gap: 10px; justify-content: center;'>
  <div style='flex-basis: 100%; width: 100%;'>
    <h3>Set Items</h3>
  </div>

  <div class='description'>
    All currently obtainable items are found here, and more may be added if desired.
  </div>

  <table class='border-gradient' style='width: 200px;'>
    <thead>
      <tr>
        <th colspan='2'>
          Item Locations
        </th>
      </tr>
    </thead>
    <tbody>
      <tr>
        <td colspan='2' style='width: 100%;'>
          <h3>
            <a href='javascript:void(0);' onclick='ShowObtainableItemsByTable("shop_items");'>
              Shops
            </a>
          </h3>
        </td>
      </tr>
    </tbody>
  </table>

  <div id='Set_Items_AJAX'></div>
  <div style='display: flex; flex-wrap: wrap; gap: 10px;' id='Set_Items_Table'></div>
</div>
