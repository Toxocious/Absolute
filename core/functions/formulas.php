<?php
	/**
	 * Fetch the level of a specific object given the experience value.
	 */
	function FetchLevel($Experience, $Type)
	{
		if ( $Experience < 0 )
		{
			$Experience = 0;
		}

		switch ($Type)
		{
			case 'Pokemon':
				return floor( pow( $Experience + 1, 1 / 3 ) );
			case 'Clan':
				return floor( pow($Experience / 3, 1 / 2.2 ) + 1 );
			default:
				return false;
		}
	}

	/**
	 * Fetch the experience of a specific object given the level value.
	 */
	function FetchExperience($Level, $Object)
	{
		if ( $Level < 1 )
		{
			$Level = 1;
		}

		switch ($Object)
		{
			case 'Pokemon':
				return pow( $Level, 3 );
			default: 
				return false;
		}
	}