<?php
  if ( !isset($_GET['Battle_ID']) )
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

  if ( $_GET['Battle_ID'] != $_SESSION['Battle']['Battle_ID'] )
  {
    $Output = null;

    return;
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
  if ( isset($_GET['Action']) )
  {
    $Action = Purify($_GET['Action']);

    $Turn_Data = $Battle->ProcessTurn($Action);

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
