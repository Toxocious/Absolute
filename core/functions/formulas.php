<?php
	/**
	 * FORMULAS FOR EACH EXP GROUP ( ^ meaning to the power of, ie level ^ 3 = level cubed, level ^ 2 = level squared )
	 * =============================
	 * Fast:       (level)^3 * 0.8
	 * Medium:     (level)^3 
	 * Parabolic:  (level)^3 * 1.2 - (level)^2 * 15 - (level) * 100 - 140
	 * Slow:       (level)^3 * 1.25
	 */

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
			case 'Trainer':
				return floor(pow($Experience + 1, 1 / 2.5));
			case 'Pokemon':
				return floor(pow($Experience + 1, 1 / 3));
			case 'Clan':
				return floor(pow($Experience + 1, 1 / 2.2));
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
			case 'Trainer':
				return pow($Level, 2.5);
			case 'Pokemon':
				return pow($Level, 3);
			case 'Clan':
				return pow($Level, 2.2);
			default: 
				return false;
		}
	}