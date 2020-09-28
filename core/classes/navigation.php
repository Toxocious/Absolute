<?php
	Class Navigation
	{
		public $PDO;
		public $User_Data;

		public function __construct()
		{
			global $PDO;
			global $User_Data;

			$this->PDO = $PDO;
			$this->User_Data = $User_Data;
		}

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
				HandleError( $e->getMessage() );
			}

			// Set the default width of the navbar.
			$Nav_Width = " style='width: 100%;'";

			echo "
				<nav>
			";

			// Display the Staff Panel button/Index button, given the user is a staff member.
			if ( $User_Data['Power'] > 1 )
			{
				$Nav_Width = " style='width: calc(100% - 203px);'";

				if ( strpos($URL['path'], '/staff/') === false )
				{
					$Link_URL = '/staff/';
					$Link_Name = 'Staff Panel';
				}
				else
				{
					$Link_URL = '/index.php';
					$Link_Name = 'Index';
				}

				echo "
					<div class='button' style='margin-right: 5px;'>
						<a href='{$Link_URL}'>{$Link_Name}</a>
					</div>
				";
			}

			echo "
					<ul class='nav-container'{$Nav_Width}>
			";

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
						if ( $Link['Menu'] === $Head['Menu'] && $Link['Power'] <= $User_Data['Power'] )
						{
							$Display_Links .= "
								<li class='dropdown-item'>
									<a href='javascript:void(0);' onclick='LoadContent(\"/staff/{$Link['Link']}\");'>{$Link['Name']}</a>
								</li>
							";
						}
					}

					/**
					 * Regular member links.
					 */
					else
					{
						if ( $Link['Menu'] === $Head['Menu'] && $Link['Power'] <= $User_Data['Power'] )
						{
							$Display_Links .= "
								<li class='dropdown-item'>
									<a href='{$Link['Link']}'>{$Link['Name']}</a>
								</li>
							";
						}
					}
				}

				/**
				 * Render the menu item and it's dropdown contents.
				 */
				echo "
					<li class='nav-item has-dropdown'>
						<a href='javascript:void(0);'>
							{$Head['Name']}
						</a>
						<ul class='dropdown'>
							{$Display_Links}
						</ul>
					</li>
				";

				$Display_Links = '';
			}

			echo "
					</ul>
				</nav>
			";
		}
	}