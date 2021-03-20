<?php
	/**
	 * Define core variables.
	 * - Game Database
	 * - Database Username
	 * - Database Password
	 * - Salt String
	 */
	define("GAME_DATABASE", "absolute");
	define("GAME_DATABASE_USER", "absolute");
	define("GAME_DATABASE_PASS", 'qwerty');
	define("GAME_DEFAULT_SALT", "5rrx4YP64TIuxqclMLaV1elGheNxJJRggMxzQjv5gQeFl84NFgXvR3NxcHuOc31SSZBTzUFEt0mYQ4Oo");

	/**
	 * Function that allows us to connect to the database.
	 */
	function DatabaseConnect($DB = GAME_DATABASE, $User = GAME_DATABASE_USER, $Pass = GAME_DATABASE_PASS)
	{
		$Host = 'localhost';
		$Char_Set = 'utf8mb4';

		$PDO_Attributes = [
			PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
			PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
			PDO::ATTR_EMULATE_PREPARES   => false,
		];

		try
		{
			$PDO = new PDO("mysql:host=$Host; dbname=$DB; charset=$Char_Set; ", $User, $Pass);
		}
		catch (PDOException $e)
		{
			$FetchDate = date("Y-m-d H:i:s");
			
			echo "
				<div>
					<b>[{$FetchDate}]</b><br />
					The database has failed to connect.<br />
					Contact Toxocious on Discord at Jess#5596.
				</div>
			";

			echo $e->getMessage();
			HandleError( $e->getMessage() );
			exit();
		}
	
		return $PDO;
	}
	$PDO = DatabaseConnect();

	/**
	 * Handle error messages.
	 * Log the error message to a file.
	 */
	function HandleError($Message)
	{
		$FetchDate = date("Y-m-d H:i:s");

		if ( !$Message )
			$Message = 'No error message was set.';
		
		file_put_contents('misc/logs/php_logs.txt', "[" . $FetchDate . "] Error: " . $Message.PHP_EOL, FILE_APPEND | LOCK_EX);
	}

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
   * Determine the current power level of the user.
   * !! Should move this to the user class file once it's made. (/core/classes/user.php)
   */
  function checkUserPower($User_Power, $Required_Power)
  {
    if ( $User_Power < $Required_Power )
    {
      echo "
        <div class='panel content'>
          <div class='head'>Unauthorized Access</div>
          <div class='body'>
            You do not have the appropriate power to access this page.
          </div>
        </div>
      ";

      require_once 'core/required/layout_bottom.php';

      exit();
    }
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
	 * Generate a randomly salted key.
	 */
	function RandSalt($Length)
	{
		$Characters = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890";
		$Salt = '';
		for ( $i = 0; $i < $Length; ++$i )
		{
			$Salt .= substr($Characters, mt_rand(0, strlen($Characters) - 1), 1);
		}

		return $Salt;
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