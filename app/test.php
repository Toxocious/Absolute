<?php
	require_once $_SERVER['DOCUMENT_ROOT'] . '/core/required/layout_top.php';

    $Base_Stats = GetBaseStats(359, 0);
    var_dump($Base_Stats);
?>

<?php
    require_once $_SERVER['DOCUMENT_ROOT'] . '/core/required/layout_bottom.php';
