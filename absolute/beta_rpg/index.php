<?php
    ob_start();
?>

Aleu

<?php
    $Content = ob_get_clean();
    require_once $_SERVER['DOCUMENT_ROOT'] . '/layout/layout.php';
