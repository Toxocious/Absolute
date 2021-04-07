<?php
  if ( isset($_GET['Battle_ID']) )
  {
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
      'Message' => [
        'Type' => 'Success',
        'Text' => 'Successfully procced battle handler.',
      ],
    ];

    foreach ( ['Ally', 'Foe'] as $Side )
    {
      $Output[$Side] = $_SESSION['Battle'][$Side];
    }
  }
  else
  {
    $Output['Message'] = [
      'Type' => 'Error',
      'Text' => 'Your Battle ID is not set.'
    ];
  }

  echo json_encode($Output);
