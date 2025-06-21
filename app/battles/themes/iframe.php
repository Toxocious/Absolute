<?php
  require_once 'core/required/session.php';

  if ( empty($_SESSION['EvoChroniclesRPG']['Battle']) )
  {
    echo "
      <div class='panel content'>
        <div class='head'>Battle</div>
        <div class='body' style='padding: 5px;'>
          <div class='error' style='margin: 5px auto 5px;'>
            A battle has not yet been created.
          </div>
        </div>
      </div>
    ";

    exit;
  }
?>

<head>
  <link type='text/css' rel='stylesheet' href='/themes/css/styles/<?= (isset($User_Data['Theme']) ? $User_Data['Theme'] : 'absol'); ?>.css' />
  <link type='text/css' rel='stylesheet' href='/themes/css/root.css' />
  <link type='text/css' rel='stylesheet' href='/themes/css/structure.css' />
  <link type='text/css' rel='stylesheet' href='/themes/css/theme.css' />
</head>

<div class='flex wrap' style='display: flex; flex-wrap: wrap; justify-content: center; padding: 5px;'>
  <!-- ally -->
  <div class='flex wrap' style='gap: 5px; width: 47.5%;'>
    <div class='flex' style='justify-content: center; flex-basis: 100%; gap: 5px;'>
      <div class='border-gradient hover' style='margin-right: 5px;'>
        <div slot='Ally_Slot_0' style='height: 30px; padding: 0px; width: 40px;'>
          <img src='<?= DOMAIN_SPRITES; ?>/Pokemon/Sprites/0_mini.png' />
        </div>
      </div>
      <div class='border-gradient hover' style='margin-right: 5px;'>
        <div slot='Ally_Slot_1' style='height: 30px; padding: 0px; width: 40px;'>
          <img src='<?= DOMAIN_SPRITES; ?>/Pokemon/Sprites/0_mini.png' />
        </div>
      </div>
      <div class='border-gradient hover' style='margin-right: 5px;'>
        <div slot='Ally_Slot_2' style='height: 30px; padding: 0px; width: 40px;'>
          <img src='<?= DOMAIN_SPRITES; ?>/Pokemon/Sprites/0_mini.png' />
        </div>
      </div>
      <div class='border-gradient hover' style='margin-right: 5px;'>
        <div slot='Ally_Slot_3' style='height: 30px; padding: 0px; width: 40px;'>
          <img src='<?= DOMAIN_SPRITES; ?>/Pokemon/Sprites/0_mini.png' />
        </div>
      </div>
      <div class='border-gradient hover' style='margin-right: 5px;'>
        <div slot='Ally_Slot_4' style='height: 30px; padding: 0px; width: 40px;'>
          <img src='<?= DOMAIN_SPRITES; ?>/Pokemon/Sprites/0_mini.png' />
        </div>
      </div>
      <div class='border-gradient hover'>
        <div slot='Ally_Slot_5' style='height: 30px; padding: 0px; width: 40px;'>
          <img src='<?= DOMAIN_SPRITES; ?>/Pokemon/Sprites/0_mini.png' />
        </div>
      </div>
    </div>

    <table class='border-gradient' style='width: 45%;'>
      <tbody>
        <tr>
          <td slot='Ally_Active'>
            <img src='<?= DOMAIN_SPRITES; ?>/Pokemon/Sprites/0.png' />
          </td>
        </tr>
        <tr>
          <td slot='Ally_Name' colspan='2' style='font-weight: bold;'>
            Empty
          </td>
        </tr>
      </tbody>
    </table>

    <table class='border-gradient' style='width: 45%;'>
      <tbody>
        <tr>
          <td>
            <b>Level</b><br />
            <span slot='Ally_Level'>-1</span>
          </td>
        </tr>
        <tr>
          <td colspan='2'>
            <b>Exp. To Next Level</b><br />
            <span slot='Ally_Exp_Needed'>-1</span>
            <div class='progress-container' style='margin: 0 auto; width: 140px;'>
              <div class='progress-bar exp' slot='Ally_Exp_Bar' style='width: 100%;'></div>
            </div>
          </td>
        </tr>
        <tr>
          <td colspan='2'>
            <b>Current HP</b><br />
            <span slot='Ally_HP'>-1</span> / <span slot='Ally_Max_HP'>-1</span>
            <div class='progress-container' style='margin: 0 auto; width: 140px;'>
              <div class='progress-bar hp' slot='Ally_HP_Bar' style='width: 100%;'></div>
            </div>
          </td>
        </tr>
      </tbody>
    </table>

    <div class='flex' style='flex-wrap: wrap; gap: 2px;'>
      <div class='flex' style='flex-basis: 100%; flex-wrap: wrap; gap: 2px; justify-content: center;'>
        <div style='border-radius: 6px; background: var(--color-primary); flex-basis: 24%; text-align: center;'>
          x<span slot='Ally_Attack_Mod'>1 </span> Att
          <span slot='Ally_Attack_Entity'></span>
        </div>
        <div style='border-radius: 6px; background: var(--color-primary); flex-basis: 24%; text-align: center;'>
          x<span slot='Ally_Defense_Mod'>1.00</span> Def
          <span slot='Ally_Defense_Entity'></span>
        </div>
        <div style='border-radius: 6px; background: var(--color-primary); flex-basis: 24%; text-align: center;'>
          x<span slot='Ally_Sp_Attack_Mod'>1.00</span> SpA
          <span slot='Ally_Sp_Attack_Entity'></span>
        </div>
        <div style='border-radius: 6px; background: var(--color-primary); flex-basis: 24%; text-align: center;'>
          x<span slot='Ally_Sp_Defense_Mod'>1.00</span> SpD
          <span slot='Ally_Sp_Defense_Entity'></span>
        </div>
        <div style='border-radius: 6px; background: var(--color-primary); flex-basis: 24%; text-align: center;'>
          x<span slot='Ally_Speed_Mod'>1.00</span> Spe
          <span slot='Ally_Speed_Entity'></span>
        </div>

        <div style='flex-basis: 24%; text-align: center;'></div>

        <div style='border-radius: 6px; background: var(--color-primary); flex-basis: 24%; text-align: center;'>
          x<span slot='Ally_Accuracy_Mod'>1.00</span> Acc
          <span slot='Ally_Accuracy_Entity'></span>
        </div>
        <div style='border-radius: 6px; background: var(--color-primary); flex-basis: 24%; text-align: center;'>
          x<span slot='Ally_Evasion_Mod'>1.00</span> Eva
          <span slot='Ally_Evasion_Entity'></span>
        </div>
      </div>
    </div>
  </div>

  <!-- Weather, Settings, Bag, etc -->
  <div style='align-items: center; display: flex; flex-basis: 5%; flex-direction: column; gap: 10px; width: 5%;'>
    <div slot='Battle_Weather' style='height: 30px; width: 40px;'></div>

    <div class='border-gradient hover'>
      <div>
        <img src='<?= DOMAIN_SPRITES; ?>/Assets/settings.png' />
      </div>
    </div>

    <div class='border-gradient hover' onclick='Battle.OpenBag(event);'>
      <div>
        <img src='<?= DOMAIN_SPRITES; ?>/Assets/bag_general.png' />
      </div>
    </div>
  </div>

  <!-- foe -->
  <div class='flex wrap' style='gap: 5px; width: 47.5%;'>
    <div class='flex' style='justify-content: center; flex-basis: 100%; gap: 5px;'>
      <div class='border-gradient hover' style='margin-right: 5px;'>
        <div slot='Foe_Slot_0' style='height: 30px; padding: 0px; width: 40px;'>
          <img src='<?= DOMAIN_SPRITES; ?>/Pokemon/Sprites/0_mini.png' />
        </div>
      </div>
      <div class='border-gradient hover' style='margin-right: 5px;'>
        <div slot='Foe_Slot_1' style='height: 30px; padding: 0px; width: 40px;'>
          <img src='<?= DOMAIN_SPRITES; ?>/Pokemon/Sprites/0_mini.png' />
        </div>
      </div>
      <div class='border-gradient hover' style='margin-right: 5px;'>
        <div slot='Foe_Slot_2' style='height: 30px; padding: 0px; width: 40px;'>
          <img src='<?= DOMAIN_SPRITES; ?>/Pokemon/Sprites/0_mini.png' />
        </div>
      </div>
      <div class='border-gradient hover' style='margin-right: 5px;'>
        <div slot='Foe_Slot_3' style='height: 30px; padding: 0px; width: 40px;'>
          <img src='<?= DOMAIN_SPRITES; ?>/Pokemon/Sprites/0_mini.png' />
        </div>
      </div>
      <div class='border-gradient hover' style='margin-right: 5px;'>
        <div slot='Foe_Slot_4' style='height: 30px; padding: 0px; width: 40px;'>
          <img src='<?= DOMAIN_SPRITES; ?>/Pokemon/Sprites/0_mini.png' />
        </div>
      </div>
      <div class='border-gradient hover'>
        <div slot='Foe_Slot_5' style='height: 30px; padding: 0px; width: 40px;'>
          <img src='<?= DOMAIN_SPRITES; ?>/Pokemon/Sprites/0_mini.png' />
        </div>
      </div>
    </div>

    <table class='border-gradient' style='width: 45%;'>
      <tbody>
        <tr>
          <td>
            <b>Level</b><br />
            <span slot='Foe_Level'>-1</span>
          </td>
        </tr>
        <tr>
          <td colspan='2'>
            <b>Exp. To Next Level</b><br />
            <span slot='Foe_Exp_Needed'>-1</span>
            <div class='progress-container' style='margin: 0 auto; width: 140px;'>
              <div class='progress-bar exp' slot='Foe_Exp_Bar' style='width: 100%;'></div>
            </div>
          </td>
        </tr>
        <tr>
          <td colspan='2'>
            <b>Current HP</b><br />
            <span slot='Foe_HP'>-1</span> / <span slot='Foe_Max_HP'>-1</span>
            <div class='progress-container' style='margin: 0 auto; width: 140px;'>
              <div class='progress-bar hp' slot='Foe_HP_Bar' style='width: 100%;'></div>
            </div>
          </td>
        </tr>
      </tbody>
    </table>

    <table class='border-gradient' style='width: 45%;'>
      <tbody>
        <tr>
          <td slot='Foe_Active'>
            <img src='<?= DOMAIN_SPRITES; ?>/Pokemon/Sprites/0.png' />
          </td>
        </tr>
        <tr>
          <td slot='Foe_Name' colspan='2' style='font-weight: bold;'>
            Empty
          </td>
        </tr>
      </tbody>
    </table>

    <div class='flex' style='flex-wrap: wrap; gap: 2px;'>
      <div class='flex' style='flex-basis: 100%; flex-wrap: wrap; gap: 2px; justify-content: center;'>
        <div style='border-radius: 6px; background: var(--color-primary); flex-basis: 24%; text-align: center;'>
          x<span slot='Foe_Attack_Mod'>1 </span> Att
          <span slot='Foe_Attack_Entity'></span>
        </div>
        <div style='border-radius: 6px; background: var(--color-primary); flex-basis: 24%; text-align: center;'>
          x<span slot='Foe_Defense_Mod'>1.00</span> Def
          <span slot='Foe_Defense_Entity'></span>
        </div>
        <div style='border-radius: 6px; background: var(--color-primary); flex-basis: 24%; text-align: center;'>
          x<span slot='Foe_Sp_Attack_Mod'>1.00</span> SpA
          <span slot='Foe_Sp_Attack_Entity'></span>
        </div>
        <div style='border-radius: 6px; background: var(--color-primary); flex-basis: 24%; text-align: center;'>
          x<span slot='Foe_Sp_Defense_Mod'>1.00</span> SpD
          <span slot='Foe_Sp_Defense_Entity'></span>
        </div>
        <div style='border-radius: 6px; background: var(--color-primary); flex-basis: 24%; text-align: center;'>
          x<span slot='Foe_Speed_Mod'>1.00</span> Spe
          <span slot='Foe_Speed_Entity'></span>
        </div>

        <div style='flex-basis: 24%; text-align: center;'></div>

        <div style='border-radius: 6px; background: var(--color-primary); flex-basis: 24%; text-align: center;'>
          x<span slot='Foe_Accuracy_Mod'>1.00</span> Acc
          <span slot='Foe_Accuracy_Entity'></span>
        </div>
        <div style='border-radius: 6px; background: var(--color-primary); flex-basis: 24%; text-align: center;'>
          x<span slot='Foe_Evasion_Mod'>1.00</span> Eva
          <span slot='Foe_Evasion_Entity'></span>
        </div>
      </div>
    </div>
  </div>

  <!-- moves -->
  <div class='flex' style='gap: 10px; justify-content: center; margin: 5px 0px; width: 100%;'>
    <input
      move='Move_0'
      type='button'
      value='???'
      style='font-weight: bold; padding: 5px 0px; width: 150px;'
    />
    <input
      move='Move_1'
      type='button'
      value='???'
      style='font-weight: bold; padding: 5px 0px; width: 150px;'
    />
    <input
      move='Move_2'
      type='button'
      value='???'
      style='font-weight: bold; padding: 5px 0px; width: 150px;'
    />
    <input
      move='Move_3'
      type='button'
      value='???'
      style='font-weight: bold; padding: 5px 0px; width: 150px;'
    />
  </div>

  <!-- dialogue -->
  <div class='border-gradient' style='display: block; font-size: 12px; width: 100%;'>
    <div id='BattleDialogue' style='display: block;'>
      Loading Battle State
    </div>
  </div>
</div>
