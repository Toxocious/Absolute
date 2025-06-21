<?php
  require_once 'core/required/session.php';

  if ( empty($_SESSION['EvoChroniclesRPG']['Battle']) )
  {
    require_once 'core/required/layout_top.php';

    echo "
      <div class='panel content'>
        <div class='head'>Battle</div>
        <div class='body' style='padding: 5px;'>
          <div class='error' style='margin: 5px auto 5px;'>
            A battle has not yet been created.
          </div>
        </div>
      </div>
    ";

    require_once 'core/required/layout_bottom.php';
    exit;
  }

  if ( isset($_SESSION['EvoChroniclesRPG']['Battle']['Battle_Layout']) )
    $User_Data['Battle_Theme'] = $_SESSION['EvoChroniclesRPG']['Battle']['Battle_Layout'];

  switch ($User_Data['Battle_Theme'])
  {
    case 'Default':
      require_once 'battles/themes/default.php';
      break;
    case 'Debug':
      require_once 'battles/themes/debug.php';
      break;
    case 'Simple':
      require_once 'battles/themes/simple.php';
      break;
    case 'iFrame':
      require_once 'battles/themes/iframe.php';
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
    In_Focus: null,

    OnPageLoad: () =>
    {
      Battle.HandleRequest(null, null, null);
    },

    Attack: (Move, event) =>
    {
      if ( typeof Move === 'undefined' )
        return;

      event = event || window.event;

      Battle.HandleRequest('Attack', Move, event);
    },

    Continue: (Postcode, event) =>
    {
      if ( typeof Postcode === 'undefined' )
        return false;

      event = event || window.event;

      Battle.HandleRequest('Continue', Postcode, event);
    },

    Restart: (Postcode, event) =>
    {
      if ( typeof Postcode === 'undefined' )
        return false;

      event = event || window.event;

      Battle.HandleRequest('Restart', Postcode, event);
    },

    SwitchPokemon: (Slot, event) =>
    {
      if ( typeof Slot === 'undefined' )
        return false;

      event = event || window.event;

      Battle.HandleRequest('Switch', Slot, event);
    },

    UseItem: (Item, Slot, event) =>
    {
      if ( typeof Item === 'undefined' || typeof Slot === 'undefined' )
        return false;

      event = event || window.event;

      Battle.HandleRequest('UseItem', {Item: Item, Slot: Slot}, event);
    },

    OpenBag: (event) =>
    {
      event = event || window.event;

      Battle.HandleRequest('Bag', event);
    },

    RenderRoster: (Side, Roster, Active, Battle_Layout) =>
    {
      if
      (
        typeof Side === 'undefined' ||
        typeof Roster === 'undefined' ||
        typeof Active === 'undefined'
      )
        return;

      document.querySelector(`[slot='${Side}_Active'] > img`).setAttribute('src', Active.Sprite);
      document.querySelector(`[slot='${Side}_Name']`).innerHTML = Active.Display_Name;
      document.querySelector(`[slot='${Side}_HP']`).innerHTML = Active.HP.toLocaleString();
      document.querySelector(`[slot='${Side}_Max_HP']`).innerHTML = Active.Max_HP.toLocaleString();
      document.querySelector(`[slot='${Side}_Level']`).innerHTML = Active.Level.toLocaleString();
      document.querySelector(`[slot='${Side}_HP_Bar']`).setAttribute('style', `width: ` + ((Active.HP / Active.Max_HP) * 100) + `%`);
      document.querySelector(`[slot='${Side}_Exp_Bar']`).setAttribute('style', `width: ${Active.Exp_Needed.Percent}%`);
      document.querySelector(`[slot='${Side}_Exp_Needed']`).innerHTML = Active.Exp_Needed.Exp.toLocaleString(undefined, {maximumFractionDigits: 0});

      for ( Active_Stat in Active.Stats )
      {
        const Current_Stat = Active.Stats[Active_Stat];
        document.querySelector(`[slot='${Side}_${Active_Stat}_Mod']`).innerHTML = Current_Stat.Mod.toLocaleString(undefined, {maximumFractionDigits: 2, minimumFractionDigits: 2});

        if ( Current_Stat.Mod === 1 )
        {
          document.querySelector(`[slot='${Side}_${Active_Stat}_Mod']`).parentNode.style.color = '#fff';
          document.querySelector(`[slot='${Side}_${Active_Stat}_Entity']`).innerHTML = '';
        }
        else if ( Current_Stat.Mod > 1 )
        {
          document.querySelector(`[slot='${Side}_${Active_Stat}_Mod']`).parentNode.style.color = '#00ff00';
          document.querySelector(`[slot='${Side}_${Active_Stat}_Entity']`).innerHTML = '&utrif;';
        }
        else
        {
          document.querySelector(`[slot='${Side}_${Active_Stat}_Mod']`).parentNode.style.color = '#ff0000';
          document.querySelector(`[slot='${Side}_${Active_Stat}_Entity']`).innerHTML = '&dtrif;';
        }

      }

      if ( Active.Fainted )
      {
        document.querySelector(`[slot='${Side}_Active'] > img`).setAttribute('style', 'filter: grayscale(100%);');
      }
      else
      {
        document.querySelector(`[slot='${Side}_Active'] > img`).setAttribute('style', '');

        if ( Active?.Statuses?.hasOwnProperty('Transformed') )
          document.querySelector(`[slot='${Side}_Active'] > img`).setAttribute('style', 'filter: drop-shadow(1px 1px 4px purple)');
      }

      for ( let i = 0; i < Roster.length; i++ )
      {
        document.querySelector(`[slot='${Side}_Slot_${i}'] > img`).setAttribute('src', Roster[i].Icon);

        if ( Battle_Layout == 'Simple' )
          document.querySelector(`[slot='${Side}_Slot_${i}'] > span`).innerText = Roster[i].Display_Name;

        if ( Roster[i].Active )
        {
          if ( Battle_Layout == 'Simple' )
            document.querySelector(`[slot='${Side}_Slot_${i}']`).style.border = 'border-bottom: 1px solid var(--color-primary);';
          else
            document.querySelector(`[slot='${Side}_Slot_${i}']`).closest('div[class]').style.boxShadow = 'inset 0 0 4px 2px red';
        }
        else
        {
          if ( Battle_Layout == 'Simple' )
            document.querySelector(`[slot='${Side}_Slot_${i}']`).style.border = '';
          else
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
            document.querySelector(`[slot='${Side}_Slot_${i}']`).setAttribute('onclick', `Battle.SwitchPokemon(${Roster[i].Slot}, event);`);
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
      if ( typeof Moves === 'undefined' )
        return false;

      for ( let i = 0; i < Moves.length; i++ )
      {
        document.querySelector(`[move='Move_${i}']`).setAttribute('onmousedown', `Battle.Attack(${i + 1}, event)`);
        document.querySelector(`[move='Move_${i}']`).setAttribute('class', Moves[i].Move_Type);
        document.querySelector(`[move='Move_${i}']`).value = Moves[i].Name;
        document.querySelector(`[move='Move_${i}']`).disabled = Moves[i].Disabled;
      }
    },

    RenderCurrencies: (Money, Abso_Coins) =>
    {
      if
      (
        typeof Money === 'undefined' ||
        typeof Abso_Coins === 'undefined'
      )
        return false;

      if ( document.getElementById('user_money') )
        document.getElementById(`user_money`).innerHTML = Money.toLocaleString(undefined, {maximumFractionDigits: 0});

      if ( document.getElementById('user_abso_coins') )
        document.getElementById(`user_abso_coins`).innerHTML = Abso_Coins.toLocaleString(undefined, {maximumFractionDigits: 0});
    },

    RenderWeather: (Weather) =>
    {
      const Weather_Element = document.querySelector('[slot="Battle_Weather"]');

      if ( typeof Weather === 'undefined' )
      {
        Weather_Element.innerHTML = '';
        return;
      }

      let Weather_Name;
      switch (Weather.Name)
      {
        case 'Hail':
          Weather_Name = 'hail';
          break;

        case 'Extremely Harsh Sunlight':
        case 'Harsh Sunlight':
          Weather_Name = 'sun';
          break;

        case 'Heavy Rain':
        case 'Rain':
          Weather_Name = 'rain';
          break;

        case 'Sandstorm':
          Weather_Name = 'sands';
          break;
      }

      Weather_Element.innerHTML = `<img src='./images/Battle/weather_${Weather_Name}.png' />`;
    },

    RenderFieldEffects: (Field_Effects) =>
    {
      let Render_Data;
      if ( Field_Effects === null )
      {
        document.getElementById('Ally_Field_Effects').innerText = 'No Active Field Effects';
        document.getElementById('Foe_Field_Effects').innerText = 'No Active Field Effects';
      }
      else
      {
        for ( Field_Side in Field_Effects )
        {
          document.getElementById(`${Field_Side}_Field_Effects`).innerText = '';

          const Processing_Side = Field_Effects[Field_Side];
          Processing_Side.forEach((Index) =>
          {
            const Field_Name = Index.Name;

            document.getElementById(`${Field_Side}_Field_Effects`).innerHTML += `
              <img alt='${Index.Name} Icon' src='./images/Battle/${Field_Name}.png' style='height: 24px; width: 40px;' />
            `;
          });
        }
      }
    },

    HandleRequest: (Action, Data = null, Data_Event = null) =>
    {
      if ( !Battle.Loading )
      {
        Battle.ID = '<?= $_SESSION['EvoChroniclesRPG']['Battle']['Battle_ID']; ?>';
        Battle.Loading = true;

        const Data_Val = new FormData();
        Data_Val.append('Battle_ID', Battle.ID);
        Data_Val.append('In_Focus', Battle.In_Focus);

        if ( Action )
          Data_Val.append('Action', Action);

        if ( Data )
        {
          Data_Val.append('Data', JSON.stringify(Data));
        }

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

            Battle.Loading = false;

            if ( req.status === 200 )
            {
              Battle.RenderMoves(JSON_Data.Ally.Active.Moves);
              Battle.RenderRoster('Ally', JSON_Data.Ally.Roster, JSON_Data.Ally.Active, JSON_Data.Battle_Layout);
              Battle.RenderRoster('Foe', JSON_Data.Foe.Roster, JSON_Data.Foe.Active, JSON_Data.Battle_Layout);

              Battle.RenderWeather(JSON_Data.Weather);
              Battle.RenderFieldEffects(JSON_Data.Field_Effects);
              Battle.RenderCurrencies(JSON_Data.Ally.Money, JSON_Data.Ally.Abso_Coins);

              if ( JSON_Data.Message.Type == 'Success' )
                document.getElementById('BattleDialogue').innerHTML = JSON_Data.Message.Text;
              else
                document.getElementById('BattleDialogue').innerHTML = `<div class='error' style='margin: 0 auto;'>${JSON_Data.Message.Text}</div>`;

              resolve(req.response);
            }
            else
            {
              if ( JSON_Data.Message.Type == 'Success' )
                document.getElementById('BattleDialogue').innerHTML = JSON_Data.Message.Text;
              else
                document.getElementById('BattleDialogue').innerHTML = `<div class='error' style='margin: 0 auto;'>${JSON_Data.Message.Text}</div>`;

              reject(Error(req.statusText))
            }
          };
        });
      }
    },
  };

  document.addEventListener("visibilitychange", () =>
  {
    Battle.In_Focus = document.hidden ? false : true;
  });

  window.onload = Battle.OnPageLoad();
</script>
