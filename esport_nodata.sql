-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 27, 2025 at 10:35 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `esport`
--
CREATE DATABASE IF NOT EXISTS `esport` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `esport`;

DELIMITER $$
--
-- Procedures
--
DROP PROCEDURE IF EXISTS `SetAutoIncrement`$$
CREATE DEFINER=`root`@`localhost` PROCEDURE `SetAutoIncrement` ()   BEGIN
   DECLARE max_id INT;

   -- Get the maximum ID
   SELECT COALESCE(MAX(id), 0) INTO max_id FROM users;

   -- Set the AUTO_INCREMENT value
   SET @next_auto_increment = max_id + 1;
   SET @query = CONCAT('ALTER TABLE users AUTO_INCREMENT = ', @next_auto_increment);
   PREPARE stmt FROM @query;
   EXECUTE stmt;
   DEALLOCATE PREPARE stmt;
END$$

DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `admin`
--

DROP TABLE IF EXISTS `admin`;
CREATE TABLE IF NOT EXISTS `admin` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `brackets`
--

DROP TABLE IF EXISTS `brackets`;
CREATE TABLE IF NOT EXISTS `brackets` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tournament_id` int(11) NOT NULL,
  `bracket_type` enum('battle_royal','round_robin','double_elimination','single_elimination') NOT NULL,
  `match_type` enum('solo','duo','squad') NOT NULL,
  `solo_players` int(11) DEFAULT NULL,
  `duo_teams` int(11) DEFAULT NULL,
  `duo_players_per_team` int(11) DEFAULT NULL,
  `squad_teams` int(11) DEFAULT NULL,
  `squad_players_per_team` int(11) DEFAULT NULL,
  `rounds` int(11) NOT NULL,
  `placement` text DEFAULT NULL,
  `rules` text DEFAULT NULL,
  `prizes` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `tournament_id` (`tournament_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `contact`
--

DROP TABLE IF EXISTS `contact`;
CREATE TABLE IF NOT EXISTS `contact` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `subject` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `submitted_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `duo_players`
--

DROP TABLE IF EXISTS `duo_players`;
CREATE TABLE IF NOT EXISTS `duo_players` (
  `player_id` int(11) NOT NULL AUTO_INCREMENT,
  `duo_id` int(11) DEFAULT NULL,
  `name` varchar(100) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `role` varchar(50) DEFAULT NULL,
  `ign` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`player_id`),
  KEY `duo_players_ibfk_1` (`duo_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `duo_registration`
--

