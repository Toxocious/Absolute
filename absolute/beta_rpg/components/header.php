<?php
    /** @var string $Avatar - URL to the user's avatar */
    /** @var string $Username - The user's username */
    /** @var string $Rank - The user's rank or role */

    date_default_timezone_set('America/Phoenix');
    $Time = date('h:i A');
?>

<section class='user-bar'>
    <div class='user-avatar'>
        <img src='<?= $Avatar; ?>' />
    </div>

    <div class='user-info'>
        <h2><?= $Username; ?></h2>
        <h4><?= $Rank; ?></h4>
    </div>

    <div class='user-actions'>
        <button><img src='<?= '/images/Items/letter.png' ?>' alt='Messages' /></button>
        <div><?= $Time; ?></div>
    </div>
</section>
