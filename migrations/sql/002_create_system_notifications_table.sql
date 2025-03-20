SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

--
-- Database: `absolute`
--
USE `absolute`;

CREATE TABLE `system_notifications` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `message` text NOT NULL,
  `metadata` json DEFAULT NULL,
  `created_at` int(11) NOT NULL,
  `processed` tinyint(1) NOT NULL DEFAULT 0,
  `processed_at` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_processed_created` (`processed`, `created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

COMMIT;
