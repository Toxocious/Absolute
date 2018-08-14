<?php
    require 'layout_top.php';

    $Profile_Info = mysqli_fetch_assoc(mysqli_query($con, "SELECT * FROM members WHERE id = '" . $_GET['id'] . "'"));
	$Pokemon_Info = mysqli_fetch_assoc(mysqli_query($con, "SELECT * FROM pokemon WHERE Owner_Current = '" . $_GET['id'] . "'"));
	$Profile_Check = mysqli_num_rows(mysqli_query($con, "SELECT * FROM members WHERE id = '" . $_GET['id'] . "'"));
	
    if ( $Profile_Check == 1 )
    {
?>

<div class='content'>
    <div class='head'><?php echo "Profile of {$Profile_Info['Username']}"; ?></div>
    <div class='box profile'>
      <div class='row'>
        <div class='left-col'>
            <div class='panel'>
                <div class='panel-heading'>
                  <?php echo $Profile_Info['Username'] . " - #" . $Profile_Info['id']; ?>              
                </div>
                <div class='panel-body'>
                    <?php echo "<img src='{$Profile_Info['Avatar']}' /><br />"; ?>
                    <?php echo "<img src='images/Assets/{$Profile_Info['Gender']}.svg' style='height: 20px; margin-left: -70px; margin-top: -78px; position: absolute; width: 20px;' />"; ?>
                    <?php
                      if ( $Profile_Info['Rank'] === '12' ) {
                        echo	"<span class='cmod'>Chat Moderator</span><br />";
                      }
                      if ( $Profile_Info['Rank'] === '69' ) {
                        echo	"<span class='gmod'>Global Moderator</span><br />";
                      }
                      if ( $Profile_Info['Rank'] === '420' ) {
                        echo	"<span class='admin'>Administrator</span><br />";
                      }
                    ?>
                    <div class='info'>
                        <div>Last Active</div>
                        <div>
                          <?php
                            if ( $Profile_Info['Last_Online'] !== null )
                            {
                              echo $Profile_Info['Last_Online'];
                            }
                            else
                            {
                              echo "Unknown";
                            }
                          ?>
                        </div>
                    </div>
                    <div class='info'>
                        <div>Joined On</div>
                        <div><?php echo $Profile_Info['Date_Registered']; ?></div>
                    </div>
                </div>
            </div>

            <?php
              if ( $row['Rank'] >= 69 ) {
            ?>
            <div class='panel' style='margin-top: 3px;'>
              <div class='panel-heading'>Staff Options</div>
              <div class='panel-body interactions' style='border-top: none;'>
                <a href='warn_user.php?id=<?php echo $Profile_Info['id']; ?>'>Warn <?php echo $Profile_Info['Username']; ?></a>
                <a href='ban_user.php?<?php echo $Profile_Info['id']; ?>'>Ban <?php echo $Profile_Info['Username']; ?></a>
                <a href='edit_user.php?<?php echo $Profile_Info['id']; ?>'>Edit <?php echo $Profile_Info['Username']; ?></a>
                <a href='logs_user.php?id=<?php echo $Profile_Info['id']; ?>'><?php echo $Profile_Info['Username']; ?>'s Logs</a>
              </div>
            </div>
            <?php
              }
            ?>

            <div class='panel' style='margin-top: 3px;'>
                <div class='panel-heading'>Interactions</div>
                <div class='panel-body interactions'>
                  <a href='messages.php?id=<?php echo $Profile_Info['id']; ?>'>Message <?php echo $Profile_Info['Username']; ?></a>
                  <a href='trade_create.php?id=<?php echo $Profile_Info['id']; ?>'>Trade <?php echo $Profile_Info['Username']; ?></a>
                  <a href='report_user.php?id=<?php echo $Profile_Info['id']; ?>'>Report <?php echo $Profile_Info['Username']; ?></a>
                </div>
            </div>
        </div>

        <div class='right-col'>
            <div class='nav'>
                <a href='javascript:void(0);' onclick='showProfile("roster", <?php echo $Profile_Info['id']; ?>)'>Roster</a>
                <a href='javascript:void(0);' onclick='showProfile("box", <?php echo $Profile_Info['id']; ?>)'>Box</a>
                <a href='javascript:void(0);' onclick='showProfile("stats", <?php echo $Profile_Info['id']; ?>)'>Stats</a>
            </div>

            <div class='panel'>
                <div class='panel-heading'>Tab Title</div>
                <div class='panel-body'>
                    Tab Data
                </div>
            </div>
        </div>
            </div>
    </div>
</div>

<script type='text/javascript'>
	function showProfile(request, id) {
		$('.right-col').prepend("<div class='description'>Loading..</div>");
		
		$.ajax({
			type: 'POST',
			url: 'ajax/ajax_profiles.php',
			data: { request: request, id: id },
			success: function(data) {
				$('.right-col').html(data);
        $('.description').hide();
			},
			error: function(data) {
				$('.description').html(data);
			}
		});
  }
  
  function displayPokemon(id) {
		$('.overlay').css({ "visibility":"visible" });

		$.ajax({
			type: 'post',
			url: 'ajax/ajax_profiles.php',
			data: { request: 'pokemon_stats', id: id },
			success: function(data) {
				$('.overlay').css({ "display":"none" });
				$('#selectedPokemon').html(data);
			},
			error: function() {
				$('.overlay').css({ "display":"none" });
				$('#selectedPokemon').html('An error has occurred while retrieving this Pokemon\'s data.<br /> Please contact <a href=\'profile.php?id=1\'>Toxocious</a> or <a href=\'profile.php?id=2\'>Ephenia</a>.');
			}
		});
	}
	
	$(function() {
		showProfile('roster', <?php echo $Profile_Info['id']; ?>);
	});
</script>

<?php
    }
    else 
    {
?>

<div class='content'>
    <div class='head'>Profile</div>
    <div class='box'>
        <font style='color: #ff0000;'>There is no data on the profile of this ID.</font>
    </div>
</div>

<?php
    }

    require 'layout_bottom.php';
?>