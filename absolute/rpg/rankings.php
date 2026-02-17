<?php
	require_once $_SERVER['DOCUMENT_ROOT'] . '/core/required/layout_top.php';

    $Valid_Categories = [ 'pokemon', 'trainers' ];

    if ( isset($_GET['Category']) && in_array($_GET['Category'], $Valid_Categories) )
    {
        $Category = Purify($_GET['Category']);
    }
    else
    {
        $Category = 'pokemon';
    }

    if ( $Category == 'pokemon' )
    {
        require_once $_SERVER['DOCUMENT_ROOT'] . '/pages/rankings/functions/fetch_pokemon_rankings.php';

        $Rankings = FetchPokemonRankings(1);
        $Top_Ranking = FetchTopPokemon();
    }
    else
    {
        require_once $_SERVER['DOCUMENT_ROOT'] . '/pages/rankings/functions/fetch_trainer_rankings.php';

        $Rankings = [];
        $Top_Ranking = [];
    }
?>

<div class='panel content'>
	<div class='head'>Global Rankings</div>
	<div class='body' style='padding: 5px;'>
         <div style='flex: 1;'>
            <div class='page-nav-container'>
                <?php
                    foreach ( $Valid_Categories as $Cat )
                    {
                        echo "
                            <div class='page-nav-item " . ($Category == $Cat ? 'active' : '') . "' id='{$Cat}Button'>
                                <a href='javascript:void(0);' onclick='ShowTab(\"" . strtolower($Cat) .
                                "\")' style='display: block;'>" . ucfirst($Cat) . "</a>
                            </div>
                        ";
                    }
                ?>
            </div>
         </div>

		<section id='rankings-ajax' class='rankings-ajax'>
            <?php
                switch ( $Category )
                {
                    case 'pokemon':
                        require $_SERVER['DOCUMENT_ROOT'] . '/pages/rankings/pages/pokemon.php';
                        break;

                    case 'trainers':

                        break;
                }
            ?>
        </section>
	</div>
</div>

<script src='<?= DOMAIN_ROOT; ?>/pages/_shared/js/ajax_functions.js'></script>

<script src='<?= DOMAIN_ROOT; ?>/pages/rankings/js/ajax_functions.js'></script>

<script src='<?= DOMAIN_ROOT; ?>/pages/rankings/js/fetch_pokemon_rankings.js'></script>
<script src='<?= DOMAIN_ROOT; ?>/pages/rankings/js/fetch_trainer_rankings.js'></script>

<?php
	require_once $_SERVER['DOCUMENT_ROOT'] . '/core/required/layout_bottom.php';
