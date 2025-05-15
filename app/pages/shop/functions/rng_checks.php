<?php
    /**
     * Determine if the purchased object is shiny.
     * @param int $Object_ID
     */
    function ShinyCheck
    (
        int $Object_ID,
        int $Shiny_Odds
    )
    {
        if ( !$Object_ID || !$Shiny_Odds )
        {
            return false;
        }

        return mt_rand(1, $Shiny_Odds) == 1;
    }

    /**
     * Determine if the purchased object is ungendered.
     * @param int $Object_ID
     */
    function UngenderedCheck
    (
        int $Object_ID,
        int $Ungendered_Odds
    )
    {
        if ( !$Object_ID || !$Ungendered_Odds )
        {
            return false;
        }

        return mt_rand(1, $Ungendered_Odds) == 1;
    }
