SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

--
-- Database: `absolute`
--
USE `absolute`;

TRUNCATE TABLE `news`;

INSERT INTO `news` (`id`, `News_Date`, `Poster_ID`, `News_Title`, `News_Text`) VALUES (NULL, '1747677443', '1', "Absolute\'s Grand Opening!", "Welcome to Absolute! After years of development, we\'re excited to welcome you to our unique text-based Pokémon experience.\r\n\r\nPokémon Absolute is an online text-based Pokémon RPG that combines the best elements from the official Pokémon games with brand new features designed to enhance your Pokémon journey. Absolute offers an immersive experience where you can catch, train, and battle with over 800 unique Pokémon, as well as interact with other players.\r\n\r\n### Dedicated Battle System\r\n- Battle with over 800 unique Pokémon\r\n- Experience field-effects and terrains\r\n- Use 800+ unique moves\r\n- Master 200+ Pokémon abilities\r\n- Equip 150+ different items\r\n\r\n### Open World Exploration\r\n- Explore hand-crafted regions and maps\r\n- Discover rare and unique wild Pokémon\r\n- Complete quests and challenges\r\n\r\n### Clans System\r\n- Create or join clans with other players\r\n- Earn experience and resources for your clan\r\n- Level up Clan Upgrades for exclusive benefits\r\n\r\n### Real-Time Chat\r\n- Connect with other trainers through our in-game chat\r\n- Form friendships and rivalries\r\n- Plan trades and battles\r\n\r\n<hr class=\'faded\' />\r\n\r\n## Connect With Us\r\n\r\nCome join our comfy community over on Discord!\r\n<a href='https://discord.gg/SHnvbsS\' target='_blank'>\r\n  <img src='https://discord.com/api/guilds/269182206621122560/widget.png?style=banner2' alt='Discord Invite Banner' />\r\n</a>\r\n\r\n## Special Thanks\r\n\r\nA huge thank you to all our beta testers and everyone who has supported this project. Pokémon Absolute wouldn\'t be possible without your feedback and enthusiasm!\r\n\r\nWe can\'t wait to see you in-game!\r\n\r\n*— The Pokémon Absolute Team*");

COMMIT;
