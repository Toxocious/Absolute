<?php
    $Page_Metadata = [
        'title' => 'News',
        'styles' => ['/themes/pages/news.css'],
        'scripts' => [],
    ];

    ob_start();
?>

<div class='panel'>
    <div class='panel-header'>
        News
    </div>

    <div class='panel-content news-post'>
        <div class='news-author'>
            <img src='https://beta.localhost/assets/images/Avatars/Custom/1.png' alt='Author Avatar' />
            <span class='news-author-name'>Toxocious</span>
            <span class='author-badge'>Admin</span>
        </div>

        <hr class='vertical' />

        <div class='news-content'>
            <div class="news-meta">
                <span class="news-tag">Announcement</span>
                <span class="news-date">April 30, 2026</span>
            </div>

            <h2>Welcome to the Absolute RPG Beta!</h2>
            <p>
                We are excited to announce that the Absolute RPG Beta is now live! This is a major milestone for our team, and we can't wait for you to experience the game we've been working on.
            </p>
            <p>
                The beta version of Absolute RPG includes a variety of features and content for players to explore. You can create your own character, embark on quests, battle monsters, and interact with other players in a vibrant online world.
            </p>
            <p>
                We encourage all players to provide feedback during the beta phase. Your input is invaluable in helping us identify bugs, balance gameplay, and improve the overall experience. Please report any issues you encounter and share your thoughts on what you'd like to see in the final release.
            </p>
            <p>
                Thank you for joining us on this journey. We look forward to hearing your feedback and making Absolute RPG the best it can be!
            </p>
        </div>
    </div>
</div>

<?php
    $Content = ob_get_clean();
    require_once $_SERVER['DOCUMENT_ROOT'] . '/layout/layout.php';
