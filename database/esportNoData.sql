-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 21, 2025 at 05:15 PM
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

-- --------------------------------------------------------

--
-- Table structure for table `admin`
--

CREATE TABLE `admin` (
  `id` int(11) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `brackets`
--

CREATE TABLE `brackets` (
  `id` int(11) NOT NULL,
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
  `prizes` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `contact`
--

CREATE TABLE `contact` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `subject` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `submitted_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `duo_players`
--

CREATE TABLE `duo_players` (
  `player_id` int(11) NOT NULL,
  `duo_id` int(11) DEFAULT NULL,
  `name` varchar(100) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `role` varchar(50) DEFAULT NULL,
  `ign` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `duo_registration`
--

CREATE TABLE `duo_registration` (
  `duo_id` int(11) NOT NULL,
  `tournament_id` int(11) DEFAULT NULL,
  `team_name` varchar(100) DEFAULT NULL,
  `mentor_name` varchar(100) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `logo_path` blob DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `game`
--

CREATE TABLE `game` (
  `id` int(11) NOT NULL,
  `tournament_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `game_id` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `expire_time` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `leaderboard`
--

CREATE TABLE `leaderboard` (
  `id` int(11) NOT NULL,
  `tournament_id` int(11) NOT NULL,
  `match_type` enum('solo','duo','squad') NOT NULL,
  `team_id` int(11) DEFAULT NULL,
  `player_id` int(11) DEFAULT NULL,
  `kills` int(11) DEFAULT 0,
  `placement` int(11) DEFAULT 0,
  `total_score` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `matches`
--

CREATE TABLE `matches` (
  `match_id` int(11) NOT NULL,
  `tournament_id` int(11) NOT NULL,
  `round_name` varchar(50) NOT NULL,
  `match_type` enum('solo','duo','squad') NOT NULL,
  `team1_id` int(11) DEFAULT NULL,
  `team2_id` int(11) DEFAULT NULL,
  `team1_score` int(11) DEFAULT 0,
  `team2_score` int(11) DEFAULT 0,
  `winner_id` int(11) DEFAULT NULL,
  `match_time` datetime DEFAULT NULL,
  `status` enum('upcoming','completed','live') DEFAULT 'upcoming'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `news_articles`
--

CREATE TABLE `news_articles` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `image` mediumblob NOT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `solo_registration`
--

CREATE TABLE `solo_registration` (
  `solo_id` int(11) NOT NULL,
  `tournament_id` int(11) DEFAULT NULL,
  `player_name` varchar(100) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `ign` varchar(50) DEFAULT NULL,
  `logo_path` blob DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `squad_players`
--

CREATE TABLE `squad_players` (
  `player_id` int(11) NOT NULL,
  `squad_id` int(11) DEFAULT NULL,
  `name` varchar(100) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `role` varchar(50) DEFAULT NULL,
  `ign` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `squad_registration`
--

CREATE TABLE `squad_registration` (
  `squad_id` int(11) NOT NULL,
  `tournament_id` int(11) DEFAULT NULL,
  `team_name` varchar(100) DEFAULT NULL,
  `mentor_name` varchar(100) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `logo_path` blob DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `streams`
--

CREATE TABLE `streams` (
  `id` int(11) NOT NULL,
  `tournament_id` int(11) NOT NULL,
  `provider` varchar(255) NOT NULL,
  `channel_name` varchar(255) NOT NULL,
  `social_media` varchar(255) DEFAULT NULL,
  `social_media_input` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tournaments`
--

CREATE TABLE `tournaments` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `selected_game` varchar(100) NOT NULL,
  `tname` varchar(255) NOT NULL,
  `sdate` date NOT NULL,
  `stime` time NOT NULL,
  `about` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `bannerimg` mediumblob DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
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
  `reset_expires` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin`
--
ALTER TABLE `admin`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `brackets`
--
ALTER TABLE `brackets`
  ADD PRIMARY KEY (`id`),
  ADD KEY `tournament_id` (`tournament_id`);

--
-- Indexes for table `contact`
--
ALTER TABLE `contact`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `duo_players`
--
ALTER TABLE `duo_players`
  ADD PRIMARY KEY (`player_id`),
  ADD KEY `duo_players_ibfk_1` (`duo_id`);

--
-- Indexes for table `duo_registration`
--
ALTER TABLE `duo_registration`
  ADD PRIMARY KEY (`duo_id`),
  ADD KEY `duo_registration_ibfk_1` (`tournament_id`);

--
-- Indexes for table `game`
--
ALTER TABLE `game`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `tournament_id` (`tournament_id`),
  ADD KEY `game_id` (`game_id`);

--
-- Indexes for table `leaderboard`
--
ALTER TABLE `leaderboard`
  ADD PRIMARY KEY (`id`),
  ADD KEY `tournament_id` (`tournament_id`);

--
-- Indexes for table `matches`
--
ALTER TABLE `matches`
  ADD PRIMARY KEY (`match_id`),
  ADD KEY `tournament_id` (`tournament_id`);

--
-- Indexes for table `news_articles`
--
ALTER TABLE `news_articles`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `solo_registration`
--
ALTER TABLE `solo_registration`
  ADD PRIMARY KEY (`solo_id`),
  ADD KEY `solo_registration_ibfk_1` (`tournament_id`);

--
-- Indexes for table `squad_players`
--
ALTER TABLE `squad_players`
  ADD PRIMARY KEY (`player_id`),
  ADD KEY `squad_players_ibfk_1` (`squad_id`);

--
-- Indexes for table `squad_registration`
--
ALTER TABLE `squad_registration`
  ADD PRIMARY KEY (`squad_id`),
  ADD KEY `squad_registration_ibfk_1` (`tournament_id`);

--
-- Indexes for table `streams`
--
ALTER TABLE `streams`
  ADD PRIMARY KEY (`id`),
  ADD KEY `tournament_id` (`tournament_id`);

--
-- Indexes for table `tournaments`
--
ALTER TABLE `tournaments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_user` (`user_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admin`
--
ALTER TABLE `admin`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `brackets`
--
ALTER TABLE `brackets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `contact`
--
ALTER TABLE `contact`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `duo_players`
--
ALTER TABLE `duo_players`
  MODIFY `player_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `duo_registration`
--
ALTER TABLE `duo_registration`
  MODIFY `duo_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `game`
--
ALTER TABLE `game`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `leaderboard`
--
ALTER TABLE `leaderboard`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `matches`
--
ALTER TABLE `matches`
  MODIFY `match_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `news_articles`
--
ALTER TABLE `news_articles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `solo_registration`
--
ALTER TABLE `solo_registration`
  MODIFY `solo_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `squad_players`
--
ALTER TABLE `squad_players`
  MODIFY `player_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `squad_registration`
--
ALTER TABLE `squad_registration`
  MODIFY `squad_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `streams`
--
ALTER TABLE `streams`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tournaments`
--
ALTER TABLE `tournaments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

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
-- Constraints for table `matches`
--
ALTER TABLE `matches`
  ADD CONSTRAINT `matches_ibfk_1` FOREIGN KEY (`tournament_id`) REFERENCES `tournaments` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

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
