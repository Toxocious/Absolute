/**
 * Show a table allowing for edits to the specified item's database fields.
 */
function ShowMoveEntry()
{
  let Form_Data = new FormData();
  Form_Data.append('Action', 'Show');

  const Selected_Move_ID = document.getElementsByName('move_entries')[0].value;
  Form_Data.append('Move_ID', Selected_Move_ID);

  SendRequest('edit_moves', Form_Data)
    .then((Move_Entry) => {
      const Move_Entry_Data = JSON.parse(Move_Entry);

      if ( typeof Move_Entry_Data.Success !== 'undefined' )
      {
        document.getElementById('Edit_Move_AJAX').className = Move_Entry_Data.Success ? 'success' : 'error';
        document.getElementById('Edit_Move_AJAX').innerHTML = Move_Entry_Data.Message;
      }

      if ( typeof Move_Entry_Data.Move_Edit_Table !== 'undefined' )
        document.getElementById('Edit_Move_Table').innerHTML = Move_Entry_Data.Move_Edit_Table;
    });
}

/**
 * Update the specified move and its battle flags in the database.
 *
 * @param Move_ID
 */
function UpdateMoveData(Move_ID)
{
  let Form_Data = new FormData();
  Form_Data.append('Action', 'Update');
  Form_Data.append('Move_ID', Move_ID);

  const Name = document.getElementsByName('Name')[0].value;
  const Class_Name = document.getElementsByName('Class_Name')[0].value;
  const Accuracy = document.getElementsByName('Accuracy')[0].value;
  const Power = document.getElementsByName('Power')[0].value;
  const Priority = document.getElementsByName('Priority')[0].value;
  const PP = document.getElementsByName('PP')[0].value;
  const Damage_Type = document.getElementsByName('Damage_Type')[0].value;
  const Move_Type = document.getElementsByName('Move_Type')[0].value;
  const Category = document.getElementsByName('Category')[0].value;
  const Ailment = document.getElementsByName('Ailment')[0].value;
  const Flinch_Chance = document.getElementsByName('Flinch_Chance')[0].value;
  const Crit_Chance = document.getElementsByName('Crit_Chance')[0].value;
  const Effect_Chance = document.getElementsByName('Effect_Chance')[0].value;
  const Ailment_Chance = document.getElementsByName('Ailment_Chance')[0].value;
  const HP_Boost = document.getElementsByName('HP_Boost')[0].value;
  const Attack_Boost = document.getElementsByName('Attack_Boost')[0].value;
  const Defense_Boost = document.getElementsByName('Defense_Boost')[0].value;
  const Sp_Attack_Boost = document.getElementsByName('Sp_Attack_Boost')[0].value;
  const Sp_Defense_Boost = document.getElementsByName('Sp_Defense_Boost')[0].value;
  const Speed_Boost = document.getElementsByName('Speed_Boost')[0].value;
  const Accuracy_Boost = document.getElementsByName('Accuracy_Boost')[0].value;
  const Evasion_Boost = document.getElementsByName('Evasion_Boost')[0].value;
  const Min_Hits = document.getElementsByName('Min_Hits')[0].value;
  const Max_Hits = document.getElementsByName('Max_Hits')[0].value;
  const Min_Turns = document.getElementsByName('Min_Turns')[0].value;
  const Max_Turns = document.getElementsByName('Max_Turns')[0].value;
  const Recoil = document.getElementsByName('Recoil')[0].value;
  const Drain = document.getElementsByName('Drain')[0].value;
  const Healing = document.getElementsByName('Healing')[0].value;
  const Stat_Chance = document.getElementsByName('Stat_Chance')[0].value;
  const authentic = document.getElementsByName('authentic')[0].value;
  const bite = document.getElementsByName('bite')[0].value;
  const bullet = document.getElementsByName('bullet')[0].value;
  const charge = document.getElementsByName('charge')[0].value;
  const contact = document.getElementsByName('contact')[0].value;
  const dance = document.getElementsByName('dance')[0].value;
  const defrost = document.getElementsByName('defrost')[0].value;
  const distance = document.getElementsByName('distance')[0].value;
  const gravity = document.getElementsByName('gravity')[0].value;
  const heal = document.getElementsByName('heal')[0].value;
  const mirror = document.getElementsByName('mirror')[0].value;
  const mystery = document.getElementsByName('mystery')[0].value;
  const nonsky = document.getElementsByName('nonsky')[0].value;
  const powder = document.getElementsByName('powder')[0].value;
  const protect = document.getElementsByName('protect')[0].value;
  const pulse = document.getElementsByName('pulse')[0].value;
  const punch = document.getElementsByName('punch')[0].value;
  const recharge = document.getElementsByName('recharge')[0].value;
  const reflectable = document.getElementsByName('reflectable')[0].value;
  const snatch = document.getElementsByName('snatch')[0].value;
  const sound = document.getElementsByName('sound')[0].value;

  Form_Data.append('Name', Name);
  Form_Data.append('Class_Name', Class_Name);
  Form_Data.append('Accuracy', Accuracy);
  Form_Data.append('Power', Power);
  Form_Data.append('Priority', Priority);
  Form_Data.append('PP', PP);
  Form_Data.append('Damage_Type', Damage_Type);
  Form_Data.append('Move_Type', Move_Type);
  Form_Data.append('Category', Category);
  Form_Data.append('Ailment', Ailment);
  Form_Data.append('Flinch_Chance', Flinch_Chance);
  Form_Data.append('Crit_Chance', Crit_Chance);
  Form_Data.append('Effect_Chance', Effect_Chance);
  Form_Data.append('Ailment_Chance', Ailment_Chance);
  Form_Data.append('HP_Boost', HP_Boost);
  Form_Data.append('Attack_Boost', Attack_Boost);
  Form_Data.append('Defense_Boost', Defense_Boost);
  Form_Data.append('Sp_Attack_Boost', Sp_Attack_Boost);
  Form_Data.append('Sp_Defense_Boost', Sp_Defense_Boost);
  Form_Data.append('Speed_Boost', Speed_Boost);
  Form_Data.append('Accuracy_Boost', Accuracy_Boost);
  Form_Data.append('Evasion_Boost', Evasion_Boost);
  Form_Data.append('Min_Hits', Min_Hits);
  Form_Data.append('Max_Hits', Max_Hits);
  Form_Data.append('Min_Turns', Min_Turns);
  Form_Data.append('Max_Turns', Max_Turns);
  Form_Data.append('Recoil', Recoil);
  Form_Data.append('Drain', Drain);
  Form_Data.append('Healing', Healing);
  Form_Data.append('Stat_Chance', Stat_Chance);
  Form_Data.append('authentic', authentic);
  Form_Data.append('bite', bite);
  Form_Data.append('bullet', bullet);
  Form_Data.append('charge', charge);
  Form_Data.append('contact', contact);
  Form_Data.append('dance', dance);
  Form_Data.append('defrost', defrost);
  Form_Data.append('distance', distance);
  Form_Data.append('gravity', gravity);
  Form_Data.append('heal', heal);
  Form_Data.append('mirror', mirror);
  Form_Data.append('mystery', mystery);
  Form_Data.append('nonsky', nonsky);
  Form_Data.append('powder', powder);
  Form_Data.append('protect', protect);
  Form_Data.append('pulse', pulse);
  Form_Data.append('punch', punch);
  Form_Data.append('recharge', recharge);
  Form_Data.append('reflectable', reflectable);
  Form_Data.append('snatch', snatch);
  Form_Data.append('sound', sound);

  SendRequest('edit_moves', Form_Data)
    .then((Update_Move) => {
      const Update_Move_Data = JSON.parse(Update_Move);

      if ( typeof Update_Move_Data.Success !== 'undefined' )
      {
        document.getElementById('Edit_Move_AJAX').className = Update_Move_Data.Success ? 'success' : 'error';
        document.getElementById('Edit_Move_AJAX').innerHTML = Update_Move_Data.Message;
      }

      if ( typeof Update_Move_Data.Move_Edit_Table !== 'undefined' )
        document.getElementById('Edit_Move_Table').innerHTML = Update_Move_Data.Move_Edit_Table;
    })
    .catch((Error) => console.error('[ECRPG] There was en error while updating the selected move.', Error));
}
