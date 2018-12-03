<?php
	class User
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
		 * Fetch the complete data set of a specific user via their `users` DB ID.
		 */
		public function FetchUserData($UserID)
		{
			global $PDO;

			try
			{
				$Fetch_User = $PDO->prepare("SELECT * FROM `users` WHERE `id` = ? LIMIT 1");
				$Fetch_User->execute([$UserID]);
				$Fetch_User->setFetchMode(PDO::FETCH_ASSOC);
				$User = $Fetch_User->fetch();
			}
			catch ( PDOException $e )
			{
				HandleError( $e->getMessage() );
			}
		}
	}