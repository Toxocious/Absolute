function ShowPokemon()
{
  const Pokemon_Value = document.getElementsByName('Pokemon_Search')[0].value;
  if ( typeof Pokemon_Value === 'undefined' )
    return;

  let Form_Data = new FormData();
  Form_Data.append('Pokemon_Value', Pokemon_Value);
  Form_Data.append('Pokemon_Action', 'Show');

  SendRequest('modify_pokemon', Form_Data)
    .then((Pokemon_Data) => {
      Pokemon_Data = JSON.parse(Pokemon_Data);

      document.getElementById('Modification_Table').innerHTML = Pokemon_Data.Modification_Table;
    })
    .catch((Error) => console.error('Error:', Error));
}
