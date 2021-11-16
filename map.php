<?php
	require_once 'core/required/layout_top.php';
?>

<div class='panel content'>
	<div class='head'>Maps</div>
	<div class='body flex' style='align-items: center; flex-flow: column; gap: 10px; justify-content: center; padding: 5px;'>
    <div class='border-gradient'>
      <div id='map_canvas'>
      </div>
    </div>

    <table class='border-gradient' style='width: 300px;'>
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
            <span id='map_shiny_odds'>1 / 8192 (0.0001%)</span>
          </td>
        </tr>

        <tr>
          <td style='width: 50%;'>
            <b>Next Encounter In</b>
          </td>
          <td style='width: 50%;'>
            <span id='map_steps_until_encounter'>-1 Steps</span>
          </td>
        </tr>
      </tbody>
    </table>
	</div>
</div>

<!-- Phaser Library -->
<script type='text/javascript' src='<?= DOMAIN_ROOT; ?>/js/dependencies/phaser.js'></script>

<!-- Map Scripts -->
<script type='text/javascript' src='<?= DOMAIN_ROOT; ?>/js/maps/network.js'></script>

<!-- Entities -->
<script type='text/javascript' src='<?= DOMAIN_ROOT; ?>/js/maps/entities/player.js'></script>

<!-- Initialize Engine & Scenes -->
<script type='text/javascript' src='<?= DOMAIN_ROOT; ?>/js/maps/render.js'></script>
<script type='text/javascript' src='<?= DOMAIN_ROOT; ?>/js/maps/init.js'></script>

<?php
	require_once 'core/required/layout_bottom.php';
