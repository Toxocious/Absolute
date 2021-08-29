			</main>

			<footer>
				<div class='copyright'>
					Pok&eacute;mon Absolute &copy; 2018 - <?= date('Y'); ?> Toxocious<br />
					Pok&eacute;mon &copy; 1995 - <?= date('Y'); ?> Nintendo/Creatures Inc./Game Freak Inc, please support the <a href='http://pokemon.com' target='_blank' rel='noopener noreferrer'>official release.</a>
				</div>


				<div class='social-links'>
					<a href='https://github.com/toxocious' target='_blank' rel='noopener noreferrer' style='color: rgb(250, 250, 250);'>
            <svg xmlns='http://www.w3.org/2000/svg' width='38' height='38' preserveAspectRatio='xMinYMin meet' viewBox='0 0 256 236'>
              <path fill='#E24329' d='M128.075 236.075l47.104-144.97H80.97l47.104 144.97z'/>
              <path fill='#FC6D26' d='M128.075 236.074L80.97 91.104H14.956l113.119 144.97z'/>
              <path fill='#FCA326' d='M14.956 91.104L.642 135.16a9.752 9.752 0 0 0 3.542 10.903l123.891 90.012-113.12-144.97z'/>
              <path fill='#E24329' d='M14.956 91.105H80.97L52.601 3.79c-1.46-4.493-7.816-4.492-9.275 0l-28.37 87.315z'/>
              <path fill='#FC6D26' d='M128.075 236.074l47.104-144.97h66.015l-113.12 144.97z'/>
              <path fill='#FCA326' d='M241.194 91.104l14.314 44.056a9.752 9.752 0 0 1-3.543 10.903l-123.89 90.012 113.119-144.97z'/>
              <path fill='#E24329' d='M241.194 91.105h-66.015l28.37-87.315c1.46-4.493 7.816-4.492 9.275 0l28.37 87.315z'/>
            </svg>
					</a>
				</div>

			</footer>
		</div>

    <?php
      /**
       * Include the necessary Absolute Chat scripts.
       */
      if ( isset($_SESSION['abso_user']) )
      {
    ?>
      <script type='text/javascript' src='<?= DOMAIN_ROOT; ?>/js/AbsoChat/absochat.js'></script>
      <script type='text/javascript' src='<?= DOMAIN_ROOT; ?>/js/AbsoChat/Handler.js'></script>
      <script type='text/javascript'>
        (function()
        {
          Absolute.user = {
            user_id: <?= $User_Data['ID']; ?>,
            postcode: <?= $User_Data['Auth_Code']; ?>,
          }

          Absolute.Enable();

          const Chat_Element = document.querySelector('#chatContent');
          const Perfect_Scrollbar = new PerfectScrollbar(Chat_Element);
          const Chat_Input = document.getElementById('chatMessage');
          Chat_Input.addEventListener('keydown', (event) => {
            if ( event.keyCode === 13 )
            {
              event.preventDefault();

              const Chat_Message = Chat_Input.value.trim();
              if ( Chat_Message !== '' && Absolute.user.connected )
              {
                socket.emit('chat-message',
                {
                  user: Absolute.user,
                  text: Chat_Message
                });

                Chat_Input.value = '';

                Perfect_Scrollbar.update();
              }
            }
          });
        })();
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
