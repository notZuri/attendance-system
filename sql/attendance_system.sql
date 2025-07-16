-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jul 03, 2025 at 07:53 AM
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
-- Database: `attendance_system`
--

-- --------------------------------------------------------

--
-- Table structure for table `attendance`
--

CREATE TABLE `attendance` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `schedule_id` int(11) NOT NULL,
  `attendance_date` date NOT NULL,
  `status` enum('present','late','absent') NOT NULL DEFAULT 'present',
  `time_in` time DEFAULT NULL,
  `time_out` time DEFAULT NULL,
  `method` enum('manual','rfid','fingerprint') DEFAULT 'manual',
  `device_response` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`device_response`)),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `attendance`
--

INSERT INTO `attendance` (`id`, `user_id`, `schedule_id`, `attendance_date`, `status`, `time_in`, `time_out`, `method`, `device_response`, `created_at`, `updated_at`) VALUES
(1, 5, 2, '2025-07-01', 'present', '11:11:54', NULL, 'manual', NULL, '2025-07-01 03:11:54', NULL),
(2, 4, 2, '2025-07-01', 'present', '11:11:54', NULL, 'manual', NULL, '2025-07-01 03:11:54', NULL),
(3, 6, 2, '2025-07-02', 'present', '01:12:40', NULL, 'manual', NULL, '2025-07-02 17:07:59', '2025-07-02 17:12:40'),
(4, 5, 2, '2025-07-02', 'late', '01:12:40', NULL, 'manual', NULL, '2025-07-02 17:07:59', '2025-07-02 17:12:40'),
(5, 4, 2, '2025-07-02', 'present', '01:12:40', NULL, 'manual', NULL, '2025-07-02 17:07:59', '2025-07-02 17:12:40'),
(6, 6, 8, '2025-07-02', 'present', '01:19:43', NULL, 'manual', NULL, '2025-07-02 17:19:43', NULL),
(7, 5, 8, '2025-07-02', 'present', '01:19:43', NULL, 'manual', NULL, '2025-07-02 17:19:43', NULL),
(8, 4, 8, '2025-07-02', 'present', '01:19:43', NULL, 'manual', NULL, '2025-07-02 17:19:43', NULL),
(9, 6, 5, '2025-07-02', 'absent', '01:21:59', NULL, 'manual', NULL, '2025-07-02 17:21:59', NULL),
(10, 5, 5, '2025-07-02', 'absent', '01:21:59', NULL, 'manual', NULL, '2025-07-02 17:21:59', NULL),
(11, 4, 5, '2025-07-02', 'absent', '01:21:59', NULL, 'manual', NULL, '2025-07-02 17:21:59', NULL),
(12, 6, 4, '2025-07-02', 'late', '01:25:04', NULL, 'manual', NULL, '2025-07-02 17:25:04', NULL),
(13, 5, 4, '2025-07-02', 'late', '01:25:04', NULL, 'manual', NULL, '2025-07-02 17:25:04', NULL),
(14, 4, 4, '2025-07-02', 'late', '01:25:04', NULL, 'manual', NULL, '2025-07-02 17:25:04', NULL),
(15, 6, 3, '2025-07-03', 'present', '13:22:15', NULL, 'manual', NULL, '2025-07-03 05:22:15', NULL),
(16, 5, 3, '2025-07-03', 'present', '13:22:15', NULL, 'manual', NULL, '2025-07-03 05:22:15', NULL),
(17, 4, 3, '2025-07-03', 'present', '13:22:15', NULL, 'manual', NULL, '2025-07-03 05:22:15', NULL),
(18, 6, 7, '2025-07-03', 'late', '13:24:05', NULL, 'manual', NULL, '2025-07-03 05:24:05', NULL),
(19, 5, 7, '2025-07-03', 'late', '13:24:05', NULL, 'manual', NULL, '2025-07-03 05:24:05', NULL),
(20, 4, 7, '2025-07-03', 'late', '13:24:05', NULL, 'manual', NULL, '2025-07-03 05:24:05', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `attendance_logs`
--

CREATE TABLE `attendance_logs` (
  `id` int(11) NOT NULL,
  `attendance_id` int(11) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `schedule_id` int(11) DEFAULT NULL,
  `action` enum('scan_attempt','scan_success','scan_failed','duplicate_detected','late_detected','manual_entry') NOT NULL,
  `method` enum('rfid','fingerprint','manual') DEFAULT NULL,
  `device_info` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`device_info`)),
  `error_message` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `fingerprints`
--

