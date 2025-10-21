-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 21, 2025 at 01:59 PM
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
-- Database: `svg`
--

-- --------------------------------------------------------

--
-- Table structure for table `announcements`
--

CREATE TABLE `announcements` (
  `id` bigint(20) NOT NULL,
  `barangay_name` text NOT NULL,
  `title` text NOT NULL,
  `description` text NOT NULL,
  `category` varchar(100) NOT NULL DEFAULT 'Advisory',
  `image_url` text DEFAULT NULL,
  `image_path` text DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `barangay_admins`
--

CREATE TABLE `barangay_admins` (
  `id` bigint(20) NOT NULL,
  `barangay_name` text NOT NULL,
  `city` text NOT NULL,
  `province` text NOT NULL,
  `region` text NOT NULL,
  `email` text NOT NULL,
  `password` text NOT NULL,
  `contact_no` text NOT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `barangay_admins`
--

INSERT INTO `barangay_admins` (`id`, `barangay_name`, `city`, `province`, `region`, `email`, `password`, `contact_no`, `created_at`) VALUES
(1, 'Barangay Debugon', 'Debug City', 'Debug Province', 'Region IV-A', 'admin1', 'asdf1234', '09123456789', '2025-10-21 18:03:03');

-- --------------------------------------------------------

--
-- Table structure for table `barangay_clearance_requests`
--

CREATE TABLE `barangay_clearance_requests` (
  `id` bigint(20) NOT NULL,
  `resident_id` bigint(20) DEFAULT NULL,
  `fullname` text NOT NULL,
  `civil_status` text NOT NULL,
  `date_of_birth` date NOT NULL,
  `house_street` text NOT NULL,
  `city` text NOT NULL,
  `province` text NOT NULL,
  `date_of_residency` date DEFAULT NULL,
  `years_residency` int(11) DEFAULT NULL,
  `purpose` text NOT NULL,
  `valid_id_url` text DEFAULT NULL,
  `email` text NOT NULL,
  `phone` text DEFAULT NULL,
  `barangay_name` text NOT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `status` varchar(50) NOT NULL DEFAULT 'Pending',
  `permit_type` varchar(100) DEFAULT 'Barangay Clearance'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `barangay_clearance_requests`
--

INSERT INTO `barangay_clearance_requests` (`id`, `resident_id`, `fullname`, `civil_status`, `date_of_birth`, `house_street`, `city`, `province`, `date_of_residency`, `years_residency`, `purpose`, `valid_id_url`, `email`, `phone`, `barangay_name`, `created_at`, `status`, `permit_type`) VALUES
(1, 1, 'Jay aldrin Tayoyo', 'Married', '2025-10-03', 'erdtfvgbtdrfyh', 'Quezon City', 'Manila', '2025-10-01', 10, '6rt7u65t', 'uploads/valid_ids/1761043903_cat.jpg', 'resident1', '960235528', 'Barangay Debugon', '2025-10-21 18:51:43', 'Pending', 'Barangay Clearance');

-- --------------------------------------------------------

--
-- Table structure for table `barangay_events`
--

CREATE TABLE `barangay_events` (
  `id` bigint(20) NOT NULL,
  `barangay_name` text NOT NULL,
  `title` text NOT NULL,
  `description` text DEFAULT NULL,
  `category` varchar(100) DEFAULT 'General',
  `venue` text DEFAULT NULL,
  `start_date` datetime NOT NULL,
  `end_date` datetime DEFAULT NULL,
  `visibility` varchar(50) DEFAULT 'public',
  `linked_announcement_id` bigint(20) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `business_permit_requests`
--

CREATE TABLE `business_permit_requests` (
  `id` bigint(20) NOT NULL,
  `resident_id` bigint(20) DEFAULT NULL,
  `business_name` text DEFAULT NULL,
  `owner_name` text DEFAULT NULL,
  `email` text DEFAULT NULL,
  `phone` text DEFAULT NULL,
  `business_type` text DEFAULT NULL,
  `dti_cert_url` text DEFAULT NULL,
  `lease_contract_url` text DEFAULT NULL,
  `purpose` text DEFAULT NULL,
  `valid_id_url` text DEFAULT NULL,
  `barangay_name` text DEFAULT NULL,
  `status` varchar(50) DEFAULT 'Pending',
  `created_at` datetime DEFAULT current_timestamp(),
  `permit_type` varchar(100) DEFAULT 'Business Permit',
  `house_street` text DEFAULT NULL,
  `city` text DEFAULT NULL,
  `province` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `event_interest`
--

CREATE TABLE `event_interest` (
  `id` bigint(20) NOT NULL,
  `event_id` bigint(20) NOT NULL,
  `resident_id` bigint(20) NOT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `goodmoral_requests`
--

CREATE TABLE `goodmoral_requests` (
  `id` bigint(20) NOT NULL,
  `resident_id` bigint(20) DEFAULT NULL,
  `fullname` text NOT NULL,
  `email` text DEFAULT NULL,
  `phone` text DEFAULT NULL,
  `date_of_birth` date DEFAULT NULL,
  `barangay_clearance_url` text DEFAULT NULL,
  `purpose` text DEFAULT NULL,
  `valid_id_url` text DEFAULT NULL,
  `barangay_name` text DEFAULT NULL,
  `status` varchar(50) DEFAULT 'Pending',
  `created_at` datetime DEFAULT current_timestamp(),
  `permit_type` varchar(100) DEFAULT 'Certificate of Good Moral Character',
  `house_street` text DEFAULT NULL,
  `city` text DEFAULT NULL,
  `province` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `indigency_requests`
--

CREATE TABLE `indigency_requests` (
  `id` bigint(20) NOT NULL,
  `resident_id` bigint(20) DEFAULT NULL,
  `fullname` text NOT NULL,
  `email` text DEFAULT NULL,
  `phone` text DEFAULT NULL,
  `proof_of_income_url` text DEFAULT NULL,
  `purpose` text DEFAULT NULL,
  `valid_id_url` text DEFAULT NULL,
  `barangay_name` text DEFAULT NULL,
  `status` varchar(50) DEFAULT 'Pending',
  `created_at` datetime DEFAULT current_timestamp(),
  `permit_type` varchar(100) DEFAULT 'Certificate of Indigency',
  `house_street` text DEFAULT NULL,
  `city` text DEFAULT NULL,
  `province` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `latebirth_requests`
--

CREATE TABLE `latebirth_requests` (
  `id` bigint(20) NOT NULL,
  `resident_id` bigint(20) DEFAULT NULL,
  `fullname` text NOT NULL,
  `email` text DEFAULT NULL,
  `phone` text DEFAULT NULL,
  `birth_record_url` text DEFAULT NULL,
  `purpose` text DEFAULT NULL,
  `valid_id_url` text DEFAULT NULL,
  `barangay_name` text DEFAULT NULL,
  `status` varchar(50) DEFAULT 'Pending',
  `created_at` datetime DEFAULT current_timestamp(),
  `permit_type` varchar(100) DEFAULT 'Certificate of Late Birth Registration',
  `house_street` text DEFAULT NULL,
  `city` text DEFAULT NULL,
  `province` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `norecord_requests`
--

CREATE TABLE `norecord_requests` (
  `id` bigint(20) NOT NULL,
  `resident_id` bigint(20) DEFAULT NULL,
  `fullname` text NOT NULL,
  `email` text DEFAULT NULL,
  `phone` text DEFAULT NULL,
  `purpose` text DEFAULT NULL,
  `valid_id_url` text DEFAULT NULL,
  `barangay_name` text DEFAULT NULL,
  `status` varchar(50) DEFAULT 'Pending',
  `created_at` datetime DEFAULT current_timestamp(),
  `permit_type` varchar(100) DEFAULT 'Certificate of No Record',
  `house_street` text DEFAULT NULL,
  `city` text DEFAULT NULL,
  `province` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` bigint(20) NOT NULL,
  `barangay_name` text NOT NULL,
  `recipient_type` varchar(50) NOT NULL,
  `recipient_id` bigint(20) DEFAULT NULL,
  `source_table` text DEFAULT NULL,
  `source_id` bigint(20) DEFAULT NULL,
  `type` varchar(100) NOT NULL,
  `title` text NOT NULL,
  `message` text NOT NULL,
  `link` text DEFAULT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `ojt_requests`
--

CREATE TABLE `ojt_requests` (
  `id` bigint(20) NOT NULL,
  `resident_id` bigint(20) DEFAULT NULL,
  `fullname` text NOT NULL,
  `email` text DEFAULT NULL,
  `phone` text DEFAULT NULL,
  `school_name` text DEFAULT NULL,
  `endorsement_letter_url` text DEFAULT NULL,
  `purpose` text DEFAULT NULL,
  `valid_id_url` text DEFAULT NULL,
  `barangay_name` text DEFAULT NULL,
  `status` varchar(50) DEFAULT 'Pending',
  `created_at` datetime DEFAULT current_timestamp(),
  `permit_type` varchar(100) DEFAULT 'Certificate of OJT / Training Endorsement',
  `house_street` text DEFAULT NULL,
  `city` text DEFAULT NULL,
  `province` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `residency_requests`
--

CREATE TABLE `residency_requests` (
  `id` bigint(20) NOT NULL,
  `resident_id` bigint(20) DEFAULT NULL,
  `fullname` text NOT NULL,
  `email` text DEFAULT NULL,
  `phone` text DEFAULT NULL,
  `house_street` text DEFAULT NULL,
  `city` text DEFAULT NULL,
  `province` text DEFAULT NULL,
  `date_of_residency` date DEFAULT NULL,
  `years_residency` int(11) DEFAULT NULL,
  `purpose` text DEFAULT NULL,
  `valid_id_url` text DEFAULT NULL,
  `barangay_name` text DEFAULT NULL,
  `status` varchar(50) DEFAULT 'Pending',
  `created_at` datetime DEFAULT current_timestamp(),
  `permit_type` varchar(100) DEFAULT 'Residency Certificate'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `residents`
--

CREATE TABLE `residents` (
  `id` bigint(20) NOT NULL,
  `last_name` text NOT NULL,
  `first_name` text NOT NULL,
  `middle_name` text DEFAULT NULL,
  `suffix` text DEFAULT NULL,
  `phone` varchar(20) NOT NULL,
  `email` text NOT NULL,
  `birthdate` date NOT NULL,
  `house_no` text NOT NULL,
  `street` text NOT NULL,
  `purok` text NOT NULL,
  `subdivision` text DEFAULT NULL,
  `barangay` text NOT NULL,
  `city` text NOT NULL,
  `province` text NOT NULL,
  `region` text NOT NULL,
  `postal` varchar(10) NOT NULL,
  `nationality` text NOT NULL,
  `agree` tinyint(1) NOT NULL DEFAULT 0,
  `updates` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` datetime DEFAULT current_timestamp(),
  `verification_status` varchar(20) NOT NULL DEFAULT 'Unverified',
  `password` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `residents`
--

INSERT INTO `residents` (`id`, `last_name`, `first_name`, `middle_name`, `suffix`, `phone`, `email`, `birthdate`, `house_no`, `street`, `purok`, `subdivision`, `barangay`, `city`, `province`, `region`, `postal`, `nationality`, `agree`, `updates`, `created_at`, `verification_status`, `password`) VALUES
(1, 'Cane', 'fly', 'I', '', '09998887777', 'resident1', '2000-01-01', '123', 'Main Street', '1', '', 'Barangay Debugon', 'Debug City', 'Debug Province', 'Region IV-A', '1234', 'Filipino', 1, 0, '2025-10-21 18:02:19', 'Verified', 'asdf1234');

-- --------------------------------------------------------

--
-- Table structure for table `resident_verifications`
--

CREATE TABLE `resident_verifications` (
  `id` bigint(20) NOT NULL,
  `resident_id` bigint(20) NOT NULL,
  `id_type` text NOT NULL,
  `valid_id_url` text NOT NULL,
  `submitted_at` datetime DEFAULT current_timestamp(),
  `status` varchar(20) NOT NULL DEFAULT 'Pending',
  `reviewed_by` text DEFAULT NULL,
  `reviewed_at` datetime DEFAULT NULL,
  `remarks` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `soloparent_requests`
--

CREATE TABLE `soloparent_requests` (
  `id` bigint(20) NOT NULL,
  `resident_id` bigint(20) DEFAULT NULL,
  `fullname` text NOT NULL,
  `email` text DEFAULT NULL,
  `phone` text DEFAULT NULL,
  `proof_of_solo_status_url` text DEFAULT NULL,
  `purpose` text DEFAULT NULL,
  `valid_id_url` text DEFAULT NULL,
  `barangay_name` text DEFAULT NULL,
  `status` varchar(50) DEFAULT 'Pending',
  `created_at` datetime DEFAULT current_timestamp(),
  `permit_type` varchar(100) DEFAULT 'Certificate of Solo Parent',
  `house_street` text DEFAULT NULL,
  `city` text DEFAULT NULL,
  `province` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `verification_rejects`
--

CREATE TABLE `verification_rejects` (
  `id` bigint(20) NOT NULL,
  `resident_id` bigint(20) NOT NULL,
  `id_type` text NOT NULL,
  `valid_id_url` text NOT NULL,
  `reason` text NOT NULL,
  `rejected_at` datetime DEFAULT current_timestamp(),
  `reviewed_by` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `announcements`
--
ALTER TABLE `announcements`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `barangay_admins`
--
ALTER TABLE `barangay_admins`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`) USING HASH;

--
-- Indexes for table `barangay_clearance_requests`
--
ALTER TABLE `barangay_clearance_requests`
  ADD PRIMARY KEY (`id`),
  ADD KEY `resident_id` (`resident_id`);

--
-- Indexes for table `barangay_events`
--
ALTER TABLE `barangay_events`
  ADD PRIMARY KEY (`id`),
  ADD KEY `linked_announcement_id` (`linked_announcement_id`);

--
-- Indexes for table `business_permit_requests`
--
ALTER TABLE `business_permit_requests`
  ADD PRIMARY KEY (`id`),
  ADD KEY `resident_id` (`resident_id`);

--
-- Indexes for table `event_interest`
--
ALTER TABLE `event_interest`
  ADD PRIMARY KEY (`id`),
  ADD KEY `event_id` (`event_id`),
  ADD KEY `resident_id` (`resident_id`);

--
-- Indexes for table `goodmoral_requests`
--
ALTER TABLE `goodmoral_requests`
  ADD PRIMARY KEY (`id`),
  ADD KEY `resident_id` (`resident_id`);

--
-- Indexes for table `indigency_requests`
--
ALTER TABLE `indigency_requests`
  ADD PRIMARY KEY (`id`),
  ADD KEY `resident_id` (`resident_id`);

--
-- Indexes for table `latebirth_requests`
--
ALTER TABLE `latebirth_requests`
  ADD PRIMARY KEY (`id`),
  ADD KEY `resident_id` (`resident_id`);

--
-- Indexes for table `norecord_requests`
--
ALTER TABLE `norecord_requests`
  ADD PRIMARY KEY (`id`),
  ADD KEY `resident_id` (`resident_id`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `ojt_requests`
--
ALTER TABLE `ojt_requests`
  ADD PRIMARY KEY (`id`),
  ADD KEY `resident_id` (`resident_id`);

--
-- Indexes for table `residency_requests`
--
ALTER TABLE `residency_requests`
  ADD PRIMARY KEY (`id`),
  ADD KEY `resident_id` (`resident_id`);

--
-- Indexes for table `residents`
--
ALTER TABLE `residents`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`) USING HASH;

--
-- Indexes for table `resident_verifications`
--
ALTER TABLE `resident_verifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `resident_id` (`resident_id`);

--
-- Indexes for table `soloparent_requests`
--
ALTER TABLE `soloparent_requests`
  ADD PRIMARY KEY (`id`),
  ADD KEY `resident_id` (`resident_id`);

--
-- Indexes for table `verification_rejects`
--
ALTER TABLE `verification_rejects`
  ADD PRIMARY KEY (`id`),
  ADD KEY `resident_id` (`resident_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `announcements`
--
ALTER TABLE `announcements`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `barangay_admins`
--
ALTER TABLE `barangay_admins`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `barangay_clearance_requests`
--
ALTER TABLE `barangay_clearance_requests`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `barangay_events`
--
ALTER TABLE `barangay_events`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `business_permit_requests`
--
ALTER TABLE `business_permit_requests`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `event_interest`
--
ALTER TABLE `event_interest`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `goodmoral_requests`
--
ALTER TABLE `goodmoral_requests`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `indigency_requests`
--
ALTER TABLE `indigency_requests`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `latebirth_requests`
--
ALTER TABLE `latebirth_requests`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `norecord_requests`
--
ALTER TABLE `norecord_requests`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `ojt_requests`
--
ALTER TABLE `ojt_requests`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `residency_requests`
--
ALTER TABLE `residency_requests`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `residents`
--
ALTER TABLE `residents`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `resident_verifications`
--
ALTER TABLE `resident_verifications`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `soloparent_requests`
--
ALTER TABLE `soloparent_requests`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `verification_rejects`
--
ALTER TABLE `verification_rejects`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `barangay_clearance_requests`
--
ALTER TABLE `barangay_clearance_requests`
  ADD CONSTRAINT `barangay_clearance_requests_ibfk_1` FOREIGN KEY (`resident_id`) REFERENCES `residents` (`id`);

--
-- Constraints for table `barangay_events`
--
ALTER TABLE `barangay_events`
  ADD CONSTRAINT `barangay_events_ibfk_1` FOREIGN KEY (`linked_announcement_id`) REFERENCES `announcements` (`id`);

--
-- Constraints for table `business_permit_requests`
--
ALTER TABLE `business_permit_requests`
  ADD CONSTRAINT `business_permit_requests_ibfk_1` FOREIGN KEY (`resident_id`) REFERENCES `residents` (`id`);

--
-- Constraints for table `event_interest`
--
ALTER TABLE `event_interest`
  ADD CONSTRAINT `event_interest_ibfk_1` FOREIGN KEY (`event_id`) REFERENCES `barangay_events` (`id`),
  ADD CONSTRAINT `event_interest_ibfk_2` FOREIGN KEY (`resident_id`) REFERENCES `residents` (`id`);

--
-- Constraints for table `goodmoral_requests`
--
ALTER TABLE `goodmoral_requests`
  ADD CONSTRAINT `goodmoral_requests_ibfk_1` FOREIGN KEY (`resident_id`) REFERENCES `residents` (`id`);

--
-- Constraints for table `indigency_requests`
--
ALTER TABLE `indigency_requests`
  ADD CONSTRAINT `indigency_requests_ibfk_1` FOREIGN KEY (`resident_id`) REFERENCES `residents` (`id`);

--
-- Constraints for table `latebirth_requests`
--
ALTER TABLE `latebirth_requests`
  ADD CONSTRAINT `latebirth_requests_ibfk_1` FOREIGN KEY (`resident_id`) REFERENCES `residents` (`id`);

--
-- Constraints for table `norecord_requests`
--
ALTER TABLE `norecord_requests`
  ADD CONSTRAINT `norecord_requests_ibfk_1` FOREIGN KEY (`resident_id`) REFERENCES `residents` (`id`);

--
-- Constraints for table `ojt_requests`
--
ALTER TABLE `ojt_requests`
  ADD CONSTRAINT `ojt_requests_ibfk_1` FOREIGN KEY (`resident_id`) REFERENCES `residents` (`id`);

--
-- Constraints for table `residency_requests`
--
ALTER TABLE `residency_requests`
  ADD CONSTRAINT `residency_requests_ibfk_1` FOREIGN KEY (`resident_id`) REFERENCES `residents` (`id`);

--
-- Constraints for table `resident_verifications`
--
ALTER TABLE `resident_verifications`
  ADD CONSTRAINT `resident_verifications_ibfk_1` FOREIGN KEY (`resident_id`) REFERENCES `residents` (`id`);

--
-- Constraints for table `soloparent_requests`
--
ALTER TABLE `soloparent_requests`
  ADD CONSTRAINT `soloparent_requests_ibfk_1` FOREIGN KEY (`resident_id`) REFERENCES `residents` (`id`);

--
-- Constraints for table `verification_rejects`
--
ALTER TABLE `verification_rejects`
  ADD CONSTRAINT `verification_rejects_ibfk_1` FOREIGN KEY (`resident_id`) REFERENCES `residents` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
