<?php
  require_once 'core/required/layout_top.php';

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

    require_once 'core/required/layout_bottom.php';
    exit;
  }
?>

<div class='panel content'>
  <div class='head'>Battle</div>
  <div class='body' style='padding: 5px;'>
    <div class='flex' style='justify-content: center;'>
      <div style='flex-basis: 100%;' id='BattleWindow'>
        <div class='flex' style='flex-wrap: wrap; justify-content: center;'>
          <div style='flex-basis: 39%;'>
            <div class='flex' style='justify-content: center; flex-basis: 100%;'>
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
            <div class='flex' style='flex-basis: 100%; justify-content: center; margin: 5px 10px;'>
              <div style='flex-basis: 70px; font-size: 12px; width: 70px;'>
                <div style='border-radius: 6px 0px 0px 6px; background: var(--color-primary); margin-top: 3px;'>
                  x<span slot='Ally_Attack_Mod'>1 </span> Att
                  <span slot='Ally_Attack_Entity'></span>
                </div>
                <div style='border-radius: 6px 0px 0px 6px; background: var(--color-primary); margin-top: 3px;'>
                  x<span slot='Ally_Defense_Mod'>1.00</span> Def
                  <span slot='Ally_Defense_Entity'></span>
                </div>
                <div style='border-radius: 6px 0px 0px 6px; background: var(--color-primary); margin-top: 3px;'>
                  x<span slot='Ally_Sp_Attack_Mod'>1.00</span> SpA
                  <span slot='Ally_Sp_Attack_Entity'></span>
                </div>
                <div style='border-radius: 6px 0px 0px 6px; background: var(--color-primary); margin-top: 3px;'>
                  x<span slot='Ally_Sp_Defense_Mod'>1.00</span> SpD
                  <span slot='Ally_Sp_Defense_Entity'></span>
                </div>
                <div style='border-radius: 6px 0px 0px 6px; background: var(--color-primary); margin-top: 3px;'>
                  x<span slot='Ally_Speed_Mod'>1.00</span> Spe
                  <span slot='Ally_Speed_Entity'></span>
                </div>
                <div style='border-radius: 6px 0px 0px 6px; background: var(--color-primary); margin-top: 3px;'>
                  x<span slot='Ally_Accuracy_Mod'>1.00</span> Acc
                  <span slot='Ally_Accuracy_Entity'></span>
                </div>
                <div style='border-radius: 6px 0px 0px 6px; background: var(--color-primary); margin-top: 3px;'>
                  x<span slot='Ally_Evasion_Mod'>1.00</span> Eva
                  <span slot='Ally_Evasion_Entity'></span>
                </div>
              </div>

              <div style='flex-basis: 120px; margin-right: 5px; width: 120px;'>
                <table class='border-gradient' style='width: 120px;'>
                  <tbody>
                    <tr>
                      <td slot='Ally_Active' style='padding: 0px;'>
                        <img src='<?= DOMAIN_SPRITES; ?>/Pokemon/Sprites/0.png' />
                      </td>
                    </tr>
                  </tbody>
                  <tbody>
                    <tr>
                      <td>
                        <b>Level</b><br />
                        <span slot='Ally_Level'>-1</span>
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>

              <div style='flex-basis: 160px; width: 160px;'>
                <div class='border-gradient' style='margin-bottom: 5px; width: 158px;'>
                  <div slot='Ally_Name' style='font-size: 12px;'>
                    Empty
                  </div>
                </div>

                <div class='border-gradient' style='margin-bottom: 5px; width: 158px;'>
                  <div style='display: block; font-size: 12px; text-align: center;'>
                    <b>HP</b>: (<span slot='Ally_HP'>0</span> / <span slot='Ally_Max_HP'>0</span>)
                    <div class='progress-container' style='width: 140px;'>
                      <div class='progress-bar hp' slot='Ally_HP_Bar' style='width: 100%;'></div>
                    </div>
                  </div>
                </div>

                <div class='border-gradient' style='width: 158px;'>
                  <div style='display: block; font-size: 12px; text-align: center;'>
                    <b>Exp To Next Level</b><br />
                    <span slot='Ally_Exp_Needed'>0</span> Exp
                    <div class='progress-container' style='width: 140px;'>
                      <div class='progress-bar exp' slot='Ally_Exp_Bar' style='width: 100%;'></div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <div class='flex' style='flex-basis: 100%; justify-content: center; margin: 5px 10px;'>
              <div style='flex-basis: 100%;'>
                <div class='border-gradient'>
                  <div id='Ally_Field_Effects' style='height: 21px;'>No Active Field Effects</div>
                </div>
              </div>
            </div>
          </div>

          <div style='margin-left: 5px; padding-top: 5px;'>
            <div slot='Battle_Weather' style='height: 30px; width: 40px;'></div>
            <div class='flex' style='flex-direction: column; margin-top: 4px;'>
              <div class='border-gradient hover'>
                <div>
                  <img src='<?= DOMAIN_SPRITES; ?>/Assets/settings.png' />
                </div>
              </div>
              <br />

              <div class='border-gradient hover' onclick='Battle.OpenBag(event);'>
                <div>
                  <img src='<?= DOMAIN_SPRITES; ?>/Assets/bag_general.png' />
                </div>
              </div>
            </div>
          </div>

          <div style='flex-basis: 39%;'>
            <div class='flex' style='justify-content: center;'>
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
            <div class='flex' style='flex-basis: 100%; justify-content: center; margin: 5px 10px;'>
              <div style='flex-basis: 160px; width: 160px;'>
                <div class='border-gradient' style='margin-bottom: 5px; width: 158px;'>
                  <div slot='Foe_Name' style='font-size: 12px;'>
                    Empty
                  </div>
                </div>

                <div class='border-gradient' style='margin-bottom: 5px; width: 158px;'>
                  <div style='display: block; font-size: 12px; text-align: center;'>
                    <b>HP</b>: (<span slot='Foe_HP'>0</span> / <span slot='Foe_Max_HP'>0</span>)
                    <div class='progress-container' style='width: 140px;'>
                      <div class='progress-bar hp' slot='Foe_HP_Bar' style='width: 100%;'></div>
                    </div>
                  </div>
                </div>

                <div class='border-gradient' style='width: 158px;'>
                  <div style='display: block; font-size: 12px; text-align: center;'>
                    <b>Exp To Next Level</b><br />
                    <span slot='Foe_Exp_Needed'>0</span> Exp
                    <div class='progress-container' style='width: 140px;'>
                      <div class='progress-bar exp' slot='Foe_Exp_Bar' style='width: 100%;'></div>
                    </div>
                  </div>
                </div>
              </div>

              <div style='flex-basis: 120px; margin-left: 7px; width: 120px;'>
                <table class='border-gradient' style='width: 120px;'>
                  <tbody>
                    <tr>
                      <td slot='Foe_Active' style='padding: 0px;'>
                        <img src='<?= DOMAIN_SPRITES; ?>/Pokemon/Sprites/0.png' />
                      </td>
                    </tr>
                  </tbody>
                  <tbody>
                    <tr>
                      <td>
                        <b>Level</b><br />
                        <span slot='Foe_Level'>-1</span>
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>
              <div style='flex-basis: 70px; font-size: 12px; width: 70px;'>
                <div style='border-radius: 0px 6px 6px 0px; background: var(--color-primary); margin-top: 3px;'>
                  x<span slot='Foe_Attack_Mod'>1.00</span> Att
                  <span slot='Foe_Attack_Entity'></span>
                </div>
                <div style='border-radius: 0px 6px 6px 0px; background: var(--color-primary); margin-top: 3px;'>
                  x<span slot='Foe_Defense_Mod'>1.00</span> Def
                  <span slot='Foe_Defense_Entity'></span>
                </div>
                <div style='border-radius: 0px 6px 6px 0px; background: var(--color-primary); margin-top: 3px;'>
                  x<span slot='Foe_Sp_Attack_Mod'>1.00</span> SpA
                  <span slot='Foe_Sp_Attack_Entity'></span>
                </div>
                <div style='border-radius: 0px 6px 6px 0px; background: var(--color-primary); margin-top: 3px;'>
                  x<span slot='Foe_Sp_Defense_Mod'>1.00</span> SpD
                  <span slot='Foe_Sp_Defense_Entity'></span>
                </div>
                <div style='border-radius: 0px 6px 6px 0px; background: var(--color-primary); margin-top: 3px;'>
                  x<span slot='Foe_Speed_Mod'>1.00</span> Spe
                  <span slot='Foe_Speed_Entity'></span>
                </div>
                <div style='border-radius: 0px 6px 6px 0px; background: var(--color-primary); margin-top: 3px;'>
                  x<span slot='Foe_Accuracy_Mod'>1.00</span> Acc
                  <span slot='Foe_Accuracy_Entity'></span>
                </div>
                <div style='border-radius: 0px 6px 6px 0px; background: var(--color-primary); margin-top: 3px;'>
                  x<span slot='Foe_Evasion_Mod'>1.00</span> Eva
                  <span slot='Foe_Evasion_Entity'></span>
                </div>
              </div>
            </div>
            <div class='flex' style='flex-basis: 100%; justify-content: center; margin: 5px 10px;'>
              <div style='flex-basis: 100%;'>
                <div class='border-gradient'>
                  <div id='Foe_Field_Effects' style='height: 21px;'>No Active Field Effects</div>
                </div>
              </div>
            </div>
          </div>

          <div style='flex-basis: 75%;'>
            <input
              move='Move_0'
              type='button'
              value='???'
              style='font-weight: bold; padding: 5px 0px; width: 24%;'
            />
            <input
              move='Move_1'
              type='button'
              value='???'
              style='font-weight: bold; padding: 5px 0px; width: 24%;'
            />
            <input
              move='Move_2'
              type='button'
              value='???'
              style='font-weight: bold; padding: 5px 0px; width: 24%;'
            />
            <input
              move='Move_3'
              type='button'
              value='???'
              style='font-weight: bold; padding: 5px 0px; width: 24%;'
            />
          </div>

          <div class='border-gradient' style='flex-basis: 70%; margin-top: 5px;'>
            <div id='BattleDialogue' style='display: block; font-size: 12px;'>
              Loading Battle State
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<?php
  require_once 'core/required/layout_bottom.php';
