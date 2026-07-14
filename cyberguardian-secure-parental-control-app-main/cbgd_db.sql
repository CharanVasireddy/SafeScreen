-- Database: cbgd_db
-- Version 1.1 (Fixed)

SET FOREIGN_KEY_CHECKS = 0;
SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

--
-- Database: `cbgd_db`
--
CREATE DATABASE IF NOT EXISTS `cbgd_db` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `cbgd_db`;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--
DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `id` int(6) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `email` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` varchar(10) NOT NULL DEFAULT 'parent',
  `reg_date` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `users`
--
INSERT INTO `users` (`id`, `name`, `email`, `password`, `role`, `reg_date`) VALUES
(1, 'Demo Parent', 'parent@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'parent', '2026-01-22 10:00:00'),
(2, 'Demo Child', 'child@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'child', '2026-01-22 10:00:00');

-- --------------------------------------------------------

--
-- Table structure for table `alerts`
--
DROP TABLE IF EXISTS `alerts`;
CREATE TABLE `alerts` (
  `id` int(6) UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` int(6) UNSIGNED DEFAULT NULL,
  `title` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `is_unread` tinyint(1) DEFAULT 1,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `alerts_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `alerts`
--
INSERT INTO `alerts` (`id`, `user_id`, `title`, `description`, `created_at`, `is_unread`) VALUES
(1, 1, 'Suspicious Login Attempt', 'We detected a login from a new device in London.', '2026-01-22 09:30:00', 1),
(2, 1, 'Screen Time Limit Reached', 'Child device has reached the 2 hour limit on TikTok.', '2026-01-21 20:00:00', 0),
(3, 1, 'App Installed', 'A new game "Roblox" was installed.', '2026-01-21 14:15:00', 0);

-- --------------------------------------------------------

--
-- Table structure for table `app_usage`
--
DROP TABLE IF EXISTS `app_usage`;
CREATE TABLE `app_usage` (
  `id` int(6) UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` int(6) UNSIGNED DEFAULT NULL,
  `app_name` varchar(50) NOT NULL,
  `usage_time` varchar(20) DEFAULT NULL,
  `progress` int(3) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `app_usage_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `app_usage`
--
INSERT INTO `app_usage` (`id`, `user_id`, `app_name`, `usage_time`, `progress`) VALUES
(1, 1, 'YouTube', '1h 45m', 75),
(2, 1, 'TikTok', '1h 12m', 60),
(3, 1, 'Instagram', '45m', 40),
(4, 1, 'Roblox', '30m', 25);

-- --------------------------------------------------------

--
-- Table structure for table `security_apps`
--
DROP TABLE IF EXISTS `security_apps`;
CREATE TABLE `security_apps` (
  `id` int(6) UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` int(6) UNSIGNED DEFAULT NULL,
  `app_name` varchar(50) NOT NULL,
  `is_safe` tinyint(1) DEFAULT 1,
  `status_text` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `security_apps_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `security_apps`
--
INSERT INTO `security_apps` (`id`, `user_id`, `app_name`, `is_safe`, `status_text`) VALUES
(1, 1, 'Antivirus Scan', 1, 'No threats found'),
(2, 1, 'Firewall', 1, 'Active and protecting'),
(3, 1, 'Data Backup', 0, 'Backup overdue by 3 days');

-- --------------------------------------------------------

--
-- Table structure for table `blocked_sites`
--
DROP TABLE IF EXISTS `blocked_sites`;
CREATE TABLE `blocked_sites` (
  `id` int(6) UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` int(6) UNSIGNED DEFAULT NULL,
  `url` varchar(255) NOT NULL,
  `reason` varchar(100) DEFAULT NULL,
  `blocked_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `blocked_sites_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `blocked_sites`
--
INSERT INTO `blocked_sites` (`id`, `user_id`, `url`, `reason`, `blocked_at`) VALUES
(1, 1, 'www.gambling-site.com', 'Restricted Category', '2026-01-22 12:00:00'),
(2, 1, 'www.malware-test.com', 'Phishing Attempt', '2026-01-20 15:30:00');

-- --------------------------------------------------------

--
-- Table structure for table `security_history`
--
DROP TABLE IF EXISTS `security_history`;
CREATE TABLE `security_history` (
  `id` int(6) UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` int(6) UNSIGNED DEFAULT NULL,
  `title` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `event_time` datetime DEFAULT current_timestamp(),
  `status` varchar(20) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `security_history_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `security_history`
--
INSERT INTO `security_history` (`id`, `user_id`, `title`, `description`, `event_time`, `status`) VALUES
(1, 1, 'System Scan', 'Full system scan completed successfully.', '2026-01-22 08:00:00', 'Safe'),
(2, 1, 'Unauthorized Access', 'Failed login attempt from unknown IP.', '2026-01-21 23:15:00', 'Blocked');

-- --------------------------------------------------------

--
-- Table structure for table `devices`
--
DROP TABLE IF EXISTS `devices`;
CREATE TABLE `devices` (
  `id` int(6) UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` int(6) UNSIGNED DEFAULT NULL,
  `device_name` varchar(50) DEFAULT NULL,
  `device_identifier` varchar(100) NOT NULL,
  `last_active` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `devices_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `devices`
--
INSERT INTO `devices` (`id`, `user_id`, `device_name`, `device_identifier`, `last_active`) VALUES
(1, 1, 'Samsung Galaxy S23', 'android-id-demo-1', '2026-01-22 16:00:00');

-- --------------------------------------------------------

--
-- Table structure for table `pairing_codes`
--
DROP TABLE IF EXISTS `pairing_codes`;
CREATE TABLE `pairing_codes` (
  `code` varchar(20) NOT NULL,
  `user_id` int(6) UNSIGNED DEFAULT NULL,
  `expires_at` datetime DEFAULT NULL,
  PRIMARY KEY (`code`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `pairing_codes_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

SET FOREIGN_KEY_CHECKS = 1;
COMMIT;
