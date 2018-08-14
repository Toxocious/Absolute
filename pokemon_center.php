<?php
	require_once 'php/layout_top.php';
?>

<style>
	.active { background: #253047 !important; color: #fff !important; }
</style>

<div class='content'>
	<div class='head'>Pokemon Center</div>
	<div class='box pokecenter'>
		<div class='nav'>
			<div onclick="showTab('Roster');" class='active'>Roster</div>
			<div onclick="showTab('Inventory');">Inventory</div>
			<div onclick="showTab('Nickname');">Nickname</div>
			<div onclick="showTab('Release');">Release</div>
    </div>
    
    <div class='row' id='pokemon_center'>
      <div class='panel' style='float: left; margin-right: 5px; width: calc(50% - 5px);'>
        <div class='panel-heading'>Roster</div>
        <div class='panel-body'>
          <?php showRoster("{$User_Data['id']}", 'Pokecenter', 'Box'); ?>
        </div>
      </div>

      <div class='panel' style='float: left; margin-bottom: 5px; width: 50%;'>
        <div class='panel-heading'>Box</div>
        <div class='panel-body' style='padding: 3px;'>
          <?php
            $Fetch_Box = mysqli_query($con, "SELECT * FROM pokemon WHERE Owner_Current = '" . $row['id'] . "' AND Slot = 7 LIMIT 50");
              
            while ( $Query_Box = mysqli_fetch_assoc($Fetch_Box) )
            {
              showImage('icon', $Query_Box['ID'], 'pokemon', 'Stats');
            }

            if ( mysqli_num_rows($Fetch_Box) == 0 ) {
              echo	"<div style='padding: 5px;'>There are no Pokemon in your box.</div>";
            }
          ?>
        </div>
      </div>

      <div class='panel' style='float: right; width: 50%;'>
        <div class='panel-heading'>Selected Pokemon</div>
        <div class='panel-body' style='padding: 3px;' id='dataDiv'>
          Please select a Pokemon to view their statistics.
        </div>
      </div>
    </div>
  </div>
</div>

<script type='text/javascript'>
	let navDivs = $('.pokecenter .nav div');
	for ( let i = 0; i < navDivs.length; i++ )
	{
		navDivs[i].addEventListener("click", function()
		{
			let current = document.getElementsByClassName("active");
			current[0].className = current[0].className.replace("active", "");
			this.className += "active";
		});
	}

	function showTab(tab)
	{
		$.get('ajax/pokecenter.php', { tab: tab }, function(data)
		{
			$('#pokemon_center').html(data);
		});
	}


	function inventoryTab(page, req, item_tab)
	{
		$.get('ajax/pokecenter.php', { page: page, req: req, item_tab: item_tab }, function(data)
		{
			$('#activeTab').html(data);
		});
	}
	
	function showData(page, req, id)
	{
		console.log("Page: " + page + "\nRequest: " + req + "\nPokemon ID: " + id);

		$.get('ajax/pokecenter.php', { page: page, req: req, id: id }, function(data)
		{
			$('#dataDiv').html(data);
		});
	}

	function changeSlot(req, id, slot)
	{
		console.log("Request: " + req + "\\ID: " + id + "\nSlot: " + slot);

		$.post('ajax/pokecenter.php', { req: req, id: id, slot, slot }, function(data)
		{
			$('#pokemon_center').html(data);
		});
	}

	function changeNick(req, id, slot)
	{
		$.post('ajax/pokecenter.php', { req: req, id: id, nickname: $('#nickname').val(), slot: slot }, function(data)
		{
			console.log("Request: " + req + "\nID: " + id + "\nNickname: " + nickname + "\nSlot: " + slot);
			$('#dataDiv').html(data);
		});
	}

	function selectItem(page, req, id)
	{
		$.get('ajax/pokecenter.php', { page: page, req: req, id: id }, function(data)
		{
			$('#dataDiv').html(data);
		});
	}
</script>

<?php
	require_once 'php/layout_bottom.php';
?>