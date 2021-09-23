from datetime import datetime
import json
import requests

def Generate_MySQL(table, data):
  with open('updated_pokedex.sql', 'a', encoding='utf-8') as f:
    insert = "\nINSERT INTO " + table + " ("

    if data is not None:
      for key in data:
          insert += key + ", "
      insert = insert[:-2]
      insert += ") VALUES ("

    if data is not None:
      for key in data:
          insert += '"' + str(data[key]) + '", '
      insert = insert[:-2]
      insert += ");"

    f.write(insert)

    return insert

def ProcessPokedex(Pokedex, Species, Pokedex_ID, Alt_ID):
  Pokedex_Data = {}

  Pokedex_Data['Pokedex_ID'] = Pokedex_ID
  Pokedex_Data['Alt_ID'] = Alt_ID

  for name in Species['names']:
    if name['language']['name'] == 'en':
      Pokedex_Data['Pokemon'] = name['name']

  if Pokedex['species']['name'] != Pokedex['name']:
    Forme = Pokedex['name'].split('-')[1]
    Pokedex_Data['Forme'] = "(" + Forme.capitalize() + ")"

  Pokedex_Data['Exp_Yield'] = Pokedex['base_experience']
  Pokedex_Data['Height'] = Pokedex['height']
  Pokedex_Data['Weight'] = Pokedex['weight']

  Pokedex_Data['Order'] = Pokedex['order']

  for i in range(len(Pokedex['abilities'])):
    Ability = Pokedex['abilities'][i]['ability']['name']
    Ability = Ability.replace('-', ' ').title()
    if not Pokedex['abilities'][i]['is_hidden']:
      Pokedex_Data['Ability_{}'.format(Pokedex['abilities'][i]['slot'])] = Ability
    else:
      Pokedex_Data['Hidden_Ability'] = Ability

  for Stat in Pokedex['stats']:
    StatName = ''
    if Stat['stat']['name'] == "hp":
      StatName = "HP"
    elif Stat['stat']['name'] == "special-attack":
      StatName = "SpAttack"
    elif Stat['stat']['name'] == "special-defense":
      StatName = "SpDefense"
    elif Stat['stat']['name'] == 'attack':
      StatName = 'Attack'
    elif Stat['stat']['name'] == 'defense':
      StatName = 'Defense'
    elif Stat['stat']['name'] == 'speed':
      StatName = 'Speed'
    Pokedex_Data[StatName] = Stat['base_stat']
    Pokedex_Data['EV_{}'.format(StatName)] = str(Stat['effort'])

  for Type in Pokedex['types']:
    Pokedex_Data['Type_{}'.format(Type['slot'])] = Type['type']['name'].capitalize()

  Pokedex_Data['Catch_Rate'] = Species['capture_rate']
  Pokedex_Data['Base_Happiness'] = Species['base_happiness']
  Pokedex_Data['Egg_Cycles'] = Species['hatch_counter']
  Pokedex_Data['Is_Baby'] = Species['is_baby']
  Pokedex_Data['Is_Legendary'] = Species['is_legendary']
  Pokedex_Data['Is_Mythical'] = Species['is_mythical']

  if Species['gender_rate'] == -1:
    Pokedex_Data['Male'] = 0
    Pokedex_Data['Female'] = 0
    Pokedex_Data['Genderless'] = 100
  else:
    Pokedex_Data['Male'] = 100 - (Species['gender_rate'] * 12.5)
    Pokedex_Data['Female'] = Species['gender_rate'] * 12.5
    Pokedex_Data['Genderless'] = 0

  for i in range(len(Species['egg_groups'])):
    Pokedex_Data['Egg_Group_{}'.format(i)] = Species['egg_groups'][i]['name'].capitalize()

  Generate_MySQL('pokedex', Pokedex_Data).encode('ascii', 'xmlcharrefreplace')

  return Pokedex_Data



print("Beginning PokeAPI Pokedex Parsing (" + datetime.now().strftime("%d-%b-%Y (%H:%M:%S.%f)") + ")")

for Pokedex in range(1, 899):
  Spec_URL = 'https://pokeapi.co/api/v2/pokemon-species/{}'.format(Pokedex)
  Spec_Req = requests.get(Spec_URL)
  Spec_Res = Spec_Req.json() if Spec_Req and Spec_Req.status_code == 200 else None
  if Spec_Res:
    for Variety in range(len(Spec_Res['varieties'])):
      Poke_URL = 'https://pokeapi.co/api/v2/pokemon/{}'.format(Spec_Res['varieties'][Variety]['pokemon']['name'])
      Poke_Req = requests.get(Poke_URL)
      Poke_Res = Poke_Req.json() if Poke_Req and Poke_Req.status_code == 200 else None
      if Poke_Res:
        Time_Started = datetime.now().timestamp()
        print('Processing Pokedex #{} Alternate Forme #{}. '.format(Pokedex, Variety), end='\r')

        Fetch_Pokedex = ProcessPokedex(Poke_Res, Spec_Res, Pokedex, Variety)

        Time_Ended = datetime.now().timestamp()
        print('({}ms)'.format((Time_Ended - Time_Started) * 1000));

print("Finished PokeAPI Pokedex Parsing (" + datetime.now().strftime("%d-%b-%Y (%H:%M:%S.%f)") + ")")
