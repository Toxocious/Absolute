<?php
  require_once 'core/required/layout_top.php';
?>

<div class='panel content'>
  <div class='head'>Pok&eacute;mon Center</div>
  <div class='body'>
    <div class='nav'>
      <div>
        <a href='javascript:void(0);' onclick="ShowTab('roster');">
          Roster
        </a>
      </div>
      <div>
        <a href='javascript:void(0);' onclick="ShowTab('moves');">
          Moves
        </a>
      </div>
      <div>
        <a href='javascript:void(0);' onclick="ShowTab('inventory');">
          Inventory
        </a>
      </div>
      <div>
        <a href='javascript:void(0);' onclick="ShowTab('nickname');">
          Nickname
        </a>
      </div>
      <div>
        <a href='javascript:void(0);' onclick="ShowTab('release');">
          Release
        </a>
      </div>
    </div>

    <div class='flex wrap' id='Pokemon_Center_Page' style='justify-content: center;'>
      <div style='display: flex; align-items: center; justify-content: center; padding: 10px;'>
        <div class='loading-element'></div>
      </div>
    </div>
  </div>
</div>

<script src='<?= DOMAIN_ROOT; ?>/pages/pokemon_center/js/ajax_functions.js'></script>
<script src='<?= DOMAIN_ROOT; ?>/pages/pokemon_center/js/inventory.js'></script>
<script src='<?= DOMAIN_ROOT; ?>/pages/pokemon_center/js/nickname.js'></script>
<script src='<?= DOMAIN_ROOT; ?>/pages/pokemon_center/js/roster.js'></script>
<script src='<?= DOMAIN_ROOT; ?>/pages/pokemon_center/js/moves.js'></script>

<script src='<?= DOMAIN_ROOT; ?>/pages/pokemon_center/js/release.js'></script>

<script>
  (function()
  {
    ShowTab('roster');
  })();
</script>

<?php
  require_once 'core/required/layout_bottom.php';
