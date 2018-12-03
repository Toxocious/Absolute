<?php
	require 'core/required/layout_top.php';

	try {
		$Fetch_News = $PDO->query("SELECT * FROM `news` ORDER BY `id` DESC LIMIT 1");
		$News_Post = $Fetch_News->fetch();

		$Fetch_News_Poster = $PDO->prepare("SELECT `Username`, `id`, `Avatar`, `Rank` FROM `users` WHERE `id` = ? LIMIT 1");
		$Fetch_News_Poster->execute([$News_Post['Poster_ID']]);
		$News_Poster = $Fetch_News_Poster->fetch();
	} catch (PDOException $e) {
		echo $e->getMessage();
	}
?>

<div class='content'>
	<div class='head'>News</div>
	<div class='box news'>
		<div class='panel'>
			<div class='panel-heading'>
				<?php
					if ( isset($_SESSION['abso_user']) )
					{
						if ( $User_Data['Rank'] >= '21' ) {
							echo	"<div style='float: left; width: 25%;'>";
							echo		"<a href='news_edit.php?id=" . $News_Post['id'] . "'>Edit Post</a>";
							echo	"</div>";
								
							echo	"<div style='float: left; width: 75%;'>";
							echo		"<b>" . $News_Post['News_Title'] . "</b>";
							echo	"</div>";
						} else {
							echo	"<div class='col-xs-12'>";
							echo		$News_Post['News_Title'];
							echo	"</div>";
						}
					}
					else
					{
						echo	"<div class='col-xs-12'>";
						echo		$News_Post['News_Title'];
						echo	"</div>";
					}
				?>
			</div>
			<div class='panel-body'>
				<div style='float: left; padding-top: 5px; width: 25%;'>
					<img src='<?= $News_Poster['Avatar']; ?>' /><br />
					<?php
						if ( $News_Poster['Rank'] === '12' ) {
							echo "<a class='cmod' href='profile.php?id=" . $News_Poster['id'] . "'>" . $News_Poster['Username'] . "</a><br />";
						}

						if ( $News_Poster['Rank'] === '69' ) {
							echo "<a class='gmod' href='profile.php?id=" . $News_Poster['id'] . "'>" . $News_Poster['Username'] . "</a><br />";
						}

						if ( $News_Poster['Rank'] === '420' ) {
							echo "<a class='admin' href='profile.php?id=" . $News_Poster['id'] . "'>" . $News_Poster['Username'] . "</a><br />";
						}
					?>
					<?= $News_Post['News_Date']; ?>
				</div>
				<div style='float: left; padding: 5px; width: 75%;'>
					<?= nl2br($News_Post['News_Text']); ?>
				</div>
			</div>
		</div>
	</div>
</div>

<?php
	require 'core/required/layout_bottom.php';