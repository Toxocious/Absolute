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

    SwitchPokemon: (Slot) =>
    {
      if ( !Slot )
        return false;

      Battle.HandleRequest(`Action=Switch&Slot=${Slot}`);
    },

    RenderRoster: (Side, Roster, Active) =>
    {
      if
      (
        typeof Side === undefined ||
        typeof Roster === undefined ||
        typeof Active === undefined
      )
        return;

      document.querySelector(`[slot='${Side}_Active'] > img`).setAttribute('src', Active.Sprite);
      document.querySelector(`[slot='${Side}_Name']`).innerHTML = Active.Display_Name;
      document.querySelector(`[slot='${Side}_HP']`).innerHTML = Active.HP.toLocaleString();
      document.querySelector(`[slot='${Side}_Max_HP']`).innerHTML = Active.Max_HP.toLocaleString();
      document.querySelector(`[slot='${Side}_Level']`).innerHTML = Active.Level.toLocaleString();

      document.querySelector(`[slot='${Side}_HP_Bar']`).setAttribute('style', `width: ` + ((Active.HP / Active.Max_HP) * 100) + `%`);
      document.querySelector(`[slot='${Side}_Exp_Bar']`).setAttribute('style', `width: ${Active.Exp_Needed.Percent}%`);
      document.querySelector(`[slot='${Side}_Exp_Needed']`).innerHTML = Active.Exp_Needed.Exp.toLocaleString();


      if ( Active.Fainted )
      {
        document.querySelector(`[slot='${Side}_Active'] > img`).setAttribute('style', 'filter: grayscale(100%);');
      }

      for ( let i = 0; i < Roster.length; i++ )
      {
        document.querySelector(`[slot='${Side}_Slot_${i}'] > img`).setAttribute('src', Roster[i].Icon);

        if ( Roster[i].Active )
        {
          document.querySelector(`[slot='${Side}_Slot_${i}']`).closest('div[class]').style.boxShadow = 'inset 0 0 4px 2px red';
        }
        else
        {
          document.querySelector(`[slot='${Side}_Slot_${i}']`).closest('div[class]').style.boxShadow = '';
        }

        if ( Roster[i].Fainted )
        {
          document.querySelector(`[slot='${Side}_Slot_${i}'] > img`).setAttribute('style', 'filter: grayscale(100%);');
          document.querySelector(`[slot='${Side}_Slot_${i}']`).style.background = '#444';
        }
        else
        {
          if ( Side == 'Ally' )
          {
            document.querySelector(`[slot='${Side}_Slot_${i}']`).setAttribute('onclick', `Battle.SwitchPokemon(${Roster[i].Slot});`);
          }
        }
      }
    },

    HandleRequest: (Data, Callback) =>
    {
      console.log(Data);
      if ( !this.Loading )
      {
        this.ID = '<?= $_SESSION['Battle']['Battle_ID']; ?>';
        this.Loading = true;

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

            this.Loading = false;

            if ( req.status === 200 )
            {
              Battle.RenderRoster('Ally', JSON_Data.Ally.Roster, JSON_Data.Ally.Active);
              Battle.RenderRoster('Foe', JSON_Data.Foe.Roster, JSON_Data.Foe.Active);

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
