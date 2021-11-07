<?php
	require_once '../../required/session.php';

  if ( empty($User_Data['ID']) || empty($_POST['PokeID']) )
	{
    echo 'The Pok&eacute;mon that you have selected does not exist.';
    return;
  }

  $Pokemon = $Poke_Class->FetchPokemonData(Purify($_POST['PokeID']));
  $Pokemon_Level = number_format(FetchLevel($Pokemon['Experience_Raw'], 'Pokemon'));

  if ( $Pokemon['Item_ID'] != null )
  {
    $Item_Icon = "
      <div class='border-gradient' style='height: 28px; width: 28px;'>
        <div>
          <img src='{$Pokemon['Item_Icon']}' style='height: 24px; width: 24px;' />
        </div>
      </div>
    ";
  }

  $Roster_Slots = '';
  for ( $i = 0; $i <= 5; $i++ )
  {
    if ( isset($User_Data['Roster'][$i]['ID'])  )
    {
      $Roster_Slot[$i] = $Poke_Class->FetchPokemonData($User_Data['Roster'][$i]['ID']);

      $Roster_Slots .= "
        <div class='border-gradient hover' style='height: 32px; width: 42px;'>
          <div style='padding: 2px;'>
            <img src='{$Roster_Slot[$i]['Icon']}' onclick=\"handlePokemon('Move', {$Pokemon['ID']}, " . ($i + 1) . ");\" />
          </div>
        </div>
      ";
    }
    else
    {
      $Roster_Slots .= "
        <div class='border-gradient hover' style='height: 32px; width: 42px;'>
          <div style='padding: 2px;'>
            <img src='" . DOMAIN_SPRITES . "/Pokemon/Sprites/0_mini.png' style='height: 30px; width: 40px;' onclick=\"handlePokemon('Move', {$Pokemon['ID']}, " . ($i + 1) . ");\" />
          </div>
        </div>
      ";
    }
  }

  $Total_Stat = array_sum($Pokemon['Stats']);
  $Total_IV = array_sum($Pokemon['IVs']);
  $Total_EV = array_sum($Pokemon['EVs']);
?>


<div class='flex' style='flex-basis: 100%; gap: 6px;'>
  <div class='flex' style='align-items: center; flex-basis: 175px; flex-wrap: wrap; justify-content: center;'>
    <div class='flex' style='align-items: center; gap: 10px; justify-content: center;'>
      <div class='border-gradient hover hw-96px padding-0px'>
        <div>
          <img class='popup' src='<?= $Pokemon['Sprite']; ?>' data-src='<?= DOMAIN_ROOT; ?>/core/ajax/pokemon.php?id=<?= $Pokemon['ID']; ?>' />
        </div>
      </div>

      <div class='flex' style='flex-basis: 30px; flex-wrap: wrap; gap: 35px 0px;'>
        <div class='border-gradient hw-30px' style='height: 28px; width: 28px;'>
          <div>
            <img src='<?= $Pokemon['Gender_Icon']; ?>' style='height: 24px; width: 24px;' />
          </div>
        </div>

        <?php
          if ( !empty($Item_Icon) )
          {
            echo $Item_Icon;
          }
        ?>
      </div>
    </div>

    <div style='flex-basis: 100%;'>
      <b>Level</b><br />
      <?= $Pokemon_Level; ?><br />
      <i style='font-size: 12px;'>(<?= $Pokemon['Experience']; ?> Exp)</i>
    </div>
  </div>

  <div class='flex' style='align-items: center; flex-basis: 120px; flex-wrap: wrap; gap: 10px; justify-content: flex-start;'>
    <b>Add To Roster</b><br />
    <?= $Roster_Slots; ?>
  </div>

  <div style='flex-basis: 40%;'>
    <table class='border-gradient' style='width: 100%;'>
      <thead>
        <tr>
          <th style='width: 25%;'>Stat</th>
          <th style='width: 25%;'>Base</th>
          <th style='width: 25%;'>IV</th>
          <th style='width: 25%;'>EV</th>
        </tr>
      </thead>
      <tbody>
        <tr>
          <td style='padding: 3px;'><b>HP</b></td>
          <td><?= number_format($Pokemon['Stats'][0]); ?></td>
          <td><?= number_format($Pokemon['IVs'][0]); ?></td>
          <td><?= number_format($Pokemon['EVs'][0]); ?></td>
        </tr>
        <tr>
          <td style='padding: 3px;'><b>Attack</b></td>
          <td><?= number_format($Pokemon['Stats'][1]); ?></td>
          <td><?= number_format($Pokemon['IVs'][1]); ?></td>
          <td><?= number_format($Pokemon['EVs'][1]); ?></td>
        </tr>
        <tr>
          <td style='padding: 3px;'><b>Defense</b></td>
          <td><?= number_format($Pokemon['Stats'][2]); ?></td>
          <td><?= number_format($Pokemon['IVs'][2]); ?></td>
          <td><?= number_format($Pokemon['EVs'][2]); ?></td>
        </tr>
        <tr>
          <td style='padding: 3px;'><b>Sp. Att</b></td>
          <td><?= number_format($Pokemon['Stats'][3]); ?></td>
          <td><?= number_format($Pokemon['IVs'][3]); ?></td>
          <td><?= number_format($Pokemon['EVs'][3]); ?></td>
        </tr>
        <tr>
          <td style='padding: 3px;'><b>Sp. Def</b></td>
          <td><?= number_format($Pokemon['Stats'][4]); ?></td>
          <td><?= number_format($Pokemon['IVs'][4]); ?></td>
          <td><?= number_format($Pokemon['EVs'][4]); ?></td>
        </tr>
        <tr>
          <td style='padding: 3px;'><b>Speed</b></td>
          <td><?= number_format($Pokemon['Stats'][5]); ?></td>
          <td><?= number_format($Pokemon['IVs'][5]); ?></td>
          <td><?= number_format($Pokemon['EVs'][5]); ?></td>
        </tr>
      </tbody>
    </table>
  </div>
