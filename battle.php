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
      Battle.HandleRequest(null, null, null);
    },

    Attack: (Move, event) =>
    {
      if ( typeof Move === undefined )
        return;

      event = event || window.event;

      Battle.HandleRequest('Attack', Move, event);
    },

    Continue: (Postcode, event) =>
    {
      if ( typeof Postcode === undefined )
        return false;

      event = event || window.event;

      Battle.HandleRequest('Continue', Postcode, event);
    },

    Restart: (Postcode, event) =>
    {
      if ( typeof Postcode === undefined )
        return false;

      event = event || window.event;

      Battle.HandleRequest('Restart', Postcode, event);
    },

    SwitchPokemon: (Slot, event) =>
    {
      if ( typeof Slot === undefined )
        return false;

      event = event || window.event;

      Battle.HandleRequest('Switch', Slot, event);
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
      else
      {
        document.querySelector(`[slot='${Side}_Active'] > img`).setAttribute('style', '');
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
          document.querySelector(`[slot='${Side}_Slot_${i}'] > img`).setAttribute('style', 'background: #444; filter: grayscale(100%);');
        }
        else
        {
          document.querySelector(`[slot='${Side}_Slot_${i}'] > img`).setAttribute('style', '');

          if ( Side == 'Ally' )
          {
            document.querySelector(`[slot='${Side}_Slot_${i}']`).setAttribute('onclick', `Battle.SwitchPokemon(${Roster[i].Slot}, e);`);
          }

          switch (Roster[i].Status)
          {
            case 'BadlyPoisoned':
            case 'Poisoned':
              document.querySelector(`[slot='${Side}_Slot_${i}']`).style.boxShadow = 'inset 0 0 4px 2px rgba(117, 80, 155, 0.7)';
              break;
            case 'Burned':
              document.querySelector(`[slot='${Side}_Slot_${i}']`).style.boxShadow = 'inset 0 0 4px 2px rgba(208, 78, 27, 0.7)';
              break;
            case 'Frozen':
              document.querySelector(`[slot='${Side}_Slot_${i}']`).style.boxShadow = 'inset 0 0 4px 2px rgba(27, 184, 208, 0.7)';
              break;
            case 'Paralyzed':
              document.querySelector(`[slot='${Side}_Slot_${i}']`).style.boxShadow = 'inset 0 0 4px 2px rgba(208, 190, 27, 0.7)';
              break;
            case 'Sleep':
              document.querySelector(`[slot='${Side}_Slot_${i}']`).style.boxShadow = 'inset 0 0 4px 2px rgba(127, 125, 108, 0.7)';
              break;
            default:
              break;
          }
        }
      }
    },

    RenderMoves: (Moves) =>
    {
      if ( typeof Moves === undefined )
        return false;

      for ( let i = 0; i < Moves.length; i++ )
      {
        document.querySelector(`[move='Move_${i}']`).setAttribute('onclick', `Battle.Attack(${i + 1}, event)`);
        document.querySelector(`[move='Move_${i}']`).setAttribute('class', Moves[i].Move_Type);
        document.querySelector(`[move='Move_${i}']`).value = Moves[i].Name;
        document.querySelector(`[move='Move_${i}']`).disabled = Moves[i].Disabled;
      }
    },

    HandleRequest: (Action, Data, Data_Event) =>
    {
      if ( !this.Loading )
      {
        this.ID = '<?= $_SESSION['Battle']['Battle_ID']; ?>';
        this.Loading = true;

        const Data_Val = new FormData();
        Data_Val.append('Battle_ID', this.ID);

        if ( Action )
          Data_Val.append('Action', Action);

        if ( Data )
          Data_Val.append('Data', Data);

        if ( Data_Event )
        {
          Data_Val.append('Is_Trusted', Data_Event.isTrusted);
          Data_Val.append('Client_X', Data_Event.clientX);
          Data_Val.append('Client_Y', Data_Event.clientY);
          Data_Val.append('Input_Type', Data_Event.type);
        }

        return new Promise((resolve, reject) =>
        {
          const req = new XMLHttpRequest();
          req.open('POST', `<?= DOMAIN_ROOT; ?>/battles/ajax/handler.php`);
          req.send(Data_Val);
          req.onerror = (error) => reject(Error(`Network Error: ${error}`));
          req.onload = () =>
          {
            let JSON_Data = JSON.parse(req.response);
            console.log(JSON_Data);

            this.Loading = false;

            if ( req.status === 200 )
            {
              Battle.RenderMoves(JSON_Data.Ally.Active.Moves);
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
