<?php
	require_once 'core/required/layout_top.php';

  if ( isset($_SESSION['EvoChroniclesRPG']) )
  {
    echo "
      <div class='panel content'>
				<div class='head'>Register</div>
				<div class='body' style='padding: 5px;'>
					You may not access this page while you're logged in.
				</div>
			</div>
    ";

    require_once 'core/required/layout_bottom.php';
    exit;
  }

  if ( isset($_POST['Register']) )
  {
    if ( empty($_POST['Username']) )
    {
      $Register_Prompt = "<div class='error'>Please enter a valid username.</div>";
    }
    else if ( empty($_POST['Password']) )
    {
      $Register_Prompt = "<div class='error'>Please enter a password.</div>";
    }
    else if ( empty($_POST['Password_Confirm']) || $_POST['Password'] != $_POST['Password_Confirm'] )
    {
      $Register_Prompt = "<div class='error'>The submitted passwords did not match.</div>";
    }

    if ( !empty($_POST['Username']) )
    {
      $Register_Username = Purify($_POST['Username']);

      try
      {
        $Check_Username_Availability = $PDO->prepare("
          SELECT *
          FROM `users`
          WHERE LOWER(`Username`) = LOWER(?)
          LIMIT 1
        ");
        $Check_Username_Availability->execute([
          $Register_Username
        ]);
        $Check_Username_Availability->setFetchMode(PDO::FETCH_ASSOC);
        $Username_Availability = $Check_Username_Availability->fetchAll();
      }
      catch ( PDOException $e )
      {
        HandleError($e);
      }

      if ( count($Username_Availability) > 0 )
      {
        $Register_Prompt = "<div class='error'>The username you have chosen is already in use.</div>";
      }
    }

    if ( empty($Register_Prompt) )
    {
      $Registrant_Data = [
        'Username' => Purify($_POST['Username']),
        'Password' => Purify($_POST['Password']),
      ];

      $Selected_Gender = Purify($_POST['Gender']);
      if ( !in_array($Selected_Gender, ['Female', 'Male', 'Ungendered']) )
        $Selected_Gender = 'Ugendered';

      $Selected_Avatar = Purify($_POST['Avatar']);
      if ( $Selected_Avatar < 1 || $Selected_Avatar > 352 )
        $Selected_Avatar = 1;

      $Avatar_Source = "/Avatars/Sprites/{$Selected_Avatar}.png";

      $Selected_Starter = Purify($_POST['Starter']);
      if ( !in_array($Selected_Starter, [ 1, 4, 7, 152, 155, 158, 252, 255, 258, 387, 390, 393, 495, 498, 501, 650, 653, 656, 722, 725, 728, 810, 813, 816 ]) )
        $Selected_Starter = 1;

      $Password_Hash = password_hash($Registrant_Data['Password'], PASSWORD_DEFAULT);
      $Signed_Up_Timestamp = time();
      $User_Auth_Code = bin2hex(random_bytes(10));

      try
      {
        $PDO->beginTransaction();

        $Create_User = $PDO->prepare("
          INSERT INTO `users` (
            `Username`,
            `Avatar`,
            `Gender`,
            `Date_Registered`,
            `Auth_Code`
          )
          VALUES ( ?, ?, ?, ?, ? )
        ");
        $Create_User->execute([
          $Registrant_Data['Username'],
          $Avatar_Source,
          $Selected_Gender,
          $Signed_Up_Timestamp,
          $User_Auth_Code
        ]);

        $Created_User_ID = $PDO->lastInsertId();

        $Create_User_Password = $PDO->prepare("
          INSERT INTO `user_passwords` (
            `ID`,
            `Username`,
            `Password`
          )
          VALUES ( ?, ?, ? )
        ");
        $Create_User_Password->execute([
          $Created_User_ID,
          $Registrant_Data['Username'],
          $Password_Hash
        ]);

        $Create_User_Currencies = $PDO->prepare("
          INSERT INTO `user_currency` (
            `ID`
          )
          VALUES ( ? )
        ");
        $Create_User_Currencies->execute([
          $Created_User_ID
        ]);

        $Starter_Gender = GenerateGender($Selected_Starter);
        $IVs = mt_rand(0, 31) . "," . mt_rand(0, 31) . "," . mt_rand(0, 31) . "," . mt_rand(0, 31) . "," . mt_rand(0, 31) . "," . mt_rand(0, 31);
        $Nature_Keys = array_keys(Natures());
        $Nature = $Nature_Keys[mt_rand(0, count($Nature_Keys) - 1)];
        $Starter_Data = GetPokedexData($Selected_Starter);

        $Create_Starter = $PDO->prepare("
          INSERT INTO `pokemon` (
            `Pokedex_ID`,
            `Alt_ID`,
            `Name`,
            `Location`,
            `Slot`,
            `Owner_Current`,
            `Owner_Original`,
            `Gender`,
            `IVs`,
            `Nature`,
            `Creation_Date`,
            `Creation_Location`
          )
          VALUES ( ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ? )
        ");
        $Create_Starter->execute([
          $Starter_Data['Pokedex_ID'],
          $Starter_Data['Alt_ID'],
          $Starter_Data['Name'],
          'Roster',
          1,
          $Created_User_ID,
          $Created_User_ID,
          $Starter_Gender,
          $IVs,
          $Nature,
          time(),
          'Starter Pokemon'
        ]);

        $Created_Starter_ID = $PDO->lastInsertId();

        $User_Roster_Update = $PDO->prepare("
          UPDATE `users`
          SET `Roster` = ?
          WHERE `ID` = ?
          LIMIT 1
        ");
        $User_Roster_Update->execute([
          $Created_Starter_ID,
          $Created_User_ID
        ]);

        $PDO->commit();
      }
      catch ( PDOException $e )
      {
        $PDO->rollback();

        HandleError($e);
      }

      $Register_Prompt = "
        <div class='success'>
          You've successfully registered an account on Pokémon Evo-Chronicles RPG!<br />
          <a href='login.php'><b>Click Here To Login</b></a>
        </div>
      ";
    }
  }
?>

<div class="panel content" style="margin: 5px; width: calc(100% - 14px);">
	<div class='head'>Register</div>
	<div class='body' style='padding-bottom: 10px;'>
		<div class='nav'>
			<div><a href='index.php' style='display: block;'>Home</a></div>
			<div><a href='login.php' style='display: block;'>Login</a></div>
			<div><a href='register.php' style='display: block;'>Register</a></div>
			<div><a href='discord.php' style='display: block;'>Discord</a></div>
		</div>

    <div class='description'>
      Please fill out the form below in order to begin your journey as a Pokemon Trainer.
    </div>

    <?php
      if ( !empty($Register_Prompt) )
        echo $Register_Prompt;
    ?>

    <form action='/register.php' method='POST'>
      <table class='border-gradient' style='width: 500px;'>
        <tbody>
          <tr>
            <td colspan='1' style='padding: 5px; width: 50%;'>
              <b>Username</b>
              <br />
              <input type='text' name='Username' />

              <br /><br />

              <b>Gender</b>
              <br />
              <select name='Gender' style='padding: 4px; text-align: center; width: 180px;'>
                <option value='Ungendered'>Ungendered</option>
                <option value='Female'>Female</option>
                <option value='Male'>Male</option>
              </select>
            </td>

            <td colspan='1' style='padding: 5px; width: 50%;'>
              <b>Password</b>
              <br />
              <input type='password' name='Password'>

              <br /><br />

              <b>Confirm Password</b>
              <br />
              <input type='password' name='Password_Confirm' />
            </td>
          </tr>
        </tbody>

        <tbody>
          <tr>
            <td colspan='1' style='padding: 5px;'>
              <b>Choose An Avatar</b>
              <br />
              <select name='Avatar' onchange='PreviewImage("Avatar", this);' style='padding: 4px; text-align: center; width: 180px;'>
                <?php
                  $Preset_Avatars = glob($_SERVER['DOCUMENT_ROOT'] . '/images/Avatars/Sprites/*.png');
                  foreach ( $Preset_Avatars as $Avatar_ID => $Avatar )
                  {
                    $Avatar_ID++;
                    echo "<option value='{$Avatar_ID}'>Avatar #{$Avatar_ID}</option>";
                  }
                ?>
              </select>
              <br />
              <img src='<?= DOMAIN_SPRITES; ?>/Avatars/Sprites/1.png' id='Avatar_Preview' />
            </td>

            <td colspan='1' style='padding: 5px;'>
              <b>Choose An Starter</b>
              <br />
              <select name='Starter' onchange='PreviewImage("Starter", this);' style='padding: 4px; text-align: center; width: 180px;'>
                <?php
                  $Possible_Starters = [
                    001 => 'Bulbasaur', 004 => 'Charmander', 007 => 'Squirtle',
                    152 => 'Chikorita', 155 => 'Cyndaquil', 158 => 'Totodile',
                    252 => 'Treecko', 255 => 'Torchic', 258 => 'Mudkip',
                    387 => 'Turtwig', 390 => 'Chimchar', 393 => 'Piplup',
                    495 => 'Snivy', 498 => 'Tepig', 501 => 'Oshawott',
                    650 => 'Chespin', 653 => 'Fennekin', 656 => 'Froakie',
                    722 => 'Rowlet', 725 => 'Litten', 728 => 'Popplio',
                    810 => 'Grookey', 813 => 'Scorbunny', 816 => 'Sobble'
                  ];
                  foreach ( $Possible_Starters as $Starter_ID => $Starter_Name )
                  {
                    echo "<option value='{$Starter_ID}'>{$Starter_Name}</option>";
                  }
                ?>
              </select>
              <br />
              <img src='<?= DOMAIN_SPRITES; ?>/Pokemon/Sprites/Normal/001.png' id='Starter_Preview' />
            </td>
          </tr>
        </tbody>

        <tbody>
          <tr>
            <td colspan='2' style='padding: 10px;'>
              <input type='submit' name='Register' value='Register' />
            </td>
          </tr>
        </tbody>
      </table>
    </form>
  </div>
</div>

<script type='text/javascript'>
  function PreviewImage(Preview_Target, Handler)
  {
    console.log('Previewing an image.', Preview_Target, Handler.value);

    let Image_Value = Handler.value;
    if ( Preview_Target == 'Starter' )
      Image_Value = Handler.value.padStart(3, '0');

    const Dir_Path = Preview_Target == 'Avatar' ? 'images/Avatars/Sprites' : '/images/Pokemon/Sprites/Normal';
    const Image_Source = `${Dir_Path}/${Image_Value}.png`;

    console.log(Dir_Path, '\n', Image_Source);
    document.getElementById(`${Preview_Target}_Preview`).src = Image_Source;
  }
</script>

<?php
  require_once 'core/required/layout_bottom.php';
