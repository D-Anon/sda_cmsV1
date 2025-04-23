-- filepath: c:\xampp\htdocs\finalnasystem\finalnasystem\finalnasystem.sql

--
-- Table structure for table `time_logs`
--

CREATE TABLE `time_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `check_in` timestamp NOT NULL DEFAULT current_timestamp(),
  `check_out` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `fk_time_logs_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `time_logs`
--

INSERT INTO `time_logs` (`user_id`, `check_in`, `check_out`) VALUES
(2, '2025-03-20 09:00:00', '2025-03-20 17:00:00'),
(3, '2025-03-20 09:15:00', '2025-03-20 17:15:00'),
(4, '2025-03-20 09:30:00', '2025-03-20 17:30:00');

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `employee_id` varchar(50) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `email` varchar(100) NOT NULL,
  `fname` varchar(100) NOT NULL,
  `mname` varchar(100) DEFAULT NULL,
  `lname` varchar(100) NOT NULL,
  `suffix` varchar(10) DEFAULT NULL,
  `birthday` date NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `country` varchar(100) DEFAULT NULL,
  `province` varchar(100) DEFAULT NULL,
  `city_municipality` varchar(100) DEFAULT NULL,
  `brgy` varchar(100) DEFAULT NULL,
  `position` varchar(255) DEFAULT NULL,
  `profile_picture` varchar(255) DEFAULT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `role_id` int(11) NOT NULL,
  `position_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `email` (`email`),
  UNIQUE KEY `employee_id` (`employee_id`),
  KEY `role_id` (`role_id`),
  KEY `fk_users_position` (`position_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `employee_id`, `username`, `password`, `email`, `fname`, `mname`, `lname`, `suffix`, `birthday`, `phone`, `address`, `country`, `province`, `city_municipality`, `brgy`, `position`, `profile_picture`, `status`, `role_id`, `position_id`, `created_at`, `updated_at`) VALUES
(2, 'EMP00002', 'agency', '03ac674216f3e15c761ee1a5e255f067953623c8b388b4459e13f978d7c846f4', 'manager@example.com', 'Agency', 'sfdsf', 'Manager', '', '1999-06-14', '1234567891', '456 Agency Rd', 'Philippines', 'Biliran', 'Naval', 'Atipolo', NULL, NULL, 'active', 2, 1, '2025-03-14 09:26:17', '2025-03-19 05:17:38'),
(3, 'EMP00003', 'unit', '03ac674216f3e15c761ee1a5e255f067953623c8b388b4459e13f978d7c846f4', 'unit@example.com', 'Unit', 'sgsegs', 'Manager', '', '2000-12-11', '1234567892', '789 Unit Blvd', 'Philippines', 'Cebu', 'Cebu', 'Hippodromo', NULL, NULL, 'inactive', 3, 1, '2025-03-14 09:26:17', '2025-03-20 01:43:30'),
(4, 'EMP00004', 'admin1', '03ac674216f3e15c761ee1a5e255f067953623c8b388b4459e13f978d7c846f4', 'officer@example.com', 'Admin', 'Meddle', 'Officer', '', '2000-11-12', '09467893458', '101 Admin Sq', 'Philippines', 'Cebu', 'Cebu', 'Atipolo', NULL, NULL, 'active', 4, 1, '2025-03-14 09:26:17', '2025-03-20 01:56:03');