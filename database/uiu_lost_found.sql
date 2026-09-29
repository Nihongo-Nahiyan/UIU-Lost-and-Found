-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Sep 29, 2026 at 08:38 PM
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
-- Database: `uiu_lost_found`
--

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `category_id` int(11) NOT NULL,
  `category_name` varchar(50) NOT NULL,
  `icon` varchar(20) DEFAULT '?',
  `description` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`category_id`, `category_name`, `icon`, `description`) VALUES
(1, 'Electronics', '💻', 'Phones, laptops and chargers'),
(2, 'Documents', '📄', 'ID cards and documents'),
(3, 'Accessories', '⌚', 'Wallets, watches and accessories'),
(4, 'Bags', '🎒', 'Backpacks and handbags'),
(5, 'Keys', '🔑', 'Keys and keychains'),
(6, 'Books', '📚', 'Books and notebooks'),
(7, 'Other', '📦', 'Other lost and found items');

-- --------------------------------------------------------

--
-- Table structure for table `claims`
--

CREATE TABLE `claims` (
  `claim_id` int(11) NOT NULL,
  `found_id` int(11) NOT NULL,
  `claimant_id` int(11) NOT NULL,
  `reason` text NOT NULL,
  `status` enum('Pending','Approved','Rejected') DEFAULT 'Pending',
  `claim_date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `claims`
--

INSERT INTO `claims` (`claim_id`, `found_id`, `claimant_id`, `reason`, `status`, `claim_date`) VALUES
(2, 4, 6, 'I have lost this laptop in the common room. Its brand is Asus VivoBook 15 X1504VA.', 'Approved', '2026-09-29 18:21:37');

-- --------------------------------------------------------

--
-- Table structure for table `found_items`
--

CREATE TABLE `found_items` (
  `found_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `category_id` int(11) NOT NULL,
  `title` varchar(100) NOT NULL,
  `description` text NOT NULL,
  `found_location` varchar(150) DEFAULT NULL,
  `found_date` date DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `approval_status` enum('Pending','Approved','Rejected') DEFAULT 'Pending',
  `claimed_status` enum('Unclaimed','Claimed') DEFAULT 'Unclaimed',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `found_items`
--

INSERT INTO `found_items` (`found_id`, `user_id`, `category_id`, `title`, `description`, `found_location`, `found_date`, `image`, `approval_status`, `claimed_status`, `created_at`) VALUES
(3, 6, 4, 'Handbag', 'Found this on the seat.', 'Library', '2026-09-28', '08cd4eba75c0363667e5824b604c9ba6.jpg', 'Approved', 'Unclaimed', '2026-09-29 17:55:53'),
(4, 7, 1, 'Laptop', 'Found a laptop behind the sofa.', 'Common Room (male)', '2026-09-30', 'b9ad4c303882a231b5e05fed3e06b7c1.jpg', 'Approved', 'Unclaimed', '2026-09-29 18:04:07');

-- --------------------------------------------------------

--
-- Table structure for table `lost_items`
--

CREATE TABLE `lost_items` (
  `lost_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `category_id` int(11) NOT NULL,
  `title` varchar(100) NOT NULL,
  `description` text NOT NULL,
  `last_location` varchar(150) DEFAULT NULL,
  `lost_date` date DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `status` enum('Pending','Approved','Rejected','Lost','Resolved') DEFAULT 'Pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `lost_items`
--

INSERT INTO `lost_items` (`lost_id`, `user_id`, `category_id`, `title`, `description`, `last_location`, `lost_date`, `image`, `status`, `created_at`) VALUES
(3, 6, 3, 'Black wallet', 'Lost at the Khan\'s Kitchen area', 'Canteen', '2026-09-30', '91a218e35c5a4b5848f2f3b57b2e72e6.jpg', 'Pending', '2026-09-29 18:02:45'),
(4, 7, 1, 'Sonny headphones', 'Found the headphones under the table on the floor.', 'Library (2nd floor)', '2026-09-29', '69b2cf36e503931137c675186b5a0029.jpg', 'Pending', '2026-09-29 18:06:48');

-- --------------------------------------------------------

--
-- Table structure for table `messages`
--

CREATE TABLE `messages` (
  `message_id` int(11) NOT NULL,
  `sender_id` int(11) NOT NULL,
  `receiver_id` int(11) NOT NULL,
  `found_id` int(11) DEFAULT NULL,
  `message` text NOT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `sent_time` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `messages`
--

INSERT INTO `messages` (`message_id`, `sender_id`, `receiver_id`, `found_id`, `message`, `is_read`, `sent_time`) VALUES
(2, 6, 7, 4, 'Hi I think you have found my laptop.', 1, '2026-09-29 18:23:18'),
(3, 7, 6, 4, 'Alright meet me at the canteen tomorrow.', 0, '2026-09-29 18:33:49'),
(4, 7, 6, 4, 'hello', 0, '2026-09-29 18:38:07');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `student_id` varchar(30) NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('student','admin') DEFAULT 'student',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `name`, `student_id`, `email`, `phone`, `password`, `role`, `created_at`) VALUES
(6, 'user1', '0112539405', 'user1@uiu.ac.bd', '01782734974', '$2y$10$K1nKkR.MsZfcDgd8tGyiLuj83c5VxU62Xd7vdM.6gQFOu6J3U6Ly.', 'student', '2026-09-29 17:41:25'),
(7, 'user3', '0112748503', 'user3@uiu.ac.bd', '01783920445', '$2y$10$zc9IfSIa9vWZaMh4e3Ehd.ldYSqF5/oK6GKiyr29EkqaoOpgfVP6O', 'student', '2026-09-29 17:42:50'),
(8, 'Admin', '123', 'admin@uiu.ac.bd', '01568388037', '$2y$10$bNnnlZdIK1T9YmbwoI2f0OCUQueaHUwPvIt52sfMX5lM.tg09ur9y', 'admin', '2026-09-29 17:58:49');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`category_id`),
  ADD UNIQUE KEY `category_name` (`category_name`);

--
-- Indexes for table `claims`
--
ALTER TABLE `claims`
  ADD PRIMARY KEY (`claim_id`),
  ADD KEY `found_id` (`found_id`),
  ADD KEY `claimant_id` (`claimant_id`);

--
-- Indexes for table `found_items`
--
ALTER TABLE `found_items`
  ADD PRIMARY KEY (`found_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `category_id` (`category_id`);

--
-- Indexes for table `lost_items`
--
ALTER TABLE `lost_items`
  ADD PRIMARY KEY (`lost_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `category_id` (`category_id`);

--
-- Indexes for table `messages`
--
ALTER TABLE `messages`
  ADD PRIMARY KEY (`message_id`),
  ADD KEY `sender_id` (`sender_id`),
  ADD KEY `receiver_id` (`receiver_id`),
  ADD KEY `found_id` (`found_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `student_id` (`student_id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `category_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `claims`
--
ALTER TABLE `claims`
  MODIFY `claim_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `found_items`
--
ALTER TABLE `found_items`
  MODIFY `found_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `lost_items`
--
ALTER TABLE `lost_items`
  MODIFY `lost_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `messages`
--
ALTER TABLE `messages`
  MODIFY `message_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `claims`
--
ALTER TABLE `claims`
  ADD CONSTRAINT `claims_ibfk_1` FOREIGN KEY (`found_id`) REFERENCES `found_items` (`found_id`),
  ADD CONSTRAINT `claims_ibfk_2` FOREIGN KEY (`claimant_id`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `found_items`
--
ALTER TABLE `found_items`
  ADD CONSTRAINT `found_items_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`),
  ADD CONSTRAINT `found_items_ibfk_2` FOREIGN KEY (`category_id`) REFERENCES `categories` (`category_id`);

--
-- Constraints for table `lost_items`
--
ALTER TABLE `lost_items`
  ADD CONSTRAINT `lost_items_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`),
  ADD CONSTRAINT `lost_items_ibfk_2` FOREIGN KEY (`category_id`) REFERENCES `categories` (`category_id`);

--
-- Constraints for table `messages`
--
ALTER TABLE `messages`
  ADD CONSTRAINT `messages_ibfk_1` FOREIGN KEY (`sender_id`) REFERENCES `users` (`user_id`),
  ADD CONSTRAINT `messages_ibfk_2` FOREIGN KEY (`receiver_id`) REFERENCES `users` (`user_id`),
  ADD CONSTRAINT `messages_ibfk_3` FOREIGN KEY (`found_id`) REFERENCES `found_items` (`found_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
