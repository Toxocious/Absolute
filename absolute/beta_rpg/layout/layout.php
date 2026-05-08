<?php
    require_once $_SERVER['DOCUMENT_ROOT'] . '/backend/session/session.php';
    require_once $_SERVER['DOCUMENT_ROOT'] . '/backend/session/user_session.php';

    require_once $_SERVER['DOCUMENT_ROOT'] . '/components/_component.php';

    $Stylesheets = [
        '/themes/root.css',
        '/themes/main.css',

        '/themes/styles/absol.css',
    ];

    if ( isset($Page_Metadata['Styles']) && is_array($Page_Metadata['Styles']) )
    {
        $Stylesheets = array_merge($Stylesheets, $Page_Metadata['Styles']);
    }
?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">

        <title><?= isset($Page_Metadata['Title']) ? $Page_Metadata['Title'] : 'Layout Test'; ?> &mdash; Pok&eacute;mon Absolute</title>

        <link href="https://fonts.googleapis.com/css2?family=Birthstone:wght@400;600;700&display=swap" rel="stylesheet">
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
        <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700&display=swap" rel="stylesheet">
    </head>

    <body>
        <!-- -->
        <header <?= isset($User_Data) ? '' : "class='logged-out'"; ?>>
            <?php
                component('header', [
                    'User_Session' => isset($User_Data) ? $User_Data : null,
                    'Absolute_Time' => $Absolute_Time,
                ]);
            ?>
        </header>

        <!-- -->
        <nav>
            <?php
                component('site_nav', [
                    'User_Session' => isset($User_Data) ? $User_Data : null,
                ]);
            ?>
        </nav>

        <!-- -->
        <section class='page-container'>
            <!-- Chat -->
             <?php
                component('chat', []);
            ?>

            <!-- Page Content -->
            <main class='page-content'>
                <?= $Content; ?>
            </main>
        </section>

        <!-- -->
        <footer>
            <?php
                component('footer', [
                    'Page_Start_Time' => $Page_Start_Time,
                ]);
            ?>
        </footer>

        <!-- -->
        <?php
            component('peeker', []);
        ?>

        <!-- -->
        <?php
            $Stylesheets = array_values(array_unique(array_filter(array_merge(
                $Stylesheets,
                Get_Component_Stylesheets()
            ))));

            foreach ( $Stylesheets as $Stylesheet ) {
                if ( $Stylesheet ) {
                    $Stylesheet_Update_Time = filemtime($_SERVER['DOCUMENT_ROOT'] . $Stylesheet);

                    echo "<link type='text/css' rel='stylesheet' href='{$Stylesheet}?v={$Stylesheet_Update_Time}' />";
                }
            }
        ?>
    </body>
</html>
