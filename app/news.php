<?php
	require_once $_SERVER['DOCUMENT_ROOT'] . '/core/required/layout_top.php';
    require_once $_SERVER['DOCUMENT_ROOT'] . '/core/functions/markdown_parsing.php';

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

    <div class='panel content'>
        <div class='head'>News</div>
        <div class='body' style='padding: 1em;'>
            <div style='margin: 0 1em 1em;'>
                Stay up to date on the latest news and updates from the staff team.
            </div>

            <table class='border-gradient' style='width: 100%;'>
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
                            <img src='<?= DOMAIN_SPRITES . '/' . $News_Post['Avatar']; ?>' /><br />
                            <?php
                                echo '<h3>' . $User_Class->DisplayUserName($News_Post['Poster_ID'], false, false, true) . '</h3>';
                                echo date('F j, Y g:i A', $News_Post['News_Date']);
                            ?>
                        </td>

                        <td class='news-post' style='padding: 10px;'>
                            <?= convert_markdown_to_html($News_Post['News_Text']); ?>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

<?php
    }

	require_once $_SERVER['DOCUMENT_ROOT'] . '/core/required/layout_bottom.php';