CREATE TABLE `fingerprints` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `fingerprint_template` longblob NOT NULL,
  `enrolled_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `fingerprint_templates`
--

CREATE TABLE `fingerprint_templates` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `template_id` varchar(50) NOT NULL,
  `template_data` longblob NOT NULL,
  `confidence_threshold` decimal(3,2) DEFAULT 0.80,
  `enrolled_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `last_used` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `hardware_sessions`
--

CREATE TABLE `hardware_sessions` (
  `id` int(11) NOT NULL,
  `device_id` varchar(64) NOT NULL,
  `device_type` varchar(32) DEFAULT NULL,
  `location` varchar(128) DEFAULT NULL,
  `status` varchar(32) DEFAULT NULL,
  `ip_address` varchar(64) DEFAULT NULL,
  `mac_address` varchar(64) DEFAULT NULL,
  `firmware_version` varchar(64) DEFAULT NULL,
  `last_heartbeat` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `hardware_sessions`
--

INSERT INTO `hardware_sessions` (`id`, `device_id`, `device_type`, `location`, `status`, `ip_address`, `mac_address`, `firmware_version`, `last_heartbeat`, `updated_at`) VALUES
(1, 'ESP32_001', 'combined', 'Room 101', 'online', '192.168.1.100', NULL, 'v1.0.0', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `is_read` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`id`, `user_id`, `title`, `message`, `is_read`, `created_at`) VALUES
(117, 6, 'Schedule Updated', 'Schedule for tomoro on 2025-07-02 has been updated.', 0, '2025-07-02 15:55:56'),
(118, 4, 'New Schedule Added', 'A new schedule for subok on 2025-08-27 has been added.', 1, '2025-07-02 16:11:59'),
(119, 5, 'New Schedule Added', 'A new schedule for subok on 2025-08-27 has been added.', 1, '2025-07-02 16:11:59'),
(120, 6, 'New Schedule Added', 'A new schedule for subok on 2025-08-27 has been added.', 0, '2025-07-02 16:11:59'),
(121, 4, 'Schedule Updated', 'Schedule for subok on 2025-07-03 has been updated.', 1, '2025-07-02 16:13:04'),
(122, 5, 'Schedule Updated', 'Schedule for subok on 2025-07-03 has been updated.', 1, '2025-07-02 16:13:04'),
(123, 6, 'Schedule Updated', 'Schedule for subok on 2025-07-03 has been updated.', 0, '2025-07-02 16:13:04'),
(124, 1, 'Student Profile Updated', 'Student Ivan Casalme (20250001) updated their profile.', 1, '2025-07-03 05:25:24');

-- --------------------------------------------------------

--
-- Table structure for table `password_change_requests`
--

CREATE TABLE `password_change_requests` (
  `id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `professor_id` int(11) NOT NULL,
  `new_password_hash` varchar(255) NOT NULL,
  `status` enum('pending','approved','declined') NOT NULL DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `reviewed_at` timestamp NULL DEFAULT NULL,
  `reviewed_by` int(11) DEFAULT NULL,
  `reason` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `password_change_requests`
--

INSERT INTO `password_change_requests` (`id`, `student_id`, `professor_id`, `new_password_hash`, `status`, `created_at`, `reviewed_at`, `reviewed_by`, `reason`) VALUES
(3, 4, 1, '$2y$10$DedxLzzmjXyan7nLyulCGuh5o19up2fBQ96cpfLVTphU.aXaPswVG', 'declined', '2025-07-01 14:52:18', '2025-07-01 14:57:07', 1, ''),
(4, 4, 1, '$2y$10$2EcOhIi0oIb.JZIUoTAkSOff2HeXCUvFsQnWVYNl1ZwY4NO9ifMUu', 'approved', '2025-07-01 14:57:36', '2025-07-01 14:58:31', 1, ''),
(5, 4, 1, '$2y$10$m6g.00eUNEWKgo0Q26yvMuUEPMpbSeyHXVeAWzgYkYFUIDWpSbSC6', 'declined', '2025-07-01 14:58:58', '2025-07-01 14:59:23', 1, 'ayoko nga'),
(6, 4, 1, '$2y$10$5Ah05In4kgopfMSbDT/hheK03lXRUpsNX88LilQ.hJ2e2Dml6NqKy', 'approved', '2025-07-01 15:00:24', '2025-07-01 15:01:26', 1, ''),
(7, 4, 1, '$2y$10$.K9IgYKl6xBy2wtKv3/yUuSKGrBQ5aW7w6csAYyFSLbRoPXp/wq.e', 'declined', '2025-07-01 15:02:45', '2025-07-01 15:13:55', 1, 'ayoko ngani'),
(8, 4, 1, '$2y$10$D7culTDD8U2d7dkBgsqFeuy4C/FSaD4renHihT3dJcAVeHNejAA0i', 'declined', '2025-07-01 15:10:02', '2025-07-01 15:13:31', 1, 'ayoko boss'),
(9, 4, 1, '$2y$10$ZIsC8qAQK8tep5sbz2V5L.dsoDH.uEEaS0DiE2lDyiQspE8kLWdT2', 'declined', '2025-07-01 15:14:13', '2025-07-01 15:15:56', 1, 'yoko'),
(10, 4, 1, '$2y$10$dfnM8PdMe16rd59iKsD/RuLAmbZVyeQKsS8BJPwFlM.xgobwUED4a', 'declined', '2025-07-01 15:24:33', '2025-07-01 15:25:11', 1, 'yoko'),
(11, 4, 1, '$2y$10$XEjcafzmH0p1FzVVPww77.hHLE5ze9q8SbmmyF8nV.rG5a/hud1Nu', 'declined', '2025-07-01 15:27:58', '2025-07-01 15:32:28', 1, 'kulit'),
(12, 4, 1, '$2y$10$nPowWf/bco9J6Y94j5hxUevnh4gRxZRFLbTXMGsJT.o1y52TOqxQi', 'declined', '2025-07-01 15:32:43', '2025-07-01 15:33:59', 1, ''),
(13, 4, 1, '$2y$10$/dmp7MeLDV3mjrTmpxu8rewMnCLwNFaPNJQArT8mH91iVlqXhkQZ6', 'declined', '2025-07-01 15:35:30', '2025-07-01 15:40:42', 1, 'ayoko nga e'),
(14, 4, 1, '$2y$10$vGmHO0rARhStCQ4F1ZLRle.6Hy5OTPALslqZspxRkzTHRsceryzc.', 'declined', '2025-07-01 15:41:35', '2025-07-01 15:43:23', 1, ''),
(15, 4, 1, '$2y$10$7Ub1MM4uTjFMem5CQzmsHeNv4YnoXNxBTdD97wtFpazgez7Y3r5gW', 'declined', '2025-07-01 15:45:08', '2025-07-01 15:52:43', 1, 'ayoko nga boss'),
(16, 4, 1, '$2y$10$CxorO30cP5qSXwFUoaTBC.CkCR.p4Vf/a9vZwvCnmF3i4oGkYW1pO', 'declined', '2025-07-01 15:45:14', '2025-07-01 15:45:43', 1, 'yoko boss'),
(17, 4, 1, '$2y$10$wqq49ky3wLkpm14K5h4KEuEnXmtKm7sR26TInL/rBRaK62fET9R8u', 'declined', '2025-07-01 15:59:49', '2025-07-01 16:16:20', 1, 'ayaw'),
(18, 4, 1, '$2y$10$bJ0NOYlkJwn7I2ctEVuF7O2C0a0G5fT6iUX4YpqdDeoyWr59xrshG', 'declined', '2025-07-01 15:59:53', '2025-07-01 16:03:10', 1, 'ayaw'),
(19, 4, 1, '$2y$10$x6wfFdaxRAxcRNZdWZQoXOoLhFY5ZvMeNzuOlEE7an3oNfVc2688G', 'declined', '2025-07-01 15:59:58', '2025-07-01 16:00:18', 1, 'yoko gagi'),
(20, 4, 1, '$2y$10$Ulrf9HXwzNuNjIC1MPqR6u7nju2tVQkwDSV.83IgBZjLlWm91Qwke', 'declined', '2025-07-01 16:09:38', '2025-07-01 16:10:57', 1, 'ayaw'),
(21, 4, 1, '$2y$10$188Oki7Kvm.KEQ1Ih.khc.sPBDRPT8k2BD2eEg1CriOO.6gql7ogG', 'declined', '2025-07-01 16:14:44', '2025-07-01 16:20:53', 1, 'ayaw'),
(22, 4, 1, '$2y$10$aY6xSfsTxXEnwWH3yOUo.eFlKcsYqZxWMUX94gfV3QG7uxy1AhTTK', 'declined', '2025-07-01 16:15:25', '2025-07-01 16:22:00', 1, 'ayaw'),
(23, 4, 1, '$2y$10$BdYJGwqE9amre.Ai2tCwvuRTqAhvffbUBTxlsPt89j32SS1tnQJxK', 'declined', '2025-07-01 16:21:22', '2025-07-01 16:22:03', 1, 'ayaw'),
(24, 4, 1, '$2y$10$5yRz4lUZjvCFtxXCB6VNRORbFoBHXutitlarRCihCq/Wyt9bL8N16', 'declined', '2025-07-01 16:25:21', '2025-07-01 16:25:40', 1, 'no'),
(25, 4, 1, '$2y$10$O500HttEnpFD6jU1ci.2nePVq.Xpan8OGSMykWr67Ewkif8BORAt6', 'declined', '2025-07-01 16:37:54', '2025-07-01 16:38:21', 1, 'kingina'),
(26, 4, 1, '$2y$10$q/9scBUKMm6IVg5vPYvKB.4hBwCbK7cfbNaf39.4PUhpKP/WOngdG', 'declined', '2025-07-01 16:41:51', '2025-07-01 16:44:45', 1, 'no'),
(27, 4, 1, '$2y$10$OAp6V6VJYOuKQ.dqbgdGS.G6trCebvhksIsfSLWtm0uIjf7PETrae', 'declined', '2025-07-01 16:46:40', '2025-07-01 16:48:14', 1, 'no no'),
(28, 4, 1, '$2y$10$.9G.vSfA92aUm7xBP.cRieuAH8rZL.5TkI.l7TLINIln0Sl43yGzi', 'declined', '2025-07-02 05:10:07', '2025-07-02 05:11:37', 1, 'no no yan bossing');

-- --------------------------------------------------------

--
-- Table structure for table `rfid_cards`
--

CREATE TABLE `rfid_cards` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `card_uid` varchar(50) NOT NULL,
  `enrolled_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `schedules`
--

CREATE TABLE `schedules` (
  `id` int(11) NOT NULL,
  `professor_id` int(11) NOT NULL,
  `subject` varchar(100) NOT NULL,
  `room` varchar(50) NOT NULL,
  `date` date NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  `late_threshold` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `is_active` tinyint(1) DEFAULT 0,
  `activated_at` timestamp NULL DEFAULT NULL,
  `completed_at` timestamp NULL DEFAULT NULL,
  `current_status` enum('pending','active','completed','cancelled') DEFAULT 'pending',
  `attendance_window_start` time DEFAULT NULL,
  `attendance_window_end` time DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `schedules`
--

INSERT INTO `schedules` (`id`, `professor_id`, `subject`, `room`, `date`, `start_time`, `end_time`, `late_threshold`, `created_at`, `is_active`, `activated_at`, `completed_at`, `current_status`, `attendance_window_start`, `attendance_window_end`) VALUES
(2, 1, 'Capstone', 'B4 - I LET U GO', '2025-07-01', '11:10:00', '11:15:00', 2, '2025-07-01 03:09:41', 0, NULL, NULL, 'completed', NULL, NULL),
(3, 1, 'OJT', 'bahay', '2025-07-02', '20:05:00', '20:10:00', 1, '2025-07-02 12:04:19', 0, NULL, NULL, 'completed', NULL, NULL),
(4, 1, 'marilag', 'marikina', '2025-07-02', '20:13:00', '20:15:00', 1, '2025-07-02 12:10:59', 0, NULL, NULL, 'completed', NULL, NULL),
(5, 1, 'sample', 'sample', '2025-07-02', '20:20:00', '20:45:00', 5, '2025-07-02 12:19:09', 0, NULL, NULL, 'completed', NULL, NULL),
(7, 1, 'tomoro', 'tomoro cafe', '2025-07-02', '23:57:00', '00:00:00', 5, '2025-07-02 15:47:54', 0, NULL, NULL, 'pending', NULL, NULL),
(8, 1, 'subok', 'subok', '2025-07-03', '00:20:00', '00:30:00', 5, '2025-07-02 16:11:59', 0, NULL, NULL, 'pending', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `students`
--

CREATE TABLE `students` (
  `id` int(11) NOT NULL,
  `fullname` varchar(100) NOT NULL,
  `student_number` varchar(50) NOT NULL,
  `course` varchar(100) DEFAULT NULL,
  `year_level` int(11) DEFAULT NULL,
  `section` varchar(50) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `students`
--

INSERT INTO `students` (`id`, `fullname`, `student_number`, `course`, `year_level`, `section`, `email`, `created_at`) VALUES
(1, 'Ivan Casalme', '20250001', 'BSIT', 3, 'A005', 'ivan123@gmail.com', '2025-07-01 03:08:00'),
(2, 'Joel Paralytic', '20250002', 'BSIT', 3, 'N007', 'joel123@gmail.com', '2025-07-01 03:08:33'),
(3, 'tiwala', '20250003', 'BSIT', 6, 'sa likod', 'tiwala@gmail.com', '2025-07-02 11:37:50');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `role` enum('student','professor') NOT NULL,
  `student_number` varchar(20) DEFAULT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `password_hash` varchar(255) NOT NULL,
  `profile_photo` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `role`, `student_number`, `name`, `email`, `phone`, `password_hash`, `profile_photo`, `created_at`) VALUES
(1, 'professor', NULL, 'Zuri', 'Zuri@gmail.com', NULL, '$2y$10$YVzN1EGZ/27HXX/YwA.9ae29YUwFs1QRuaS9zVMyJcMTt/MdO57/y', NULL, '2025-06-04 21:58:22'),
(4, 'student', '20250001', 'Ivan Casalme', 'ivan123@gmail.com', '6666666666', '$2y$10$5Ah05In4kgopfMSbDT/hheK03lXRUpsNX88LilQ.hJ2e2Dml6NqKy', NULL, '2025-07-01 03:08:00'),
(5, 'student', '20250002', 'Joel Paralytic', 'joel123@gmail.com', '5555555555', '$2y$10$C9oV/jbfAM1Y/05UjBmTne/4wYqsPyGCrkYBzDq72YpJtUXEn7y/G', NULL, '2025-07-01 03:08:33'),
(6, 'student', '20250003', 'tiwala', 'tiwala@gmail.com', '6969696969', '$2y$10$6YdQJ07G08RQBV9cU.0NK.xNnwT9RQ0/rUUhOBoy.phZpsgbEnasu', NULL, '2025-07-02 11:37:50');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `attendance`
--
ALTER TABLE `attendance`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `schedule_id` (`schedule_id`);

--
-- Indexes for table `attendance_logs`
--
ALTER TABLE `attendance_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `attendance_id` (`attendance_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `schedule_id` (`schedule_id`);

--
-- Indexes for table `fingerprints`
--
ALTER TABLE `fingerprints`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `fingerprint_templates`
--
ALTER TABLE `fingerprint_templates`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `template_id` (`template_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `hardware_sessions`
--
ALTER TABLE `hardware_sessions`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `password_change_requests`
--
ALTER TABLE `password_change_requests`
  ADD PRIMARY KEY (`id`),
  ADD KEY `student_id` (`student_id`),
  ADD KEY `professor_id` (`professor_id`),
  ADD KEY `reviewed_by` (`reviewed_by`);

--
-- Indexes for table `rfid_cards`
--
ALTER TABLE `rfid_cards`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `card_uid` (`card_uid`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `schedules`
--
ALTER TABLE `schedules`
  ADD PRIMARY KEY (`id`),
  ADD KEY `professor_id` (`professor_id`);

--
-- Indexes for table `students`
--
ALTER TABLE `students`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `student_number` (`student_number`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `student_number` (`student_number`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `attendance`
--
ALTER TABLE `attendance`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `attendance_logs`
--
ALTER TABLE `attendance_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `fingerprints`
--
ALTER TABLE `fingerprints`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `fingerprint_templates`
--
ALTER TABLE `fingerprint_templates`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `hardware_sessions`
--
ALTER TABLE `hardware_sessions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=125;

--
-- AUTO_INCREMENT for table `password_change_requests`
--
ALTER TABLE `password_change_requests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

--
-- AUTO_INCREMENT for table `rfid_cards`
--
ALTER TABLE `rfid_cards`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `schedules`
--
ALTER TABLE `schedules`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `students`
--
ALTER TABLE `students`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `attendance`
--
ALTER TABLE `attendance`
  ADD CONSTRAINT `attendance_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `attendance_ibfk_2` FOREIGN KEY (`schedule_id`) REFERENCES `schedules` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `fingerprints`
--
ALTER TABLE `fingerprints`
  ADD CONSTRAINT `fingerprints_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `fk_notification_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `password_change_requests`
--
ALTER TABLE `password_change_requests`
  ADD CONSTRAINT `fk_pwchange_professor` FOREIGN KEY (`professor_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_pwchange_reviewer` FOREIGN KEY (`reviewed_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_pwchange_student` FOREIGN KEY (`student_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `rfid_cards`
--
ALTER TABLE `rfid_cards`
  ADD CONSTRAINT `rfid_cards_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `schedules`
--
ALTER TABLE `schedules`
  ADD CONSTRAINT `schedules_ibfk_1` FOREIGN KEY (`professor_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

-- =============================
-- Enrollment Sessions Table (for hardware enrollment)
-- =============================
CREATE TABLE IF NOT EXISTS enrollment_sessions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    type ENUM('rfid', 'fingerprint') NOT NULL,
    status ENUM('waiting', 'success', 'error', 'cancelled') DEFAULT 'waiting',
    scanned_id VARCHAR(100) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    completed_at TIMESTAMP NULL DEFAULT NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- (Legacy fields and tables are left untouched for now)
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
