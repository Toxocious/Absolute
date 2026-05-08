<?php
    $Page_Metadata = [
        'Title' => 'Pokemon Center',
        // 'Styles' => ['/themes/pages/pokemon_center.css'],
        // 'Scripts' => [],
    ];

    ob_start();
?>

<div class='panel'>
    <div class='panel-header'>
        Pokemon Center
    </div>

    <div class='panel-content'>
        <p>Welcome to the Pokemon Center! This is where you can heal your Pokemon and manage your team. More features will be added here in future updates.</p>
    </div>
</div>

<?php
    $Content = ob_get_clean();
    require_once $_SERVER['DOCUMENT_ROOT'] . '/layout/layout.php';
