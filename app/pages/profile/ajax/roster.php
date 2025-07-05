<?php
    require_once $_SERVER['DOCUMENT_ROOT'] . '/core/required/session.php';

    require_once $_SERVER['DOCUMENT_ROOT'] . '/pages/profile/functions/roster.php';

    if ( !empty($_GET['Action']) && in_array($_GET['Action'], ['Get_Roster']) )
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
        case 'Get_Roster':
            echo json_encode([
                GetRosterJSON($User_ID)
            ]);
            break;
    }
