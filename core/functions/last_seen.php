<?php
  /**
   * Returns a string indicating the last time the object was seen.
   *
   * @param $Timestamp
   */
  function LastSeenDate($Timestamp)
  {
    if ( empty($Timestamp) )
      return 'Never';

    $Last_Seen_Date = new DateTime(date('Y-m-d H:i:s', $Timestamp));
    $Current_Date = new DateTime();

    $Time_Difference = $Last_Seen_Date->diff($Current_Date);

    $Years = $Time_Difference->format('%y');
    if ( $Years !== '0' )
      return "{$Years} Year(s) ago";

    $Months = $Time_Difference->format('%m');
    if ( $Months !== '0' )
      return "{$Months} Month(s) ago";

    $Days = $Time_Difference->format('%d');
    if ( $Days !== '0' )
      return "{$Days} Day(s) ago";

    $Hours = $Time_Difference->format('%h');
    if ( $Hours !== '0' )
      return "{$Hours} Hour(s) ago";

    $Minutes = $Time_Difference->format('%i');
    if ( $Minutes !== '0' )
      return "{$Minutes} Min(s) ago";

    $Seconds = $Time_Difference->format('%s');
    return "{$Seconds} Sec(s) ago";
  }
