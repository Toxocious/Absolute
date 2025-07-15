<div class='panel content'>
    <div class='head'>Test Page</div>
    <div class='body' style='padding: 5px;'>
        <!-- Profile Banner -->
        <div class='profile-banner'>
            <!-- custom set banner image for absolute premium users? -->
            <img class="profile-banner-image" src="<?= DOMAIN_SPRITES; ?>/Profile_Banners/Default/absol.jpg" alt="Profile Banner" />

            <div class="profile-banner-overlay">
                <img
                    src="<?= $Profile_User['Avatar'] ?? '/images/Avatars/Sprites/' . mt_rand(1, 352) . '.png'; ?>"
                    alt="<?= $Profile_User['Username']; ?>"
                    class="profile-avatar"
                />

                <div>
                    <h1 class="profile-name"><?= $Profile_User['Username'] ?></h1>
                    <p class="profile-rank"><?= $User_Class->DisplayUserRank($Profile_User['ID']) ?? 'Member' ?></p>
                </div>

                <?php if (!$Is_Own_Profile) { ?>
                    <div class="profile-actions">
                        <div>
                            <a href="/trade.php?user=<?= $Profile_User['ID'] ?>" class="profile-btn">
                                Trade
                            </a>
                            <a href="/battle.php?user=<?= $Profile_User['ID'] ?>" class="profile-btn">
                                Battle
                            </a>
                        </div>
                        <div>
                            <a href="/battle.php?user=<?= $Profile_User['ID'] ?>" class="profile-btn">
                                Message
                            </a>
                            <a href="/battle.php?user=<?= $Profile_User['ID'] ?>" class="profile-btn">
                                Report
                            </a>
                        </div>
                    </div>
                <?php } ?>
            </div>
        </div>
        <div class='profile-banner-ribbon'>
            <div class="status-indicator <?= $Online_Status_Class ?>">
                <b>Last Online:</b> <?= LastSeenDate($Profile_User['Last_Active'], 'week'); ?>
            </div>
        </div>

        <!-- Profile Nav -->
        <div style='display: flex; flex-direction: row; flex-wrap: nowrap; gap: 0.5em;'>
            <div class='page-nav-container' style='flex-direction: column; flex-basis: unset; justify-content: unset; width: 200px;'>
                <?php
                    foreach ( $Profile_Buttons as $Profile_Button )
                    {
                        echo "
                            <div class='page-nav-item' style='width: unset;'>
                                <a href='javascript:void(0);' id='{$Profile_Button}Button' onclick=\"ShowTab('{$Profile_Button}', {$Profile_ID});\">
                                    " . ucfirst($Profile_Button) . "
                                </a>
                            </div>
                        ";
                    }
                ?>
            </div>

            <table class='border-gradient' id='ProfileAJAX' style='width: 100%;'>
                <tbody>
                    <tr>
                        <td>
                            <div style='padding: 0.5em;'>
                                Loading
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
