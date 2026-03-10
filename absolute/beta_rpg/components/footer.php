<?php
    /** @var int $Page_Start_Time - Time when the server started processing the page load */
?>

<div>
    Page Generation Time: <?= round(microtime(true) - $Page_Start_Time, 4) * 1000; ?>ms
</div>

<hr />
