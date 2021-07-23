<?php
  if ( !isset($_POST['Battle_ID']) )
  {
    $Output['Message'] = [
      'Type' => 'Error',
      'Text' => 'Your Battle ID is not set.'
    ];
  }

  require_once '../../battles/classes/battle.php';
  require_once '../../core/required/session.php';

  $Fight = $_SESSION['Battle']['Battle_Type'];
  $Battle = new $Fight();

  if ( $_POST['Battle_ID'] != $_SESSION['Battle']['Battle_ID'] )
  {
    $_SESSION['Battle']['Protection']['Battle_ID'] = 'Invalid Postcode';
  }

  $Output = [
    'Time_Started' => $_SESSION['Battle']['Time_Started'],
    'Battle_Type' => $_SESSION['Battle']['Battle_Type'],
    'Started' => $_SESSION['Battle']['Started'],
    'Battle_ID' => $_SESSION['Battle']['Battle_ID'],
    'Turn_ID' => $_SESSION['Battle']['Turn_ID'],
  ];

  /**
   * Process the desired battle action.
   */
  if
  (
    isset($_POST['Action']) &&
    $_POST['Action'] != 'null' &&
    isset($_POST['Data']) &&
    $_POST['Data'] != 'null'
  )
  {
    if ( isset($_POST['Client_X']) )
      $_SESSION['Battle']['Input']['Client_X'] = Purify($_POST['Client_X']);

    if ( isset($_POST['Client_Y']) )
      $_SESSION['Battle']['Input']['Client_Y'] = Purify($_POST['Client_Y']);

    if ( isset($_POST['Input_Type']) )
      $_SESSION['Battle']['Input']['Type'] = Purify($_POST['Input_Type']);

    if ( isset($_POST['Is_Trusted']) )
      $_SESSION['Battle']['Input']['Is_Trusted'] = Purify($_POST['Is_Trusted']);

    if ( isset($_POST['In_Focus']) )
      $_SESSION['Battle']['Logging']['In_Focus'] = Purify($_POST['In_Focus']);

    $Action = Purify($_POST['Action']);
    $Data = Purify($_POST['Data']);

    $Turn_Data = $Battle->ProcessTurn($Action, $Data);

    $Output['Message'] = $Turn_Data;
  }
  else
  {
    if ( isset($_SESSION['Battle']['Dialogue']) )
    {
      $Output['Message'] = $_SESSION['Battle']['Dialogue'];
    }
    else
    {
      $Output['Message'] = [
        'Type' => 'Success',
        'Text' => 'The battle has begun.'
      ];
    }
  }

  foreach ( ['Ally', 'Foe'] as $Side )
  {
    $Output[$Side] = $_SESSION['Battle'][$Side];
  }

  $_SESSION['Battle']['Dialogue'] = $Output['Message'];

  echo json_encode($Output);
