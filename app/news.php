<?php
	require_once 'core/required/layout_top.php';

	try
	{
    $Fetch_News_Post = $PDO->prepare("
      SELECT *
      FROM `news`
        INNER JOIN `users`
        ON `news`.`Poster_ID` = `users`.`ID`
      ORDER BY `news`.`id` DESC
      LIMIT 1
    ");
    $Fetch_News_Post->execute([ ]);
    $News_Post = $Fetch_News_Post->fetch();
	}
	catch ( PDOException $e )
	{
		HandleError($e);
	}

  if ( empty($News_Post) )
  {
?>

    <div class='panel content'>
      <div class='head'>News</div>
      <div class='body' style='padding: 0.5em;'>
        <img src='<?= DOMAIN_SPRITES; ?>/Pokemon/Sprites/Shiny/359.png' alt='Shiny Absol' /><br />
        Not a single news post has ever been made..<br />
        What are these developers up to?
      </div>
    </div>

<?php
  }
  else
  {
?>

<table class='border-gradient'<?= $style; ?>>
	<thead>
		<tr>
			<th colspan='2'>
				<?= $News_Post['News_Title']; ?>
			</th>
		</tr>
	</thead>
	<tbody>
		<tr>
			<td style='padding: 5px 30px; width: 150px;'>
				<img src='<?= DOMAIN_SPRITES . '/' . $News_Poster['Avatar']; ?>' /><br />
				<?php
					echo '<h3>' . $User_Class->DisplayUserName($News_Post['Poster_ID'], false, false, true) . '</h3>';
					echo $News_Post['News_Date'];
				?>
			</td>

			<td style='padding: 10px;'>
				<?= html_entity_decode($News_Post['News_Text']); ?>
			</td>
		</tr>
	</tbody>
</table>

<?php
  }

	require_once 'core/required/layout_bottom.php';
