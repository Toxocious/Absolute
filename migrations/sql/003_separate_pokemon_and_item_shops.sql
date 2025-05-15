SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

--
-- Database: `absolute`
--
USE `absolute`;

UPDATE `shop_items` SET `Obtained_Place` = 'item_shop' WHERE `Obtained_Place` = 'pokemon_shop';

INSERT INTO `shops` (`ID`, `Name`, `Description`, `Sells_Pokemon`, `Sells_Items`, `Obtained_Place`, `Shiny_Odds`, `Ungendered_Odds`) VALUES (NULL, 'Item Shop', 'Welcome to the Item Shop!<br />We keep a large stock of beneficial items in store!', '0', '1', 'item_shop', '0', '0');

ALTER TABLE `shop_pokemon` DROP `Remaining`;
ALTER TABLE `shop_items` DROP `Remaining`;

COMMIT;
