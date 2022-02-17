<?php
  require_once $_SERVER['DOCUMENT_ROOT'] . '/core/required/session.php';
  require_once $_SERVER['DOCUMENT_ROOT'] . '/staff/functions/auth.php';

  if ( !AuthorizeUser() )
  {
    echo "
      <div class='panel content'>
        <div class='head'>Staff Panel</div>
        <div class='body' style='padding: 5px'>
          You aren't authorized to be here.
        </div>
      </div>
    ";

    exit;
  }

  // Contains the 'ShowPokedexDropdown' function that the Pokemon spawner uses.
  require_once $_SERVER['DOCUMENT_ROOT'] . '/staff/functions/spawn_pokemon.php';
?>

<div style='display: flex; flex-direction: column; flex-wrap: wrap; gap: 10px; justify-content: center;'>
  <div style='flex-basis: 100%; width: 100%;'>
    <h3>Pok&eacute;mon Spawner</h3>
  </div>

  <div id='Spawn_Pokemon_AJAX'></div>

  <?php
    echo ShowSpawnablePokemonDropdown();
  ?>

  <div id='Spawn_Pokemon_Table'></div>
</div>
