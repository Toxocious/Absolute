<?php
	define("GAME_DATABASE", "absolute");
	define("GAME_DATABASE_USER", "root");
	define("GAME_DATABASE_PASS", '$bQ721qb9oS3WIh#SQgEGzA7');
	define("GAME_DEFAULT_SALT", "5rrx4YP64TIuxqclMLaV1elGheNxJJRggMxzQjv5gQeFl84NFgXvR3NxcHuOc31SSZBTzUFEt0mYQ4Oo");

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
			echo "
				<div>
					The database has failed to connect.<br />
					Please contact a staff member.
				</div>
			";
			echo $e->getMessage();
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

	/*
		* DETERMINE THE DOMAIN
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