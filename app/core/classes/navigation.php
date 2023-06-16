<?php
  require_once $_SERVER['DOCUMENT_ROOT'] . '/staff/functions/permissions.php';
  require_once $_SERVER['DOCUMENT_ROOT'] . '/staff/functions/report.php';

	Class Navigation
	{
		/**
		 * Render the nav bar.
		 */
		public function Render($Class)
		{
			global $PDO;
			global $User_Data;

			/**
			 * Parse the current URL.
			 */
			$URL = parse_url((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]");

			/**
			 * Call for the necessary pages via the `pages` table.
			 */
			try
			{
				$Query_Headers = $PDO->prepare("SELECT * FROM `navigation` WHERE `Class` = ? AND `Type` = 'Header'");
				$Query_Headers->execute([ $Class ]);
				$Query_Headers->setFetchMode(PDO::FETCH_ASSOC);
				$Headers = $Query_Headers->fetchAll();

				$Query_Links = $PDO->prepare("SELECT * FROM `navigation` WHERE `Class` = ? AND `Type` = 'Link'");
				$Query_Links->execute([ $Class ]);
				$Query_Links->setFetchMode(PDO::FETCH_ASSOC);
				$Links = $Query_Links->fetchAll();
			}
			catch ( PDOException $e )
			{
				HandleError($e);
			}

			echo "
				<nav>
          <button id='navButton'>
            <svg viewBox='0 0 22 22' preserveAspectRatio='xMidYMid meet'>
              <g>
                <path d='M21,6H3V5h18V6z M21,11H3v1h18V11z M21,17H3v1h18V17z'></path>
              </g>
            </svg>
          </button>

          <button id='chatButton'>
            <img src='" . DOMAIN_SPRITES . "/Pokemon/Icons/Normal/359.png' />
          </button>
			";

			// Display the Staff Panel button/Index button, given the user is a staff member.
			if ( $User_Data['Is_Staff'] )
			{
				if ( strpos($URL['path'], '/staff/') === false )
				{
					$Link_URL = DOMAIN_ROOT . '/staff/';
					$Link_Name = 'Staff Panel';
				}
				else
				{
					$Link_URL = DOMAIN_ROOT . '/news.php';
					$Link_Name = 'Index';
				}

        $Notification_Amount = 0;
        $Notification_Text = '';

        $Reported_Users = count(GetActiveReports());
        if ( $Reported_Users > 0 )
          $Notification_Amount += $Reported_Users;

        if ( $Notification_Amount > 0 && $Link_Name == 'Staff Panel' )
          $Notification_Text = " (<b style='color: red;'> {$Notification_Amount} </b>)";

				echo "
					<div class='nav-container'>
            <div class='button'>
              <a href='{$Link_URL}'>{$Link_Name}{$Notification_Text}</a>
            </div>
				";
			}
			else
			{
				echo "
					<div class='nav-container'>
				";
			}

			// Loop through navigation headers.
			$Display_Links = '';
			foreach ( $Headers as $Key => $Head )
			{
				/**
				 * Loop through the appropriate links.
				 * Only display if it's under the proper menu, and you have the sufficient power level.
				 */
				foreach ( $Links as $Key => $Link )
				{
					/**
					 * If the link is set to be hidden, continue.
					 */
					if ( $Link['Hidden'] == 'yes' )
					{
						continue;
					}

					/**
					 * Staff panel links.
					 */
					if ( $Class == 'Staff' )
					{
						if ( $Link['Menu'] === $Head['Menu'] && CheckUserPermission($Link['Required_Permission']) )
						{
              $Notification_Amount = '';

              switch ( $Link['Name'] )
              {
                case 'Reported Users':
                  $Reported_Users = count(GetActiveReports());
                  if ( $Reported_Users > 0 )
                    $Notification_Amount = " (<b style='color: red;'> {$Reported_Users} </b>)";
                  break;
              }

							$Display_Links .= "
								<div class='dropdown-item'>
									<a href='javascript:void(0);' onclick='LoadPage(\"/staff/{$Link['Link']}\");'>{$Link['Name']}{$Notification_Amount}</a>
								</div>
							";
						}
					}

					/**
					 * Regular member links.
					 */
					else
					{
						if ( $Link['Menu'] === $Head['Menu'] && CheckUserPermission($Link['Required_Permission']) )
						{
							$Display_Links .= "
								<div class='dropdown-item'>
									<a href='" . DOMAIN_ROOT . "{$Link['Link']}'>{$Link['Name']}</a>
								</div>
							";
						}
					}
				}

				/**
				 * Render the menu item and it's dropdown contents.
				 */
				echo "
					<div class='nav-item has-dropdown'>
            <a href='javascript:void(0);'>
							<span>{$Head['Name']}</span>
						</a>
						<ul class='dropdown'>
							{$Display_Links}
						</ul>
					</div>
				";

				$Display_Links = '';
			}

			echo "
					</div>
				</nav>
			";
		}
	}
