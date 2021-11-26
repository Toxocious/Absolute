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
      case 'Map':
        return floor(pow($Experience + 1, 1 / 2.34));
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
      case 'Map':
        return pow($Level, 2.34);
			default:
				return false;
		}
	}

  /**
   * Calculate how much Exp. is needed to reach the next level.
   * @param int $Current_Exp
   * @param string $Formula
   */
  function FetchExpToNextLevel($Current_Exp, $Formula, $Return_Percent = false)
  {
    $Current_Level = FetchLevel($Current_Exp, $Formula);
    $Next_Level_Exp = FetchExperience($Current_Level + 1, $Formula);

    if (!$Return_Percent)
    {
      return $Next_Level_Exp - $Current_Exp;
    }
    else
    {
      $Last_Level_Exp = FetchExperience($Current_Level, $Formula);

      if ($Current_Exp == 0)
      {
        return [
          'Percent' => 0,
          'Exp' => 1,
        ];
      }

      return [
        'Percent' => round(100 * (1 - (($Next_Level_Exp - $Current_Exp) / ($Next_Level_Exp - $Last_Level_Exp))), 2),
        'Exp' => $Next_Level_Exp - $Current_Exp,
      ];
    }
  }
