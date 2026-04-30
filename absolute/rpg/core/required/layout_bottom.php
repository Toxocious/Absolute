			</main>

        <?php
            if (defined('LOCAL'))
            {
                echo "<footer style='display: flex; flex-direction: column;'>";
                try {
                    $stats = $PDO->getQueryStats();

                    echo "
                        <div>
                            Page Generation Time: " . round(microtime(true) - $page_script_start_time, 4) * 1000 . "ms<br>
                            " . sprintf(
                            "Database Queries: %d &mdash; Total: %.4fms &mdash; Avg: %.4fms &mdash; Max: %.4fms",
                            $stats['count'],
                            $stats['total_time'] * 1000,
                            $stats['average_time'] * 1000,
                            $stats['max_time'] * 1000
                        ) . "
                        </div>
                    ";

                    // Render all SQL queries that were executed for the page load.
                    if ( false )
                    {
                        foreach ( $stats['queries'] as $query )
                        {
                            echo "<hr class='faded' /><div>" . htmlspecialchars($query['query']) . "</div>";
                            if ( !empty($query['params']) )
                            {
                                echo "<div>Params: " . json_encode($query['params']) . "</div>";
                            }
                            echo "<div>Duration: " . round($query['duration'] * 1000, 4) . "ms</div>";
                        }
                    }
                } catch (Exception $e) {
                    error_log("Failed to get query stats: " . $e->getMessage());
                }
                echo "</footer>";
            }
            else
            {
        ?>
			<footer>
				<div>
					Pok&eacute;mon Absolute &copy; 2018 - <?= date('Y'); ?> Toxocious<br />
					Pok&eacute;mon &copy; 1995 - <?= date('Y'); ?> Nintendo/Creatures Inc./Game Freak Inc, please support the <a href='http://pokemon.com' target='_blank' rel='noopener noreferrer'>official release.</a>
				</div>
            </footer>
        <?php
            }
        ?>

        <!-- absol peeker -->
        <div class="footer-peeker">
            <img src="<?= DOMAIN_SPRITES; ?>/Assets/Layout/Peekers/<?= (isset($User_Data['Theme']) ? $User_Data['Theme'] : 'absol'); ?>.png" alt="<?= (isset($User_Data['Theme']) ? $User_Data['Theme'] : 'absol'); ?>" class="" width="200" height="244" loading="lazy" decoding="async">
        </div>

        <!-- -->

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
                User_ID: <?= (int) $User_Data['ID']; ?>,
                Username: <?= json_encode($User_Data['Username']); ?>,
                Rank: <?= json_encode($User_Data['Rank']); ?>,
                Auth_Code: <?= json_encode($User_Data['Auth_Code']); ?>,
                Avatar: <?= json_encode(str_replace('https://localhost/', '../', $User_Data['Avatar'])); ?>,
                Connected: true,
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
                    }
                }
            });
      </script>
    <?php
        }
    ?>
	</body>
</html>
