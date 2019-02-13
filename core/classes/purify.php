<?php
	/**
	 * A dedicated class to deal with user inputs.
	 */
	Class Purify
	{
		public $Input;

		public $Swears = [
			"anal",
			"ass",
			"asshole",
			"bitch",
			"bullshit",
			"clit",
			"cock",
			"cuck",
			"cum",
			"cummies",
			"cumshot",
			"cunt",
			"dick",
			"fag",
			"faggot",
			"nigger",
			"nigga",
			"nig",
			"pedo",
			"pedophile",
			"penis",
			"porn",
			"pussy",
			"rape",
			"rapist",
			"slut",
			"slutty",
			"tit",
			"titty",
			"titties",			
			"vagina",
			"vaginal",
			"whore",
		];

		public $Validate = [
			'username' => [
				'min'			=> 3,
				'max'			=> 20,
				'charset' => 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789_',
			],
			'password' => [
				'min'			=> 6,
				'max'			=> 99,
				'charset' => 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789~!@#$%^&*()-=_+[]{}\|;:?.,',
			],
		];

		/**
		 * A function to determine if the string contains allows characters.
		 */
		public function ContainsCharacters($Text, $Charset)
		{
			$Text_Len = strlen($Text);
			$Output = [];

			for ( $i = 0; $i < $Text_Len; $i++ )
			{
				if ( strpos( $Charset, substr($Text, $i, 1) ) == false )
				{
					$Output[] = substr($Text, $i, 1);
				}
			}

			if ( count($Output) == 0 )
			{
				return true;
			}

			return $Output;
		}

		/**
		 * Validate that a string is fitting for a specific system.
		 */
		public function Validate($Parameter)
		{
			$Sys = self::$Validate[$Parameter];
			$Text = $this->text;

			if ( strlen($Text) < $Sys['min'] || strlen($Text) > $Sys['max'] )
			{
				return "The length of your input needs to be between {$Sys['min']} and {$Sys['max']} characters.";
			}
			if ( trim($Text) != $Text )
			{
				return "Your input may not begin or end with a space.";
			}

			$Contains = $this->ContainsCharacters($Text, $Sys['charset']);

			if ( $Contains !== true )
			{
				unset($Invalid);

				foreach( $Contains as $Key => $Character )
				{
					$Invalid = !isset($Invalid) ? $Character : $Invalid . ', ' . $Character;
				}

				return "
					Your input contains invalid characters.<br />
					The following characters are invalid: <br />
					{$Invalid}
				";
			}

			if ( $this->Censor($Text) != $Text )
			{
				return "Some text within your input was found to be censorable.";
			}

			return true;
		}

		/**
		 * General function to cleanse input text.
		 */
		function Cleanse($input)
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
		 * Censor input text if text has been found that matches words on the censor list.
		 */
		public function Censor()
		{
			$Text = $this->text;

			if ( is_array($Text) )
			{
				foreach ( $Text as $Key => $Char )
				{
					$Text[$Key] = strtr( $Text[$Key], self::$Swears );
				}
			}
			else
			{
				foreach ( self::$Swears as $Key => $Swear )
				{
					$Text = str_ireplace( $Swear, "****", $Text );
				}
			}

			$this->text = $Text;

			return $Text;
		}
	}