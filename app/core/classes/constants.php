<?php
	/**
	 * A class that's use is to setup constant values that are used across the RPG.
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
			'Money'				=> [ 
				'Value' => 'Money',
				'Name' => 'Money',
				'Icon' => 'images/Assets/Money.png',
				'Tradeable' => true
			],
			'Abso_Coins'	=> [ 
				'Value' => 'Abso_Coins',
				'Name' => 'ECRPG Coins',
				'Icon' => 'images/Assets/Abso_Coins.png',
				'Tradeable' => true
			],
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
		 * Clans
		 */
		public $Clan = [
			"Creation_Cost" => 69420,
		];
		
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