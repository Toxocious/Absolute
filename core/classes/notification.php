<?php
	/**
	 * Dedicated to class to handle sending notifications to the user on the front-end.
	 */
	Class Notification
	{
		public $PDO;
		public $Purify;
		public $User_Data;

		public function __construct()
		{
			global $PDO;
			$this->PDO = $PDO;

			global $User_Data;
			$this->User = $User_Data;
		}

		/**
		 * Send a notification to a user.
		 */
		public function SendNotification($Sent_By, $Sent_To, $Message)
		{
			global $PDO;
			global $Purify;
			global $User_Data;

			$Sent_By = $Purify->Cleanse($Sent_By);
			$Sent_To = $Purify->Cleanse($Sent_To);
			$Message = $Purify->Cleanse($Message);

			try
			{
				$Insert = $PDO->prepare("INSERT INTO `notifications` (`Message`, `Sent_To`, `Sent_By`, `Sent_On`) VALUES (?, ?, ?, ?)");
				$Insert->execute([ $Message, $Sent_To, $Sent_By, time() ]);
			}
			catch ( PDOException $e )
			{
				HandleError( $e->getMessage() );
			}
		}

		/**
		 * Display any unseen notifications to the appropriate user.
		 */
		public function ShowNotification($User_ID)
		{
			global $PDO;
			global $Purify;

			$User = $Purify->Cleanse($User_ID);

			/**
			 * Fetch all unseen notifications so that they may be displayed.
			 * Also set all unseen notifications to seen, so they won't be displayed again.
			 */
			try
			{
				$Fetch_Notification = $PDO->prepare("SELECT * FROM `notifications` WHERE `Sent_To` = ? AND `Seen` = 'no'");
				$Fetch_Notification->execute([ $User ]);
				$Fetch_Notification->setFetchMode(PDO::FETCH_ASSOC);
				$Notifications = $Fetch_Notification->fetchAll();

				/**
				 * Loop through each unseen notification.
				 */
				if ( $Notifications && count($Notifications) > 0 )
				{
					foreach ( $Notifications as $Key => $Value )
					{
						/**
						 * Set the seen status of the notification to 'yes', so it doesn't get displayed anymore.
						 */
						$Update_Notification = $PDO->prepare("UPDATE `notifications` SET `Seen` = 'yes' WHERE `ID` = ?");
						$Update_Notification->execute([ $Value['ID'] ]);

						echo "
							<div class='notification'>
								<div style='float: right;'>
									<a href='javascript:void(0);' onclick='$(this).parent().parent().hide();'>
										<b>x</b>
									</a>
								</div>
								
								{$Value['Message']}
							</div>
						";
					}
				}
			}
			catch ( PDOException $e )
			{
				HandleError( $e->getMessage() );
			}
		}
	}