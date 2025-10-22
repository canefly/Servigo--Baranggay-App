-- phpMyAdmin SQL Dump
-- version 5.2.1deb3
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Oct 22, 2025 at 08:51 PM
-- Server version: 8.0.43-0ubuntu0.24.04.2
-- PHP Version: 8.3.6

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
  `id` bigint NOT NULL,
  `barangay_name` text COLLATE utf8mb4_general_ci NOT NULL,
  `title` text COLLATE utf8mb4_general_ci NOT NULL,
  `description` text COLLATE utf8mb4_general_ci NOT NULL,
  `category` varchar(100) COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'Advisory',
  `image_url` text COLLATE utf8mb4_general_ci,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `announcements`
--

INSERT INTO `announcements` (`id`, `barangay_name`, `title`, `description`, `category`, `image_url`, `created_at`) VALUES
(1, 'San Isidro', 'ahahaha', 'hohohoho', 'Advisory', '/uploads/announcements/1761064466_Gy43PxnXEAMANtG.jpg', '2025-10-22 00:34:26'),
(2, 'San Isidro', 'waiodhaoiwndawd', 'aegfefefefefe', 'Event', '/servigo/uploads/announcements/1761064780_GzasCYta4AE3oTm.jpg', '2025-10-22 00:39:40'),
(3, 'San Isidro', 'erwsgvesdrfcghbedr', 'rtgertetre', 'Event', '/servigo/uploads/announcements/1761070201_cat.jpg', '2025-10-22 02:10:01'),
(6, 'Barangay Debugon', 'TESTINGG ANNOUCNCEMENT', 'haha a cat.', 'Advisory', '//uploads/announcements/1761146901_cat.jpg', '2025-10-22 23:28:21'),
(7, 'Barangay Debugon', 'hihihihi', 'hhohoho', 'Advisory', 'uploads/announcements/1761153156_GzxwRruaoAYMgvg.jpg', '2025-10-23 01:12:36');

-- --------------------------------------------------------

--
-- Table structure for table `barangay_admins`
--

