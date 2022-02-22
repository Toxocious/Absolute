/**
 * Update the selected pokemon counter.
 */
function UpdateSelectedPokemonCounter()
{
  const Selected_Pokemon = document.querySelectorAll('#Releasable_Pokemon :checked');

  document.getElementById('Total_Selected_Pokemon').innerText = Selected_Pokemon.length;
}

/**
 * Get the total number of releasable Pokemon that the user has.
 */
async function GetReleasablePokemon()
{
  let Form_Data = new FormData();
  Form_Data.append('Action', 'Get_Releasable_Pokemon');

  await SendRequest('release', Form_Data)
    .then((Releasable_Pokemon) => {
      Releasable_Pokemon = JSON.parse(Releasable_Pokemon)[0];

      document.getElementById(`Total_Releasable_Pokemon`).innerText = Releasable_Pokemon.Amount ?? '0';

      if ( Releasable_Pokemon.Pokemon.length > 0 )
      {
        document.getElementById('Release_Button').disabled = false;
        document.getElementById('Release_Button').setAttribute('onclick', 'SelectPokemonForRelease()');

        for ( const Pokemon of Releasable_Pokemon.Pokemon )
        {
          document.getElementById('Releasable_Pokemon').innerHTML += `
            <option value='${Pokemon.ID}' style='padding: 3px;'>
              ${Pokemon.Display_Name} ${Pokemon.Gender.substr(0, 1)} (Level: ${Pokemon.Level})
            </option>
          `;
        }
      }
    })
    .catch((Error) => console.error('Error:', Error));
}

/**
 * Allow the user to double check the Pokemon that they've selected for release.
 */
async function SelectPokemonForRelease()
{
  const Releasable_Pokemon_Element = document.getElementById('Releasable_Pokemon');

  const Selected_Pokemon_Amount = Releasable_Pokemon_Element.value;
  if ( Selected_Pokemon_Amount < 1 )
    return;

  let Selected_Pokemon = [];
  for ( const Pokemon of Releasable_Pokemon_Element.options )
  {
    if ( Pokemon.selected )
    {
      Selected_Pokemon.push(Pokemon.value);
    }
  }

  let Form_Data = new FormData();
  Form_Data.append('Action', 'Process_Selected_Pokemon');
  Form_Data.append('Selected_Pokemon', JSON.stringify(Selected_Pokemon));

  await SendRequest('release', Form_Data)
    .then((Processed_Pokemon) => {
      Processed_Pokemon = JSON.parse(Processed_Pokemon);
      console.log(Processed_Pokemon);

      if ( Processed_Pokemon.Success != null )
      {
        document.getElementById('Pokemon_Center_Moves_AJAX').className = Processed_Pokemon.Success ? 'success' : 'error';
        document.getElementById('Pokemon_Center_Moves_AJAX').innerHTML = Processed_Pokemon.Message;
      }

      if ( Processed_Pokemon.Pokemon != null )
      {
        document.getElementById('Release_Page_1').style.display = 'none';
        document.getElementById('Release_Page_2').style.display = 'block';

        let Processed_Pokemon_Text = '';
        for ( const Pokemon of Processed_Pokemon.Pokemon )
        {
          Processed_Pokemon_Text += `
            <div style='width: 250px;'>
              ${Pokemon.Display_Name} ${Pokemon.Gender} (Level: ${Pokemon.Level})
            </div>
          `;
        }

        document.getElementById('Release_Page_2').innerHTML = `
          <h3>Selected Pok&eacute;mon For Release</h3>

          <br />
          <hr class='faded' />
          <br />

          <div style='display: flex; flex-direction: row; flex-wrap: wrap; gap: 5px; justify-content: center;'>
            ${Processed_Pokemon_Text}
          </div>

          <br />
          <hr class='faded' />
          <br />

          <button onclick='FinalizeRelease();'>
            Release Pok&eacute;mon
          </button>
        `;
      }
    })
    .catch((Error) => console.error('Error:', Error));
}

/**
 * Finalize the releasing of the selected Pokemon.
 */
async function FinalizeRelease()
{
  let Form_Data = new FormData();
  Form_Data.append('Action', 'Release_Pokemon');

  await SendRequest('release', Form_Data)
    .then((Release_Pokemon) => {
      Release_Pokemon = JSON.parse(Release_Pokemon);

      document.getElementById('Pokemon_Center_Release_AJAX').className = Release_Pokemon.Success ? 'success' : 'error';
      document.getElementById('Pokemon_Center_Release_AJAX').innerHTML = Release_Pokemon.Message;

      document.getElementById('Release_Page_1').style.display = 'block';
      document.getElementById('Release_Page_2').style.display = 'none';
      document.getElementById('Release_Page_2').innerText = '';

      GetReleasablePokemon();
    })
    .catch((Error) => console.error('Error:', Error));
}
