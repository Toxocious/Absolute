from datetime import datetime
import json
import requests

def Generate_MySQL(table, data):
  with open('updated_moves.sql', 'a', encoding='utf-8') as f:
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

def ProcessMove(Move, Total):
  Time_Started = datetime.now().timestamp()
  
  Move_URL = 'https://pokeapi.co/api/v2/move/{}'.format(Move)
  Move_API_Req = requests.get(Move_URL)
  Move_API_Res = Move_API_Req.json() if Move_API_Req and Move_API_Req.status_code == 200 else None

  if Move_API_Res:
    print('Processing Move #{} of {}. '.format(Move, Total), end='\r')
    Move_Data = {}
    Move_Data['Accuracy'] = Move_API_Res['accuracy']
    Move_Data['Effect_Chance'] = Move_API_Res['effect_chance']
    if Move_API_Res['effect_entries'] is not None:
      for Move_Effect in Move_API_Res['effect_entries']:
        if Move_Effect['language']['name'] == 'en':
          Move_Data['Effect_Short'] = Move_Effect['short_effect']
    Move_Data['ID'] = Move_API_Res['id']
    if Move_API_Res['names'] is not None:
      for Names in Move_API_Res['names']:
        if Names['language']['name'] == 'en':
          Move_Data['Name'] = Names['name']
    Move_Data['Power'] = Move_API_Res['power']
    Move_Data['PP'] = Move_API_Res['pp']
    Move_Data['Priority'] = Move_API_Res['priority']
    Move_Data['Move_Type'] = Move_API_Res['type']['name'].capitalize()
    Move_Data['Target'] = Move_API_Res['target']['name'].capitalize()
    if Move_API_Res['stat_changes'] is not None:
      for Stat_Change in Move_API_Res['stat_changes']:
        Move_Data[Stat_Change['stat']['name'].capitalize()+'_Boost'] = Stat_Change['change']
    if Move_API_Res['damage_class'] is not None:
      Move_Data['Damage_Type'] = Move_API_Res['damage_class']['name'].capitalize()
    if Move_API_Res['meta'] is not None:
      Move_Data['Ailment'] = Move_API_Res['meta']['ailment']['name'].capitalize()
      Move_Data['Ailment_Chance'] = Move_API_Res['meta']['ailment_chance']
      Move_Data['Category'] = Move_API_Res['meta']['category']['name'].capitalize()
      Move_Data['Crit_Chance'] = Move_API_Res['meta']['crit_rate']
      Move_Data['Drain'] = Move_API_Res['meta']['drain']
      Move_Data['Flinch_Chance'] = Move_API_Res['meta']['flinch_chance']
      Move_Data['Healing'] = Move_API_Res['meta']['healing']
      Move_Data['Max_Hits'] = Move_API_Res['meta']['max_hits']
      Move_Data['Max_Turns'] = Move_API_Res['meta']['max_turns']
      Move_Data['Min_Hits'] = Move_API_Res['meta']['min_hits']
      Move_Data['Min_Turns'] = Move_API_Res['meta']['min_turns']
      Move_Data['Stat_Chance'] = Move_API_Res['meta']['stat_chance']

    Generate_MySQL('moves', Move_Data).encode('ascii', 'xmlcharrefreplace')
    
    Time_Ended = datetime.now().timestamp()
    print('({}ms)'.format((Time_Ended - Time_Started) * 1000));
    return Move_Data


# https://pokeapi.co/api/v2/move/pursuit/
# https://pokeapi.co/api/v2/move/1/
Base_URL = 'https://pokeapi.co/api/v2/move/'
API_Req = requests.get(Base_URL)
API_Res = API_Req.json() if API_Req and API_Req.status_code == 200 else None

if API_Res:
  print("Beginning PokeAPI Move Parsing (" + datetime.now().strftime("%d-%b-%Y (%H:%M:%S.%f)") + ")")
  for Move in range(1, API_Res['count']):
    Fetch_Move = ProcessMove(Move, API_Res['count'])

  print("Finished PokeAPI Moves Parsing (" + datetime.now().strftime("%d-%b-%Y (%H:%M:%S.%f)") + ")")
