<?php
  require_once '../../required/session.php';

  if ( !isset($_POST['Tab']) )
  {
    echo "
      <div class='error'>
        An error occurred while processing your input.<br />
        Please try again.
      </div>
    ";

    return;
  }

  $Tab = $Purify->Cleanse($_POST['Tab']);
  $Current_Page = isset($_POST['Page']) ? $Purify->Cleanse($_POST['Page']) : 1;
  $Display_Limit = 20;

  $Begin = ($Current_Page - 1) * $Display_Limit;
  if ( $Begin < 0 )
    $Begin = 1;

  /**
   * Construct the correct SQL query for the active tab.
   * Defaults to the Pokemon tab.
   */
  switch($Tab)
  {
    case 'Pokemon':
      $Rankings_Query = "SELECT `ID` FROM `pokemon` ORDER BY `Experience` DESC LIMIT {$Begin},{$Display_Limit}";
      $Rankings_Parameters = [];

      $First_Place_Query = "SELECT `ID` FROM `pokemon` ORDER BY `Experience` DESC LIMIT 1";
      $First_Place_Parameters = [];

      break;

    case 'Trainer':
      $Rankings_Query = "SELECT `id` FROM `users` ORDER BY `TrainerExp` DESC LIMIT {$Begin},{$Display_Limit}";
      $Rankings_Parameters = [];

      $First_Place_Query = "SELECT `id` FROM `users` ORDER BY `TrainerExp` DESC LIMIT 1";
      $First_Place_Parameters = [];

      break;

    default:
      $Rankings_Query = "SELECT `ID` FROM `pokemon` ORDER BY `Experience` DESC LIMIT {$Begin},{$Display_Limit}";
      $Rankings_Parameters = [];

      $First_Place_Query = "SELECT `ID` FROM `pokemon` ORDER BY `Experience` DESC LIMIT 1";
      $First_Place_Parameters = [];

      break;
  }

  /**
   * Perform the database queries.
   */
  $Fetch_Rankings = $PDO->prepare($Rankings_Query);
  $Fetch_Rankings->execute($Rankings_Parameters);
  $Fetch_Rankings->setFetchMode(PDO::FETCH_ASSOC);
  $Rankings = $Fetch_Rankings->fetchAll();

  $Fetch_First_Place = $PDO->prepare($First_Place_Query);
  $Fetch_First_Place->execute($First_Place_Parameters);
  $Fetch_First_Place->setFetchMode(PDO::FETCH_ASSOC);
  $First_Place = $Fetch_First_Place->fetch();

  /**
   * Given the current tab, fetch the first place data.
   * Defaults to the Pokemon tab.
   */
  switch($Tab)
  {
    case 'Pokemon':
      $First_Place = $Poke_Class->FetchPokemonData($First_Place['ID']);
      $First_Place_User = $User_Class->DisplayUserName($First_Place['Owner_Current'], false, true, true);
      break;

    case 'Trainer':
      $First_Place = $User_Class->FetchUserData($First_Place['id']);
      $First_Place_User = $User_Class->DisplayUserName($First_Place['ID'], false, true, true);
      break;

    default:
      $First_Place = $Poke_Class->FetchPokemonData($First_Place['ID']);
      $First_Place_User = $User_Class->DisplayUserName($First_Place['Owner_Current'], false, true, true);
      break;
  }
?>

<div style='flex-basis: 100%; width: 100%;'>
  <h3><?= $Tab; ?> Rankings</h3>
</div>

<table class='border-gradient' style='margin: 5px auto; flex-basis: 35%; width: 35%;'>
  <thead>
    <th colspan='3'>
      <b><?= (isset($First_Place['Display_Name']) ? $First_Place['Display_Name'] : $First_Place['Username']); ?></b>
    </th>
  </thead>
  <tbody>
    <tr>
      <td colspan='1' rowspan='2' style='width: 100px;'>
        <img src='<?= (isset($First_Place['Sprite']) ? $First_Place['Sprite'] : $First_Place['Avatar']); ?>' />
      </td>
      <td colspan='2'>
        <b><?= (isset($First_Place['Display_Name']) ? $First_Place['Display_Name'] : $First_Place_User); ?></b>
        <?= (isset($First_Place['Nickname']) ? "<br />( <i>{$First_Place['Nickname']}</i> )" : '') ?>
      </td>
    </tr>
    <tr>
      <td colspan='2'>
        <b>Level</b>: <?= (isset($First_Place['Level']) ? $First_Place['Level'] : $First_Place[$Tab . '_Level']); ?>
        <br />
        <b>Experience</b>: <?= (isset($First_Place['Experience']) ? $First_Place['Experience'] : $First_Place[$Tab . '_Exp']); ?>
      </td>
    </tr>
    <tr>
      <td colspan='3' style='padding: 5px;'>
        <?php
          switch($Tab)
          {
            case 'Pokemon':
              echo "
                <b>Current Owner</b>
                {$First_Place_User}
              ";

              break;
            
            case 'Trainer':
              echo $First_Place['Status'];
              
              break;

            default:
              echo "
                <b>Current Owner</b>
                {$First_Place_User}
              ";

              break;
          }
        ?>
      </td>
    </tr>
  </tbody>
</table>

<table class='border-gradient' style='margin: 5px auto; flex-basis: 70%; width: 600px;'>
  <tbody>
    <?php
      foreach ( $Rankings as $Rank_Key => $Rank_Val )
      {
        if ( $Rank_Key === 0 )
          continue;

        $Rank_Key++;

        if ( $Tab === 'Pokemon' )
        {
          $Poke_Rank_Data = $Poke_Class->FetchPokemonData($Rank_Val['ID']);
          $Username = $User_Class->DisplayUserName($Poke_Rank_Data['Owner_Current'], false, false, true);
        }
        else
        {
          $User_Rank_Data = $User_Class->FetchUserData($Rank_Val['id']);
          $Username = $User_Class->DisplayUserName($Rank_Val['id'], false, false, true);
        }

        $Display = [
          'Sprite' => (isset($Poke_Rank_Data) ? $Poke_Rank_Data['Icon'] : $User_Rank_Data['Avatar']),
          'Display_Name' => (isset($Poke_Rank_Data) ? $Poke_Rank_Data['Display_Name'] : $Username),
          'Nickname' => (isset($Poke_Rank_Data) ? "<br /><i>{$Poke_Rank_Data['Nickname']}</i>" : ''),
          'Level' => (isset($Poke_Rank_Data) ? $Poke_Rank_Data['Level'] : $User_Rank_Data['Trainer_Level']),
          'Experience' => (isset($Poke_Rank_Data) ? $Poke_Rank_Data['Experience'] : $User_Rank_Data['Trainer_Exp']),
          'Username' => (isset($Poke_Rank_Data) ? $Username : "<a href=''>Battle User</a>"),
        ];

        echo "
          <tr>
            <td colspan='1' style='width: 50px;'>
              #{$Rank_Key}
            </td>
            <td colspan='1' style='width: 100px;'>
              <img src='{$Display['Sprite']}' />
            </td>
            <td colspan='1' style='width: 150px;'>
              {$Display['Display_Name']}
              {$Display['Nickname']}
            </td>
            <td colspan='1' style='width: 150px;'>
              Level: {$Display['Level']}
              <br />
              Exp: {$Display['Experience']}
            </td>
            <td colspan='1' style='width: 150px;'>
              {$Display['Username']}
            </td>
          </tr>
        ";
      }
    ?>
  </tbody>
</table>