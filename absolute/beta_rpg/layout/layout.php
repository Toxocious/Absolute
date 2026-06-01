<?php
    require_once $_SERVER['DOCUMENT_ROOT'] . '/backend/session/session.php';
    require_once $_SERVER['DOCUMENT_ROOT'] . '/backend/session/user_session.php';

    require_once $_SERVER['DOCUMENT_ROOT'] . '/components/_component.php';

    $Stylesheets = [
        '/themes/root.css',
        '/themes/main.css',

        '/themes/styles/absol.css',

        '/themes/components/poke_viewer.css',
    ];

    if ( isset($Page_Metadata['Styles']) && is_array($Page_Metadata['Styles']) )
    {
        $Stylesheets = array_merge($Stylesheets, $Page_Metadata['Styles']);
    }

    $Scripts = [];

    if ( isset($Page_Metadata['Scripts']) && is_array($Page_Metadata['Scripts']) )
    {
        $Scripts = $Page_Metadata['Scripts'];
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

        <script src="<?= '/js/components/poke_viewer.js'; ?>" defer></script>
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
                <?php
                    if ( isset($Page_Metadata['Requires_Session']) && $Page_Metadata['Requires_Session'] === true && !isset($User_Data) )
                    {
                        component('requires_session');
                    }
                    else
                    {
                        echo $Content;
                    }
                ?>
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

        <!-- Component And Page Styles-->
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

        <!-- Component and Page Scripts -->
         <?php
            $Scripts = array_filter(array_merge(
                $Scripts,
                Get_Component_Scripts()
            ));

             foreach ( $Scripts as $Script ) {
                $Script_Src = null;
                $Script_Module = false;
                $Script_Defer = true;

                if ( is_string($Script) ) {
                    $Script_Src = $Script;
                } elseif ( is_array($Script) ) {
                    $Script_Src = $Script['src'] ?? null;
                    $Script_Module = !empty($Script['module']);
                    $Script_Defer = !array_key_exists('defer', $Script) || (bool)$Script['defer'];
                }

                if ( !$Script_Src ) {
                    continue;
                }

                $Version = time();
                if ( str_starts_with($Script_Src, '/') && file_exists($_SERVER['DOCUMENT_ROOT'] . $Script_Src) ) {
                    $Version = filemtime($_SERVER['DOCUMENT_ROOT'] . $Script_Src);
                }

                $Module_Attribute = $Script_Module ? " type='module'" : '';
                $Defer_Attribute = $Script_Defer ? ' defer' : '';

                echo "<script{$Module_Attribute}{$Defer_Attribute} src='{$Script_Src}?v={$Version}'></script>";
            }
         ?>
    </body>
</html>
