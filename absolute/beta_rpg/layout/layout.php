<?php
    $page_script_start_time = microtime(true);

    require_once $_SERVER['DOCUMENT_ROOT'] . '/components/_component.php';

    $Stylesheets = [
        '/themes/root.css',
        '/themes/main.css',
        '/themes/styles/absol.css',
    ];

    $User_Data = [
        'Username' => 'Toxocious',
        'Rank' => 'Administrator',
        'Avatar' => '/images/Avatars/Custom/1.png',
    ];
?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">

        <title>Layout Test &mdash; Pok&eacute;mon Absolute</title>

        <!-- <link type='text/css' rel='stylesheet' href='/themes/css/root.css' />
        <link type='text/css' rel='stylesheet' href='/themes/css/main.css' />
        <link type='text/css' rel='stylesheet' href='/themes/css/styles/absol.css' /> -->
    </head>

    <body>
        <!-- -->
        <header>
            <?php
                $Stylesheets[] = component('header', [
                    'Avatar'  => $User_Data['Avatar'],
                    'Username'  => $User_Data['Username'],
                    'Rank' => $User_Data['Rank'],
                ]);
            ?>
        </header>

        <!-- -->
        <main>
            <?= $Content; ?>
        </main>

        <!-- -->
        <footer>
            <?php
                $Stylesheets[] = component('footer', [
                    'Page_Start_Time' => $page_script_start_time,
                ]);
            ?>
        </footer>

        <!-- -->
        <?php
            foreach ( $Stylesheets as $Stylesheet ) {
                if ( $Stylesheet ) {
                    echo "<link type='text/css' rel='stylesheet' href='{$Stylesheet}' />";
                }
            }
        ?>
    </body>
</html>
