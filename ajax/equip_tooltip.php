<?php
	if ( isset($_POST['equip_id']) )
  {
    require '../session.php';

    echo "
      <style>
        .mstooltip > .mstooltip_name
        {
          background-image: url(https://absobeta.gwiddle.co.uk/images/Equipment/Assets/equip_tooltip.png);
          background-position: 0px 0px;
          background-repeat: no-repeat;
          background-size: auto;
          font-size: 14px;
          font-weight: bold;
          padding: 7px 0px 0px 20px;
          text-align: left;
        }
        
        .mstooltip > .mstooltip_potential
        {
          background-image: url(https://absobeta.gwiddle.co.uk/images/Equipment/Assets/equip_tooltip.png);
          background-position: 0px -26px;
          background-repeat: no-repeat;
          background-size: auto;
          font-size: 12px;
          padding: 5px 0px 0px;
          text-align: center;
        }
        
        .mstooltip > .mstooltip_equiptype
        {
          background-image: url(https://absobeta.gwiddle.co.uk/images/Equipment/Assets/equip_tooltip.png);
          background-position: 0px -26px;
          background-repeat: no-repeat;
          background-size: auto;
          color: #997f5c;
          font-size: 12px;
          padding: 5px 0px 0px;
          text-align: center;
        }
        
        .mstooltip > .mstooltip_icon
        {
          background-image: url(https://absobeta.gwiddle.co.uk/images/Equipment/Assets/equip_tooltip.png);
          background-position: 0px -50px;
          background-repeat: no-repeat;
          background-size: auto;
        }
        .mstooltip > .mstooltip_icon > img:nth-child(1)
        {
          image-rendering: pixelated;
          margin-left: -75px;
          margin-top: 35px;
          transform: scale(2);
        }
        .mstooltip > .mstooltip_icon > img:nth-child(2)
        {
          height: 77px;
          left: -58px;
          position: relative;
          top: 18px;
          width: 77px;
        }
        
        .mstooltip > .mstooltip_category
        {
          background-image: url(https://absobeta.gwiddle.co.uk/images/Equipment/Assets/equip_tooltip.png);
          background-position: 0px -130px;
          background-repeat: no-repeat;
          background-size: auto;
          font-size: 13px;
          padding-bottom: 10px;
          padding-left: 13px;
          padding-top: 35px;
          text-align: left;
          text-transform: capitalize;
        }
        
        .mstooltip > .mstooltip_stats > div
        {
          background-image: url(https://absobeta.gwiddle.co.uk/images/Equipment/Assets/equip_tooltip.png);
          background-position: 0px -160px;
          background-repeat: no-repeat;
          background-size: auto;
          font-size: 13px;
          padding: 2px 0px 2px 13px;
          text-align: left;
        }
        
        .mstooltip > .mstooltips_pot
        {
          background-image: url(https://absobeta.gwiddle.co.uk/images/Equipment/Assets/equip_tooltip.png);
          background-position: 0px -183px;
          background-repeat: no-repeat;
          background-size: auto;
          padding-top: 10px;
          text-align: left;
        }
        
        .mstooltip > .mstooltips_pot > div:nth-child(1)
        {
          color: skyblue;
        }
        
        .mstooltip > .mstooltips_pot > div
        {
          font-size: 13px;
          padding: 2px 0px 2px 13px;
          text-align: left;
        }
        
        .mstooltip > .mstooltips_bpot
        {
          background-image: url(https://absobeta.gwiddle.co.uk/images/Equipment/Assets/equip_tooltip.png);
          background-position: 0px -183px;
          background-repeat: no-repeat;
          background-size: auto;
          padding-top: 10px;
          text-align: left;
        }        
        
        .mstooltip > .mstooltips_bpot > div:nth-child(1)
        {
          color: skyblue;
        }
        
        .mstooltip > .mstooltips_bpot > div
        {
          background-size: auto;
          font-size: 13px;
          padding: 2px 0px 2px 13px;
          text-align: left;
        }
        
        .mstooltip > .mstooltips_bot
        {
          background: url(https://absobeta.gwiddle.co.uk/images/Equipment/Assets/equip_tooltip.png);
          background-position: 0px -100px;
          background-repeat: no-repeat;
          background-size: auto;
          text-align: left;
        }
      </style>
    ";

    $equip_data = mysqli_fetch_assoc(mysqli_query($con, "SELECT * FROM equips WHERE Auto = {$_POST['equip_id']}"));

		echo "
			<div class='mstooltip'>
				<div class='mstooltip_name'>" . $equip_data['Name'] . "</div>
				<div class='mstooltip_potential'>(Legendary Item)</div>
				<div class='mstooltip_equiptype'>Untradeable, Unique Item</div>
				<div class='mstooltip_icon'>
					<img src='https://absobeta.gwiddle.co.uk/images/Equipment/Rings/0" . $equip_data['ID'] . ".png' />
					<img src='https://absobeta.gwiddle.co.uk/images/Equipment/Assets/ItemIcon.cover.png' />
				</div>
				<div class='mstooltip_category'>Category: " . substr($equip_data['Item_Table'], 6, -1) . "</div>
				<div class='mstooltip_stats'>
					<div>STR: +100</div>
					<div>DEX: +100</div>
					<div>INT: +100</div>
					<div>LUK: +100</div>
					<div>Attack Power: +10</div>
				</div>
				<div class='mstooltips_pot'>
					<div>
						<img src='https://i.imgur.com/zWHUspD.png' /> Potential
					</div>
					<div>STR: +6</div>
					<div>DEX: +6</div>
				</div>
				<div class='mstooltips_bpot'>
					<div>
						<img src='https://i.imgur.com/zWHUspD.png' /> Bonus Potential
					</div>
					<div>STR: +6</div>
					<div>DEX: +6</div>
					<div>LUK: +6</div>
				</div>
				<img class='mstooltips_bot' />
			</div>
		";
	}