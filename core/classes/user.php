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
		 * =========
		 * Username, ID, Rank, Power, Playtime, Registration Date, Last Active, Signature, Chat/RPG Ban Details, Currencies
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
		
		/**
		 * Displays the user rank where applicable (staff page, profiles, etc).
		 */
		public function DisplayUserRank($UserID)
		{
			global $PDO;

			try
			{
				$Fetch_Rank = $PDO->prepare("SELECT `Rank` FROM `users` WHERE `id` = ? LIMIT 1");
				$Fetch_Rank->execute([$UserID]);
				$Fetch_Rank->setFetchMode(PDO::FETCH_ASSOC);
				$Rank = $Fetch_Rank->fetch();
			}
			catch ( PDOException $e )
			{
				HandleError( $e->getMessage() );
			}

			switch($Rank['Rank'])
			{
				case 'Administrator':
					echo "<div class='admin'>Administrator</div>";
					break;
				case 'Bot':
					echo "<div class='bot'>Bot</div>";
					break;
				case 'Developer':
					echo "<div class='dev'>Developer</div>";
					break;
				case 'Super Moderator':
					echo "<div class='super_mod'>Super Moderator</div>";
					break;
				case 'Moderator':
					echo "<div class='mod'>Moderator</div>";
					break;
				case 'Chat Moderator':
					echo "<div class='chat_mod'>Chat Moderator</div>";
					break;
				case 'Member':
					echo "<div class='member'>Member</div>";
					break;
			}
		}

		public function DisplayUserName($UserID, $Clan_Tag = false)
		{
			global $PDO;

			try
			{
				$Fetch_Username = $PDO->prepare("SELECT `Username` FROM `users` WHERE `id` = ? LIMIT 1");
				$Fetch_Username->execute([$UserID]);
				$Fetch_Username->setFetchMode(PDO::FETCH_ASSOC);
				$Username = $Fetch_Username->fetch();
			}
			catch ( PDOException $e )
			{
				HandleError( $e->getMessage() );
			}

			return $Username['Username'];
		}
	}