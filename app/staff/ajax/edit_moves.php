<?php
  require_once $_SERVER['DOCUMENT_ROOT'] . '/core/required/session.php';
  require_once $_SERVER['DOCUMENT_ROOT'] . '/staff/functions/edit_moves.php';

  $Move_ID = null;
  if ( !empty($_GET['Move_ID']) )
    $Move_ID = Purify($_GET['Move_ID']);

  try
  {
    $Get_Move_Entry_Data = $PDO->prepare("
      SELECT `ID`
      FROM `moves`
      WHERE `ID` = ?
      LIMIT 1
    ");
    $Get_Move_Entry_Data->execute([ $Move_ID ]);
    $Get_Move_Entry_Data->setFetchMode(PDO::FETCH_ASSOC);
    $Move_Entry_Data = $Get_Move_Entry_Data->fetch();
  }
  catch ( PDOException $e )
  {
    HandleError($e);
  }

  if ( empty($Move_ID) || empty($Move_Entry_Data) )
  {
    echo json_encode([
      'Success' => false,
      'Message' => "The move entry that you have requested doesn't exist.",
    ]);

    exit;
  }

  if ( !empty($_GET['Action']) && in_array($_GET['Action'], ['Show', 'Update']) )
    $Action = Purify($_GET['Action']);

  if ( empty($Action) )
  {
    echo json_encode([
      'Success' => false,
      'Message' => 'An invalid action was selected.',
    ]);

    exit;
  }

  $Name = null;
  if ( !empty($_GET['Name']) )
    $Name = Purify($_GET['Name']);

  $Class_Name = null;
  if ( !empty($_GET['Class_Name']) )
    $Class_Name = Purify($_GET['Class_Name']);

  $Accuracy = null;
  if ( !empty($_GET['Accuracy']) )
    $Accuracy = Purify($_GET['Accuracy']);

  $Power = null;
  if ( !empty($_GET['Power']) )
    $Power = Purify($_GET['Power']);

  $Priority = null;
  if ( !empty($_GET['Priority']) )
    $Priority = Purify($_GET['Priority']);

  $PP = null;
  if ( !empty($_GET['PP']) )
    $PP = Purify($_GET['PP']);

  $Damage_Type = null;
  if ( !empty($_GET['Damage_Type']) )
    $Damage_Type = Purify($_GET['Damage_Type']);

  $Move_Type = null;
  if ( !empty($_GET['Move_Type']) )
    $Move_Type = Purify($_GET['Move_Type']);

  $Category = null;
  if ( !empty($_GET['Category']) )
    $Category = Purify($_GET['Category']);

  $Ailment = null;
  if ( !empty($_GET['Ailment']) )
    $Ailment = Purify($_GET['Ailment']);

  $Flinch_Chance = null;
  if ( !empty($_GET['Flinch_Chance']) )
    $Flinch_Chance = Purify($_GET['Flinch_Chance']);

  $Crit_Chance = null;
  if ( !empty($_GET['Crit_Chance']) )
    $Crit_Chance = Purify($_GET['Crit_Chance']);

  $Effect_Chance = null;
  if ( !empty($_GET['Effect_Chance']) )
    $Effect_Chance = Purify($_GET['Effect_Chance']);

  $Ailment_Chance = null;
  if ( !empty($_GET['Ailment_Chance']) )
    $Ailment_Chance = Purify($_GET['Ailment_Chance']);

  $HP_Boost = null;
  if ( !empty($_GET['HP_Boost']) )
    $HP_Boost = Purify($_GET['HP_Boost']);

  $Attack_Boost = null;
  if ( !empty($_GET['Attack_Boost']) )
    $Attack_Boost = Purify($_GET['Attack_Boost']);

  $Defense_Boost = null;
  if ( !empty($_GET['Defense_Boost']) )
    $Defense_Boost = Purify($_GET['Defense_Boost']);

  $Sp_Attack_Boost = null;
  if ( !empty($_GET['Sp_Attack_Boost']) )
    $Sp_Attack_Boost = Purify($_GET['Sp_Attack_Boost']);

  $Sp_Defense_Boost = null;
  if ( !empty($_GET['Sp_Defense_Boost']) )
    $Sp_Defense_Boost = Purify($_GET['Sp_Defense_Boost']);

  $Speed_Boost = null;
  if ( !empty($_GET['Speed_Boost']) )
    $Speed_Boost = Purify($_GET['Speed_Boost']);

  $Accuracy_Boost = null;
  if ( !empty($_GET['Accuracy_Boost']) )
    $Accuracy_Boost = Purify($_GET['Accuracy_Boost']);

  $Evasion_Boost = null;
  if ( !empty($_GET['Evasion_Boost']) )
    $Evasion_Boost = Purify($_GET['Evasion_Boost']);

  $Min_Hits = null;
  if ( !empty($_GET['Min_Hits']) )
    $Min_Hits = Purify($_GET['Min_Hits']);

  $Max_Hits = null;
  if ( !empty($_GET['Max_Hits']) )
    $Max_Hits = Purify($_GET['Max_Hits']);

  $Min_Turns = null;
  if ( !empty($_GET['Min_Turns']) )
    $Min_Turns = Purify($_GET['Min_Turns']);

  $Max_Turns = null;
  if ( !empty($_GET['Max_Turns']) )
    $Max_Turns = Purify($_GET['Max_Turns']);

  $Recoil = null;
  if ( !empty($_GET['Recoil']) )
    $Recoil = Purify($_GET['Recoil']);

  $Drain = null;
  if ( !empty($_GET['Drain']) )
    $Drain = Purify($_GET['Drain']);

  $Healing = null;
  if ( !empty($_GET['Healing']) )
    $Healing = Purify($_GET['Healing']);

  $Stat_Chance = null;
  if ( !empty($_GET['Stat_Chance']) )
    $Stat_Chance = Purify($_GET['Stat_Chance']);

  $authentic = null;
  if ( !empty($_GET['authentic']) )
    $authentic = Purify($_GET['authentic']);

  $bite = null;
  if ( !empty($_GET['bite']) )
    $bite = Purify($_GET['bite']);

  $bullet = null;
  if ( !empty($_GET['bullet']) )
    $bullet = Purify($_GET['bullet']);

  $charge = null;
  if ( !empty($_GET['charge']) )
    $charge = Purify($_GET['charge']);

  $contact = null;
  if ( !empty($_GET['contact']) )
    $contact = Purify($_GET['contact']);

  $dance = null;
  if ( !empty($_GET['dance']) )
    $dance = Purify($_GET['dance']);

  $defrost = null;
  if ( !empty($_GET['defrost']) )
    $defrost = Purify($_GET['defrost']);

  $distance = null;
  if ( !empty($_GET['distance']) )
    $distance = Purify($_GET['distance']);

  $gravity = null;
  if ( !empty($_GET['gravity']) )
    $gravity = Purify($_GET['gravity']);

  $heal = null;
  if ( !empty($_GET['heal']) )
    $heal = Purify($_GET['heal']);

  $mirror = null;
  if ( !empty($_GET['mirror']) )
    $mirror = Purify($_GET['mirror']);

  $mystery = null;
  if ( !empty($_GET['mystery']) )
    $mystery = Purify($_GET['mystery']);

  $nonsky = null;
  if ( !empty($_GET['nonsky']) )
    $nonsky = Purify($_GET['nonsky']);

  $powder = null;
  if ( !empty($_GET['powder']) )
    $powder = Purify($_GET['powder']);

  $protect = null;
  if ( !empty($_GET['protect']) )
    $protect = Purify($_GET['protect']);

  $pulse = null;
  if ( !empty($_GET['pulse']) )
    $pulse = Purify($_GET['pulse']);

  $punch = null;
  if ( !empty($_GET['punch']) )
    $punch = Purify($_GET['punch']);

  $recharge = null;
  if ( !empty($_GET['recharge']) )
    $recharge = Purify($_GET['recharge']);

  $reflectable = null;
  if ( !empty($_GET['reflectable']) )
    $reflectable = Purify($_GET['reflectable']);

  $snatch = null;
  if ( !empty($_GET['snatch']) )
    $snatch = Purify($_GET['snatch']);

  $sound = null;
  if ( !empty($_GET['sound']) )
    $sound = Purify($_GET['sound']);


  switch ( $Action )
  {
    case 'Show':
      $Move_Edit_Table = ShowMoveEditTable($Move_ID);

      echo json_encode([
        'Move_Edit_Table' => $Move_Edit_Table,
      ]);
      break;

    case 'Update':
      $Update_Move_Entry = UpdateMoveData(
        $Move_ID, $Name, $Class_Name, $Accuracy, $Power, $Priority, $PP, $Damage_Type, $Move_Type, $Category, $Ailment, $Flinch_Chance, $Crit_Chance, $Effect_Chance, $Ailment_Chance, $HP_Boost, $Attack_Boost, $Defense_Boost, $Sp_Attack_Boost, $Sp_Defense_Boost, $Speed_Boost, $Accuracy_Boost, $Evasion_Boost, $Min_Hits, $Max_Hits, $Min_Turns, $Max_Turns, $Recoil, $Drain, $Healing, $Stat_Chance, $authentic, $bite, $bullet, $charge, $contact, $dance, $defrost, $distance, $gravity, $heal, $mirror, $mystery, $nonsky, $powder, $protect, $pulse, $punch, $recharge, $reflectable, $snatch, $sound
      );

      echo json_encode([
        'Success' => $Update_Move_Entry['Success'],
        'Message' => $Update_Move_Entry['Message'],
        'Move_Edit_Table' => ShowMoveEditTable($Move_ID),
      ]);
      break;
  }