CREATE TABLE `barangay_admins` (
  `id` bigint NOT NULL,
  `barangay_name` text COLLATE utf8mb4_general_ci NOT NULL,
  `city` text COLLATE utf8mb4_general_ci NOT NULL,
  `province` text COLLATE utf8mb4_general_ci NOT NULL,
  `region` text COLLATE utf8mb4_general_ci NOT NULL,
  `email` text COLLATE utf8mb4_general_ci NOT NULL,
  `password` text COLLATE utf8mb4_general_ci NOT NULL,
  `contact_no` text COLLATE utf8mb4_general_ci NOT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP
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
  `id` bigint NOT NULL,
  `resident_id` bigint DEFAULT NULL,
  `fullname` text COLLATE utf8mb4_general_ci NOT NULL,
  `civil_status` text COLLATE utf8mb4_general_ci NOT NULL,
  `date_of_birth` date NOT NULL,
  `house_street` text COLLATE utf8mb4_general_ci NOT NULL,
  `city` text COLLATE utf8mb4_general_ci NOT NULL,
  `province` text COLLATE utf8mb4_general_ci NOT NULL,
  `date_of_residency` date DEFAULT NULL,
  `years_residency` int DEFAULT NULL,
  `purpose` text COLLATE utf8mb4_general_ci NOT NULL,
  `valid_id_url` text COLLATE utf8mb4_general_ci,
  `email` text COLLATE utf8mb4_general_ci NOT NULL,
  `phone` text COLLATE utf8mb4_general_ci,
  `barangay_name` text COLLATE utf8mb4_general_ci NOT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `status` varchar(50) COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'Pending',
  `permit_type` varchar(100) COLLATE utf8mb4_general_ci DEFAULT 'Barangay Clearance'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `barangay_clearance_requests`
--

INSERT INTO `barangay_clearance_requests` (`id`, `resident_id`, `fullname`, `civil_status`, `date_of_birth`, `house_street`, `city`, `province`, `date_of_residency`, `years_residency`, `purpose`, `valid_id_url`, `email`, `phone`, `barangay_name`, `created_at`, `status`, `permit_type`) VALUES
(1, 1, 'Jay aldrin Tayoyo', 'Married', '2025-10-03', 'erdtfvgbtdrfyh', 'Quezon City', 'Manila', '2025-10-01', 10, '6rt7u65t', 'uploads/valid_ids/1761043903_cat.jpg', 'resident1', '960235528', 'Barangay Debugon', '2025-10-21 18:51:43', 'Cancelled', 'Barangay Clearance');

-- --------------------------------------------------------

--
-- Table structure for table `barangay_events`
--

CREATE TABLE `barangay_events` (
  `id` bigint NOT NULL,
  `barangay_name` text COLLATE utf8mb4_general_ci NOT NULL,
  `title` text COLLATE utf8mb4_general_ci NOT NULL,
  `description` text COLLATE utf8mb4_general_ci,
  `category` varchar(100) COLLATE utf8mb4_general_ci DEFAULT 'General',
  `venue` text COLLATE utf8mb4_general_ci,
  `start_date` datetime NOT NULL,
  `end_date` datetime DEFAULT NULL,
  `visibility` varchar(50) COLLATE utf8mb4_general_ci DEFAULT 'public',
  `linked_announcement_id` bigint DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `business_permit_requests`
--

CREATE TABLE `business_permit_requests` (
  `id` bigint NOT NULL,
  `resident_id` bigint DEFAULT NULL,
  `business_name` text COLLATE utf8mb4_general_ci,
  `owner_name` text COLLATE utf8mb4_general_ci,
  `email` text COLLATE utf8mb4_general_ci,
  `phone` text COLLATE utf8mb4_general_ci,
  `business_type` text COLLATE utf8mb4_general_ci,
  `dti_cert_url` text COLLATE utf8mb4_general_ci,
  `lease_contract_url` text COLLATE utf8mb4_general_ci,
  `purpose` text COLLATE utf8mb4_general_ci,
  `valid_id_url` text COLLATE utf8mb4_general_ci,
  `barangay_name` text COLLATE utf8mb4_general_ci,
  `status` varchar(50) COLLATE utf8mb4_general_ci DEFAULT 'Pending',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `permit_type` varchar(100) COLLATE utf8mb4_general_ci DEFAULT 'Business Permit',
  `house_street` text COLLATE utf8mb4_general_ci,
  `city` text COLLATE utf8mb4_general_ci,
  `province` text COLLATE utf8mb4_general_ci
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `event_interest`
--

CREATE TABLE `event_interest` (
  `id` bigint NOT NULL,
  `event_id` bigint NOT NULL,
  `resident_id` bigint NOT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `goodmoral_requests`
--

CREATE TABLE `goodmoral_requests` (
  `id` bigint NOT NULL,
  `resident_id` bigint DEFAULT NULL,
  `fullname` text COLLATE utf8mb4_general_ci NOT NULL,
  `email` text COLLATE utf8mb4_general_ci,
  `phone` text COLLATE utf8mb4_general_ci,
  `date_of_birth` date DEFAULT NULL,
  `barangay_clearance_url` text COLLATE utf8mb4_general_ci,
  `purpose` text COLLATE utf8mb4_general_ci,
  `valid_id_url` text COLLATE utf8mb4_general_ci,
  `barangay_name` text COLLATE utf8mb4_general_ci,
  `status` varchar(50) COLLATE utf8mb4_general_ci DEFAULT 'Pending',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `permit_type` varchar(100) COLLATE utf8mb4_general_ci DEFAULT 'Certificate of Good Moral Character',
  `house_street` text COLLATE utf8mb4_general_ci,
  `city` text COLLATE utf8mb4_general_ci,
  `province` text COLLATE utf8mb4_general_ci
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `indigency_requests`
--

CREATE TABLE `indigency_requests` (
  `id` bigint NOT NULL,
  `resident_id` bigint DEFAULT NULL,
  `fullname` text COLLATE utf8mb4_general_ci NOT NULL,
  `email` text COLLATE utf8mb4_general_ci,
  `phone` text COLLATE utf8mb4_general_ci,
  `proof_of_income_url` text COLLATE utf8mb4_general_ci,
  `purpose` text COLLATE utf8mb4_general_ci,
  `valid_id_url` text COLLATE utf8mb4_general_ci,
  `barangay_name` text COLLATE utf8mb4_general_ci,
  `status` varchar(50) COLLATE utf8mb4_general_ci DEFAULT 'Pending',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `permit_type` varchar(100) COLLATE utf8mb4_general_ci DEFAULT 'Certificate of Indigency',
  `house_street` text COLLATE utf8mb4_general_ci,
  `city` text COLLATE utf8mb4_general_ci,
  `province` text COLLATE utf8mb4_general_ci
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `latebirth_requests`
--

CREATE TABLE `latebirth_requests` (
  `id` bigint NOT NULL,
  `resident_id` bigint DEFAULT NULL,
  `fullname` text COLLATE utf8mb4_general_ci NOT NULL,
  `email` text COLLATE utf8mb4_general_ci,
  `phone` text COLLATE utf8mb4_general_ci,
  `birth_record_url` text COLLATE utf8mb4_general_ci,
  `purpose` text COLLATE utf8mb4_general_ci,
  `valid_id_url` text COLLATE utf8mb4_general_ci,
  `barangay_name` text COLLATE utf8mb4_general_ci,
  `status` varchar(50) COLLATE utf8mb4_general_ci DEFAULT 'Pending',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `permit_type` varchar(100) COLLATE utf8mb4_general_ci DEFAULT 'Certificate of Late Birth Registration',
  `house_street` text COLLATE utf8mb4_general_ci,
  `city` text COLLATE utf8mb4_general_ci,
  `province` text COLLATE utf8mb4_general_ci
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `norecord_requests`
--

CREATE TABLE `norecord_requests` (
  `id` bigint NOT NULL,
  `resident_id` bigint DEFAULT NULL,
  `fullname` text COLLATE utf8mb4_general_ci NOT NULL,
  `email` text COLLATE utf8mb4_general_ci,
  `phone` text COLLATE utf8mb4_general_ci,
  `purpose` text COLLATE utf8mb4_general_ci,
  `valid_id_url` text COLLATE utf8mb4_general_ci,
  `barangay_name` text COLLATE utf8mb4_general_ci,
  `status` varchar(50) COLLATE utf8mb4_general_ci DEFAULT 'Pending',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `permit_type` varchar(100) COLLATE utf8mb4_general_ci DEFAULT 'Certificate of No Record',
  `house_street` text COLLATE utf8mb4_general_ci,
  `city` text COLLATE utf8mb4_general_ci,
  `province` text COLLATE utf8mb4_general_ci
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` bigint NOT NULL,
  `barangay_name` text COLLATE utf8mb4_general_ci NOT NULL,
  `recipient_type` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `recipient_id` bigint DEFAULT NULL,
  `source_table` text COLLATE utf8mb4_general_ci,
  `source_id` bigint DEFAULT NULL,
  `type` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `title` text COLLATE utf8mb4_general_ci NOT NULL,
  `message` text COLLATE utf8mb4_general_ci NOT NULL,
  `link` text COLLATE utf8mb4_general_ci,
  `is_read` tinyint(1) DEFAULT '0',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `ojt_requests`
--

CREATE TABLE `ojt_requests` (
  `id` bigint NOT NULL,
  `resident_id` bigint DEFAULT NULL,
  `fullname` text COLLATE utf8mb4_general_ci NOT NULL,
  `email` text COLLATE utf8mb4_general_ci,
  `phone` text COLLATE utf8mb4_general_ci,
  `school_name` text COLLATE utf8mb4_general_ci,
  `endorsement_letter_url` text COLLATE utf8mb4_general_ci,
  `purpose` text COLLATE utf8mb4_general_ci,
  `valid_id_url` text COLLATE utf8mb4_general_ci,
  `barangay_name` text COLLATE utf8mb4_general_ci,
  `status` varchar(50) COLLATE utf8mb4_general_ci DEFAULT 'Pending',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `permit_type` varchar(100) COLLATE utf8mb4_general_ci DEFAULT 'Certificate of OJT / Training Endorsement',
  `house_street` text COLLATE utf8mb4_general_ci,
  `city` text COLLATE utf8mb4_general_ci,
  `province` text COLLATE utf8mb4_general_ci
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `residency_requests`
--

CREATE TABLE `residency_requests` (
  `id` bigint NOT NULL,
  `resident_id` bigint DEFAULT NULL,
  `fullname` text COLLATE utf8mb4_general_ci NOT NULL,
  `email` text COLLATE utf8mb4_general_ci,
  `phone` text COLLATE utf8mb4_general_ci,
  `house_street` text COLLATE utf8mb4_general_ci,
  `city` text COLLATE utf8mb4_general_ci,
  `province` text COLLATE utf8mb4_general_ci,
  `date_of_residency` date DEFAULT NULL,
  `years_residency` int DEFAULT NULL,
  `purpose` text COLLATE utf8mb4_general_ci,
  `valid_id_url` text COLLATE utf8mb4_general_ci,
  `barangay_name` text COLLATE utf8mb4_general_ci,
  `status` varchar(50) COLLATE utf8mb4_general_ci DEFAULT 'Pending',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `permit_type` varchar(100) COLLATE utf8mb4_general_ci DEFAULT 'Residency Certificate'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `residents`
--

CREATE TABLE `residents` (
  `id` bigint NOT NULL,
  `last_name` text COLLATE utf8mb4_general_ci NOT NULL,
  `first_name` text COLLATE utf8mb4_general_ci NOT NULL,
  `middle_name` text COLLATE utf8mb4_general_ci,
  `suffix` text COLLATE utf8mb4_general_ci,
  `phone` varchar(20) COLLATE utf8mb4_general_ci NOT NULL,
  `email` text COLLATE utf8mb4_general_ci NOT NULL,
  `birthdate` date NOT NULL,
  `house_no` text COLLATE utf8mb4_general_ci NOT NULL,
  `street` text COLLATE utf8mb4_general_ci NOT NULL,
  `purok` text COLLATE utf8mb4_general_ci NOT NULL,
  `subdivision` text COLLATE utf8mb4_general_ci,
  `barangay` text COLLATE utf8mb4_general_ci NOT NULL,
  `city` text COLLATE utf8mb4_general_ci NOT NULL,
  `province` text COLLATE utf8mb4_general_ci NOT NULL,
  `region` text COLLATE utf8mb4_general_ci NOT NULL,
  `postal` varchar(10) COLLATE utf8mb4_general_ci NOT NULL,
  `nationality` text COLLATE utf8mb4_general_ci NOT NULL,
  `agree` tinyint(1) NOT NULL DEFAULT '0',
  `updates` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `verification_status` varchar(20) COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'Unverified',
  `password` varchar(255) COLLATE utf8mb4_general_ci NOT NULL
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
  `id` bigint NOT NULL,
  `resident_id` bigint NOT NULL,
  `id_type` text COLLATE utf8mb4_general_ci NOT NULL,
  `valid_id_url` text COLLATE utf8mb4_general_ci NOT NULL,
  `submitted_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `status` varchar(20) COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'Pending',
  `reviewed_by` text COLLATE utf8mb4_general_ci,
  `reviewed_at` datetime DEFAULT NULL,
  `remarks` text COLLATE utf8mb4_general_ci
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `soloparent_requests`
--

CREATE TABLE `soloparent_requests` (
  `id` bigint NOT NULL,
  `resident_id` bigint DEFAULT NULL,
  `fullname` text COLLATE utf8mb4_general_ci NOT NULL,
  `email` text COLLATE utf8mb4_general_ci,
  `phone` text COLLATE utf8mb4_general_ci,
  `proof_of_solo_status_url` text COLLATE utf8mb4_general_ci,
  `purpose` text COLLATE utf8mb4_general_ci,
  `valid_id_url` text COLLATE utf8mb4_general_ci,
  `barangay_name` text COLLATE utf8mb4_general_ci,
  `status` varchar(50) COLLATE utf8mb4_general_ci DEFAULT 'Pending',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `permit_type` varchar(100) COLLATE utf8mb4_general_ci DEFAULT 'Certificate of Solo Parent',
  `house_street` text COLLATE utf8mb4_general_ci,
  `city` text COLLATE utf8mb4_general_ci,
  `province` text COLLATE utf8mb4_general_ci
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `verification_rejects`
--

CREATE TABLE `verification_rejects` (
  `id` bigint NOT NULL,
  `resident_id` bigint NOT NULL,
  `id_type` text COLLATE utf8mb4_general_ci NOT NULL,
  `valid_id_url` text COLLATE utf8mb4_general_ci NOT NULL,
  `reason` text COLLATE utf8mb4_general_ci NOT NULL,
  `rejected_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `reviewed_by` text COLLATE utf8mb4_general_ci
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
  MODIFY `id` bigint NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `barangay_clearance_requests`
--
ALTER TABLE `barangay_clearance_requests`
  MODIFY `id` bigint NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `barangay_events`
--
ALTER TABLE `barangay_events`
  MODIFY `id` bigint NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `business_permit_requests`
--
ALTER TABLE `business_permit_requests`
  MODIFY `id` bigint NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `event_interest`
--
ALTER TABLE `event_interest`
  MODIFY `id` bigint NOT NULL AUTO_INCREMENT;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
