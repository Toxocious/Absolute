<?php 
  require 'php/required/db.php';
	require 'php/core/global_functions.php';
?>

<div class='content'>
	<div class='head'>Pokemon Center</div>
	<div class='box pokecenter'>
		<div class='nav'>
			<div onclick="showTab('Roster')">Roster</div>
			<div onclick="showTab('Bag')">Bag</div>
			<div onclick="showTab('Nickname')">Nickname</div>
			<div onclick="showTab('Release')">Release</div>
    </div>
    
    <div class='row' id='pokemon_center'>
      <div class='panel' style='float: left; margin-right: 5px; width: calc(50% - 5px);'>
        <div class='panel-heading'>Roster</div>
        <div class='panel-body'>
          <?php showRoster("{$User_Data['id']}", 'Pokecenter'); ?>
        </div>
      </div>

      <div class='panel' style='float: left; margin-bottom: 5px; width: 50%;'>
        <div class='panel-heading'>Box</div>
        <div class='panel-body' style='padding: 3px;'>
          <?php
            $Fetch_Box = mysqli_query($con, "SELECT * FROM pokemon WHERE Owner_Current = '" . $row['id'] . "' AND Slot = 7 LIMIT 50");
              
            while ( $Query_Box = mysqli_fetch_assoc($Fetch_Box) )
            {
              showImage('icon', $Query_Box['ID'], 'pokemon');
            }

            if ( mysqli_num_rows($Fetch_Box) == 0 )
            {
              echo	"<div style='padding: 5px;'>There are no Pokemon in your box.</div>";
            }
          ?>
        </div>
      </div>

      <div class='panel' style='float: right; width: 50%;'>
        <div class='panel-heading'>Selected Pokemon</div>
        <div class='panel-body' style='padding: 3px;'>
          selected pokemon
        </div>
      </div>
    </div>
  </div>
</div>

<script type='text/javascript'>
	function showTab(tab)
	{
		$.get('ajax/pokecenter.php', { tab: tab }, function(data)
		{
			$('#pokemon_center').html(data);
		});
	}
</script>

<?php 
  require 'php/required/layout_bottom.php';
?>