<?php
  require_once $_SERVER['DOCUMENT_ROOT'] . '/core/required/session.php';
  require_once $_SERVER['DOCUMENT_ROOT'] . '/staff/functions/edit_moves.php';
  require_once $_SERVER['DOCUMENT_ROOT'] . '/staff/functions/spawn_pokemon.php';

  $Pokedex_ID = null;
  if ( !empty($_GET['Pokedex_ID']) )
    $Pokedex_ID = Purify($_GET['Pokedex_ID']);

  try
  {
    $Get_Pokedex_Entry_Data = $PDO->prepare("
      SELECT `ID`
      FROM `pokedex`
      WHERE `ID` = ?
      LIMIT 1
    ");
    $Get_Pokedex_Entry_Data->execute([ $Pokedex_ID ]);
    $Get_Pokedex_Entry_Data->setFetchMode(PDO::FETCH_ASSOC);
    $Pokedex_Entry_Data = $Get_Pokedex_Entry_Data->fetch();
  }
  catch ( PDOException $e )
  {
    HandleError($e);
  }

  if ( empty($Pokedex_ID) || empty($Pokedex_Entry_Data) )
  {
    echo json_encode([
      'Success' => false,
      'Message' => "The Pok&eacute;mon that you have requested doesn't exist.",
    ]);

    exit;
  }

  if ( !empty($_GET['Action']) && in_array($_GET['Action'], ['Show', 'Spawn']) )
    $Action = Purify($_GET['Action']);

  if ( empty($Action) )
  {
    echo json_encode([
      'Success' => false,
      'Message' => 'An invalid action was selected.',
    ]);

    exit;
  }

  $Recipient = null;
  if ( !empty($_GET['Recipient']) )
    $Recipient = Purify($_GET['Recipient']);

  $Creation_Location = null;
  if ( !empty($_GET['Creation_Location']) )
    $Creation_Location = Purify($_GET['Creation_Location']);

  $Level = null;
  if ( !empty($_GET['Level']) )
    $Level = Purify($_GET['Level']);

  $Frozen = null;
  if ( !empty($_GET['Frozen']) )
    $Frozen = Purify($_GET['Frozen']);

  $Gender = null;
  if ( !empty($_GET['Gender']) )
    $Gender = Purify($_GET['Gender']);

  $Type = null;
  if ( !empty($_GET['Type']) )
    $Type = Purify($_GET['Type']);

  $Nature = null;
  if ( !empty($_GET['Nature']) )
    $Nature = Purify($_GET['Nature']);

  $Ability = null;
  if ( !empty($_GET['Ability']) )
    $Ability = Purify($_GET['Ability']);

  $IV_HP = null;
  if ( !empty($_GET['IV_HP']) )
    $IV_HP = Purify($_GET['IV_HP']);

  $IV_Attack = null;
  if ( !empty($_GET['IV_Attack']) )
    $IV_Attack = Purify($_GET['IV_Attack']);

  $IV_Defense = null;
  if ( !empty($_GET['IV_Defense']) )
    $IV_Defense = Purify($_GET['IV_Defense']);

  $IV_Sp_Attack = null;
  if ( !empty($_GET['IV_Sp_Attack']) )
    $IV_Sp_Attack = Purify($_GET['IV_Sp_Attack']);

  $IV_Sp_Defense = null;
  if ( !empty($_GET['IV_Sp_Defense']) )
    $IV_Sp_Defense = Purify($_GET['IV_Sp_Defense']);

  $IV_Speed = null;
  if ( !empty($_GET['IV_Speed']) )
    $IV_Speed = Purify($_GET['IV_Speed']);

  $EV_HP = null;
  if ( !empty($_GET['EV_HP']) )
    $EV_HP = Purify($_GET['EV_HP']);

  $EV_Attack = null;
  if ( !empty($_GET['EV_Attack']) )
    $EV_Attack = Purify($_GET['EV_Attack']);

  $EV_Defense = null;
  if ( !empty($_GET['EV_Defense']) )
    $EV_Defense = Purify($_GET['EV_Defense']);

  $EV_Sp_Attack = null;
  if ( !empty($_GET['EV_Sp_Attack']) )
    $EV_Sp_Attack = Purify($_GET['EV_Sp_Attack']);

  $EV_Sp_Defense = null;
  if ( !empty($_GET['EV_Sp_Defense']) )
    $EV_Sp_Defense = Purify($_GET['EV_Sp_Defense']);

  $EV_Speed = null;
  if ( !empty($_GET['EV_Speed']) )
    $EV_Speed = Purify($_GET['EV_Speed']);


  switch ( $Action )
  {
    case 'Show':
      $Spawn_Table = ShowSpawnPokemonTable($Pokedex_ID);

      echo json_encode([
        'Spawn_Table' => $Spawn_Table,
      ]);
      break;

    case 'Spawn':
      $Spawn_Pokedex_Entry = SpawnPokemon(
        $Pokedex_ID,
        $Recipient,
        $Creation_Location,
        $Level,
        $Frozen,
        $Gender,
        $Type,
        $Nature,
        $Ability,
        $IV_HP,
        $IV_Attack,
        $IV_Defense,
        $IV_Sp_Attack,
        $IV_Sp_Defense,
        $IV_Speed,
        $EV_HP,
        $EV_Attack,
        $EV_Defense,
        $EV_Sp_Attack,
        $EV_Sp_Defense,
        $EV_Speed
      );

      echo json_encode([
        'Success' => $Spawn_Pokedex_Entry['Success'],
        'Message' => $Spawn_Pokedex_Entry['Message'],
        'Spawn_Pokemon_Table' => ShowSpawnPokemonTable($Pokedex_ID),
      ]);
      break;
  }
