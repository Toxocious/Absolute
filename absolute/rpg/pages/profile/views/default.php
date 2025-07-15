<div class='panel content'>
    <div class='head'>
        <?= $Profile_User['Username']; ?>'s Profile
    </div>

    <div class='body' style='padding: 5px;'>
        <div class='flex'>
            <div style='flex-basis: 350px; margin-right: 5px;'>
                <table style='width: 350px;'>
                    <tbody>
                        <tr>
                            <td rowspan='2'>
                                <?= ( $Profile_User['Avatar'] ? "<img src='{$Profile_User['Avatar']}' />" : 'This user has no avatar set.' ); ?>
                            </td>
                            <td>
                                <div style='font-size: 1.5em;'>
                                    <?= $Profile_User['Username']; ?>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <?= $User_Class->DisplayUserRank($Profile_User['ID']); ?>
                            </td>
                        </tr>
                    </tbody>
                </table>

                <table class='border-gradient' style='width: 350px;'>
                    <thead></thead>
                    <tbody>
                        <thead>
                            <tr>
                                <td colspan='4'>
                                    <b>User Activity</b>
                                </td>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td colspan='2'>
                                    <b>Joined On</b>
                                </td>
                                <td colspan='2'>
                                    <?= date("F j, Y (g:i A)", $Profile_User['Date_Registered']); ?>
                                </td>
                            </tr>
                            <tr>
                                <td colspan='2'>
                                    <b>Playtime</b>
                                </td>
                                <td colspan='2'>
                                    <?= $Profile_User['Playtime']; ?>
                                </td>
                            </tr>
                            <tr>
                                <td colspan='2'>
                                    <b>Last Online</b>
                                </td>
                                <td colspan='2'>
                                    <?= LastSeenDate($Profile_User['Last_Active'], 'week'); ?>
                                </td>
                            </tr>
                            <tr>
                                <td colspan='2'>
                                    <b>Visiting Page</b>
                                </td>
                                <td colspan='2'>
                                    <?= $Profile_User['Last_Page']; ?>
                                </td>
                            </tr>
                        </tbody>

                        <thead>
                            <tr>
                                <th colspan='4'>Currencies</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <?php
                                    foreach ( $Constants->Currency as $Currency )
                                    {
                                        echo "
                                            <td colspan='2' style='width: 175px;'>
                                                <img src='{$Currency['Icon']}' />
                                            </td>
                                        ";
                                    }
                                ?>
                            </tr>
                            <tr>
                                <?php
                                    foreach ( $Constants->Currency as $Currency )
                                    {
                                        echo "
                                            <td colspan='2'>
                                                " . number_format($Profile_User[$Currency['Value']]) . "
                                            </td>
                                        ";
                                    }
                                ?>
                            </tr>
                        </tbody>
                    </tbody>
                </table>

                <?php if (!$Is_Own_Profile) { ?>
                    <table class='border-gradient' style='margin-top: 5px; width: 350px;'>
                        <thead>
                            <tr>
                                <th colspan='4'>Interactions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td colspan='2' style='width: 50%;'>
                                    <a href='<?= DOMAIN_ROOT; ?>/direct_messages.php?Message_Recipient=<?= $Profile_User['ID']; ?>'>
                                        Message <?= $Profile_User['Username']; ?>
                                    </a>
                                </td>
                                <td colspan='2' style='width: 50%;'>
                                    <a href='<?= DOMAIN_ROOT; ?>/trades.php?Action=Create&ID=<?= $Profile_User['ID']; ?>'>
                                        Trade With <?= $Profile_User['Username']; ?>
                                    </a>
                                </td>
                            </tr>
                            <tr>
                                <td colspan='2' style='width: 50%;'>
                                    <a href='<?= DOMAIN_ROOT; ?>/battle_create.php?Battle_Type=Trainer&Foe=<?= $Profile_User['ID']; ?>'>
                                        Battle <?= $Profile_User['Username']; ?>
                                    </a>
                                </td>
                                <td colspan='2' style='width: 50%;'>
                                    <a href='<?= DOMAIN_ROOT; ?>/report.php?Reporting_User=<?= $Profile_User['ID']; ?>'>
                                        Report <?= $Profile_User['Username']; ?>
                                    </a>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                <?php } ?>
            </div>

            <div style='flex: 1;'>
                <div class='page-nav-container'>
                <?php
                    foreach ( $Profile_Buttons as $Profile_Button )
                    {
                        echo "
                            <div class='page-nav-item'>
                                <a href='javascript:void(0);' id='{$Profile_Button}Button' onclick=\"ShowTab('{$Profile_Button}', {$Profile_ID});\">
                                    " . ucfirst($Profile_Button) . "
                                </a>
                            </div>
                        ";
                    }
                ?>
                </div>

                <table class='border-gradient' id='ProfileAJAX' style='margin-top: 5px; width: calc(100% - 5px);'>
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
</div>
