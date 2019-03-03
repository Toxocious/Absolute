<?php
	require '../../required/session.php';

	if ( isset($_SESSION['Trade']) )
	{
		/**
		 * If both sides of the trade are empty, throw an error.
		 * There's no need to create the trade if no appropriate data is being sent.
		 */
		if
		( 
			empty($_SESSION['Trade']['Sender']['Pokemon']) && empty($_SESSION['Trade']['Sender']['Currency']) && empty($_SESSION['Trade']['Sender']['Items']) && 
			empty($_SESSION['Trade']['Receiver']['Pokemon']) && empty($_SESSION['Trade']['Receiver']['Currency']) && empty($_SESSION['Trade']['Receiver']['Items'])
		)
		{
			echo "<div class='error' style='margin-bottom: 5px;'>Both sides of the trade may not be empty.</div>";
		}
		else
		{
			$Receiver_Data = $UserClass->FetchUserData( $_SESSION['Trade']['Receiver']['User'] );
			echo "<div class='success' style='margin-bottom: 5px;'>You have successfully sent a trade to {$Receiver_Data['Username']}</div>";

			/**
			 * Process the sender's half of the trade.
			 */
			$Sender_Pokemon = '';
			$Sender_Currency = '';
			$Sender_Items = '';
			foreach( $_SESSION['Trade']['Sender']['Pokemon'] as $Key => $Pokemon_1 )
			{
				try
				{
					$Update_Location = $PDO->prepare("UPDATE `pokemon` SET `Location` = 'Trade' WHERE `ID` = ? LIMIT 1");
					$Update_Location->execute([ $Pokemon_1['ID'] ]);
				}
				catch( PDOException $e )
				{
					HandleError( $e->getMessage() );
				}

				$Sender_Pokemon .= $Pokemon_1['ID'] . ",";
			}
			foreach( $_SESSION['Trade']['Sender']['Currency'] as $Key => $Currency_1 )
			{
				$Sender_Currency .= $Currency_1['Currency'] . "-" . $Currency_1['Quantity'] . ",";
			}
			foreach( $_SESSION['Trade']['Sender']['Items'] as $Key => $Items_1 )
			{
				$Sender_Items .= $Items_1['Row'] . "-" . $Items_1['ID'] . "-" . $Items_1['Quantity'] . "-" . $Items_1['Owner'] . ",";
			}

			/**
			 * Process the receiver's half of the trade.
			 */
			$Receiver_Pokemon = '';
			$Receiver_Currency = '';
			$Receiver_Items = '';
			foreach( $_SESSION['Trade']['Receiver']['Pokemon'] as $Key => $Pokemon_2 )
			{
				try
				{
					$Update_Location = $PDO->prepare("UPDATE `pokemon` SET `Location` = 'Trade' WHERE `ID` = ? LIMIT 1");
					$Update_Location->execute([ $Pokemon_2['ID'] ]);
				}
				catch( PDOException $e )
				{
					HandleError( $e->getMessage() );
				}

				$Receiver_Pokemon .= $Pokemon_2['ID'] . ",";
			}
			foreach( $_SESSION['Trade']['Receiver']['Currency'] as $Key => $Currency_2 )
			{
				$Receiver_Currency .= $Currency_2['Currency'] . "-" . $Currency_2['Quantity'] . ",";
			}
			foreach( $_SESSION['Trade']['Receiver']['Items'] as $Key => $Items_2 )
			{
				$Receiver_Items .= $Items_2['Row'] . "-" . $Items_2['ID'] . "-" . $Items_2['Quantity'] . "-" . $Items_2['Owner'] . ",";
			}

			/**
			 * Create a row in the database table `trades` with the necessary trade information.
			 */
			try
			{
				$Create_Query = $PDO->prepare("
					INSERT INTO `trades` (
						`Sender`,
						`Sender_Pokemon`,
						`Sender_Items`,
						`Sender_Currency`,
						`Receiver`,
						`Receiver_Pokemon`,
						`Receiver_Items`,
						`Receiver_Currency`
					)
					VALUES (?, ?, ?, ?, ?, ?, ?, ?)
				");
				$Create_Query->execute([
					$_SESSION['Trade']['Sender']['User'],
					substr($Sender_Pokemon, 0, -1),
					substr($Sender_Items, 0, -1),
					substr($Sender_Currency, 0, -1),
					$_SESSION['Trade']['Receiver']['User'],
					substr($Receiver_Pokemon, 0, -1),
					substr($Receiver_Items, 0, -1),
					substr($Receiver_Currency, 0, -1)
				]);
			}
			catch( PDOException $e )
			{
				HandleError( $e->getMessage() );
			}
		}
	}
	else
	{
		echo "<div class='error' style='margin-bottom: 5px;'>The trade could not be made, as as error has occurred.</div>";
	}