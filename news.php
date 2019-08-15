<?php
	require 'core/required/layout_top.php';

	try
	{
		$Fetch_News = $PDO->query("SELECT * FROM `news` ORDER BY `id` DESC LIMIT 1");
		$News_Post = $Fetch_News->fetch();

		$Fetch_News_Poster = $PDO->prepare("SELECT `Username`, `id`, `Avatar`, `Rank` FROM `users` WHERE `id` = ? LIMIT 1");
		$Fetch_News_Poster->execute([$News_Post['Poster_ID']]);
		$News_Poster = $Fetch_News_Poster->fetch();
	}
	catch ( PDOException $e )
	{
		HandleError( $e->getMessage() );
	}

	if ( !isset($_SESSION['abso_user']) )
	{
		$width = " style='margin: 5px; width: calc(100% - 10px);'";
	}
	else
	{
		$width = '';
	}
?>

<div class='content'<?= $width; ?>>
	<div class='head'>News</div>
	<div class='box news'>
		
		<table class='standard' style='width: calc(100% - 10px);'>
			<thead>
				<tr>
					<th colspan='2'>
						<?= $News_Post['News_Title']; ?>
					</th>
				</tr>
			</thead>
			<tbody>
				<tr>
					<td style='padding: 5px 30px;'>
						<img src='<?= $News_Poster['Avatar']; ?>' /><br />
						<?php
							echo $User_Class->DisplayUserName($News_Post['Poster_ID']);
							echo "<br />";
							echo $News_Post['News_Date'];
						?>
					</td>

					<td>
						<?= nl2br($News_Post['News_Text']); ?>
					</td>
				</tr>
			</tbody>
		</table>

	</div>
</div>

<?php
	require 'core/required/layout_bottom.php';