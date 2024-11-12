let socket;

const Absolute = {
    port: 8080,
    active: false,
    user: {},
    messages: {},

    isConnected: function () {
        return socket.connected;
    },

    handleInputBox: function () {
        if (!Absolute.user.Connected) {
            $('#chatMessage').css('background', '#666').attr('disabled', true);
        } else {
            $('#chatMessage').css('background', '').attr('disabled', false);
        }
    },

    /**
     * Initialization of Absolute Chat.
     */
    Enable: function () {
        /**
         * Structure the socket commands.
         */
        if (typeof socket != 'object') {
            /**
             * Create new array of messages.
             */
            Handler.Messages = [];

            /**
             * Start the connection to the server.
             */
            // socket = io('https://www.absoluterpg.com:' + Absolute.port,
            socket = io('http://localhost:8080', {
                withCredentials: true,
                reconnectionDelay: 2000,
                reconnectionAttempts: 15,
                reconnection: true,
                secure: true,
            });

            /**
             * Handle the connection of the user.
             */
            socket.on('connect', function () {
                Absolute.active = true;
                Absolute.user.Connected = true;

                console.log('[Chat | Client] Connected to socket.');

                Handler.Clear();
                Absolute.handleInputBox();

                socket.emit('auth', {
                    UserID: Absolute.user.UserID,
                    Auth_Code: Absolute.user.Auth_Code,
                    Connected: Absolute.user.Connected,
                });
            });

            /**
             * Handle the user upon disconnecting.
             */
            socket.on('disconnect', function () {
                Absolute.handleInputBox();
                Handler.AddMessage({
                    User: {
                        ID: 3,
                        Name: 'Absol',
                        Rank: 'bot',
                        Avatar: '../images/Avatars/Custom/3.png',
                    },
                    Message: {
                        Content:
                            'You have been disconnected from Absolute Chat. Please refresh the page.',
                        Private: {
                            isPrivate: true,
                            Private_To: Absolute.user.UserID,
                        },
                    },
                });
            });

            /**
             * Handle sent messages.
             */
            socket.on('chat-message', function (data) {
                Handler.AddMessage(data);
            });
        }

        Handler.Display();
    },

    /**
     * Disabling of Absolute Chat.
     */
    Disable: function () {
        Absolute.active = false;
    },
};
