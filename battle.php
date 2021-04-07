<?php
	require_once 'core/required/session.php';

	switch ($User_Data['Battle_Theme'])
	{
		case 'Default':
			require_once 'battles/themes/default.php';
			break;
		case 'Debug':
			require_once 'battles/themes/debug.php';
			break;
		default:
			require_once 'battles/themes/default.php';
			break;
	}
?>

<script type='text/javascript'>
	const Battle = {
		Loading: false,
		Ended: false,
		Bag: false,
		Clicks: [],
		ID: null,

		OnPageLoad: () =>
		{
			Battle.HandleRequest(null, null);
		},

		RenderRoster: (Side, Roster) =>
		{
			for ( let i = 0; i < Roster.length; i++ )
			{
				document.querySelector(`[slot='${Side}_Slot_${i}'] > img`).setAttribute('src', Roster[i].Icon);

				if ( Roster[i].Fainted )
					document.querySelector(`[slot='${Side}_Slot_${i}'] > img`).setAttribute('style', 'filter: grayscale(100%);');

				if ( Roster[i].Active )
				{
					document.querySelector(`[slot='${Side}_Active'] > img`).setAttribute('src', Roster[i].Sprite);
					document.querySelector(`[slot='${Side}_Name']`).innerHTML = Roster[i].Display_Name;
					document.querySelector(`[slot='${Side}_HP']`).innerHTML = Roster[i].HP;
					document.querySelector(`[slot='${Side}_Max_HP']`).innerHTML = Roster[i].Max_HP;
					document.querySelector(`[slot='${Side}_Level']`).innerHTML = Roster[i].Level.toLocaleString();

					if ( Roster[i].Fainted )
						document.querySelector(`[slot='${Side}_Active'] > img`).setAttribute('style', 'filter: grayscale(100%);');
				}
				else
				{
					if ( Side == 'Ally' )
						document.querySelector(`[slot='${Side}_Slot_${i}']`).setAttribute('onclick', `Battle.SwitchPokemon(${Roster[i].Slot});`);
				}
			}
		},

		SwitchPokemon: (Slot) =>
		{
			if ( !Slot || Slot < 2 || Slot > 5 )
				return false;

			Battle.HandleRequest(`Action=Switch&Slot=${Slot}`);
		},
		HandleRequest: (Data, Callback) =>
		{
			if ( !this.Loading )
			{
				this.ID = '<?= $_SESSION['Battle']['Battle_ID']; ?>';

				return new Promise((resolve, reject) =>
				{
					const req = new XMLHttpRequest();
					req.open('GET', `<?= DOMAIN_ROOT; ?>/battles/ajax/handler.php?Battle_ID=${this.ID}&${Data}`);
					req.send();
					req.onerror = (error) => reject(Error(`Network Error: ${error}`));
					req.onload = () =>
					{
						let JSON_Data = JSON.parse(req.response);
						console.log(JSON_Data);

						if ( req.status === 200 )
						{
							Battle.RenderRoster('Ally', JSON_Data.Ally.Roster);
							Battle.RenderRoster('Foe', JSON_Data.Foe.Roster);

							document.getElementById('BattleDialogue').innerHTML = JSON_Data.Message.Text;
							resolve(req.response);
						}
						else
						{
							document.getElementById('BattleDialogue').innerHTML = JSON_Data.Message.Text;
							reject(Error(req.statusText))
						}
					};
				});
			}
		},
	};
	
	window.onload = Battle.OnPageLoad();
</script>
