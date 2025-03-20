<?php
    /**
     * Used to handle the processing and displayment of content
     * that is loaded via AJAX across multiple pages.
     */
    function Pagination
    (
        string $SQL_Query,
        array $SQL_Parameters,
        int $User_ID,
        int $Current_Page,
        int $Display_Limit,
        int $Colspan = 3,
        string $Onclick_Link = null,
        bool $Return = false
    )
    {
        global $PDO;

        $SQL_Query = Purify($SQL_Query);
        $SQL_Parameters = count($SQL_Parameters) > 0 ? Purify($SQL_Parameters) : null;
        $User_ID = Purify($User_ID);
        $Current_Page = Purify($Current_Page);
        $Display_Limit = Purify($Display_Limit);
        $Colspan = $Colspan > 0 ? Purify($Colspan) : $Colspan = 3;
        $Onclick_Link = Purify($Onclick_Link);

        $Temp_Link = $Onclick_Link;

        try
        {
            $Page_Prepare = $PDO->prepare($SQL_Query);
            $Page_Prepare->execute($SQL_Parameters);
            $Total_Results = $Page_Prepare->fetchColumn();
        }
        catch ( PDOException $e )
        {
            HandleError($e);
        }

        $Total_Pages = ceil($Total_Results / $Display_Limit);

        if ( $Current_Page < 1 )
        {
            $Current_Page = 1;
        }

        $Links = [
            'Next' => '',
            'Previous' => '',
            'Pages' => '',
        ];

        /**
         * Display the proper element to go back to page one.
         */
        if ( $Current_Page != 1 )
        {
            $Temp_Link = $Onclick_Link;

            if ( !$Temp_Link )
            {
                $Temp_Link = "onclick='Update_Box(1, {$User_ID});'";
            }
            else
            {
                $Temp_Link = str_replace('[PAGE]', 1, $Temp_Link);
            }

            $Links['Previous'] .= "
                <td colspan='{$Colspan}' style='width: calc(100% / 7);'>
                    <a href='javascript:void(0);' {$Temp_Link}>
                        &lt;&lt;
                    </a>
                </td>
            ";
        }
        else
        {
        $Links['Previous'] .= "
                <td colspan='{$Colspan}' style='width: calc(100% / 7);'>
                    <span>
                        &lt;&lt;
                    </span>
                </td>
            ";
        }

        /**
         * Display the proper element to go back a single page.
         */
        if ( $Current_Page > 1 )
        {
            $Temp_Link = $Onclick_Link;

            if ( !$Temp_Link )
            {
                $Temp_Link = "onclick='Update_Box(" . ( $Current_Page - 1 ) . ", {$User_ID});'";
            }
            else
            {
                $Temp_Link = str_replace('[PAGE]', ( $Current_Page - 1 ), $Temp_Link);
            }

            $Links['Previous'] .= "
                <td colspan='{$Colspan}' style='width: calc(100% / 7);'>
                    <a href='javascript:void(0);' {$Temp_Link}>
                        &lt;
                    </a>
                </td>
            ";
        }
        else
        {
            $Links['Previous'] .= "
                <td colspan='{$Colspan}' style='width: calc(100% / 7);'>
                    <span>
                        &lt;
                    </span>
                </td>
            ";
        }

        /**
         * Display the proper element to go forward a single page.
         */
        if ( $Current_Page < $Total_Pages )
        {
            $Temp_Link = $Onclick_Link;

            if ( !$Temp_Link )
            {
                $Temp_Link = "onclick='Update_Box(" . ( $Current_Page + 1 ) . ", {$User_ID});'";
            }
            else
            {
                $Temp_Link = str_replace('[PAGE]', ( $Current_Page + 1 ), $Temp_Link);
            }

            $Links['Next'] .= "
                <td colspan='{$Colspan}' style='width: calc(100% / 7);'>
                    <a href='javascript:void(0);' {$Temp_Link}>
                        &gt;
                    </a>
                </td>
            ";
        }
        else
        {
            $Links['Next'] .= "
                <td colspan='{$Colspan}' style='width: calc(100% / 7);'>
                    <span>
                        &gt;
                    </span>
                </td>
            ";
        }

        /**
         * Display the proper element to go to the last page.
         */
        if ( $Current_Page != $Total_Pages )
        {
            $Temp_Link = $Onclick_Link;

            if ( !$Temp_Link )
            {
                $Temp_Link = "onclick='Update_Box({$Total_Pages}, {$User_ID});'";
            }
            else
            {
                $Temp_Link = str_replace('[PAGE]', $Total_Pages, $Temp_Link);
            }

            $Links['Next'] .= "
                <td colspan='{$Colspan}' style='width: calc(100% / 7);'>
                    <a href='javascript:void(0);' {$Temp_Link}>
                        &gt;&gt;
                    </a>
                </td>
            ";
        }
        else
        {
            $Links['Next'] .= "
                <td colspan='{$Colspan}' style='width: calc(100% / 7);'>
                    <span>
                        &gt;&gt;
                    </span>
                </td>
            ";
        }

        /**
         * Determine which three page numbers to display to the user.
         */
        if ( $Total_Pages == 1 )
        {
            $Links['Pages'] .= "
                <td colspan='{$Colspan}' style='width: calc(100% / 7);'></td>
                <td colspan='{$Colspan}' style='width: calc(100% / 7);'>
                    <b>1</b>
                </td>
                <td colspan='{$Colspan}' style='width: calc(100% / 7);'></td>
            ";
        }
        else
        {
            if ( $Current_Page == 1 )
            {
                $Links['Pages'] .= "
                    <td colspan='{$Colspan}' style='width: calc(100% / 7);'></td>
                ";
            }

            for ( $x = ( $Current_Page - 1 ); $x < ( ( $Current_Page + 1 ) + 1 ); $x++ )
            {
                if ( ( $x > 0 ) && ( $x <= $Total_Pages ) )
                {
                    if ( $x == $Current_Page )
                    {
                        $Links['Pages'] .= "
                            <td colspan='{$Colspan}' style='width: calc(100% / 7);'>
                                <b>{$x}</b>
                            </td>
                        ";
                    }
                    else
                    {
                        $Temp_Link = $Onclick_Link;

                        if ( !$Temp_Link )
                        {
                            $Temp_Link = "onclick='Update_Box({$x}, {$User_ID});'";
                        }
                        else
                        {
                            $Temp_Link = str_replace('[PAGE]', $x, $Temp_Link);
                        }

                        $Links['Pages'] .= "
                            <td colspan='{$Colspan}' style='width: calc(100% / 7);'>
                                <a href='javascript:void(0);' {$Temp_Link}>{$x}</a>
                            </td>
                        ";
                    }
                }
            }

            if ( $Current_Page == $Total_Pages )
            {
                $Links['Pages'] .= "
                    <td colspan='{$Colspan}' style='width: calc(100% / 7);'></td>
                ";
            }
        }

        $Pagination_HTML = "
            <tr data-current-page='{$Current_Page}' data-total-pages='{$Total_Pages}'>
                {$Links['Previous']}
                {$Links['Pages']}
                {$Links['Next']}
            </tr>
        ";

        if ( $Return )
        {
            return $Pagination_HTML;
        }

        /**
         * Display the pages to the user.
         */
        echo $Pagination_HTML;
    }
