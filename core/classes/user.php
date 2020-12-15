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
		public function FetchUserData($User_ID)
		{
			global $PDO;

			try
			{
				$Fetch_User = $PDO->prepare("SELECT * FROM `users` INNER JOIN `user_currency` ON `users`.`id`=`user_currency`.`User_ID` WHERE `id` = ?");
				$Fetch_User->execute([ $User_ID ]);
				$Fetch_User->setFetchMode(PDO::FETCH_ASSOC);
				$User = $Fetch_User->fetch();
			}
			catch ( PDOException $e )
			{
				HandleError( $e->getMessage() );
			}

			if ( !isset($User) || !$User )
				return false;

			if ( $User['RPG_Ban'] == 'yes' )
				$Banned_RPG = true;
			else
				$Banned_RPG = false;

			if ( $User['Chat_Ban'] == 'yes' )
				$Banned_Chat = true;
			else
				$Banned_Chat = false;

			return [
				'ID' => $User['id'],
				'Username' => $User['Username'],
				'Roster' => $User['Roster'],
				'Avatar' => DOMAIN_SPRITES . $User['Avatar'],
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
		 * Update the user's currency.
		 * @param $User_ID - The id of the user that we're updating.
		 * @param $Currency - The DB field name of the currency that we're updating.
		 * @param $Amount - The amount that we're manipulating the field by.
		 * @param $Operand - What action to take on the db field, ie addition, subtraction, etc.
		 */
		public function RemoveCurrency(int $User_ID, string $Currency, int $Amount)
		{
			global $PDO;

			if ( !$User_ID || !$Currency )
			{
				return false;
			}

			try
			{
				$Select_Query = $PDO->prepare("UPDATE `user_currency` SET `{$Currency}` = `{$Currency}` - ? WHERE `User_ID` = ? LIMIT 1");
				$Select_Query->execute([ $Amount, $User_ID ]);
			}
			catch ( PDOException $e )
			{
				HandleError($e);
			}
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
					return "<div class='administrator' style='font-size: {$Font_Size}px'>Administrator</div>";
					break;
				case 'Bot':
					return "<div class='bot' style='font-size: {$Font_Size}px'>Bot</div>";
					break;
				case 'Developer':
					return "<div class='developer' style='font-size: {$Font_Size}px'>Developer</div>";
					break;
				case 'Super Moderator':
					return "<div class='super_mod' style='font-size: {$Font_Size}px'>Super Moderator</div>";
					break;
				case 'Moderator':
					return "<div class='moderator' style='font-size: {$Font_Size}px'>Moderator</div>";
					break;
				case 'Chat Moderator':
					return "<div class='chat_mod' style='font-size: {$Font_Size}px'>Chat Moderator</div>";
					break;
				case 'Member':
					return "<div class='member' style='font-size: {$Font_Size}px'>Member</div>";
					break;
			}
		}

		public function DisplayUserName($UserID, $Clan_Tag = false, $Display_ID = false, $Link = false)
		{
			global $PDO;

			try
			{
				$Fetch_User = $PDO->prepare("SELECT `id`, `Username`, `Rank` FROM `users` WHERE `id` = ? LIMIT 1");
				$Fetch_User->execute([ $UserID ]);
				$Fetch_User->setFetchMode(PDO::FETCH_ASSOC);
				$User = $Fetch_User->fetch();
			}
			catch ( PDOException $e )
			{
				HandleError( $e->getMessage() );
			}

			if ( $Display_ID )
			{
				$Append_ID = " - #" . number_format($User['id']);
			}
			else
			{
				$Append_ID = '';
			}

			/**
			 * Hyperlink it.
			 */
			if ( $Link )
			{
				$Apply_Link_1 = "<a href='" . DOMAIN_ROOT . " /profile.php?id={$User['id']}'>";
				$Apply_Link_2 = "</a>";
			}
			else
			{
				$Apply_Link_1 = "";
				$Apply_Link_2 = "";
			}

			switch ( $User['Rank'] )
			{
				case 'Administrator':
					return "{$Apply_Link_1}<span class='administrator'>{$User['Username']}{$Append_ID}</span>{$Apply_Link_2}";
					break;
				case 'Bot':
					return "{$Apply_Link_1}<span class='bot'>{$User['Username']}{$Append_ID}</span>{$Apply_Link_2}";
					break;
				case 'Developer':
					return "{$Apply_Link_1}<span class='developer'>{$User['Username']}{$Append_ID}</span>{$Apply_Link_2}";
					break;
				case 'Super Moderator':
					return "{$Apply_Link_1}<span class='super_mod'>{$User['Username']}{$Append_ID}</span>{$Apply_Link_2}";
					break;
				case 'Moderator':
					return "{$Apply_Link_1}<span class='moderator'>{$User['Username']}{$Append_ID}</span>{$Apply_Link_2}";
					break;
				case 'Chat Moderator':
					return "{$Apply_Link_1}<span class='chat_mod'>{$User['Username']}{$Append_ID}</span>{$Apply_Link_2}";
					break;
				case 'Member':
					return "{$Apply_Link_1}<span class='member'>{$User['Username']}{$Append_ID}</span>{$Apply_Link_2}";
					break;
				default:
					return "{$Apply_Link_1}<span class='member'>{$User['Username']}{$Append_ID}</span>{$Apply_Link_2}";
					break;
			}
		}
	}