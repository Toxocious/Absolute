<?php
	require_once 'core/required/layout_top.php';

	try
	{
		$Fetch_News = $PDO->query("SELECT * FROM `news` ORDER BY `id` DESC LIMIT 1");
		$News_Post = $Fetch_News->fetch();

		$Fetch_News_Poster = $PDO->prepare("SELECT `ID`, `Username`, `Avatar`, `Rank` FROM `users` WHERE `ID` = ? LIMIT 1");
		$Fetch_News_Poster->execute([ $News_Post['Poster_ID'] ]);
		$News_Poster = $Fetch_News_Poster->fetch();
	}
	catch ( PDOException $e )
	{
		HandleError($e);
	}

	if ( !isset($_SESSION['Absolute']) )
	{
		$style = " style='margin: 5px; width: calc(100% - 14px);'";
	}
	else
	{
		$style = " style='margin: 0px 5px; width: 100%;'";
	}
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
	require_once 'core/required/layout_bottom.php';
