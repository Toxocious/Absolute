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
	define("GAME_DATABASE_PASS", '$bQ721qb9oS3WIh#SQgEGzA7');
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
					Please contact a staff member.
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
			file_put_contents('logs/php_logs.txt', "[" . $FetchDate . "] Error: " . $Message.PHP_EOL, FILE_APPEND | LOCK_EX);
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
	 * Generate a randomly salted key.
	 */
	function randomSalt($charCount)
	{
		$chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890";
		$salty = '';
		for ( $i = 0; $i < $charCount; ++$i )
		{
			$salty .= substr($chars, mt_rand(0, strlen($chars) - 1), 1);
		}

		return $salty;
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

	/******************************************************************************************************************
	 * Handle text/input data.
	 * --------
	 * Refactor this later?
	 * Remove Purify() function above upon refactor?
	 *******************************************************************************************************************/
	function Text($t)
	{
	  return new Text($t);
	}
	function protect($data)
	{
		return Text($data)->out();
	}
	class Text
	{
		public $Text;
		public static $SWEARS = [
			"fuck",
			"bitch",
			"cunt",
			"penis",
			"pussy",
			"vagina",
			"vaginea",
			"asshole",
			"nigga",
			"nigger",
			"whore",
			"anal",
			"rape",
			"cumshot",
			"dickhead",
			"faggot",
			"pedophile",
			"slut",
			"rapist",
			"goddamn",
			"vaginal",
			"piss",
			"prick",
			"porn",
			".xxx",
			"clit",
			"condom",
			"arse",
			" shit",
			"shit ",
			"bullshit",
		];
		public static $VALIDATION = [
			'username' => [
				'pre' => 'Your username',
				'min' => 3,
				'max' => 18,
				'numbersonly' => 'nope',
				'characters' => 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789_',
			],
			'item_name' => [
				'pre' => 'Item names',
				'min' => 2,
				'max' => 25,
				'numbersonly' => 'nope',
				'characters' => 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789_',
			],
			'clan_name' => [
				'pre' => 'The clan&#39;s name',
				'min' => 4,
				'max' => 35,
				'characters' => 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789_ ',
			],
			'clan_rank' => [
				'pre' => 'The clan&#39;s rank',
				'min' => 2,
				'max' => 40,
				'characters' => 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789_ ',
			],
			'clan_tag' => [
				'pre' => 'The clan&#39;s tag',
				'min' => 1,
				'max' => 4,
				'characters' => 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789',
			],
			'nickname' => [
				'pre' => 'Your nickname',
				'min' => 1,
				'max' => 20,
				'characters' => 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789-?!/+.,:;[]() ',
			],
			'password' => [
				'pre' => 'Your password',
				'min' => 6,
				'max' => 50,
				'characters' => 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789~!@#$%^&*()-=_+[]{}\|;:?.,',
			],
		];

		public function __construct($input)
		{
			$this->Text = $input;
		}

		//I guess its private for now??
		private function containsChars($Text, $AllowedCharacters)
		{
			$strlen = strlen($Text);
			$Output = [];
			for ($i = 0; $i < $strlen; ++$i) {
				if (strpos($AllowedCharacters, substr($Text, $i, 1)) === false) {
					$Output[] = substr($Text, $i, 1);
				}
			}
			if (count($Output) == 0) {
				return true;
			}

			return $Output;
		}

		//Validates that the text conforms to the specificaions of a specific system
		public function validate($for)
		{
			$Q = self::$VALIDATION[$for];
			$Text = $this->Text;

			if (strlen($Text) < $Q['min'] || strlen($Text) > $Q['max']) {
				return $Q['pre']." must be between ".number_format($Q['min'])." and ".number_format($Q['max'])." characters long.";
			}
			if (isset($Q['numbersonly']) && is_numeric($Text)) {
				return $Q['pre']." cannot be made out of just numbers.";
			}
			if (str_replace(' ', '', $Text) == '') {
				return $Q['pre']." cannot be made only of spaces.";
			}
			if (trim($Text) != $Text) {
				return $Q['pre']." cannot have a space at the beginning or the end.";
			}

			$Contains = $this->containsChars($Text, $Q['characters']);
			if ($Contains !== true) {
				unset($x);
				foreach ($Contains as $key => $Char) {
					$x = !isset($x) ? $Char : $x.', '.$Char;
				}

				return $Q['pre']." contains invalid characters.<br>The following characters are invalid: ".$x;
			}

			if ($this->censor($Text) != $Text) {
				return $Q['pre']." was found to be censorable.";
			}

			return true;
		}

		//filter all charters besides numbers
		public function num()
		{
			$text = $this->Text;
			if (is_array($text)) {
				foreach ($text as $key => $T) {
					$text[$key] = preg_replace("/[^0-9]/", "", $T);
				}
			} else {
				$text = preg_replace("/[^0-9]/", "", $this->Text);
			}
			$this->Text = $text;

			return $this->Text;
		}

		//Filters the text as
		public function in()
		{
			$text = $this->Text;
			if (is_array($text)) {
				foreach ($text as $key => $T) {
					$text[$key] = trim(stripslashes($T));
				}
			} else {
				$text = trim(stripslashes($this->Text));
			}
			$this->Text = $text;

			return $text;
		}

		//Filters the text as it gets displayed out
		public function out()
		{
			$text = $this->Text;
			if (is_array($text)) {
				foreach ($text as $key => $T) {
					$T = htmlentities($T, ENT_NOQUOTES, "UTF-8");
					$T = nl2br($T, false);
					$text[$key] = $T;
				}
			} else {
				$text = htmlentities($text, ENT_NOQUOTES, "UTF-8");
				$text = nl2br($text, false);
			}
			$this->Text = $text;

			return $text;
		}

		//Filter the text going out with BBCode
		public function bb_out()
		{
			require_once "bbcodes/JBBCode/Parser.php";
			require_once "bbcodes/bbcode.function.inc.php";

			$parser = new JBBCode\Parser();
			$parser->addCodeDefinitionSet(new JBBCode\DefaultCodeDefinitionSet());

			$text = $this->censor(); //Censor current text
			$text = htmlentities($text, ENT_NOQUOTES, "UTF-8"); //XSS Protection before BBCode processing

			// &$str lol who wrote this
			processBBCode_quote($text); // [quote][/quote] for fourms

			$parser->parse($text);

			return nl2br(smilies($parser->getAsHtml()), false);
		}

		//censor words from the censor list.
		public function censor()
		{
			$text = $this->Text;
			if (is_array($text)) {
				foreach ($text as $key => $T) {
					$text[$key] = strtr($text[$key], self::$SWEARS);
				}
			} else {
				foreach (self::$SWEARS as $q => $swear) {
					$text = str_ireplace($swear, RandomSalt(5), $text);
				}
			}
			$this->Text = $text;

			return $text;
		}
	}