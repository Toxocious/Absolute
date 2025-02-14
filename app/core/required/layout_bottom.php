			</main>

			<footer>
        <?php
          if (defined('LOCAL'))
          {
                  try {
                      $stats = $PDO->getQueryStats();

                      echo "
                          <div class='debug-info flex flex-row'>
                              Generation Time: " . round(microtime(true) - $page_script_start_time, 4) * 1000 . "ms<br>
                              " . sprintf(
                                "Queries: %d | Total: %.4fms | Avg: %.4fms | Max: %.4fms",
                                $stats['count'],
                                $stats['total_time'] * 1000,
                                $stats['average_time'] * 1000,
                                $stats['max_time'] * 1000
                            ) . "
                          </div>
                      ";
                  } catch (Exception $e) {
                      error_log("Failed to get query stats: " . $e->getMessage());
                  }
          }
          else
          {
        ?>

				<div class='copyright'>
					Pok&eacute;mon Absolute &copy; 2018 - <?= date('Y'); ?> Toxocious<br />
					Pok&eacute;mon &copy; 1995 - <?= date('Y'); ?> Nintendo/Creatures Inc./Game Freak Inc, please support the <a href='http://pokemon.com' target='_blank' rel='noopener noreferrer'>official release.</a>
				</div>

        <?php
          }
        ?>
			</footer>
		</div>

    <?php
      /**
       * Include the necessary Absolute Chat scripts.
       */
      if ( isset($_SESSION['Absolute']) )
      {
    ?>
      <script type='text/javascript' src='<?= DOMAIN_ROOT; ?>/js/chat/client.js'></script>
      <script type='text/javascript'>
          /**
           * Set up the user object that the socket will send.
           */
          let User = {
              User_ID: <?= $User_Data['ID']; ?>,
              Username: '<?= $User_Data['Username']; ?>',
              Rank: '<?= $User_Data['Rank']; ?>',
              Auth_Code: '<?= $User_Data['Auth_Code']; ?>',
              Avatar: '<?= $User_Data['Avatar']; ?>',
              Connected: false,
          }

          /**
           * Set up a new instance of the chat client socket.
           */
          const ChatClient = new AbsoluteChatClient.Absolute(User);
          ChatClient.Initialize();

          /**
           * Handle sent chat messages.
           */
          const Chat_Element = document.querySelector('#chatContent');
          const Perfect_Scrollbar = new PerfectScrollbar(Chat_Element);
          const Chat_Input = document.getElementById('chatMessage');
          Chat_Input.addEventListener('keydown', (event) => {
            if ( event.keyCode === 13 )
            {
              event.preventDefault();

              const Chat_Message = Chat_Input.value.trim();
              if ( Chat_Message !== '' && User.Connected )
              {

                ChatClient.socket.emit('chat-message',
                {
                  User: User,
                  Message: {
                    Text: Chat_Message,
                  }
                });

                Chat_Input.value = '';

                Perfect_Scrollbar.update();
              }
            }
          });
      </script>
    <?php
      }
    ?>

		<script type='text/javascript'>
      (function(root, document) {
        "use strict";

        [].forEach.call(document.getElementsByClassName("popup"), function(el) {
          el.lightbox = new IframeLightbox(el, {
            scrolling: false,
            rate: 500,
            touch: false,
          });
        });
      })("undefined" !== typeof window ? window : this, document);
		</script>
	</body>
</html>
