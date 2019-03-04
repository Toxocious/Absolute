<?php
  require 'core/required/layout_top.php';

  $Profile_Search = Text($_GET['id'])->in();
  try
  {
    $Fetch_Profile_User = $PDO->prepare("SELECT `id`, `Username`, `Avatar`, `Rank`, `Money`, `Last_Active`, `Date_Registered`, `Playtime` FROM `users` WHERE `id` = ? OR `Username` = ? LIMIT 1");
    $Fetch_Profile_User->execute([$Profile_Search, $Profile_Search]);
    $Profile_Data = $Fetch_Profile_User->fetch();

    $Fetch_Profile_Pokemon = $PDO->prepare("SELECT `ID` FROM `pokemon` WHERE `Owner_Current` = ? AND `Location` = 'Roster' ORDER BY `Slot` ASC LIMIT 6");
    $Fetch_Profile_Pokemon->execute([$Profile_Search]);
    $Fetch_Profile_Pokemon->setFetchMode(PDO::FETCH_ASSOC);
		$Fetch_Roster = $Fetch_Profile_Pokemon->fetchAll();
  }
  catch ( PDOException $e )
  {
    echo $e->getMessage();
  }
	
  if ( isset($Profile_Data['id']) )
  {
    $Current_Time = time();
    $Calc_Difference = $Current_Time - $Profile_Data['Last_Active'];
?>

<div class='content'>
  <div class='head'><?= $Profile_Data['Username']; ?>'s Profile</div>
  <div class='box profile'>
    <?php
      if ( $User_Data['Power'] > 5 )
      {
        if ( $User_Data['Style'] === '1' )  
        {
          $Border_Color = "#4A618F";
          $Background_Hover = "#253147";
        }
        else
        {
          $BorderColor = "#fff";
        }

        echo "
          <style>
            .content .box.profile .staff_options div:hover
            { background: " . $Background_Hover . "; }
          </style>
        ";
    ?>
    <div class='row' style='margin-bottom: 5px;'>
      <div class='panel'>
        <div class='panel-heading'>Staff Options</div>
        <div class='panel-body staff_options'>
          <div style='border-right: 1px solid <?= $Border_Color; ?>; float: left; padding: 3px; width: 25%;'><a style='display: block;' href='warn_user.php?id=<?= $Profile_Data['id']; ?>'>Warn <?= $Profile_Data['Username']; ?></a></div>
          <div style='border-right: 1px solid <?= $Border_Color; ?>; float: left; padding: 3px; width: 25%;'><a style='display: block;' href='ban_user.php?<?= $Profile_Data['id']; ?>'>Ban <?= $Profile_Data['Username']; ?></a></div>
          <div style='border-right: 1px solid <?= $Border_Color; ?>; float: left; padding: 3px; width: 25%;'><a style='display: block;' href='edit_user.php?<?= $Profile_Data['id']; ?>'>Edit <?= $Profile_Data['Username']; ?></a></div>
          <div style='float: left; padding: 3px; width: 25%;'><a style='display: block;' href='logs_user.php?id=<?= $Profile_Data['id']; ?>'><?= $Profile_Data['Username']; ?>'s Logs</a></div>
        </div>
      </div>
    </div>
    <?php
      }
    ?>

    <div class='row'>
      <div class='panel' style='float: left; margin-right: 0.5%; width: 49.75%;'>
        <div class='panel-heading'><?= $Profile_Data['Username']; ?> - #<?= number_format($Profile_Data['id']); ?></div>
        <div class='panel-body' style='padding: 5.5px;'>
          <div style='float: left; width: 35%;'>
            <img src='<?= $Profile_Data['Avatar']; ?>' />
          </div>
          <div style='float: left; width: 65%;'>
            <?= $UserClass->DisplayUserRank($Profile_Data['id']); ?>
            <div style='font-size: 12px;'>
              <b>Joined On:</b> <?= date("F j, Y (g:i A)", $Profile_Data['Date_Registered']); ?>
            </div>
            <div style='font-size: 12px;'>
              <b>Last Active:</b> <?= lastseen($Profile_Data['Last_Active'], 'week'); ?>
            </div>
            <?php
              $Playtime_Sec = $Profile_Data['Playtime'];
              if ($Playtime_Sec == 0) {
                $Playtime_Is = "None";
              } elseif ($Playtime_Sec <= 59) {
                $Playtime_Is = $Playtime_Sec." Second(s)";
              } elseif ($Playtime_Sec >= 60 && $Playtime_Sec <= 3599) {
                $Playtime_Is = floor($Playtime_Sec / 60)." Minute(s)";
              } elseif ($Playtime_Sec >= 3600 && $Playtime_Sec <= 86399) {
                $Playtime_Is = round($Playtime_Sec / 3600, 1)." Hour(s)";
              } else {
                $Playtime_Is = round($Playtime_Sec / 86400, 2)." Day(s)";
              }
            ?>
            <div style='font-size: 12px;'><b>Playtime:</b> <?= $Playtime_Is; ?></div>
            <?php
              if ( $Calc_Difference / 60 < 15 )
              {
                echo "<div style='color: #00ff00; font-size: 18px;'>Online</div>";
              }
              else
              {
                echo "<div style='color: #ff0000; font-size: 18px;'>Offline</div>";
              }
            ?>
          </div>
        </div>
      </div>

      <div class='panel' style='float: left; margin-bottom: 6px; width: 49.75%;'>
        <div class='panel-heading'>Interactions</div>
        <div class='panel-body interactions'>
          <div>
            <div style='float: left; padding: 3px; width: 50%;'><a href='<?= Domain(1); ?>/trades.php?Action=Create&ID=<?= $Profile_Data['id'] ?>' style='display: block;'>Trade With <?= $Profile_Data['Username']; ?></a></div>
            <div style='float: left; padding: 3px; width: 50%;'><a href='#' style='display: block;'>Message <?= $Profile_Data['Username']; ?></a></div>
            <div style='float: left; padding: 3px; width: 50%;'><a href='battle_create.php?Battle=Trainer&Foe=<?= $Profile_Data['id']; ?>' style='display: block;'>Battle <?= $Profile_Data['Username']; ?></a></div>
            <div style='float: left; padding: 3px; width: 50%;'><a href='#' style='display: block;'>Report <?= $Profile_Data['Username']; ?></a></div>
          </div>
        </div>
      </div>

      <div class='panel' style='float: left; width: 49.75%;'>
        <div class='panel-heading'>Navigation</div>
        <div class='panel-body navi'>
          <div>
            <div style='float: left; padding: 2px; width: calc(100% / 3);'><a href='javascript:void(0);' onclick='profileTab("Roster");' style='display: block;'>Roster</a></div>
            <div style='float: left; padding: 2px; width: calc(100% / 3);'><a href='javascript:void(0);' onclick='profileTab("Box");' style='display: block;'>Box</a></div>
            <!--<div style='float: left; padding: 2px; width: 25%;'><a href='javascript:void(0);' onclick='profileTab("Inventory");' style='display: block;'>Inventory</a></div>-->
            <div style='float: left; padding: 2px; width: calc(100% / 3);'><a href='javascript:void(0);' onclick='profileTab("Stats");' style='display: block;'>Statistics</a></div>
          </div>
        </div>
      </div>
    </div>

    <div class='row' id='profileAJAX' style='margin-top: 5px;'>
      <div class='panel'>
        <div class='panel-heading'>Loading</div>
        <div class='panel-body'>Loading</div>
      </div>
    </div>
  </div>
</div>

<script type='text/javascript'>
  $(function()
  {
    profileTab('Roster');
  });

  function profileTab(tab)
  {
    $('#profileAJAX').html("<div class='panel'><div class='panel-heading'>Loading</div><div class='panel-body' style='padding: 5px;'>Loading</div></div>");
    $.get('core/ajax/profile/' + tab + '.php', { id: '<?= $_GET['id']; ?>' }, function(data)
    {
      $('#profileAJAX').html(data);
      $("img.popup.cboxElement").colorbox({ iframe: true, innerWidth: 680, innerHeight: 491 });
    });
  }

  var currentFilter = [
    0,0,0,0
  ];

  function filterSelect(row, type)
  {
    console.log(this);
    switch (row)
    {
      case 1: var Cells = ['normal', 'shiny', 'soulless', 'forest', 'cloud', 'spirit']; break;
      case 2: var Cells = ['m','f','g','?']; break;
      case 3: var Cells = ['level','pokedex','id','abc','iv','item']; break;
      case 4: var Cells = ['asc','desc']; break;
    }

    for (var i = 0; i < Cells.length; ++i)
    {
      $('#'+row+'_'+Cells[i]).attr('class', 'searchColor').css({"color":"", "cursor":"pointer"});
    }

    if (currentFilter[row-1] != type)
    {
      currentFilter[row-1] = type;
    }
    else
    {
      currentFilter[row-1] = 0;
    }
  }
</script>

<?php
  }
  else
  {
    echo "
      <div class='content'>
        <div class='head'>Nonexistent Profile</div>
        <div class='box'>
          <div class='error'>This user does not exist.</div>
        </div>
      </div>
    ";
  }
  require 'core/required/layout_bottom.php';
?>