<?php
	require_once 'core/required/layout_top.php';
?>

<div class='panel content'>
	<div class='head'>Maps</div>
	<div class='body' style='align-items: center; display: flex; flex-flow: column; gap: 10px; justify-content: center; padding: 5px;'>
    <table class='border-gradient' style='flex-basis: 300px;'>
      <thead>
        <tr>
          <th colspan='2'>
            <span id='map_name'>Unknown Map</span>
          </th>
        </tr>
      </thead>
      <tbody>
        <tr>
          <td style='padding: 0px;'>
            <div
              id='map_canvas'
              style='height: 240px; width: 240px;'
            >
            </div>
          </td>
          <td id='map_dialogue'>
            You wander around aimlessly.
          </td>
        </tr>
      </tbody>

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
<script type='text/javascript' src='<?= DOMAIN_ROOT; ?>/js/dependencies/GridEngine.min.js'></script>

<!-- Map Scripts -->
<script type='text/javascript' src='<?= DOMAIN_ROOT; ?>/maps/js/eventhandler.js'></script>
<script type='text/javascript' src='<?= DOMAIN_ROOT; ?>/maps/js/network.js'></script>
<script type='text/javascript' src='<?= DOMAIN_ROOT; ?>/maps/js/tileinfo.js'></script>

<!-- Entities -->
<script type='text/javascript' src='<?= DOMAIN_ROOT; ?>/maps/js/entities/player.js'></script>
<script type='text/javascript' src='<?= DOMAIN_ROOT; ?>/maps/js/entities/transition.js'></script>
<script type='text/javascript' src='<?= DOMAIN_ROOT; ?>/maps/js/entities/npc.js'></script>
<script type='text/javascript' src='<?= DOMAIN_ROOT; ?>/maps/js/entities/encounter.js'></script>

<!-- Initialize Engine & Scenes -->
<script type='text/javascript' src='<?= DOMAIN_ROOT; ?>/maps/js/hud.js'></script>
<script type='text/javascript' src='<?= DOMAIN_ROOT; ?>/maps/js/render.js'></script>
<script type='text/javascript' src='<?= DOMAIN_ROOT; ?>/maps/js/init.js'></script>

<?php
	require_once 'core/required/layout_bottom.php';
