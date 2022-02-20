<?php
	/**
	 * Filters user inputs.
	 * Add more parameters later on for more diversity.
	 */
	function Purify($Input)
	{
		if ( !$Input )
			return false;

		$Input_Type = gettype($Input);
		$Input_As_Text = $Input;

		if ( is_array($Input_As_Text) )
		{
			foreach ( $Input_As_Text as $K => $V )
			{
				$V = htmlentities($V, ENT_NOQUOTES, "UTF-8");
				$V = nl2br($V, false);
				$Input_As_Text[$K] = $V;
			}
		}
		else
		{
			$Input_As_Text = htmlentities($Input_As_Text, ENT_NOQUOTES, "UTF-8");
			$Input_As_Text = nl2br($Input_As_Text, false);
		}

		/**
		 * Return the variable as it's original type.
		 */
		switch ( $Input_Type )
		{
			case 'boolean':
				return (bool) $Input_As_Text;
			case 'integer':
				return (integer) $Input_As_Text;
			case 'double':
				return (double) $Input_As_Text;
			case 'string':
				return (string) $Input_As_Text;
			case 'array':
				return (array) $Input_As_Text;
			case 'object':
				return (object) $Input_As_Text;
			case 'NULL':
				return null;
		}

		return false;
	}

	/**
	 * Performs a check to see if the current date is between two dates.
	 */
	function isBetweenDates($date1, $date2)
	{
		$paymentDate = new DateTime(); // Today
		$contractDateBegin = new DateTime($date1);
		$contractDateEnd = new DateTime($date2);

		if
    (
      $paymentDate->getTimestamp() > $contractDateBegin->getTimestamp() &&
      $paymentDate->getTimestamp() < $contractDateEnd->getTimestamp()
    )
		{
			return true;
		}

		return false;
	}

	/**
   * Last seen functions.
   * Converts unix timestamp to a readable format.
   */
  function lastseen($ts, $totimestamp = '')
  {
    $getseconds = time() - $ts;

		if ($totimestamp == 'hour' && $getseconds > 3600)
		{
      $lastseen = date("F j, Y (g:i A)", $ts);
		}
		elseif ($totimestamp == 'day' && $getseconds > 86400)
		{
      $lastseen = date("F j, Y (g:i A)", $ts);
		}
		elseif ($totimestamp == 'week' && $getseconds > 604800)
		{
      $lastseen = date("F j, Y (g:i A)", $ts);
		}
		elseif ($totimestamp == 'month' && $getseconds > 2419200)
		{
      $lastseen = date("F j, Y (g:i A)", $ts);
		}
		elseif ($totimestamp == 'year' && $getseconds > 29030400)
		{
      $lastseen = date("F j, Y (g:i A)", $ts);
		}
		else
		{
			if ( $getseconds <= 59 )
			{
        $lastseen = "".$getseconds." Second(s) Ago";
			}
			elseif ($getseconds >= 60 && $getseconds <= 3599)
			{
        $minutes = floor($getseconds / 60);
        $lastseen = "".$minutes." Minute(s) Ago";
			}
			elseif ($getseconds >= 3600 && $getseconds <= 86399)
			{
        $hours = floor($getseconds / 3600);
        $lastseen = "".$hours." Hour(s) Ago";
			}
			elseif ($getseconds >= 86400 && $getseconds <= 604799)
			{
        $days = floor($getseconds / 86400);
        $lastseen = "".$days." Day(s) Ago";
			}
			elseif ($getseconds >= 604800 && $getseconds <= 2419199)
			{
        $weeks = floor($getseconds / 604800);
        $lastseen = "".$weeks." Week(s) Ago";
			}
			elseif ($getseconds >= 2419200 && $getseconds <= 29030399)
			{
        $months = floor($getseconds / 2419200);
        $lastseen = "".$months." Month(s) Ago";
			}
			elseif ($getseconds > 365 * 86400 * 10)
			{
        $years = floor($getseconds / 29030400);
        $lastseen = "".$years." Year(s) Ago";
			}
			else
			{
        $lastseen = "Never";
      }
    }

    return $lastseen;
  }
