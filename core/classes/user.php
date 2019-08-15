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

			if ( !isset($User) || !$User )
			{
				return "Error";
			}

			if ( $User['RPG_Ban'] == 'yes' )
			{
				$Banned_RPG = true;
			}
			else
			{
				$Banned_RPG = false;
			}

			if ( $User['Chat_Ban'] == 'yes' )
			{
				$Banned_Chat = true;
			}
			else
			{
				$Banned_Chat = false;
			}

			return [
				'ID' => $User['id'],
				'Username' => $User['Username'],
				'Roster' => $User['Roster'],
				'Avatar' => Domain(1) . $User['Avatar'],
				'Banned_RPG' => $Banned_RPG,
				'Banned_Chat' => $Banned_Chat,
				'Money' => $User['Money'],
				'Abso_Coins' => $User['Abso_Coins'],
				'Status' => $User['Status'],
				'Staff_Message' => $User['Staff_Message'],
				'Power' => $User['Power'],
				'Rank' => $User['Rank'],
				'Mastery_Points_Total' => $User['Mastery_Points_Total'],
				'Mastery_Points_Used' => $User['Mastery_Points_Used'],
			];
		}

		/**
		 * Fetch the user's masteries.
		 */
		public function FetchMasteries($User_ID)
		{
			
		}
		
		/**
		 * Displays the user rank where applicable (staff page, profiles, etc).
		 */
		public function DisplayUserRank($UserID, $Font_Size = 18)
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
				case 'Member':
					return "<div class='member' style='font-size: {$Font_Size}px'>Member</div>";
					break;
			}
		}

		public function DisplayUserName($UserID, $Clan_Tag = false, $Display_ID = false)
		{
			global $PDO;

			try
			{
				$Fetch_Username = $PDO->prepare("SELECT `id`, `Username`, `Rank` FROM `users` WHERE `id` = ? LIMIT 1");
				$Fetch_Username->execute([ $UserID ]);
				$Fetch_Username->setFetchMode(PDO::FETCH_ASSOC);
				$Username = $Fetch_Username->fetch();
			}
			catch ( PDOException $e )
			{
				HandleError( $e->getMessage() );
			}

			if ( $Display_ID )
			{
				$Append_ID = " - #" . number_format($Username['id']);
			}
			else
			{
				$Append_ID = '';
			}

			switch ( $Username['Rank'] )
			{
				case 'Administrator':
					return "<span class='admin' style='font-size: 14px'>{$Username['Username']}{$Append_ID}</span>";
					break;
				case 'Bot':
					return "<span class='bot' style='font-size: 14px'>{$Username['Username']}{$Append_ID}</span>";
					break;
				case 'Developer':
					return "<span class='dev' style='font-size: 14px'>{$Username['Username']}{$Append_ID}</span>";
					break;
				case 'Super Moderator':
					return "<span class='super_mod' style='font-size: 14px'>{$Username['Username']}{$Append_ID}</span>";
					break;
				case 'Moderator':
					return "<span class='mod' style='font-size: 14px'>{$Username['Username']}{$Append_ID}</span>";
					break;
				case 'Chat Moderator':
					return "<span class='chat_mod' style='font-size: 14px'>{$Username['Username']}{$Append_ID}</span>";
					break;
				case 'Member':
					return "<span class='member' style='font-size: 14px'>{$Username['Username']}{$Append_ID}</span>";
					break;
				default:
					return "<span class='member' style='font-size: 14px'>{$Username['Username']}{$Append_ID}</span>";
					break;
			}
		}
	}