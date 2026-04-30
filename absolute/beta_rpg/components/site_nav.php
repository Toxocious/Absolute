<?php
    // Dynamically fetch what nav to show depending on if the user is in the staff panel or a regular page.

    $Navigation_Links = [
        [
            'Header' => 'Staff Panel',
            'Link' => '/staff/',
        ],
        [
            'Header' => 'Pokemon',
            'Dropdown_Links' => [
                [
                    'Name' => 'Pokemon Center',
                    'Link' => '/pokemon_center.php',
                ],
                [
                    'Name' => 'Evolution Center',
                    'Link' => '/evolution_center.php',
                ],
            ]
        ],
        [
            'Header' => 'Economy',
            'Dropdown_Links' => [
                [
                    'Name' => 'Shops',
                    'Link' => '/shops.php',
                ],
                [
                    'Name' => 'Trade Center',
                    'Link' => 'trade_center.php',
                ],
                [
                    'Name' => 'Trade Interest',
                    'Link' => '/trade_interest.php',
                ],
            ]
        ],
        [
            'Header' => 'Exporation',
            'Dropdown_Links' => [
                [
                    'Name' => 'World Map',
                    'Link' => '/world_map.php',
                ],
                [
                    'Name' => 'Mystery Dungeons',
                    'Link' => '/mystery_dungeons.php',
                ],
            ],
        ],
        [
            'Header' => 'Battle',
            'Dropdown_Links' => [
                [
                    'Name' => 'Battle A Trainer',
                    'Link' => '/battle_trainer.php',
                ],
                [
                    'Name' => 'Regional Gyms',
                    'Link' => '/battle_gyms.php',
                ],
                [
                    'Name' => 'Battle Frontier',
                    'Link' => '/battle_frontier.php',
                ],
                [
                    'Name' => 'Raid Bosses',
                    'Link' => '/battle_raids.php',
                ],
            ],
        ],
        [
            'Header' => 'Clans',
            'Dropdown_Links' => [
                [
                    'Name' => 'Create A Clan',
                    'Link' => '/clan_creation.php',
                ],
                [
                    'Name' => 'Clan Home',
                    'Link' => '/clan_home.php',
                ],
                [
                    'Name' => 'Clan Rankings',
                    'Link' => '/clan_rankings.php',
                ],
            ],
        ],
        [
            'Header' => 'Community',
            'Dropdown_Links' => [
                [
                    'Name' => 'News',
                    'Link' => '/news.php',
                ],
                [
                    'Name' => 'Global Rankings',
                    'Link' => '/global_rankings.php',
                ],
                [
                    'Name' => 'Online List',
                    'Link' => '/online_list.php',
                ],
                [
                    'Name' => 'Staff List',
                    'Link' => '/staff_list.php',
                ],
            ],
        ],
    ]
?>

<section class='nav-container'>
    <?php
        $Navigation_HTML = '';
        foreach ( $Navigation_Links as $Navigation_Section )
        {
            $Navigation_Template = "
                <div class='nav-item [[NAVIGATION_SECTION_DROPDOWN_CLASS]]'>
                    <a href='[[NAVIGATION_SECTION_LINK]]'>
                        <span>{$Navigation_Section['Header']}</span>
                    </a>
                    [[NAVIGATION_SECTION_DROPDOWN]]
                </div>
            ";

            if ( isset($Navigation_Section['Dropdown_Links']) && count($Navigation_Section['Dropdown_Links']) > 0 )
            {
                $Navigation_Section_Dropdown_Html = "<ul class='dropdown'>";

                foreach ( $Navigation_Section['Dropdown_Links'] as $Navigation_Dropdowns )
                {
                    $Navigation_Section_Dropdown_Html .= "
                        <div class='dropdown-item'>
                            <a href='{$Navigation_Dropdowns['Link']}'>
                                {$Navigation_Dropdowns['Name']}
                            </a>
                        </div>
                    ";
                }

                $Navigation_Section_Dropdown_Html .= "</ul>";

                $Navigation_Template = str_replace('[[NAVIGATION_SECTION_LINK]]', 'javascript:void(0);', $Navigation_Template);
                $Navigation_Template = str_replace('[[NAVIGATION_SECTION_DROPDOWN]]', $Navigation_Section_Dropdown_Html, $Navigation_Template);
                $Navigation_Template = str_replace('[[NAVIGATION_SECTION_DROPDOWN_CLASS]]', 'has-dropdown', $Navigation_Template);
            }
            else
            {
                $Navigation_Template = str_replace('[[NAVIGATION_SECTION_LINK]]', $Navigation_Section['Link'], $Navigation_Template);
                $Navigation_Template = str_replace('[[NAVIGATION_SECTION_DROPDOWN]]', '', $Navigation_Template);
                $Navigation_Template = str_replace('[[NAVIGATION_SECTION_DROPDOWN_CLASS]]', '', $Navigation_Template);
            }

            $Navigation_HTML .= $Navigation_Template;
        }

        echo $Navigation_HTML;
    ?>
</section>
