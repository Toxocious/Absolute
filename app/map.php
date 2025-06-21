<?php
	require_once 'core/required/layout_top.php';

  unset($_SESSION['EvoChroniclesRPG']['Maps']);
?>

<div class='panel content'>
	<div class='head'>Maps</div>
	<div class='body' style='align-items: center; display: flex; flex-flow: column; gap: 10px; justify-content: center; padding: 5px;'>
    <div style='display: flex; flex-basis: 280px; flex-wrap: wrap; width: 600px;'>
      <div class='border-gradient' style='height: 280px; width: 280px;'>
        <div
          id='map_canvas'
          style='height: 280px; width: 280px;'
        >
        </div>
      </div>

      <table class='border-gradient' style='min-height: 240px; width: 300px;'>
        <thead>
          <tr>
            <th id='map_name'>
              Unknown Map
            </th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <td id='map_dialogue'>
              You wander around aimlessly.
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <table class='border-gradient' style='min-width: 485px; max-width: 485px;'>
      <thead>
        <tr>
          <th colspan='2'>
            Map Stats
          </th>
        </tr>
      </thead>
      <tbody>
        <tr>
          <td style='width: 50%;'>
            <b>Map Level</b>
          </td>
          <td style='width: 50%;'>
            <span id='map_level'>-1</span>
          </td>
        </tr>
        <tr>
          <td colspan='2'>
            <b>Next Level In</b>: <span id='map_exp_to_level'>-1</span> Exp
            <div class='progress-container' style='margin: 0 auto; width: 200px;'>
              <div class='progress-bar exp' id='map_exp_bar' style='width: 100%;'></div>
            </div>
          </td>
        </tr>

        <tr>
          <td style='width: 50%;'>
            <b>Shiny Odds</b>
          </td>
          <td style='width: 50%;'>
            <span id='map_shiny_odds'>1 / 8192 (0.0122%)</span>
          </td>
        </tr>
      </tbody>
    </table>

	</div>
</div>

<!-- Phaser Library -->
<script type='text/javascript' src='<?= DOMAIN_ROOT; ?>/js/dependencies/phaser.js'></script>
<script type='text/javascript' src='<?= DOMAIN_ROOT; ?>/js/dependencies/phaser-GridEngine.min.js'></script>
<script type='text/javascript' src='<?= DOMAIN_ROOT; ?>/js/dependencies/phaser-rexawaitloaderplugin.min.js'></script>

<!-- Map Scripts -->
<script type='text/javascript' src='<?= DOMAIN_ROOT; ?>/maps/js/network.js'></script>
<script type='text/javascript' src='<?= DOMAIN_ROOT; ?>/maps/js/tileinfo.js'></script>

<!-- Entities -->
<script type='text/javascript' src='<?= DOMAIN_ROOT; ?>/maps/js/entities/warp.js'></script>
<script type='text/javascript' src='<?= DOMAIN_ROOT; ?>/maps/js/entities/player.js'></script>
<script type='text/javascript' src='<?= DOMAIN_ROOT; ?>/maps/js/entities/transition.js'></script>
<script type='text/javascript' src='<?= DOMAIN_ROOT; ?>/maps/js/entities/npc.js'></script>
<script type='text/javascript' src='<?= DOMAIN_ROOT; ?>/maps/js/entities/encounter.js'></script>

<!-- Initialize Engine & Scenes -->
<script type='text/javascript' src='<?= DOMAIN_ROOT; ?>/maps/js/render.js'></script>
<script type='text/javascript' src='<?= DOMAIN_ROOT; ?>/maps/js/init.js'></script>

<?php
	require_once 'core/required/layout_bottom.php';
