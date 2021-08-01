<?php
	require_once 'core/required/layout_top.php';
?>

<div class='panel content'>
	<div class='head'>Support Absolute</div>
	<div class='body' style='padding: 5px;'>
    <div class='description'>
      If you would like to support Absolute's development, please consider donating below.
      <br />
      We currently accept Bitcoin and Ethereum.
      <br /><br />
      <b>We will need the <u>transaction ID</u> of your Bitcoin/Ethereum transaction to confirm your donation.</b>
    </div>

    <div class='flex wrap' style='justify-content: center;'>
      <div class='border-gradient' style='flex-basis: 40%; margin: 0px 5px;'>
        <div>
          <img src='<?= DOMAIN_SPRITES . '/Assets/btc-svg.png' ?>' style='height: 100px; width: 100px;' />
          <br />
          <b>Bitcoin</b>
          <br /><br />

          <form name='btc_form'>
            How much would you like to donate?<br />
            <i>This value is presumed to be USD.</i>

            <div class='flex'>

            </div>
          </form>
        </div>
      </div>

      <div class='border-gradient' style='flex-basis: 40%; margin: 0px 5px;'>
        <div>
          <img src='<?= DOMAIN_SPRITES . '/Assets/eth-svg.png' ?>' style='height: 100px; width: 100px;' />
          <br />
          <b>Ethereum</b>
          <br /><br />

          <form name='eth_form'>
            How much would you like to donate?<br />
            <i>This value is presumed to be USD.</i>
            <select>
              <option></option>
            </select>
          </form>
        </div>
      </div>
    </div>
	</div>
</div>

<?php
	require_once 'core/required/layout_bottom.php';
