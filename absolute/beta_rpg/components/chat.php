<?php
    if ( isset($_SESSION['Absolute_Beta']['Logged_In_As']) )
    {
?>

<aside class='chat panel panel-vertical'>
    <div class='panel-header'>
        Chat
    </div>

    <div class='panel-content' id='chatContent'>
        <p style='text-align: center; color: var(--color-text-secondary);'>Chat functionality will be implemented in a future update.</p>
    </div>

    <div class='panel-footer'>
        <input type='text' id='chatMessage' placeholder='Send a message to chat!' disabled />
    </div>
</aside>

<?php
    }
?>
