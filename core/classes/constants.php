<?php
	/**
	 * A class that's use is to setup CONSTANT values that are used across the RPG.
	 */
	Class Constants
	{
		public $PDO;

		/**
		 * Construct and initialize the class.
		 */
		public function __construct()
		{
			global $PDO;
			$this->PDO = $PDO;
		}

		/**
		 * Currencies
		 */
		public $Currency = [
			'Money' => [ 'Value' => 'Money', 'Name' => 'Money', 'Tradeable' => true ],
		];

		/**
		 * Shops
		 */
		public $Shiny_Odds = [
			'pokemon_shop' => 8192,
		];

		/**
		 * Maps
		 */


		/**
		 * Achievements
		 */
		public $Achievements = [
			[
				'Name' 				=> 'Trainer Level',
				'Description' => 'Aquire Trainer Level *.',
				'Tiers' 			=> [ 7, 8, 9, 10, 11, 12 ],
				'Stat' 				=> 'trainer_exp',
				'Display' 		=> '* Exp.',
			],
		];
	}