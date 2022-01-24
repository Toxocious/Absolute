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
		public function FetchUserData($User_Query)
		{
			global $PDO;

			if ( !$User_Query )
				return false;

			$User_Query = Purify($User_Query);

			try
			{
				$Fetch_User = $PDO->prepare("
					SELECT *
					FROM `users`
					INNER JOIN `user_currency`
					ON `users`.`ID` = `user_currency`.`ID`
					WHERE `users`.`ID` = ?
					LIMIT 1
				");
				$Fetch_User->execute([ $User_Query ]);
				$Fetch_User->setFetchMode(PDO::FETCH_ASSOC);
				$User = $Fetch_User->fetch();
			}
			catch ( PDOException $e )
			{
				HandleError( $e->getMessage() );
			}

			if ( !$User )
				return false;

			$Roster = $this->FetchRoster($User['ID']);
			if ( !$Roster )
				$Roster = null;

			if ( !isset($User) || !$User )
				return false;

			if ( $User['RPG_Ban'] )
				$Banned_RPG = true;
			else
				$Banned_RPG = false;

			if ( $User['Chat_Ban'] )
				$Banned_Chat = true;
			else
				$Banned_Chat = false;

			if ( $User['Playtime'] == 0 )
				$Playtime = "None";
			elseif ( $User['Playtime'] <= 59 )
				$Playtime = $User['Playtime']." Second(s)";
			elseif ( $User['Playtime'] >= 60 && $User['Playtime'] <= 3599 )
				$Playtime = floor($User['Playtime'] / 60)." Minute(s)";
			elseif ( $User['Playtime'] >= 3600 && $User['Playtime'] <= 86399 )
				$Playtime = round($User['Playtime'] / 3600, 1)." Hour(s)";
			else
				$Playtime = round($User['Playtime'] / 86400, 2)." Day(s)";

			return [
				'ID' => $User['ID'],
				'Username' => $User['Username'],
				'Roster' => $Roster,
				'Roster_Hash' => $User['Roster'],
				'Avatar' => DOMAIN_SPRITES . $User['Avatar'],
				'RPG_Ban' => $Banned_RPG,
				'Chat_Ban' => $Banned_Chat,
				'Money' => $User['Money'],
        'Abso_Coins' => $User['Abso_Coins'],
        'Trainer_Level' => number_format(FetchLevel($User['TrainerExp'], 'Trainer')),
        'Trainer_Level_Raw' => FetchLevel($User['TrainerExp'], 'Trainer'),
        'Trainer_Exp' => number_format($User['TrainerExp']),
				'Trainer_Exp_Raw' => $User['TrainerExp'],
				'Clan' => $User['Clan'],
				'Clan_Exp' => number_format($User['Clan_Exp']),
				'Clan_Exp_Raw' => $User['Clan_Exp'],
				'Clan_Rank' => $User['Clan_Rank'],
				'Clan_Title' => $User['Clan_Title'],
        'Map_Experience' => $User['Map_Experience'],
        'Map_ID' => $User['Map_ID'],
        'Map_Position' => [
          'Map_X' => $User['Map_X'],
          'Map_Y' => $User['Map_Y'],
          'Map_Z' => $User['Map_Z'],
        ],
        'Map_Steps_To_Encounter' => $User['Map_Steps_To_Encounter'],
        'Gender' => $User['Gender'],
				'Status' => $User['Status'],
				'Staff_Message' => $User['Staff_Message'],
				'Power' => $User['Power'],
				'Rank' => $User['Rank'],
				'Mastery_Points_Total' => $User['Mastery_Points_Total'],
				'Mastery_Points_Used' => $User['Mastery_Points_Used'],
				'Last_Active' => $User['Last_Active'],
				'Date_Registered' => $User['Date_Registered'],
				'Last_Page' => $User['Last_Page'],
				'Playtime' => $Playtime,
				'Auth_Code' => $User['Auth_Code'],
				'Theme' => $User['Theme'],
				'Battle_Theme' => $User['Battle_Theme'],
			];
		}

		/**
		 * Fetch a given user's roster.
		 * @param int $User_ID
		 */
		public function FetchRoster
		(
			int $User_ID
		)
		{
			global $PDO;

			if ( !$User_ID )
				return false;

			try
			{
				$User_Check = $PDO->prepare("SELECT `ID` FROM `users` WHERE `ID` = ? LIMIT 1");
				$User_Check->execute([ $User_ID ]);
				$User_Check->setFetchMode(PDO::FETCH_ASSOC);
				$User = $User_Check->fetch();
			}
			catch ( PDOException $e )
			{
				HandleError($e);
			}

			if ( !$User )
				return false;

			try
			{
				$Fetch_Roster = $PDO->prepare("SELECT * FROM `pokemon` WHERE `Owner_Current` = ? AND `Location` = 'Roster' ORDER BY `Slot` ASC LIMIT 6");
				$Fetch_Roster->execute([ $User_ID ]);
				$Fetch_Roster->setFetchMode(PDO::FETCH_ASSOC);
				$Roster = $Fetch_Roster->fetchAll();
			}
			catch ( PDOException $e )
			{
				HandleError($e);
			}

			return $Roster;
		}

		/**
		 * Remove some of the user's currency.
		 * @param int $User_ID - The id of the user that we're updating.
		 * @param string $Currency - The DB field name of the currency that we're updating.
		 * @param int $Amount - The amount of currency that we're removing.
		 */
		public function RemoveCurrency(int $User_ID, string $Currency, int $Amount)
		{
			global $PDO;

			if ( !$User_ID || !$Currency || !$Amount )
				return false;

			try
			{
        $PDO->beginTransaction();

				$Select_Query = $PDO->prepare("UPDATE `user_currency` SET `{$Currency}` = `{$Currency}` - ? WHERE `ID` = ? LIMIT 1");
				$Select_Query->execute([ $Amount, $User_ID ]);

        $PDO->commit();
			}
			catch ( PDOException $e )
			{
        $PDO->rollBack();
				HandleError($e);
			}

			return true;
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
				$Apply_Link_1 = "<a href='" . DOMAIN_ROOT . "/profile.php?id={$User['id']}'>";
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

    /**
     * Create and/or update the desired stat of a user.
     *
     * @param {int} $User_ID
     * @param {string} $Stat_Name
     * @param {int} $Stat_Value
     */
    public static function UpdateStat
    (
      int $User_ID,
      string $Stat_Name,
      int $Stat_Value
    )
    {
      global $PDO;

      if ( empty($Stat_Value) || $Stat_Value == 0 )
        return false;

      try
      {
        $Stat = $PDO->prepare("
          INSERT INTO `user_stats` (`User_ID`, `Stat_Name`, `Stat_Value`)
          VALUES (?, ?, ?)
          ON DUPLICATE KEY UPDATE `Stat_Value` = `Stat_Value` + VALUES(`Stat_Value`)
        ");
        $Stat->execute([ $User_ID, $Stat_Name, $Stat_Value ]);
      }
      catch ( PDOException $e )
      {
        HandleError($e);
      }

      return true;
    }
	}
