let socket;

const ECRPGClient = {
    port: 8080,
    active: false,
    user: {},
    messages: {},

    isConnected: function () {
        return socket.connected;
    },

    handleInputBox: function () {
        if (!ECRPGClient.user.Connected) {
            $('#chatMessage').css('background', '#666').attr('disabled', true);
        } else {
            $('#chatMessage').css('background', '').attr('disabled', false);
        }
    },

    /**
     * Initialization of Evo-Chronicles RPG Chat.
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
            // socket = io('https://www.evochronicles.com:' + ECRPGClient.port,
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
                ECRPGClient.active = true;
                ECRPGClient.user.Connected = true;

                console.log('[Chat | Client] Connected to socket.');

                Handler.Clear();
                ECRPGClient.handleInputBox();

                socket.emit('auth', {
                    UserID: ECRPGClient.user.UserID,
                    Auth_Code: ECRPGClient.user.Auth_Code,
                    Connected: ECRPGClient.user.Connected,
                });
            });

            /**
             * Handle the user upon disconnecting.
             */
            socket.on('disconnect', function () {
                ECRPGClient.handleInputBox();
                Handler.AddMessage({
                    User: {
                        ID: 3,
                        Name: 'Absol',
                        Rank: 'bot',
                        Avatar: '../images/Avatars/Custom/3.png',
                    },
                    Message: {
                        Content:
                            'You have been disconnected from Evo-Chronicles RPG Chat. Please refresh the page.',
                        Private: {
                            isPrivate: true,
                            Private_To: ECRPGClient.user.UserID,
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
     * Disabling of Evo-Chronicles RPG Chat.
     */
    Disable: function () {
        ECRPGClient.active = false;
    },
};
