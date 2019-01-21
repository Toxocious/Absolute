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
		<div class='panel'>
			<div class='panel-heading' style='height: 27px;'>
				<?php
					if ( isset($_SESSION['abso_user']) )
					{
						if ( $User_Data['Power'] >= '3' )
						{
							echo	"<div style='float: left; width: 25%;'>";
							echo		"<a href='news_edit.php?id=" . $News_Post['id'] . "' style='color: #888;'>Edit Post</a>";
							echo	"</div>";
								
							echo	"<div style='float: left; width: 75%;'>";
							echo		"<b>" . $News_Post['News_Title'] . "</b>";
							echo	"</div>";
						}
						else
						{
							echo	"<div style='width: 100%;'>";
							echo		$News_Post['News_Title'];
							echo	"</div>";
						}
					}
					else
					{
						echo	"<div style='width: 100%;'>";
						echo		$News_Post['News_Title'];
						echo	"</div>";
					}
				?>
			</div>
			<div class='panel-body'>
				<div style='float: left; padding-top: 5px; width: 25%;'>
					<img src='<?= $News_Poster['Avatar']; ?>' /><br />
					<?php
						switch( $News_Poster['Rank'] )
						{
							case "Administrator":
								echo "<a class='admin' href='profile.php?id={$News_Poster['id']}'>{$News_Poster['Username']}</a><br />";
								break;
							case "Bot":
								echo "<a class='bot' href='profile.php?id={$News_Poster['id']}'>{$News_Poster['Username']}</a><br />";
								break;
							case "Developer":
								echo "<a class='dev' href='profile.php?id={$News_Poster['id']}'>{$News_Poster['Username']}</a><br />";
								break;
							case "Super Moderator":
								echo "<a class='super_mod' href='profile.php?id={$News_Poster['id']}'>{$News_Poster['Username']}</a><br />";
								break;
							case "Moderator":
								echo "<a class='mod' href='profile.php?id={$News_Poster['id']}'>{$News_Poster['Username']}</a><br />";
								break;
							case "Chat Moderator":
								echo "<a class='chat_mod' href='profile.php?id={$News_Poster['id']}'>{$News_Poster['Username']}</a><br />";
								break;
							case "Member":
								echo "<a class='member' href='profile.php?id={$News_Poster['id']}'>{$News_Poster['Username']}</a><br />";
								break;
						}

						echo $News_Post['News_Date'];
					?>
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