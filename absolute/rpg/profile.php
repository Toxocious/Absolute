<?php
    require_once $_SERVER['DOCUMENT_ROOT'] . '/core/required/layout_top.php';

    $Profile_View = !empty($_GET['view']) ? Purify($_GET['view']) : 'default';
    if ( !in_array($Profile_View, ['minimal', 'default']) ) {
        $Profile_View = 'default';
    }

    $Profile_ID = !empty($_GET['id']) ? Purify($_GET['id']) : $User_Data['ID'];
    $Profile_User = $User_Class->FetchUserData($Profile_ID);

    if (!$Profile_User) {
        echo "
            <div class='panel content'>
                <div class='head'>Test Page</div>
                <div class='body' style='padding: 5px;'>
                    This user does not exist.
                </div>
            </div>
        ";

        require_once $_SERVER['DOCUMENT_ROOT'] . '/core/required/layout_bottom.php';
        exit;
    }

    $Is_Own_Profile = ($Profile_ID == $User_Data['ID']);

    $Is_Online = ($Time - $Profile_User['Last_Active']) <= (15 * 60);
    $Online_Status_Class = $Is_Online ? 'online' : 'offline';

    $Profile_Buttons = [ 'roster', 'box', 'inventory', 'achievements', 'stats' ];

    require_once $_SERVER['DOCUMENT_ROOT'] . "/pages/profile/views/{$Profile_View}.php";
?>

<script>
    const AjaxContainer = document.querySelector('#ProfileAJAX > tbody > tr > td');
</script>

<script src='<?= DOMAIN_ROOT; ?>/pages/_shared/js/ajax_functions.js'></script>
<script src='<?= DOMAIN_ROOT; ?>/pages/_shared/js/get_pokemon_sprites.js'></script>

<script src='<?= DOMAIN_ROOT; ?>/pages/profile/js/ajax_functions.js'></script>

<script src='<?= DOMAIN_ROOT; ?>/pages/profile/js/roster.js'></script>
<script src='<?= DOMAIN_ROOT; ?>/pages/profile/js/box.js'></script>
<script src='<?= DOMAIN_ROOT; ?>/pages/profile/js/inventory.js'></script>

<script>
    (function()
    {
        ShowTab('roster', <?= $Profile_ID; ?>);
    })();
</script>

<?php
    require_once $_SERVER['DOCUMENT_ROOT'] . '/core/required/layout_bottom.php';
