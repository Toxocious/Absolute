<?php
    require_once $_SERVER['DOCUMENT_ROOT'] . '/pages/shop/functions/purchase.php';

    if ( !empty($_GET['Action']) && in_array($_GET['Action'], ['Purchase_Object']) )
    {
        $Action = Purify($_GET['Action']);
    }

    if ( empty($Action) )
    {
        $Action = 'Purchase_Object';
    }

    $Shop_ID = null;
    if ( !empty($_GET['Shop_ID']) )
    {
        $Shop_ID = Purify($_GET['Shop_ID']);
    }

    $Object_ID = null;
    if ( !empty($_GET['Object_ID']) )
    {
        $Object_ID = Purify($_GET['Object_ID']);
    }

    $Object_Type = null;
    if ( !empty($_GET['Object_Type']) )
    {
        $Object_Type = Purify($_GET['Object_Type']);
    }

    switch ( $Action )
    {
        case 'Purchase_Object':
        default:
            $Purchase = PurchaseObject($Shop_ID, $Object_ID, $Object_Type);
            echo json_encode([
                'Object_Data' => $Purchase
            ]);
            break;
    }