DROP TABLE IF EXISTS `duo_registration`;
CREATE TABLE IF NOT EXISTS `duo_registration` (
  `duo_id` int(11) NOT NULL AUTO_INCREMENT,
  `tournament_id` int(11) DEFAULT NULL,
  `team_name` varchar(100) DEFAULT NULL,
  `mentor_name` varchar(100) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `logo_path` blob DEFAULT NULL,
  PRIMARY KEY (`duo_id`),
  KEY `duo_registration_ibfk_1` (`tournament_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `game`
--

DROP TABLE IF EXISTS `game`;
CREATE TABLE IF NOT EXISTS `game` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tournament_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `game_id` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `expire_time` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `tournament_id` (`tournament_id`),
  KEY `game_id` (`game_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `leaderboard`
--

DROP TABLE IF EXISTS `leaderboard`;
CREATE TABLE IF NOT EXISTS `leaderboard` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tournament_id` int(11) NOT NULL,
  `rank` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `prize` decimal(10,2) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `tournament_id` (`tournament_id`,`rank`),
  UNIQUE KEY `unique_tournament_rank` (`tournament_id`,`rank`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `news_articles`
--

DROP TABLE IF EXISTS `news_articles`;
CREATE TABLE IF NOT EXISTS `news_articles` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `image` mediumblob NOT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `solo_registration`
--

DROP TABLE IF EXISTS `solo_registration`;
CREATE TABLE IF NOT EXISTS `solo_registration` (
  `solo_id` int(11) NOT NULL AUTO_INCREMENT,
  `tournament_id` int(11) DEFAULT NULL,
  `player_name` varchar(100) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `ign` varchar(50) DEFAULT NULL,
  `logo_path` blob DEFAULT NULL,
  PRIMARY KEY (`solo_id`),
  KEY `solo_registration_ibfk_1` (`tournament_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `squad_players`
--

DROP TABLE IF EXISTS `squad_players`;
CREATE TABLE IF NOT EXISTS `squad_players` (
  `player_id` int(11) NOT NULL AUTO_INCREMENT,
  `squad_id` int(11) DEFAULT NULL,
  `name` varchar(100) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `role` varchar(50) DEFAULT NULL,
  `ign` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`player_id`),
  KEY `squad_players_ibfk_1` (`squad_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `squad_registration`
--

DROP TABLE IF EXISTS `squad_registration`;
CREATE TABLE IF NOT EXISTS `squad_registration` (
  `squad_id` int(11) NOT NULL AUTO_INCREMENT,
  `tournament_id` int(11) DEFAULT NULL,
  `team_name` varchar(100) DEFAULT NULL,
  `mentor_name` varchar(100) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `logo_path` blob DEFAULT NULL,
  PRIMARY KEY (`squad_id`),
  KEY `squad_registration_ibfk_1` (`tournament_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `streams`
--

DROP TABLE IF EXISTS `streams`;
CREATE TABLE IF NOT EXISTS `streams` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tournament_id` int(11) NOT NULL,
  `provider` varchar(255) NOT NULL,
  `channel_name` varchar(255) NOT NULL,
  `social_media` varchar(255) DEFAULT NULL,
  `social_media_input` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `tournament_id` (`tournament_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tournaments`
--

DROP TABLE IF EXISTS `tournaments`;
CREATE TABLE IF NOT EXISTS `tournaments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `selected_game` varchar(100) NOT NULL,
  `tname` varchar(255) NOT NULL,
  `sdate` date NOT NULL,
  `stime` time NOT NULL,
  `about` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `bannerimg` mediumblob DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_user` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
CREATE TABLE IF NOT EXISTS `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `full_name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `uname` varchar(255) DEFAULT NULL,
  `country` varchar(255) DEFAULT NULL,
  `city` varchar(255) DEFAULT NULL,
  `role` varchar(255) DEFAULT NULL,
  `cover_photo` longblob DEFAULT NULL,
  `profile_pic` longblob DEFAULT NULL,
  `dob` date DEFAULT NULL,
  `is_suspended` tinyint(1) DEFAULT 0,
  `status` varchar(255) DEFAULT NULL COMMENT 'User status (e.g., active, inactive, banned)',
  `verify_token` varchar(255) DEFAULT NULL COMMENT 'Token for verification purposes',
  `is_verified` tinyint(1) DEFAULT 0,
  `reset_token` varchar(255) DEFAULT NULL,
  `reset_code` varchar(255) DEFAULT NULL,
  `reset_expires` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `brackets`
--
ALTER TABLE `brackets`
  ADD CONSTRAINT `brackets_ibfk_1` FOREIGN KEY (`tournament_id`) REFERENCES `tournaments` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `duo_players`
--
ALTER TABLE `duo_players`
  ADD CONSTRAINT `duo_players_ibfk_1` FOREIGN KEY (`duo_id`) REFERENCES `duo_registration` (`duo_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `duo_registration`
--
ALTER TABLE `duo_registration`
  ADD CONSTRAINT `duo_registration_ibfk_1` FOREIGN KEY (`tournament_id`) REFERENCES `tournaments` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `game`
--
ALTER TABLE `game`
  ADD CONSTRAINT `game_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `game_ibfk_2` FOREIGN KEY (`tournament_id`) REFERENCES `tournaments` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `leaderboard`
--
ALTER TABLE `leaderboard`
  ADD CONSTRAINT `leaderboard_ibfk_1` FOREIGN KEY (`tournament_id`) REFERENCES `tournaments` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `solo_registration`
--
ALTER TABLE `solo_registration`
  ADD CONSTRAINT `solo_registration_ibfk_1` FOREIGN KEY (`tournament_id`) REFERENCES `tournaments` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `squad_players`
--
ALTER TABLE `squad_players`
  ADD CONSTRAINT `squad_players_ibfk_1` FOREIGN KEY (`squad_id`) REFERENCES `squad_registration` (`squad_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `squad_registration`
--
ALTER TABLE `squad_registration`
  ADD CONSTRAINT `squad_registration_ibfk_1` FOREIGN KEY (`tournament_id`) REFERENCES `tournaments` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `streams`
--
ALTER TABLE `streams`
  ADD CONSTRAINT `streams_ibfk_1` FOREIGN KEY (`tournament_id`) REFERENCES `tournaments` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `tournaments`
--
ALTER TABLE `tournaments`
  ADD CONSTRAINT `fk_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
