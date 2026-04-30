<?php
    require_once $_SERVER['DOCUMENT_ROOT'] . '/core/required/ajax_header.php';
    header('Content-Type: application/json');
    require_once $_SERVER['DOCUMENT_ROOT'] . '/pages/trade_interest/functions/trade_interest.php';

    $Possible_Actions = [
        'Get_Box',
        'Update_Pokemon_Interest',
    ];

    if ( !empty($_GET['Action']) && in_array($_GET['Action'], $Possible_Actions) )
    {
        $Action = Purify($_GET['Action']);
    }

    if ( empty($Action) || !in_array($Action, $Possible_Actions) )
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

    switch ( $Action )
    {
        case 'Get_Box':
            $Pokemon_Type = !empty($_GET['Pokemon_Type']) ? Purify($_GET['Pokemon_Type']) : 'Normal';

            echo json_encode([
                GetBoxedPokemon($Pokemon_Type, $Page)
            ]);
            break;

        case 'Update_Pokemon_Interest':
            $Pokemon_ID = !empty($_GET['Pokemon_ID']) ? Purify($_GET['Pokemon_ID']) : '';
            if ( empty($Pokemon_ID) || $Pokemon_ID == '' )
            {
                echo json_encode([
                    'Success' => false,
                    'Message' => 'No Pokémon ID was provided.',
                ]);
                exit;
            }

            $Possible_Interests = ['Yes', 'No', 'Undecided'];
            $Interest = in_array($_GET['Trade_Interest'] ?? '', $Possible_Interests, true)
                ? Purify($_GET['Trade_Interest'])
                : 'Undecided';

            $Updated_Interest = UpdatePokemonInterest($Pokemon_ID, $Interest);

            echo json_encode([
                'Success' => $Updated_Interest,
            ]);
            break;
    }
