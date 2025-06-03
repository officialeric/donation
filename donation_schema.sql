-- phpMyAdmin SQL Dump
-- Orphanage Donation Platform Database Schema
-- Database: `donation`

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

-- Create database if not exists
CREATE DATABASE IF NOT EXISTS `donation` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `donation`;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `phone` varchar(16) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','donor') NOT NULL DEFAULT 'donor',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users` (Default admin user)
--

INSERT INTO `users` (`id`, `username`, `email`, `phone`, `password`, `role`, `created_at`, `updated_at`) VALUES
(1, 'admin', 'admin@donation.com', '1234567890', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', '2024-01-01 00:00:00', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `orphanages`
--

CREATE TABLE `orphanages` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `location` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `contact_person` varchar(255) NOT NULL,
  `contact_phone` varchar(20) NOT NULL,
  `contact_email` varchar(255) NOT NULL,
  `bank_account` varchar(255) DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping sample data for table `orphanages`
--

INSERT INTO `orphanages` (`id`, `name`, `location`, `description`, `contact_person`, `contact_phone`, `contact_email`, `bank_account`, `image`, `status`, `created_at`, `updated_at`) VALUES
(1, 'Hope Children Home', 'Dar es salaam', 'A loving home for children in need, providing education, healthcare, and emotional support to help them build a brighter future.', 'Sarah Johnson', '+1-555-0123', 'sarah@hopechildrenshome.org', '1234567890', NULL, 'active', '2024-01-01 00:00:00', NULL),
(2, 'Sunshine Orphanage', ' Dodoma', 'Dedicated to providing a safe and nurturing environment for orphaned children, with focus on education and life skills development.', 'Michael Chen', '+1-555-0456', 'michael@sunshineorphanage.org', '0987654321', NULL, 'active', '2024-01-01 00:00:00', NULL),
(3, 'Little Angels Home', 'Mwanza', 'Caring for children from infancy to adulthood, providing comprehensive support including education, healthcare, and vocational training.', 'Maria Rodriguez', '+1-555-0789', 'maria@littleangelshome.org', '1122334455', NULL, 'active', '2024-01-01 00:00:00', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `donations`
--

CREATE TABLE `donations` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `orphanage_id` int(11) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `payment_status` enum('pending','completed','failed','refunded') NOT NULL DEFAULT 'pending',
  `payment_method` varchar(50) NOT NULL,
  `transaction_id` varchar(255) DEFAULT NULL,
  `message` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Indexes for dumped tables
--

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `orphanages`
--
ALTER TABLE `orphanages`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `donations`
--
ALTER TABLE `donations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `orphanage_id` (`orphanage_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `orphanages`
--
ALTER TABLE `orphanages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `donations`
--
ALTER TABLE `donations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `donations`
--
ALTER TABLE `donations`
  ADD CONSTRAINT `donations_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `donations_ibfk_2` FOREIGN KEY (`orphanage_id`) REFERENCES `orphanages` (`id`) ON DELETE CASCADE;

COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
