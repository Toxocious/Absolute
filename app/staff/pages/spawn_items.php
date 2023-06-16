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

  require_once $_SERVER['DOCUMENT_ROOT'] . '/staff/functions/spawn_items.php';
?>

<div style='display: flex; flex-direction: column; flex-wrap: wrap; gap: 10px; justify-content: center;'>
  <div style='flex-basis: 100%; width: 100%;'>
    <h3>Item Spawner</h3>
  </div>

  <div id='Spawn_Item_AJAX'></div>

  <table class='border-gradient' style='width: 400px;'>
    <tbody>
      <tr>
        <td colspan='2' style='padding: 5px;'>
          <?php
            echo ShowSpawnableItemDropdown();
          ?>
        </td>
      </tr>
    </tbody>

    <tbody>
      <tr>
        <td colspan='1' style='width: 50%;'>
          <h3>Recipient</h3>
        </td>
        <td colspan='1' style='width: 50%;'>
          <input type='text' name='Recipient' placeholder='Username / ID' />
        </td>
      </tr>

      <tr>
        <td colspan='1' style='width: 50%;'>
          <h3>Amount</h3>
        </td>
        <td colspan='1' style='width: 50%;'>
          <input type='number' name='Amount' value='1' placeholder='Amount To Be Spawned' />
        </td>
      </tr>

      <tr>
        <td colspan='2'>
          <button onclick='SpawnItem();'>
            Spawn Item
          </button>
        </td>
      </tr>
    </tbody>
  </table>

  <div id='Spawn_Item_Table'></div>
</div>
