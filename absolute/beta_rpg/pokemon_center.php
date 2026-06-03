<?php
    require_once $_SERVER['DOCUMENT_ROOT'] . '/backend/session/session.php';
    require_once $_SERVER['DOCUMENT_ROOT'] . '/backend/session/user_session.php';

    require_once $_SERVER['DOCUMENT_ROOT'] . '/backend/data/pokemon_data.php';

    require_once $_SERVER['DOCUMENT_ROOT'] . '/components/_component.php';

    $Page_Metadata = [
        'Title' => 'Pokemon Center',
        'Styles' => ['/themes/pages/pokemon_center.css'],
        'Scripts' => [[
            'src' => '/js/pages/pokemon_center.js',
            'module' => true,
        ]],
        'Requires_Session' => true,
    ];

    ob_start();
?>

<div class='panel'
    data-pokemon-center-api
    data-team-endpoint='/api/pokemon_center/team.php'
    data-box-endpoint='/api/pokemon_center/boxed.php'
    data-move-change-endpoint='/api/pokemon_center/move_change.php'
    data-current-page='1'
>
    <div class='panel-header'>
        Pokemon Center
    </div>

    <div class='panel-nav'>
        <button data-nav-section='roster' class='active'>Roster</button>
        <button data-nav-section='moves'>Moves</button>
        <button data-nav-section='inventory'>Inventory</button>
        <button data-nav-section='nickname'>Nickname</button>
        <button data-nav-section='release'>Release</button>
    </div>

    <div class='panel-content pokemon-center-content'>
        <?php
            component('pokemon_center/roster_tab', [
                'User_Data' => $User_Data,
            ]);

            // component('pokemon_center/moves_tab', [
            //     'User_Data' => $User_Data,
            // ]);

            // component('pokemon_center/inventory_tab', [
            //     'User_Data' => $User_Data,
            // ]);

            // component('pokemon_center/nickname_tab', [
            //     'User_Data' => $User_Data,
            // ]);

            // component('pokemon_center/release_tab', [
            //     'User_Data' => $User_Data,
            // ]);
        ?>
    </div>
</div>

<?php
    $Content = ob_get_clean();
    require_once $_SERVER['DOCUMENT_ROOT'] . '/layout/layout.php';
