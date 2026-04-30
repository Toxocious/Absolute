<?php
    require_once $_SERVER['DOCUMENT_ROOT'] . '/core/required/ajax_header.php';
    header('Content-Type: application/json');

    require_once $_SERVER['DOCUMENT_ROOT'] . '/pages/rankings/functions/fetch_pokemon_rankings.php';
    require_once $_SERVER['DOCUMENT_ROOT'] . '/pages/rankings/functions/fetch_trainer_rankings.php';

    if ( !empty($_GET['Action']) && in_array($_GET['Action'], ['Get_Pokemon', 'Get_Trainers']) )
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

    $Species = null;
    if ( !empty($_GET['Species']) )
    {
        $Species = Purify($_GET['Species']);
    }

    $Type = 'all';
    if ( !empty($_GET['Type']) )
    {
        $Type = Purify($_GET['Type']);
    }

    switch ( $Action )
    {
        case 'Get_Pokemon':
            echo json_encode([
                FetchPokemonRankings($Page, 30, $Species, $Type)
            ]);
            break;

            case 'Get_Trainers':
            echo json_encode([
                FetchTrainerRankings($Page)
            ]);
            break;
    }
