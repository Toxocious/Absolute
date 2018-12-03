<?php
	/**
	 * Fetch the level of a specific object given the experience value.
	 */
	function FetchLevel($Experience, $Object)
	{
		// If the experience is somehow less than zero, force it to be zero.
		if ( $Experience < 0 )
		{
			$Experience = 0;
		}

		switch ($Object)
		{
			case 'Pokemon':
				return floor(pow($Experience + 1, 1 / 3));
			default:
				return false;
		}
	}

	/**
	 * Fetch the experience of a specific object given the level value.
	 */
	function FetchExperience($Level, $Object)
	{
		// If the level is somehow less than one, force it to be one.
		if ( $Level < 1 )
		{
			$Level = 1;
		}

		switch ($Object)
		{
			case 'Pokemon':
				return pow($Level, 3);
			default: 
				return false;
		}
	}