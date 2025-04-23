-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Mar 20, 2025 at 08:11 AM
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
-- Database: `finalnasystem`
--

-- --------------------------------------------------------

--
-- Table structure for table `deductions`
--

CREATE TABLE `deductions` (
  `id` int(11) NOT NULL,
  `deduction_name` varchar(255) NOT NULL,
  `amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `deductions`
--

INSERT INTO `deductions` (`id`, `deduction_name`, `amount`, `description`, `created_at`, `updated_at`) VALUES
(1, 'Health Insurance Premium', 1200.00, 'Monthly premium for employee health insurance', '2025-03-20 06:11:10', '2025-03-20 06:11:10'),
(2, 'Life Insurance Premium', 800.00, 'Life insurance coverage for employees', '2025-03-20 06:11:10', '2025-03-20 06:11:10'),
(3, 'Accident Insurance', 500.00, 'Deduction for accidental coverage insurance', '2025-03-20 06:11:10', '2025-03-20 06:11:10'),
(4, 'Retirement Pension Fund', 1500.00, 'Employee contribution to pension plan', '2025-03-20 06:11:10', '2025-03-20 06:11:10'),
(5, 'Provident Fund Contribution', 1000.00, 'Contribution towards employee provident fund', '2025-03-20 06:11:10', '2025-03-20 06:11:10'),
(6, 'Government Tax Deduction', 2000.00, 'Mandatory tax deduction from salary', '2025-03-20 06:11:10', '2025-03-20 06:11:10'),
(7, 'SSS Contribution', 600.00, 'Social Security System (SSS) contribution', '2025-03-20 06:11:10', '2025-03-20 06:11:10'),
(8, 'PhilHealth Contribution', 400.00, 'PhilHealth insurance deduction', '2025-03-20 06:11:10', '2025-03-20 06:11:10'),
(9, 'Pag-IBIG Contribution', 300.00, 'Pag-IBIG Fund contribution for housing loans', '2025-03-20 06:11:10', '2025-03-20 06:11:10'),
(10, 'Loan Repayment - Emergency', 2000.00, 'Repayment of emergency loan taken from company', '2025-03-20 06:11:10', '2025-03-20 06:11:10'),
(11, 'Loan Repayment - Housing', 5000.00, 'Housing loan repayment deduction', '2025-03-20 06:11:10', '2025-03-20 06:11:10'),
(12, 'Performance Penalty', 500.00, 'Deduction for not meeting performance targets', '2025-03-20 06:11:10', '2025-03-20 06:11:10'),
(13, 'Tardiness Deduction', 200.00, 'Penalty for arriving late to work', '2025-03-20 06:11:10', '2025-03-20 06:11:10'),
(14, 'Absence Deduction', 1000.00, 'Salary deduction for unexcused absences', '2025-03-20 06:11:10', '2025-03-20 06:11:10'),
(15, 'Training Bond Deduction', 1500.00, 'Deduction for company-sponsored training expenses', '2025-03-20 06:11:10', '2025-03-20 06:11:10'),
(16, 'Uniform Maintenance Fee', 300.00, 'Deduction for uniform maintenance', '2025-03-20 06:11:10', '2025-03-20 06:11:10'),
(17, 'Boarding House', 3500.00, 'Bayad npd utro.', '2025-03-20 07:07:04', '2025-03-20 07:07:30');

-- --------------------------------------------------------

--
-- Table structure for table `events`
--

CREATE TABLE `events` (
  `id` int(11) NOT NULL,
  `event_name` varchar(255) NOT NULL,
  `event_date` datetime NOT NULL,
  `location` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `created_by` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `events`
--

INSERT INTO `events` (`id`, `event_name`, `event_date`, `location`, `description`, `created_by`, `created_at`) VALUES
(4, 'OJT Assesment', '2025-04-05 08:30:00', 'Multi Purpose Room 1', 'all OJTS', 14, '2025-03-18 03:35:14'),
(5, 'Agency Meeting', '2025-03-23 06:30:00', 'Multi Purpose Room 1', 'Agency Manager and OJT\'s', 14, '2025-03-18 03:42:39'),
(6, 'AIA Event', '2025-03-18 08:30:00', 'Multi Purpose Room 1', 'All Employees', 14, '2025-03-18 03:45:57'),
(7, 'dgssfsf', '2025-03-14 01:48:00', 'Multi Purpose Room 1', 'eesesffse', 14, '2025-03-18 03:48:37'),
(10, 'Recruitment Day 1', '2025-03-18 05:30:00', 'Multi Purpose Room 1', 'All Life Planners', 14, '2025-03-18 04:37:35'),
(11, 'Recruitment Day 2', '2025-03-18 08:30:00', 'Multi Purpose Room 1', 'All Life Planners', 14, '2025-03-18 05:24:34'),
(12, 'AIA Event', '2025-03-22 11:30:00', 'Multi Purpose Room 1', 'All Employees', 23, '2025-03-20 03:01:11');

-- --------------------------------------------------------

--
-- Table structure for table `payroll`
--

CREATE TABLE `payroll` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `salary` decimal(10,2) NOT NULL,
  `deductions` decimal(10,2) DEFAULT 0.00,
  `net_salary` decimal(10,2) GENERATED ALWAYS AS (`salary` - `deductions`) VIRTUAL,
  `pay_date` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `positions`
--

CREATE TABLE `positions` (
  `id` int(11) NOT NULL,
  `position_name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `salary` decimal(10,2) NOT NULL DEFAULT 0.00,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `positions`
--

INSERT INTO `positions` (`id`, `position_name`, `description`, `salary`, `created_at`, `updated_at`) VALUES
(1, 'Manager', 'Oversees department operations', 375.00, '2025-03-18 07:25:28', '2025-03-20 06:37:08'),
(2, 'HR Officer', 'Handles recruitment and employee welfare', 281.25, '2025-03-18 07:25:28', '2025-03-20 06:37:08'),
(3, 'Software Developer', 'Develops and maintains software applications', 437.50, '2025-03-18 07:25:28', '2025-03-20 06:37:08'),
(4, 'Marketing Specialist', 'Manages marketing campaigns', 312.50, '2025-03-18 07:25:28', '2025-03-20 06:37:08'),
(5, 'Financial Analyst', 'Handles financial planning and analysis', 343.75, '2025-03-18 07:25:28', '2025-03-20 06:37:08'),
(6, 'Sales Representative', 'mao ni 2.8', 400.25, '2025-03-20 06:53:05', '2025-03-20 07:02:17'),
(8, 'Sales Representative 1', 'mao na pd ni', 450.75, '2025-03-20 07:02:54', '2025-03-20 07:02:54');

-- --------------------------------------------------------

--
-- Table structure for table `roles`
--

CREATE TABLE `roles` (
  `id` int(11) NOT NULL,
  `role_name` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `roles`
--

INSERT INTO `roles` (`id`, `role_name`) VALUES
(4, 'admin_officer'),
(2, 'agency_manager'),
(6, 'financial_officer'),
(7, 'intern'),
(5, 'marketing_officer'),
(1, 'super_admin'),
(3, 'unit_manager');

-- --------------------------------------------------------

--
-- Table structure for table `tasks`
--

CREATE TABLE `tasks` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `assigned_to` int(11) NOT NULL,
  `status` enum('pending','in_progress','completed') DEFAULT 'pending',
  `due_date` date DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `time_logs`
--

CREATE TABLE `time_logs` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `check_in` timestamp NOT NULL DEFAULT current_timestamp(),
  `check_out` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
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
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `employee_id`, `username`, `password`, `email`, `fname`, `mname`, `lname`, `suffix`, `birthday`, `phone`, `address`, `country`, `province`, `city_municipality`, `brgy`, `position`, `profile_picture`, `status`, `role_id`, `position_id`, `created_at`, `updated_at`) VALUES
(2, 'EMP00002', 'agency', '03ac674216f3e15c761ee1a5e255f067953623c8b388b4459e13f978d7c846f4', 'manager@example.com', 'Agency', 'sfdsf', 'Manager', '', '1999-06-14', '1234567891', '456 Agency Rd', 'Philippines', 'Biliran', 'Naval', 'Atipolo', NULL, NULL, 'active', 2, 1, '2025-03-14 09:26:17', '2025-03-19 05:17:38'),
(3, 'EMP00003', 'unit', '03ac674216f3e15c761ee1a5e255f067953623c8b388b4459e13f978d7c846f4', 'unit@example.com', 'Unit', 'sgsegs', 'Manager', '', '2000-12-11', '1234567892', '789 Unit Blvd', 'Philippines', 'Cebu', 'Cebu', 'Hippodromo', NULL, NULL, 'inactive', 3, 1, '2025-03-14 09:26:17', '2025-03-20 01:43:30'),
(4, 'EMP00004', 'admin1', '03ac674216f3e15c761ee1a5e255f067953623c8b388b4459e13f978d7c846f4', 'officer@example.com', 'Admin', 'Meddle', 'Officer', '', '2000-11-12', '09467893458', '101 Admin Sq', 'Philippines', 'Cebu', 'Cebu', 'Atipolo', NULL, NULL, 'active', 4, 1, '2025-03-14 09:26:17', '2025-03-20 01:56:03'),
(5, 'EMP00005', 'marketing', '03ac674216f3e15c761ee1a5e255f067953623c8b388b4459e13f978d7c846f4', 'marketing@example.com', 'Marketing', 'gsgssgs', 'Officer', '', '2000-07-22', '09324876943', '202 Market Ln', 'Philippines', 'Biliran', 'Naval', 'Atipolo', NULL, NULL, 'inactive', 5, 1, '2025-03-14 09:26:17', '2025-03-20 04:54:27'),
(6, 'EMP00006', 'financial', '03ac674216f3e15c761ee1a5e255f067953623c8b388b4459e13f978d7c846f4', 'finance@example.com', 'Financial', 'gsegeg', 'Officer', '', '2002-08-03', '09843467894', '303 Finance Ave', 'Philippines', 'Leyte', 'Biliran', 'San Isidro', NULL, NULL, 'active', 6, 1, '2025-03-14 09:26:17', '2025-03-20 05:52:20'),
(7, 'EMP00007', 'intern', '03ac674216f3e15c761ee1a5e255f067953623c8b388b4459e13f978d7c846f4', 'intern@example.com', 'Intern', NULL, 'User', NULL, '0000-00-00', '1234567896', '404 Intern Ct', NULL, NULL, NULL, NULL, NULL, NULL, 'active', 7, 1, '2025-03-14 09:26:17', '2025-03-18 07:27:33'),
(9, 'EMP00009', 'admin', '03ac674216f3e15c761ee1a5e255f067953623c8b388b4459e13f978d7c846f4', 'admin2@gmail.com', 'Super', NULL, 'Admin', NULL, '0000-00-00', '09746836485', 'Cebu City', NULL, NULL, NULL, NULL, NULL, NULL, 'active', 1, 1, '2025-03-14 15:47:27', '2025-03-20 03:31:09'),
(13, 'EMP00013', 'jhonrickyvero', '03ac674216f3e15c761ee1a5e255f067953623c8b388b4459e13f978d7c846f4', 'jhonrickyv@gmail.com', 'Jhon', NULL, 'Vero', NULL, '0000-00-00', '09324876943', 'Cebu City', NULL, NULL, NULL, NULL, NULL, NULL, 'active', 7, 1, '2025-03-14 16:08:14', '2025-03-20 03:31:09'),
(14, 'EMP00014', 'admin2', '03ac674216f3e15c761ee1a5e255f067953623c8b388b4459e13f978d7c846f4', 'admin@example.com', 'Admin', NULL, 'User', NULL, '0000-00-00', '1234567890', '123 Admin St', NULL, NULL, NULL, NULL, NULL, NULL, 'active', 1, 1, '2025-03-14 16:26:30', '2025-03-18 07:27:33'),
(15, 'EMP00015', 'jorickdocallos', '03ac674216f3e15c761ee1a5e255f067953623c8b388b4459e13f978d7c846f4', 'jorick@gmail.com', 'Jorick', NULL, 'Docallos', NULL, '0000-00-00', '09746836485', 'Naval, Biliran', NULL, NULL, NULL, NULL, NULL, NULL, 'active', 7, 1, '2025-03-16 02:05:12', '2025-03-20 03:31:09'),
(16, 'EMP00016', 'neilquepo', '03ac674216f3e15c761ee1a5e255f067953623c8b388b4459e13f978d7c846f4', 'neil@gmail.com', 'Neil', NULL, 'Quepo', NULL, '0000-00-00', '09624563256', 'Cebu City', NULL, NULL, NULL, NULL, NULL, NULL, 'active', 7, 1, '2025-03-16 02:06:34', '2025-03-20 03:31:09'),
(17, 'EMP00017', 'intern2', '03ac674216f3e15c761ee1a5e255f067953623c8b388b4459e13f978d7c846f4', 'intern2@gmail.com', 'Intern', 'eeegerg', 'Intern', 'Jr.', '2003-04-22', '09123456789', 'Atipolo, naval, biliran', 'Philippines', 'Biliran', 'Naval', 'Atipolo', NULL, NULL, 'active', 7, 2, '2025-03-16 02:21:43', '2025-03-20 03:31:09'),
(18, 'EMP00021', 'jhonrickyv', '03ac674216f3e15c761ee1a5e255f067953623c8b388b4459e13f978d7c846f4', 'jhonrickyvero@example.com', 'Jhon Ricky', 'Picardal', 'Vero', 'Jr.', '2002-06-23', '09458934723', NULL, 'Philippines', 'Biliran', 'Naval', 'Atipolo', NULL, NULL, 'active', 7, 3, '2025-03-18 08:13:47', '2025-03-20 03:31:09'),
(19, 'EMP00022', 'intern3', '03ac674216f3e15c761ee1a5e255f067953623c8b388b4459e13f978d7c846f4', 'intern3@gmail.com', 'feee', 'sdadd', 'fsfdf', 'Jr.', '2002-06-22', '09458934723', NULL, 'Philippines', 'Biliran', 'Naval', 'Atipolo', NULL, NULL, 'active', 7, 3, '2025-03-18 08:50:14', '2025-03-20 03:31:09'),
(23, 'EMP00023', 'admin3', '03ac674216f3e15c761ee1a5e255f067953623c8b388b4459e13f978d7c846f4', 'admin3@gmail.com', 'Admin3', 'Finn', 'Clove', '', '2001-11-12', '09746836485', NULL, 'Philippines', 'Biliran', 'Cebu', 'Atipolo', NULL, NULL, 'active', 1, 2, '2025-03-20 02:40:58', '2025-03-20 02:59:34'),
(24, 'EMP00024', 'admin4', '03ac674216f3e15c761ee1a5e255f067953623c8b388b4459e13f978d7c846f4', 'admin4@example.com', 'Admin4', 'wfwefwef', 'wefwefewf', '', '2003-11-04', '09746836485', NULL, 'Philippines', 'Biliran', 'Cebu', 'Atipolo', NULL, NULL, 'active', 1, 2, '2025-03-20 03:37:21', '2025-03-20 03:37:21');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `deductions`
--
ALTER TABLE `deductions`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `events`
--
ALTER TABLE `events`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`);

--
-- Indexes for table `payroll`
--
ALTER TABLE `payroll`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `positions`
--
ALTER TABLE `positions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `position_name` (`position_name`);

--
-- Indexes for table `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `role_name` (`role_name`);

--
-- Indexes for table `tasks`
--
ALTER TABLE `tasks`
  ADD PRIMARY KEY (`id`),
  ADD KEY `assigned_to` (`assigned_to`);

--
-- Indexes for table `time_logs`
--
ALTER TABLE `time_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `employee_id` (`employee_id`),
  ADD KEY `role_id` (`role_id`),
  ADD KEY `fk_users_position` (`position_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `deductions`
--
ALTER TABLE `deductions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `events`
--
ALTER TABLE `events`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `payroll`
--
ALTER TABLE `payroll`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `positions`
--
ALTER TABLE `positions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `roles`
--
ALTER TABLE `roles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `tasks`
--
ALTER TABLE `tasks`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `time_logs`
--
ALTER TABLE `time_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `events`
--
ALTER TABLE `events`
  ADD CONSTRAINT `events_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `payroll`
--
ALTER TABLE `payroll`
  ADD CONSTRAINT `payroll_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `tasks`
--
ALTER TABLE `tasks`
  ADD CONSTRAINT `tasks_ibfk_1` FOREIGN KEY (`assigned_to`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `time_logs`
--
ALTER TABLE `time_logs`
  ADD CONSTRAINT `time_logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `fk_users_position` FOREIGN KEY (`position_id`) REFERENCES `positions` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `users_ibfk_1` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