</div>

<!--
<div style='flex-basis: 60%;'>
  <div class='border-gradient hover hw-96px padding-0px' style='margin: 0 auto;'>
    <div>
      <img class='popup' src='{$Pokemon['Sprite']}' data-src='" . DOMAIN_ROOT . "/core/ajax/pokemon.php?id={$Pokemon['ID']}' />
    </div>
  </div>

  <div style='float: left; margin-top: -65px; margin-left: 50px;'>
    <div class='border-gradient hw-30px' style='margin: 5px 0px 5px 5px;'>
      <div>
        <img src='{$Pokemon['Gender_Icon']}' style='height: 24px; width: 24px;' />
      </div>
    </div>
    {$Item}
  </div>

  <div class='flex' style='margin: 5px;'>
    <div style='flex-basis: 50%;'>
      <b>Level</b><br />
      {$Pokemon_Level}<br />
    </div>
    <div style='flex-basis: 50%;'>
      <b>Experience</b><br />
      {$Pokemon['Experience']}
    </div>
  </div>

  <div class='flex' style='gap: 5px; justify-content: center;'>
    {$Slots}
  </div>
</div>

<div style='flex-basis: 40%;'>
  <table class='border-gradient' style='width: 100%;'>
    <thead>
      <tr>
        <th style='width: 25%;'>Stat</th>
        <th style='width: 25%;'>Base</th>
        <th style='width: 25%;'>IV</th>
        <th style='width: 25%;'>EV</th>
      </tr>
    </thead>
    <tbody>
      <tr>
        <td style='padding: 3px;'><b>HP</b></td>
        <td>" . number_format($Pokemon['Stats'][0]) . "</td>
        <td>" . number_format($Pokemon['IVs'][0]) . "</td>
        <td>" . number_format($Pokemon['EVs'][0]) . "</td>
      </tr>
      <tr>
        <td style='padding: 3px;'><b>Attack</b></td>
        <td>" . number_format($Pokemon['Stats'][1]) . "</td>
        <td>" . number_format($Pokemon['IVs'][1]) . "</td>
        <td>" . number_format($Pokemon['EVs'][1]) . "</td>
      </tr>
      <tr>
        <td style='padding: 3px;'><b>Defense</b></td>
        <td>" . number_format($Pokemon['Stats'][2]) . "</td>
        <td>" . number_format($Pokemon['IVs'][2]) . "</td>
        <td>" . number_format($Pokemon['EVs'][2]) . "</td>
      </tr>
      <tr>
        <td style='padding: 3px;'><b>Sp. Att</b></td>
        <td>" . number_format($Pokemon['Stats'][3]) . "</td>
        <td>" . number_format($Pokemon['IVs'][3]) . "</td>
        <td>" . number_format($Pokemon['EVs'][3]) . "</td>
      </tr>
      <tr>
        <td style='padding: 3px;'><b>Sp. Def</b></td>
        <td>" . number_format($Pokemon['Stats'][4]) . "</td>
        <td>" . number_format($Pokemon['IVs'][4]) . "</td>
        <td>" . number_format($Pokemon['EVs'][4]) . "</td>
      </tr>
      <tr>
        <td style='padding: 3px;'><b>Speed</b></td>
        <td>" . number_format($Pokemon['Stats'][5]) . "</td>
        <td>" . number_format($Pokemon['IVs'][5]) . "</td>
        <td>" . number_format($Pokemon['EVs'][5]) . "</td>
      </tr>
    </tbody>
  </table>
</div>
