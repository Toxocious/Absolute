<?php
	/**
	 * Define core variables.
	 * - Game Database
	 * - Database Username
	 * - Database Password
	 * - Salt String
	 */
	define("GAME_DATABASE", "absolute");
	define("GAME_DATABASE_USER", "root");
	define("GAME_DATABASE_PASS", '');
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
	function HandleError($Message = "")
	{
		$FetchDate = date("Y-m-d H:i:s");
		if ( $Message != '' )
		{
			file_put_contents('misc/logs/php_logs.txt', "[" . $FetchDate . "] Error: " . $Message.PHP_EOL, FILE_APPEND | LOCK_EX);
		}
	}

	/**
	 * Filters user inputs.
	 * Add more parameters later on for more diversity.
	 */
	function Purify($input)
	{
		$text = $input;

		if ( is_array($text) )
		{
			foreach ( $text as $key => $T )
			{
				$T = htmlentities($T, ENT_NOQUOTES, "UTF-8");
				$T = nl2br($T, false);
				$text[$key] = $T;
			}
		}
		else
		{
			$text = htmlentities($text, ENT_NOQUOTES, "UTF-8");
			$text = nl2br($text, false);
		}

		return $text;
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
        <div class='content'>
          <div class='head'>Unauthorized Access</div>
          <div class='box'>
            You do not have the appropriate power to access this page.
          </div>
        </div>
      ";

      require 'core/required/layout_bottom.php';

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
		else
		{
			return false;
		}
	}

	/**
	 * Generate an array of prices given a string.
	 */
	function GeneratePrice($Prices)
	{
		global $Constants;

		$Price_Array = [];
		$Fetch_Price = explode(',', $Prices);

		foreach ( $Fetch_Price as $Key => $String )
		{
			$Currency = explode(':', $String);
			if ( isset($Constants->Currency[$Currency[0]]) )
			{
				$Price_Array[ $Currency[0] ] = [
					'Value'		=> $Constants->Currency[$Currency[0]]['Value'],
					'Name'		=> $Constants->Currency[$Currency[0]]['Name'],
					'Amount'	=> $Currency[1],
				];
			}
		}

		return $Price_Array;
	}

	/**
	 * Generate a string of prices, given an array.
	 */
	function GeneratePriceString($Price_Array)
	{
		global $Constants;

		$Price_String = '';
		foreach ( $Price_Array as $Type => $Amount )
		{
			if ( isset($Constants->Currency[$Type]) && $Amount > 0 )
			{
				$Price_String .= $Type . ':' . $Amount . ',';
			}
		}

		return rtrim($Price_String, ',');
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
	 * Pagination function.
	 */
	function Pagi($Query, $User, $Parameters, $Page, $Link, $Limit)
  {
    global $PDO;

    try
    {
      $Prepare = $PDO->prepare($Query);
      $Prepare->execute($Parameters);
      $Total = $Prepare->fetchColumn();
    }
    catch ( PDOException $e )
    {
      HandleError( $e->getMessage() );
		}

		$Pages = ceil($Total / $Limit);

    if ( $Page == 0 )
    {
      $Page = 1;
    }
    
    /**
     * Render pagination navigation links.
     */
		$Adjacent = 1;
    $Link_Previous = '';
    $Link_Next = '';
    $Text = '';

    if ( $Page != 1 )
    {
      $Link_Previous .= "<div style='width: 10%;'><a href='javascript:void(0);' onclick='updateBox(1, " . $User . ");'> << </a></div>";
    }
    else
    {
      $Link_Previous .= "<div style='width: 10%;'><span> << </span></div>";
    }

    if ( $Page > 1 )
    {
      $Link_Previous .= "<div style='width: 10%;'><a href='javascript:void(0);' onclick='updateBox(" . ( $Page - 1 ) . ", " . $User . ");'> < </a></div>";
    }
    else
    {
      $Link_Previous .= "<div style='width: 10%;'><span> < </span></div>";
    }

    if ( $Page < $Pages )
    {
      $Link_Next .= "<div style='width: 10%;'><a href='javascript:void(0);' onclick='updateBox(" . ( $Page + 1 ) . ", " . $User . ");'> > </a></div>";
    }
    else
    {
      $Link_Next .= "<div style='width: 10%;'><span> > </span></div>";
    }

    if ( $Page != $Pages )
    {
      $Link_Next .= "<div style='width: 10%;'><a href='javascript:void(0);' onclick='updateBox(" . $Pages . ", " . $User . ");'> >> </a></div>";
    }
    else
    {
      $Link_Next .= "<div style='width: 10%;'><span> >> </span></div>";
		}

    for ( $x = ( $Page - $Adjacent ); $x < ( ( $Page + $Adjacent ) + 1 ); $x++ )
    {
      if ( ( $x > 0 ) && ( $x <= $Pages ) )
      {
				if ( $Page == 1 && $Pages == 1 )
				{
					$Width = '60%';
				}
				else if ( $Page == 1 || $Page == $Pages )
				{
					$Width = '30%';
				}
				else
				{
					$Width = '20%;';
				}

        if ( $x == $Page )
        {
          $Text .= "<div style='width: {$Width}'><b style='display: block;'>$x</b></div>";
        }
        else
        {
          $Text .= "<div style='width: {$Width}'><a style='display: block;' href='javascript:void(0);' onclick=\"updateBox($x, $User);\">$x</a></div>";
        }
			}
    }

    /**
     * Echo the pagination navigation bar.
     */
		echo "
      <div class='pagi'>
        {$Link_Previous} {$Text} {$Link_Next}
      </div>
    ";
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

	/**
	 * Determine which domain to tack onto URLs.
	 */
	function Domain($Area)
	{
		if ( $Area === 1 )
		{
			if ( $_SERVER['HTTP_HOST'] == "localhost" )
			{
				return;
			}
		}
		else
		{
			return;
		}
	}