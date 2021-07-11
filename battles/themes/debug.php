<?php
  require_once 'core/required/layout_top.php';

  if ( !isset($_SESSION['Battle']) )
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

    return;
  }
?>

<div class='panel content'>
  <div class='head'>Battle</div>
  <div class='body' style='padding: 5px;'>
    <div class='flex' style='justify-content: center;'>
      <div style='flex-basis: 70%;' id='BattleWindow'>
        <div class='flex' style='flex-wrap: wrap; justify-content: center;'>
          <div style='flex-basis: 49%;'>
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
                        <b>Level</b>: <span slot='Ally_Level'>-1</span>
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
                  <div style='font-size: 12px; text-align: center;'>
                    <b>HP</b>: (<span slot='Ally_HP'>0</span> / <span slot='Ally_Max_HP'>0</span>)
                    <div class='progress-container' style='width: 140px;'>
                      <div class='progress-bar hp' slot='Ally_HP_Bar' style='width: 100%;'></div>
                    </div>
                  </div>
                </div>

                <div class='border-gradient' style='width: 158px;'>
                  <div style='font-size: 12px; text-align: center;'>
                    <b>Level Up</b>: <span slot='Ally_Exp_Needed'>0</span> Exp
                    <div class='progress-container' style='width: 140px;'>
                      <div class='progress-bar exp' slot='Ally_Exp_Bar' style='width: 100%;'></div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <div style='flex-basis: 49%;'>
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
                  <div style='font-size: 12px; text-align: center;'>
                    <b>HP</b>: (<span slot='Foe_HP'>0</span> / <span slot='Foe_Max_HP'>0</span>)
                    <div class='progress-container' style='width: 140px;'>
                      <div class='progress-bar hp' slot='Foe_HP_Bar' style='width: 100%;'></div>
                    </div>
                  </div>
                </div>

                <div class='border-gradient' style='width: 158px;'>
                  <div style='font-size: 12px; text-align: center;'>
                    <b>Level Up</b>: <span slot='Foe_Exp_Needed'>0</span> Exp
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
                        <b>Level</b>: <span slot='Foe_Level'>-1</span>
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>
          </div>

          <div style='flex-basis: 96%;'>
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

          <div class='border-gradient' style='flex-basis: 96%; margin-top: 5px;'>
            <div id='BattleDialogue' style='font-size: 12px;'>
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
