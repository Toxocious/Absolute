<?php
    /**
     * Converts a given time in seconds to a human-readable date format.
     *
     * @param int $time The time in seconds to convert.
     *
     * @return string The formatted date string.
     */
    function Time_To_Date(int $time): string {
        // Convert the time from seconds to a Unix timestamp
        $timestamp = $time;

        // Format the timestamp into a human-readable date string
        return date("d/m/Y", $timestamp);
    }
