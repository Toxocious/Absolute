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
		 * Render the navigation bar.
		 */
		public function Render($Class)
		{
			global $PDO;
			global $User_Data;

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
			
			/**
			 * Display the navigation bar and links.
			 */
			echo "<nav>";
			$Nav_Width = " style='width: 100%;'";
			if ( $User_Data['Power'] > 1 )
			{
				$Nav_Width = " style='width: calc(100% - 195px);'";

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
				<div class='navigation'{$Nav_Width}>
					<ul>
			";

			/**
			 * Loop through the nav headers.
			 */
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
								<li>
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
								<li>
									<a href='{$Link['Link']}'>{$Link['Name']}</a>
								</li>
							";
						}
					}
				}

				echo "
					<li class='dropdown'>
						<a href='javascript:void(0);'>{$Head['Name']}</a>
						<ul class='dropdown-content'>
							{$Display_Links}
						</ul>
					</li>
				";

				$Display_Links = '';
			}

			echo "
					</ul>
				</div>
			";
			
			echo "</nav>";
		}
	}