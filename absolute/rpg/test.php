<?php
	require_once $_SERVER['DOCUMENT_ROOT'] . '/core/required/session.php';
    require_once $_SERVER['DOCUMENT_ROOT'] . '/core/required/user_session.php';
?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">

        <title>Layout Test &mdash; Pok&eacute;mon Absolute</title>

        <link type='text/css' rel='stylesheet' href='/themes/css/root.css' />
        <link type='text/css' rel='stylesheet' href='/themes/css/styles/absol.css' />
    </head>

    <body>
        <!-- -->
        <header>
            <section class='user-bar'>
                <div class='user-avatar'>
                    <img src='<?= $User_Data['Avatar']; ?>' />
                </div>

                <div class='user-info'>
                    <h2><?= $User_Data['Username']; ?></h2>
                    <h4><?= $User_Data['Rank']; ?></h4>
                </div>
            </section>
        </header>

        <!-- -->
        <main>
        </main>

        <!-- -->
        <footer>

        </footer>

        <!-- -->
        <style>
            *, *::before, *::after {
                box-sizing: border-box;
            }

            html {
                background: var(--color-septary);
                background-image: var(--background-gradient);
                background-attachment: fixed;
                background-repeat: no-repeat;
                background-size: cover;
                min-height: 100vh;
            }

            body {
                border-color: var(--color-white);
                border-style: solid;

                border: 2px solid;
                border-top: none;
                border-bottom: none;

                max-width: 1100px;
                margin: 0 auto;
            }

            header {
                background: var(--banner-image);
                background-position: center;
                background-size: var(--banner-bg-size);
                display: var(--display-flex);
                flex-basis: 100%;
                height: calc(var(--banner-height) * 2);
                justify-content: flex-start;
                position: relative;

                .user-bar {
                    display: var(--display-flex);

                    border: none;
                    border-top: 1px solid var(--color-mid-grey);

                    overflow: hidden;
                    position: absolute;

                    bottom: 0px;
                    left: 0px;

                    padding: 0.5em;

                    width: 100%;

                    &::before {
                        align-items: center;
                        animation: userbar-slider-silver 10s linear infinite reverse;
                        background-repeat: no-repeat;
                        background: linear-gradient(90deg, rgba(255, 255, 255, 0) 70%, rgba(255, 255, 255, 0.4) 95%, rgba(255, 255, 255, 0));
                        background-size: 500% 500%;
                        content: " ";
                        height: 2px;
                        position: absolute;
                        left: 0;
                        top: -1px;
                        width: 100%;
                    }

                    &::after {
                        content: " ";
                        position: absolute;
                        left: 0;
                        /* top: -14px; */
                        bottom: -14px;
                        width: 100%;
                        height: 28px;

                        animation: userbar-slider 10s linear infinite reverse;

                        background:
                            /* bright traveling hot spot */
                            radial-gradient(
                                ellipse 120px 3px at 80% 50%,
                                rgba(255, 255, 255, 0.95) 0%,
                                rgba(180, 220, 255, 0.6) 40%,
                                transparent 100%
                            ),
                            /* wider blue bloom */
                            radial-gradient(
                                ellipse 300px 14px at 80% 50%,
                                rgba(40, 100, 220, 0.5) 0%,
                                transparent 100%
                            ),
                            /* base line glow across full width */
                            linear-gradient(
                                to bottom,
                                transparent 0%,
                                rgba(30, 80, 180, 0.3) 50%,
                                transparent 100%
                            );

                        background-size: 500% 100%, 500% 100%, 100% 100%;
                        background-repeat: no-repeat;

                        /* soft outer glow */
                        filter: blur(0.6px);
                    }

                    div {
                        height: 100px;
                        width: 100px;
                    }
                }
            }

            @keyframes userbar-slider-silver {
                0% {
                    background-position-x: -200%
                }

                to {
                    background-position-x: 200%
                }
            }

            @keyframes userbar-slider {
                0%   { background-position: 0% 50%, 0% 50%, 0% 50%; }
                100% { background-position: 100% 50%, 100% 50%, 0% 50%; }
            }
        </style>
    </body>
</html>
