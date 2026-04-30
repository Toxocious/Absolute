<?php
    require_once $_SERVER['DOCUMENT_ROOT'] . '/core/required/ajax_header.php';
    header('Content-Type: application/json');

    require_once $_SERVER['DOCUMENT_ROOT'] . '/pages/profile/functions/box.php';

    if ( !empty($_GET['Action']) && in_array($_GET['Action'], ['Get_Box']) )
    {
        $Action = Purify($_GET['Action']);
    }

    if ( empty($Action) )
    {
        echo json_encode([
            'Success' => false,
            'Message' => 'An invalid action was selected.',
        ]);

        exit;
    }

    $Page = 1;
    if ( !empty($_GET['Page']) )
    {
        $Page = Purify($_GET['Page']);
    }

    $User_ID = Purify($_GET['User_ID']) ?? null;
    if ( !$User_ID || empty($User_ID) || !is_numeric($User_ID) || $User_ID <= 0 )
    {
        echo json_encode([
            'Success' => false,
            'Message' => 'An invalid user ID was provided.',
        ]);

        exit;
    }

    switch ( $Action )
    {
        case 'Get_Box':
            echo json_encode([
                GetBoxedPokemon($User_ID, $Page)
            ]);
            break;
    }
