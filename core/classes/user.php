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

			return [
				'ID' => $User['id'],
				'Username' => $User['Username'],
				'Avatar' => Domain(1) . $User['Avatar'],
			];
		}
		
		/**
		 * Displays the user rank where applicable (staff page, profiles, etc).
		 */
		public function DisplayUserRank($UserID, $Font_Size = null)
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

			if ( $Font_Size == null )
			{
				$Font_Size = 18;
			}

			switch($Rank['Rank'])
			{
				case 'Administrator':
					return "<div class='admin' style='font-size: {$Font_Size}px'>Administrator</div>";
					break;
				case 'Bot':
					return "<div class='bot' style='font-size: {$Font_Size}px'>Bot</div>";
					break;
				case 'Developer':
					return "<div class='dev' style='font-size: {$Font_Size}px'>Developer</div>";
					break;
				case 'Super Moderator':
					return "<div class='super_mod' style='font-size: {$Font_Size}px'>Super Moderator</div>";
					break;
				case 'Moderator':
					return "<div class='mod' style='font-size: {$Font_Size}px'>Moderator</div>";
					break;
				case 'Chat Moderator':
					return "<div class='chat_mod' style='font-size: {$Font_Size}px'>Chat Moderator</div>";
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