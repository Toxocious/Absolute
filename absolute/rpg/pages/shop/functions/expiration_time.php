<?php
    /**
     * Format a Unix timestamp into a human-readable format showing days and hours
     *
     * @param int $timestamp Unix timestamp to format
     * @return string Formatted time in "Xd Xhr" format
     */
    function format_expiration_time($timestamp) {
        // Calculate time difference between timestamp and current time
        $time_difference = $timestamp - time();

        // Handle expired timestamps
        if ($time_difference <= 0) {
            return "Expired";
        }

        // Convert seconds to days and hours
        $days = floor($time_difference / (60 * 60 * 24));
        $hours = floor(($time_difference % (60 * 60 * 24)) / (60 * 60));

        // Format the output
        return "{$days}d {$hours}hr";
    }
