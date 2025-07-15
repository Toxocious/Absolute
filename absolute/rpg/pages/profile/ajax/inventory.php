<?php
    require_once $_SERVER['DOCUMENT_ROOT'] . '/core/required/session.php';

    require_once $_SERVER['DOCUMENT_ROOT'] . '/pages/profile/functions/inventory.php';

    if ( !empty($_GET['Action']) && in_array($_GET['Action'], ['Get_Inventory']) )
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

    $Category = 'Pokeballs';
    if ( !empty($_GET['Category']) )
    {
        $Category = Purify($_GET['Category']);
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
        case 'Get_Inventory':
            echo json_encode([
                GetInventory($User_ID, $Category)
            ]);
            break;
    }
