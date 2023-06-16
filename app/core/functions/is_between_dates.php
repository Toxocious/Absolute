<?php
  /**
   * Performs a check to see if the current date is between two dates.
   * Returns true even if the current date is the same as the supplied start or end dates.
   *
   * @param $Date_Start
   * @param $Date_End
   */
  function IsBetweenDates($Date_Start, $Date_End)
  {
    $Current_Date = new DateTime('03/01/2022');
    $Current_Timestamp = $Current_Date->format('U');

    $Start_Date = new DateTime($Date_Start);
    $Start_Timestamp = $Start_Date->format('U');

    $End_Date = new DateTime($Date_End);
    $End_Timestamp = $End_Date->format('U');

    if ( $Current_Timestamp >= $Start_Timestamp && $Current_Timestamp <= $End_Timestamp )
      return true;

    return false;
  }
