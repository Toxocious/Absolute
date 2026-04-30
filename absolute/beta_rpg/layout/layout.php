<?php
    $page_script_start_time = microtime(true);

    require_once $_SERVER['DOCUMENT_ROOT'] . '/components/_component.php';

    $Stylesheets = [
        '/themes/root.css',
        '/themes/main.css',

        '/themes/styles/absol.css',
    ];

    if ( isset($Page_Metadata['styles']) && is_array($Page_Metadata['styles']) )
    {
        $Stylesheets = array_merge($Stylesheets, $Page_Metadata['styles']);
    }

    $User_Data = [
        'Username' => 'Jess',
        'Rank' => 'Administrator',
        'Avatar' => '/assets/images/Avatars/Custom/1.png',
    ];
?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">

        <title>Layout Test &mdash; Pok&eacute;mon Absolute</title>

        <link href="https://fonts.googleapis.com/css2?family=Birthstone:wght@400;600;700&display=swap" rel="stylesheet">
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
        <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700&display=swap" rel="stylesheet">
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
        <nav>
            <?php
                $Stylesheets[] = component('site_nav', []);
            ?>
        </nav>

        <!-- -->
        <section class='page-container'>
            <!-- Chat -->
            <aside class='chat panel panel-vertical'>
                <div class='panel-header'>
                    Chat
                </div>

                <div class='panel-content' id='chatContent'>
                    <p style='text-align: center; color: var(--color-text-secondary);'>Chat functionality will be implemented in a future update.</p>
                </div>

                <div class='panel-footer'>
                    <input type='text' id='chatMessage' placeholder='Send a message to chat!' disabled />
                </div>
            </aside>

            <!-- Page Content -->
            <main class='page-content'>
                <?= $Content; ?>
            </main>
        </section>

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
            $Stylesheets[] = component('peeker', []);
        ?>

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
