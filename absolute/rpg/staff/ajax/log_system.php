<?php
  require_once $_SERVER['DOCUMENT_ROOT'] . '/core/required/session.php';

  $Log_Type = 'Pokemon';
  if ( !empty($_GET['Log_Type']) && in_array($_GET['Log_Type'], ['Battle', 'Map', 'Trade', 'Shop', 'Login']) )
    $Log_Type = Purify($_GET['Log_Type']);

  $User = 1;
  if ( !empty($_GET['Log_User']) )
    $User = Purify($_GET['Log_User']);

  $Log_Limit = 2000;
  if ( !empty($_GET['Log_Limit']) )
    $Log_Limit = Purify($_GET['Log_Limit']);

  $User_Info = $User_Class->FetchUserData($User);
  if ( !$User_Info )
  {
    echo "This user does not exist.";
    exit;
  }
?>

<br />
<h3><?= $User_Info['Username']; ?> &mdash; <?= $Log_Type; ?> Logs</h3>
<img src='<?= $User_Info['Avatar']; ?>' />

<?php
  switch ( $Log_Type )
  {
    case 'Battle':
      require_once $_SERVER['DOCUMENT_ROOT'] . '/staff/ajax/logs/battle.php';
      break;

    case 'Map':
      require_once $_SERVER['DOCUMENT_ROOT'] . '/staff/ajax/logs/map.php';
      break;

    case 'Shop':
      require_once $_SERVER['DOCUMENT_ROOT'] . '/staff/ajax/logs/shop.php';
      break;

    case 'Trade':
      require_once $_SERVER['DOCUMENT_ROOT'] . '/staff/ajax/logs/trades.php';
      break;

    default:
      require_once $_SERVER['DOCUMENT_ROOT'] . '/staff/ajax/logs/battle.php';
      break;
  }
