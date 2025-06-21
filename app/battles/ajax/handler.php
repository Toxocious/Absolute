<?php
  error_reporting(-1);
  ini_set('display_errors', 'On');

  require_once '../classes/battle.php';

  spl_autoload_register(function($Class)
  {
    $Battle_Directory = dirname(__DIR__, 1);
    $Class = strtolower($Class);

    if (file_exists($Battle_Directory . "/classes/{$Class}.php"))
      require_once $Battle_Directory . "/classes/{$Class}.php";

    if (file_exists($Battle_Directory . "/fights/{$Class}.php"))
      require_once $Battle_Directory . "/fights/{$Class}.php";
  });

  require_once '../../core/required/session.php';

  if ( empty($_SESSION['EvoChroniclesRPG']['Battle']) )
  {
    $Output['Message'] = 'You do not have a valid Battle session.';
    $_SESSION['EvoChroniclesRPG']['Battle']['Dialogue'] = $Output['Message'];

    echo json_encode($Output);
  }

  $Fight = $_SESSION['EvoChroniclesRPG']['Battle']['Battle_Type'];

  switch ($Fight)
  {
    case 'trainer':
      $Foe = $_SESSION['EvoChroniclesRPG']['Battle']['Foe_ID'];
      $Battle = new Trainer($User_Data['ID'], $Foe);
      break;

    default:
      $Foe = $_SESSION['EvoChroniclesRPG']['Battle']['Foe_ID'];
      $Battle = new Trainer($User_Data['ID'], $Foe);
      break;
  }

  $Output = [
    'Time_Started' => $_SESSION['EvoChroniclesRPG']['Battle']['Time_Started'],
    'Battle_Layout' => empty($_SESSION['EvoChroniclesRPG']['Battle']['Battle_Layout']) ? $User_Data['Battle_Theme'] : $_SESSION['EvoChroniclesRPG']['Battle']['Battle_Layout'],
    'Battle_Type' => $_SESSION['EvoChroniclesRPG']['Battle']['Battle_Type'],
    'Started' => $_SESSION['EvoChroniclesRPG']['Battle']['Started'],
    'Battle_ID' => $_SESSION['EvoChroniclesRPG']['Battle']['Battle_ID'],
    'Turn_ID' => $_SESSION['EvoChroniclesRPG']['Battle']['Turn_ID'],
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
    $Action = Purify($_POST['Action']);
    $Data = Purify($_POST['Data']);

    if ( isset($_POST['Battle_ID']) )
      $_SESSION['EvoChroniclesRPG']['Battle']['Logging']['Battle_ID'] = Purify($_POST['Battle_ID']);
    else
      $_SESSION['EvoChroniclesRPG']['Battle']['Logging']['Battle_ID'] = 'Battle ID - Not Sent';

    if ( isset($_POST['Client_X']) )
      $_SESSION['EvoChroniclesRPG']['Battle']['Logging']['Input']['Client_X'] = Purify($_POST['Client_X']);
    else
      $_SESSION['EvoChroniclesRPG']['Battle']['Logging']['Input']['Client_X'] = -1;

    if ( isset($_POST['Client_Y']) )
      $_SESSION['EvoChroniclesRPG']['Battle']['Logging']['Input']['Client_Y'] = Purify($_POST['Client_Y']);
    else
      $_SESSION['EvoChroniclesRPG']['Battle']['Logging']['Input']['Client_Y'] = -1;

    if ( isset($_POST['Input_Type']) )
      $_SESSION['EvoChroniclesRPG']['Battle']['Logging']['Input']['Type'] = Purify($_POST['Input_Type']);
    else
      $_SESSION['EvoChroniclesRPG']['Battle']['Logging']['Input']['Type'] = null;

    if ( isset($_POST['Is_Trusted']) )
      $_SESSION['EvoChroniclesRPG']['Battle']['Logging']['Input']['Is_Trusted'] = Purify($_POST['Is_Trusted']);
    else
      $_SESSION['EvoChroniclesRPG']['Battle']['Logging']['Input']['Is_Trusted'] = 0;

    if ( isset($_POST['In_Focus']) )
      $_SESSION['EvoChroniclesRPG']['Battle']['Logging']['In_Focus'] = Purify($_POST['In_Focus']);
    else
      $_SESSION['EvoChroniclesRPG']['Battle']['Logging']['In_Focus'] = 0;

    if ( !empty($_SESSION['EvoChroniclesRPG']['Battle']['Postcodes']) )
    {
      if ( !empty($_SESSION['EvoChroniclesRPG']['Battle']['Postcodes']['Continue']) )
        $Expected_Postcode = $_SESSION['EvoChroniclesRPG']['Battle']['Postcodes']['Continue'];
      else
        $Expected_Postcode = $_SESSION['EvoChroniclesRPG']['Battle']['Postcodes']['Restart'];

      $_SESSION['EvoChroniclesRPG']['Battle']['Logging']['Postcode'] = [
        'Expected' => $Expected_Postcode,
        'Received' => str_replace('"', "", $Data)
      ];
    }

    $Battle->Log_Data->AddAction($Action);
    if ( !empty($_SESSION['EvoChroniclesRPG']['Battle']['Postcodes']['Restart']) )
      $Battle->Log_Data->Finalize();

    $Turn_Data = $Battle->ProcessTurn($Action, $Data);

    $Output['Message'] = $Turn_Data;
  }
  else
  {
    if ( !empty($_SESSION['EvoChroniclesRPG']['Battle']['Dialogue']) )
    {
      $Output['Message'] = $_SESSION['EvoChroniclesRPG']['Battle']['Dialogue'];
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
    $Output[$Side] = $_SESSION['EvoChroniclesRPG']['Battle'][$Side];
  }

  if ( !empty($_SESSION['EvoChroniclesRPG']['Battle']['Weather']) )
  {
    $Output['Weather'] = $_SESSION['EvoChroniclesRPG']['Battle']['Weather'];
  }

  if ( !empty($_SESSION['EvoChroniclesRPG']['Battle']['Field_Effects']) )
  {
    $Output['Field_Effects'] = $_SESSION['EvoChroniclesRPG']['Battle']['Field_Effects'];
  }

  if ( !empty($_SESSION['EvoChroniclesRPG']['Battle']['Terrain']) )
  {
    $Output['Terrain'] = $_SESSION['EvoChroniclesRPG']['Battle']['Terrain'];
  }

  $_SESSION['EvoChroniclesRPG']['Battle']['Dialogue'] = $Output['Message'];

  echo json_encode($Output);
