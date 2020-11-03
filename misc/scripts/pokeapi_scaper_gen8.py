import json
import requests
import pokebase
from pprint import pprint

def generate_mysql_insert(table, data):
    insert = "INSERT INTO " + table + " ("

    for key in data:
        insert += key + ", "
    insert = insert[:-2]
    insert += ") VALUES ("

    for key in data:
        insert += "'" + str(data[key]) + "', "
    insert = insert[:-2]
    insert += ");"
    
    return insert

def process_pokemon(pokemon, pokemon_species):
    poke_data = {}

    poke_id = -1
    poke_dex_nums = pokemon.get('pokedex_numbers')
    for dex in poke_dex_nums:
        if dex['pokedex']['name'] == 'national':
            poke_id = dex['entry_number']

    print("Fetching the data for Pokemon #", poke_id)

    poke_data['Poke_ID'] = poke_id
    poke_data['Alt_ID'] = 0

    pokemon_name = ''
    for name in pokemon.get('names'):
        if name['language']['name'] == "en":
            pokemon_name = name['name']

    poke_data['Name'] = pokemon_name

    if pokemon_species['name'] == pokemon['name']:
        poke_data['Forme'] = ''
        poke_data['Alt_ID'] = 0
    else:
        poke_data['Forme'] = pokemon['name'].capitalize()
        poke_data['Alt_ID'] = -1

    type_1 = 'None'
    type_2 = 'None'
    for poke_type in pokemon_species.get('types'):
        if poke_type['slot'] == 1:
            type_1 = poke_type['type']['name'].capitalize()
        elif poke_type['slot'] == 2:
            type_2 = poke_type['type']['name'].capitalize()
    
    poke_data['Type_1'] = type_1
    poke_data['Type_2'] = type_2

    ability_1 = 'None'
    ability_2 = 'None'
    hidden_ability = 'None'
    for ability in pokemon_species.get('abilities'):
        if ability['slot'] == 1:
            ability_1 = ability['ability']['name'].capitalize()
        elif ability['slot'] == 2:
            ability_2 = ability['ability']['name'].capitalize()
        elif ability['slot'] == 3:
            hidden_ability = ability['ability']['name'].capitalize()

    poke_data['Ability_1'] = ability_1
    poke_data['Ability_2'] = ability_2
    poke_data['Ability_Hidden'] = hidden_ability

    hp = 0
    hp_ev = 0
    attack = 0
    attack_ev = 0
    defense = 0
    defense_ev = 0
    spattack = 0
    spattack_ev = 0
    spdefense = 0
    spdefense_ev = 0
    speed = 0
    speed_ev = 0

    for stat in pokemon_species.get('stats'):
        if stat['stat']['name'] == 'speed':
            speed = stat['base_stat']
            speed_ev = stat['effort']
        elif stat['stat']['name'] == 'special-defense':
            spdefense = stat['base_stat']
            spdefense_ev = stat['effort']
        elif stat['stat']['name'] == 'special-attack':
            spattack = stat['base_stat']
            spattack_ev = stat['effort']
        elif stat['stat']['name'] == 'defense':
            defense = stat['base_stat']
            defense_ev = stat['effort']
        elif stat['stat']['name'] == 'attack':
            attack = stat['base_stat']
            attack_ev = stat['effort']
        else:
            hp = stat['base_stat']
            hp_ev = stat['effort']

    poke_data['HP'] = hp
    poke_data['Attack'] = attack
    poke_data['Defense'] = defense
    poke_data['SpAttack'] = spattack
    poke_data['SpDefense'] = spdefense
    poke_data['Speed'] = speed

    poke_data['EV_Yield'] = str(hp_ev) + "," + str(attack_ev) + "," + str(defense_ev) + "," + str(spattack_ev) + "," + str(spdefense_ev) + "," + str(speed_ev)

    if pokemon['gender_rate'] == -1:
        male = 0
        female = 0
        genderless = 100
    else:
        male = 100 - (pokemon['gender_rate'] * 12.5)
        female = pokemon['gender_rate'] * 12.5
        genderless = 0

    poke_data['Male'] = male
    poke_data['Female'] = female
    poke_data['Genderless'] = genderless

    poke_data['Catch_Rate'] = pokemon['capture_rate']
    poke_data['Base_Happiness'] = pokemon['base_happiness']
    poke_data['Egg_Steps'] = (pokemon['hatch_counter'] + 1) * 255

    egg_group_1 = 'None'
    egg_group_2 = 'None'
    for egg_group in pokemon.get('egg_groups'):
        if egg_group_1 == 'None':
            egg_group_1 = egg_group['name'].capitalize()
        elif egg_group_2 == 'None':
            egg_group_2 = egg_group['name'].capitalize()
        else:
            print("ERROR ; EGG GROUP ; ERROR")

    poke_data['Egg_Group_1'] = egg_group_1
    poke_data['Egg_Group_2'] = egg_group_2

    poke_data['Weight'] = pokemon_species['Weight']
    poke_data['Height'] = pokemon_species['Height']

    classification = ''
    for genus in pokemon.get('genera'):
        if genus['language']['name'] == "en":
            classification = genus['genus']

    poke_data['Classification'] = classification

    return poke_data

pokemon_url = "https://pokeapi.co/api/v2/pokemon/{}"
for i in range(1, 898): 
    pokemon_species = requests.get(pokemon_url.format(i))
    pokemon_species = pokemon_species.json()
    pokemon_species_data = pokemon_species.get('species')

    for k, v in pokemon_species_data.items():
        if k == 'url':
            pokemon = requests.get(v)
            pokemon = pokemon.json()
            poke_data = process_pokemon(pokemon, pokemon_species)
            
            print(generate_mysql_insert('pokedex', poke_data).encode('ascii', 'xmlcharrefreplace'))
