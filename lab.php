<?php
  include 'layout_top.php';
?>

<div class='content'>
  <div class='head'>Pokemon Lab</div>
  <div class='box laboratory'>
    <div class='description' style='margin-bottom: 3px; margin-top: 0px; width: 100%;'>
      Welcome to the Pokemon Lab!<br />
      We find some eggs on occasion, and you'll be able to pick some up here.
    </div>

    <div class='panel panel-default'>
      <div class='panel-heading'>
        <div style='float: left; font-style: normal !important;'>
          There are 
          <?php 
            $Egg_Count = mysqli_num_rows(mysqli_query($con, "SELECT * FROM lab"));
            echo number_format($Egg_Count);
          ?> 
          eggs in the lab.
        </div>
        <div style='margin-right: 190px !important;'>
          Available Eggs
        </div>
        <div id='refresh' style='float: left; margin-left: 97.5%; margin-top: -20px;'>
          <img src='images/Assets/options.png' onclick='refreshLab();' style='height: 22px; width: 22px;' />
        </div>
      </div>
      <div class='panel-body' id='lab_eggs'>
        <?php
          $Fetch_Egg = mysqli_query($con, "SELECT `ID` FROM `lab` ORDER BY RAND() LIMIT 5;");
          
          foreach ( $Fetch_Egg as $ID )
          {
            $Egg_Data = mysqli_fetch_assoc(mysqli_query($con, "SELECT * FROM `lab` WHERE `ID` = {$ID['ID']}"));

            echo "
              <div class='lab-egg' onclick='getEgg({$ID['ID']});'>
                <img src='images/Pokemon/egg.png' />
              </div>
            ";
          }
        ?>
      </div>
    </div>
  </div>
</div>

<script type='text/javascript'>
  function getEgg(id)
  {
    $('#lab_eggs').html("<div style='padding: 5px;'><div class='spinner' style='left: 48%; position: relative;'></div></div>");
    $.post("ajax/lab.php", { request: 'get', id: id }, function(data, status)
    {
      $('.laboratory').html(data);
      console.log(status);
    });
  }

  function refreshLab()
  {
    $('#lab_eggs').html("<div style='padding: 5px;'><div class='spinner' style='left: 48%; position: relative;'></div></div>");
    $.get("ajax/lab.php", { request: 'refresh' }, function(data, status)
    {
      $('#lab_eggs').html(data);
    });
  }
</script>

<?php
  include 'layout_bottom.php';
?>