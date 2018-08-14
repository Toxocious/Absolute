<?php
	require	'php/layout_top.php';
?>

<div class='content'>
	<div class='head'>News</div>
	<div class='box news'>
		<div class='row'>
			<?php
				$News_Post = mysqli_query($con, "SELECT * FROM `news` ORDER BY `id` DESC LIMIT 1");
					
				while ( $Query = mysqli_fetch_assoc($News_Post) ) {
					$Poster_Data = mysqli_fetch_assoc(mysqli_query($con, "SELECT * FROM members WHERE id = '" . $Query['Poster_ID'] . "'"));
						
					echo	"<div class='panel panel-default'>";
					echo		"<div class='panel-heading'>";
						
										if ( $User_Data['Rank'] >= '21' ) {
											echo	"<div class='col-xs-3'>";
											echo		"<a href='news_edit.php?id=" . $Query['id'] . "'>Edit Post</a>";
											echo	"</div>";
												
											echo	"<div class='col-xs-9'>";
											echo		"<b>" . $Query['News_Title'] . "</b>";
											echo	"</div>";
										} else {
											echo	"<div class='col-xs-12'>";
											echo		$Query['News_Title'];
											echo	"</div>";
										}
						
					echo		"</div>";
						
					echo		"<div class='panel-body'>";
					echo			"<div class='col-xs-3'>";
					echo				"<div>";
					echo					"<img src='" . $Poster_Data['Avatar'] . "' /><br />";

					if ( $Poster_Data['Rank'] === '12' ) {
						echo "<a class='cmod' href='profile.php?id=" . $Query['Poster_ID'] . "'>" . $Poster_Data['Username'] . "</a><br />";
					}

					if ( $Poster_Data['Rank'] === '69' ) {
						echo "<a class='gmod' href='profile.php?id=" . $Query['Poster_ID'] . "'>" . $Poster_Data['Username'] . "</a><br />";
					}

					if ( $Poster_Data['Rank'] === '420' ) {
						echo "<a class='admin' href='profile.php?id=" . $Query['Poster_ID'] . "'>" . $Poster_Data['Username'] . "</a><br />";
					}

					echo					"<span>" . $Query['News_Date'] . "</span>";

					echo				"</div>";

					//echo				"<a href='profile.php?id=" . $Query['Poster_ID'] . "'>" . $Poster_Data['Username'] . "</a><br />";
					
					echo			"</div>";
						
					echo			"<div class='col-xs-9'>";
					echo				nl2br($Query['News_Text']);
					echo			"</div>";
					echo		"</div>";
					echo	"</div>";
				}
			?>
		</div>
	</div>
</div>

<?php
	require	'php/layout_bottom.php';
?>