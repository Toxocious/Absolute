</main>

			<footer>
        <?php
          if (defined('LOCAL'))
          {
            $Query_Count = $database_connections['evo_chronicles_rpg']->get_count();

            echo "
              <div>
                Page generated in " . round(microtime(true) - $page_script_start_time, 4) . "s<br />
                <b>Total SQL Queries</b>: " . number_format($Query_Count) . "
              </div>
            ";
          }
          else
          {
        ?>

				<div class='copyright'>
					Pokémon Evo-Chronicles RPG © 2018 - <?= date('Y'); ?> Toxocious<br />
					Pok&eacute;mon &copy; 1995 - <?= date('Y'); ?> Nintendo/Creatures Inc./Game Freak Inc, please support the <a href='http://pokemon.com' target='_blank' rel='noopener noreferrer'>official release.</a>
				</div>


				<div class='social-links'>
					<a href='https://github.com/toxocious' target='_blank' rel='noopener noreferrer' style='color: rgb(250, 250, 250); font-size: 3em;'>
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="icon" viewBox="0 0 16 16" style='height: 1em; width: 1em;'>
              <path d="M8 0C3.58 0 0 3.58 0 8c0 3.54 2.29 6.53 5.47 7.59.4.07.55-.17.55-.38 0-.19-.01-.82-.01-1.49-2.01.37-2.53-.49-2.69-.94-.09-.23-.48-.94-.82-1.13-.28-.15-.68-.52-.01-.53.63-.01 1.08.58 1.23.82.72 1.21 1.87.87 2.33.66.07-.52.28-.87.51-1.07-1.78-.2-3.64-.89-3.64-3.95 0-.87.31-1.59.82-2.15-.08-.2-.36-1.02.08-2.12 0 0 .67-.21 2.2.82.64-.18 1.32-.27 2-.27.68 0 1.36.09 2 .27 1.53-1.04 2.2-.82 2.2-.82.44 1.1.16 1.92.08 2.12.51.56.82 1.27.82 2.15 0 3.07-1.87 3.75-3.65 3.95.29.25.54.73.54 1.48 0 1.07-.01 1.93-.01 2.2 0 .21.15.46.55.38A8.012 8.012 0 0 0 16 8c0-4.42-3.58-8-8-8z"></path>
            </svg>
					</a>
				</div>

        <?php
          }
        ?>
			</footer>
		</div>

    <?php
      /**
       * Include the necessary Evo-Chronicles RPG Chat scripts.
       */
      if ( isset($_SESSION['EvoChroniclesRPG']) )
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
          const ChatClient = new ECRPGChatClient.ECRPG(User);
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
