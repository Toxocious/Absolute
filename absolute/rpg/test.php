<?php
	require_once $_SERVER['DOCUMENT_ROOT'] . '/core/required/layout_top.php';

    $Base_Stats = GetBaseStats(359, 0);
    var_dump($Base_Stats);

    if ( $User_Data['Roster'] )
    {
        foreach ( $User_Data['Roster'] as $Roster_Pokemon )
        {
            $Roster_Pokemon = GetPokemonData($Roster_Pokemon['ID']);

            echo "
                <div onclick='PokemonViewer.open(\"{$Roster_Pokemon['ID']}\")'>
                    <div>
                        <img src='{$Roster_Pokemon['Icon']}' />
                    </div>
                </div>
            ";
        }
    }

    function NamedArgsTest($username, $email = null, $role = 'subscriber', $active = true) {
        return [
            'username' => $username,
            'email'    => $email,
            'role'     => $role,
            'active'   => $active
        ];
    }

    $NamedArgsOutput = NamedArgsTest(
        username: "john_doe",
        role: "admin"
    );

    var_dump($NamedArgsOutput);
?>

<?php
    require_once $_SERVER['DOCUMENT_ROOT'] . '/core/required/layout_bottom.php';
