<?php
  require_once 'core/required/layout_top.php';

  if ( !isset($_SESSION['EvoChroniclesRPG']) )
  {
    $width = " style='margin: 5px; width: calc(100% - 10px);'";
  }
  else
  {
    $width = "";
  }
?>

<div class='panel content'<?= $width; ?>>
  <div class='head'>403 FORBIDDEN</div>
  <div class='body' style='padding: 5px;'>
    Access to the requested resource is forbidden.
    <br /><br />

    <a href="javascript:history.go(-1);">Go Back A Page?</a>
  </div>
</div>

<?php
  require_once 'core/required/layout_bottom.php';
