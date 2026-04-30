<?php
    require_once $_SERVER['DOCUMENT_ROOT'] . '/backend/session/session.php';

    $Page_Metadata = [
        'Title' => 'Index',
        'Styles' => ['/themes/pages/index.css'],
    ];

    $Last_Active = strtotime("-24 hours", time());

    try
    {
        $Count_Data = $db->count([
            'users',
            // 'pokemon',
        ]);
    }
    catch ( PDOException $e )
    {
        HandleError($e);
    }

    ob_start();
?>

<div class='panel index-page'>
    <div class='panel-header'>
        Index
    </div>

    <div class='panel-content'>
        <div class='description'>
            The Pok&eacute;mon Absolute is home to <b><?= number_format($Count_Data['users'] ?? 0); ?></b> trainers and <b><?= number_format($Count_Data['pokemon'] ?? 0); ?></b> Pok&eacute;mon!
        </div>

        <div>
            The Pok&eacute;mon Absolute is an up-to-date multiplayer Pok&eacute;mon RPG, featuring all currently released canonical Pok&eacute;mon
            from the main Pok&eacute;mon games!
            <br /><br />

            Among featuring a plethora of unique gameplay content to explore, we offer content that will appeal to all
            trainers, new and old, including content that Pok&eacute;mon veterans will find nostalgic.
            <br /><br />

            Sign up, catch and train brand new Pok&eacute;mon, and initiate trades with other users so that you can rise to the top!
            <br /><br />

            We have all sorts of Pok&eacute;mon, including Normal and Shiny ones!
            <br />

            <img src='<?= DOMAIN_SPRITES; ?>/Pokemon/Sprites/Normal/359.png' />
            <img src='<?= DOMAIN_SPRITES; ?>/Pokemon/Sprites/Shiny/359.png' />
        </div>
    </div>
</div>

<div class='platform-notes'>
    This website is designed and optimized for Chromium based browsers.<br />
    It's recommended to use a Chromium based browser such as Google Chrome or Brave while browsing this website.
</div>

<?php
    $Content = ob_get_clean();
    require_once $_SERVER['DOCUMENT_ROOT'] . '/layout/layout.php';
