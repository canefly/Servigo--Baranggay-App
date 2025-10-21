-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 08, 2025 at 10:49 PM
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
-- Database: `sms`
--

-- --------------------------------------------------------

--
-- Table structure for table `academic_background`
--

CREATE TABLE `academic_background` (
  `id` int(11) NOT NULL,
  `student_id` varchar(50) NOT NULL,
  `primary_school` varchar(255) DEFAULT NULL,
  `primary_year` varchar(10) DEFAULT NULL,
  `secondary_school` varchar(255) DEFAULT NULL,
  `secondary_year` varchar(10) DEFAULT NULL,
  `tertiary_school` varchar(255) DEFAULT NULL,
  `tertiary_year` varchar(10) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `academic_background`
--

INSERT INTO `academic_background` (`id`, `student_id`, `primary_school`, `primary_year`, `secondary_school`, `secondary_year`, `tertiary_school`, `tertiary_year`) VALUES
(1, 'S2025-001', 'Bagong Silang Elementary School', '2016', 'Benigno Aquino High School', '2022', 'Bestlink College of the Philippines', '2025'),
(2, 'S2025-002', 'Novaliches Elementary School', '2016', 'Novaliches High School', '2022', 'Bestlink College of the Philippines', '2025'),
(3, 'S2025-003', 'Camarin Elementary School', '2015', 'Camarin High School', '2021', 'Bestlink College of the Philippines', '2025'),
(4, 'S2025-004', 'Bagumbong Elementary School', '2016', 'Bagumbong High School', '2022', 'Bestlink College of the Philippines', '2025'),
(5, 'S2025-005', 'Sauyo Elementary School', '2015', 'Sauyo High School', '2021', 'Bestlink College of the Philippines', '2025'),
(6, 'S2025-006', 'Fairview Elementary School', '2016', 'Fairview High School', '2022', 'Bestlink College of the Philippines', '2025'),
(7, 'S2025-007', 'Taguig Elementary School', '2015', 'Taguig Science HS', '2021', 'Bestlink College of the Philippines', '2025'),
(8, 'S2025-008', 'Pasig Elementary School', '2016', 'Pasig City HS', '2022', 'Bestlink College of the Philippines', '2025'),
(9, 'S2025-009', 'Makati Elementary School', '2015', 'Makati HS', '2021', 'Bestlink College of the Philippines', '2025'),
(10, 'S2025-010', 'Manila Elementary School', '2016', 'Manila Science HS', '2022', 'Bestlink College of the Philippines', '2025'),
(11, 'S2025-011', 'Mandaluyong Elementary', '2016', 'Mandaluyong HS', '2022', 'Bestlink College of the Philippines', '2025'),
(12, 'S2025-012', 'Pasay Elementary School', '2015', 'Pasay City HS', '2021', 'Bestlink College of the Philippines', '2025'),
(13, 'S2025-013', 'Caloocan Elementary School', '2015', 'Caloocan HS', '2021', 'Bestlink College of the Philippines', '2025'),
(14, 'S2025-014', 'Valenzuela Elementary', '2014', 'Valenzuela HS', '2020', 'Bestlink College of the Philippines', '2025'),
(15, 'S2025-015', 'Malabon Elementary', '2014', 'Malabon HS', '2020', 'Bestlink College of the Philippines', '2025'),
(16, 'S2025-016', 'Quezon Elementary', '2015', 'Quezon City HS', '2021', 'Bestlink College of the Philippines', '2025'),
(17, 'S2025-017', 'San Jose Elementary', '2015', 'San Jose HS', '2021', 'Bestlink College of the Philippines', '2025'),
(18, 'S2025-018', 'San Mateo Elementary', '2016', 'San Mateo HS', '2022', 'Bestlink College of the Philippines', '2025'),
(19, 'S2025-019', 'Marikina Elementary', '2015', 'Marikina HS', '2021', 'Bestlink College of the Philippines', '2025'),
(20, 'S2025-020', 'Montalban Elementary', '2016', 'Montalban HS', '2022', 'Bestlink College of the Philippines', '2025'),
(21, 'S2025-021', 'Bocaue Elementary', '2015', 'Bocaue HS', '2021', 'Bestlink College of the Philippines', '2025'),
(22, 'S2025-022', 'Sta. Maria Elementary', '2016', 'Sta. Maria HS', '2022', 'Bestlink College of the Philippines', '2025'),
(23, 'S2025-023', 'Obando Elementary', '2015', 'Obando HS', '2021', 'Bestlink College of the Philippines', '2025'),
(24, 'S2025-024', 'Hagonoy Elementary', '2014', 'Hagonoy HS', '2020', 'Bestlink College of the Philippines', '2025'),
(25, 'S2025-025', 'Malolos Elementary', '2015', 'Malolos HS', '2021', 'Bestlink College of the Philippines', '2025'),
(26, 'S2025-026', 'Baliuag Elementary', '2015', 'Baliuag HS', '2021', 'Bestlink College of the Philippines', '2025'),
(27, 'S2025-027', 'Paombong Elementary', '2014', 'Paombong HS', '2020', 'Bestlink College of the Philippines', '2025'),
(28, 'S2025-028', 'San Ildefonso Elementary', '2016', 'San Ildefonso HS', '2022', 'Bestlink College of the Philippines', '2025'),
(29, 'S2025-029', 'San Miguel Elementary', '2015', 'San Miguel HS', '2021', 'Bestlink College of the Philippines', '2025'),
(30, 'S2025-030', 'Plaridel Elementary', '2014', 'Plaridel HS', '2020', 'Bestlink College of the Philippines', '2025');

-- --------------------------------------------------------

--
-- Table structure for table `academic_records`
--

CREATE TABLE `academic_records` (
  `record_id` int(11) NOT NULL,
  `student_id` varchar(255) DEFAULT NULL,
  `subject_id` int(11) DEFAULT NULL,
  `grade` varchar(255) DEFAULT NULL,
  `term` varchar(255) DEFAULT NULL,
  `school_year` varchar(255) DEFAULT NULL,
  `remarks` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `academic_records`
--

INSERT INTO `academic_records` (`record_id`, `student_id`, `subject_id`, `grade`, `term`, `school_year`, `remarks`) VALUES
(1, 'S2025-001', 1, '1.50', '1st Sem', '2025-2026', 'Passed'),
(2, 'S2025-001', 2, '1.75', '1st Sem', '2025-2026', 'Passed'),
(3, 'S2025-002', 1, '2.00', '1st Sem', '2025-2026', 'Passed'),
(4, 'S2025-003', 3, '1.25', '1st Sem', '2025-2026', 'Excellent');

-- --------------------------------------------------------

--
-- Table structure for table `archived_students`
--

CREATE TABLE `archived_students` (
  `archive_id` int(11) NOT NULL,
  `student_id` varchar(20) NOT NULL,
  `first_name` varchar(100) NOT NULL,
  `last_name` varchar(100) NOT NULL,
  `program` varchar(100) NOT NULL,
  `year_level` int(11) DEFAULT 0,
  `section` varchar(50) DEFAULT NULL,
  `student_status` enum('Enrolled','Dropped','Graduated') DEFAULT 'Enrolled',
  `contact_no` varchar(50) DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  `birthdate` date DEFAULT NULL,
  `photo_path` varchar(255) DEFAULT NULL,
  `archived_date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `document_requests`
--

CREATE TABLE `document_requests` (
  `request_id` int(11) NOT NULL,
  `student_id` varchar(255) DEFAULT NULL,
  `document_type` varchar(255) DEFAULT NULL,
  `notes` text NOT NULL,
  `request_date` date DEFAULT NULL,
  `status` varchar(255) DEFAULT NULL,
  `release_date` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `document_requests`
--

INSERT INTO `document_requests` (`request_id`, `student_id`, `document_type`, `notes`, `request_date`, `status`, `release_date`) VALUES
(1, 'S2025-008', 'Enrollment Certificate', 'Need for scholarship', '2025-10-08', 'Pending', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `file_storage`
--

CREATE TABLE `file_storage` (
  `file_id` int(11) NOT NULL,
  `student_id` varchar(255) DEFAULT NULL,
  `file_type` varchar(255) DEFAULT NULL,
  `file_path` text DEFAULT NULL,
  `uploaded_by` int(11) DEFAULT NULL,
  `upload_date` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `file_storage`
--

INSERT INTO `file_storage` (`file_id`, `student_id`, `file_type`, `file_path`, `uploaded_by`, `upload_date`) VALUES
(1, 'S2025-001', 'Enrollment Form', '/uploads/docs/S2025-001_enrollment.pdf', 2, '2025-10-08 20:30:21');

-- --------------------------------------------------------

--
-- Table structure for table `guardians`
--

CREATE TABLE `guardians` (
  `guardian_id` int(11) NOT NULL,
  `student_id` varchar(255) DEFAULT NULL,
  `name` varchar(255) DEFAULT NULL,
  `relation` varchar(255) DEFAULT NULL,
  `contact_no` varchar(255) DEFAULT NULL,
  `address` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `guardians`
--

INSERT INTO `guardians` (`guardian_id`, `student_id`, `name`, `relation`, `contact_no`, `address`) VALUES
(1, 'S2025-001', 'Marites Manalo', 'Mother', '09180010001', 'Quezon City'),
(2, 'S2025-002', 'Rogelio Domingo', 'Father', '09180010002', 'Caloocan City'),
(3, 'S2025-003', 'Ana Lopez', 'Mother', '09180010003', 'Valenzuela City'),
(4, 'S2025-004', 'Joseph Ferrer', 'Father', '09180010004', 'Quezon City'),
(5, 'S2025-005', 'Lourdes Villanueva', 'Mother', '09180010005', 'Malabon City'),
(6, 'S2025-006', 'Pedro Salvador', 'Father', '09180010006', 'Navotas City'),
(7, 'S2025-007', 'Maricel Aquino', 'Mother', '09180010007', 'Taguig City'),
(8, 'S2025-008', 'Julius Reyes', 'Father', '09180010008', 'Pasig City'),
(9, 'S2025-009', 'Diana Chua', 'Mother', '09180010009', 'Makati City'),
(10, 'S2025-010', 'Mario Del Rosario', 'Father', '09180010010', 'Manila'),
(11, 'S2025-011', 'Alicia Alcantara', 'Mother', '09180010011', 'Mandaluyong'),
(12, 'S2025-012', 'Ramon Santiago', 'Father', '09180010012', 'Pasay City'),
(13, 'S2025-013', 'Evelyn Dizon', 'Mother', '09180010013', 'Quezon City'),
(14, 'S2025-014', 'Nestor Vergara', 'Father', '09180010014', 'Quezon City'),
(15, 'S2025-015', 'Rhea Ocampo', 'Mother', '09180010015', 'Valenzuela City'),
(16, 'S2025-016', 'Arnold Ramos', 'Father', '09180010016', 'Caloocan City'),
(17, 'S2025-017', 'Melissa Cruz', 'Mother', '09180010017', 'Quezon City'),
(18, 'S2025-018', 'Oscar Valdez', 'Father', '09180010018', 'Manila'),
(19, 'S2025-019', 'Rowena Torres', 'Mother', '09180010019', 'Quezon City'),
(20, 'S2025-020', 'Victor Gutierrez', 'Father', '09180010020', 'Quezon City'),
(21, 'S2025-021', 'Carina Reyes', 'Mother', '09180010021', 'Malabon City'),
(22, 'S2025-022', 'Jose Gomez', 'Father', '09180010022', 'Caloocan City'),
(23, 'S2025-023', 'Myra Morales', 'Mother', '09180010023', 'Quezon City'),
(24, 'S2025-024', 'Rodolfo Alvarez', 'Father', '09180010024', 'Pasig City'),
(25, 'S2025-025', 'Liza Santos', 'Mother', '09180010025', 'Taguig City'),
(26, 'S2025-026', 'Hector Estrada', 'Father', '09180010026', 'Makati City'),
(27, 'S2025-027', 'Glenda Villarin', 'Mother', '09180010027', 'Manila'),
(28, 'S2025-028', 'Dennis Soriano', 'Father', '09180010028', 'Quezon City'),
(29, 'S2025-029', 'Helen Castro', 'Mother', '09180010029', 'Valenzuela City'),
(30, 'S2025-030', 'Ricardo Pineda', 'Father', '09180010030', 'Caloocan City');

-- --------------------------------------------------------

--
-- Table structure for table `health_records`
--

CREATE TABLE `health_records` (
  `health_id` int(11) NOT NULL,
  `student_id` varchar(255) DEFAULT NULL,
  `checkup_date` date DEFAULT NULL,
  `health_status` varchar(255) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `referred_to_sps` tinyint(1) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `health_records`
--

INSERT INTO `health_records` (`health_id`, `student_id`, `checkup_date`, `health_status`, `notes`, `referred_to_sps`) VALUES
(1, 'S2025-001', '2025-08-15', 'Healthy', 'Routine medical checkup. Cleared.', 0);

-- --------------------------------------------------------

--
-- Table structure for table `masterlists`
--

CREATE TABLE `masterlists` (
  `masterlist_id` int(11) NOT NULL,
  `term` varchar(255) DEFAULT NULL,
  `year` varchar(255) DEFAULT NULL,
  `program` varchar(255) DEFAULT NULL,
  `section` varchar(50) DEFAULT NULL,
  `year_level` int(11) DEFAULT NULL,
  `generated_by` int(11) DEFAULT NULL,
  `generation_date` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `masterlists`
--

INSERT INTO `masterlists` (`masterlist_id`, `term`, `year`, `program`, `section`, `year_level`, `generated_by`, `generation_date`) VALUES
(1, '1st Sem', '2025-2026', 'BSIT', '11001', 1, 2, '2025-10-08 20:30:21');

-- --------------------------------------------------------

--
-- Table structure for table `masterlist_details`
--

CREATE TABLE `masterlist_details` (
  `id` int(11) NOT NULL,
  `masterlist_id` int(11) DEFAULT NULL,
  `student_id` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `masterlist_details`
--

INSERT INTO `masterlist_details` (`id`, `masterlist_id`, `student_id`) VALUES
(1, 1, 'S2025-001'),
(2, 1, 'S2025-002'),
(3, 1, 'S2025-003');

-- --------------------------------------------------------

--
-- Table structure for table `roles`
--

CREATE TABLE `roles` (
  `role_id` int(11) NOT NULL,
  `name` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `roles`
--

INSERT INTO `roles` (`role_id`, `name`, `description`) VALUES
(1, 'Admin', 'Has full system access: manage users, view audit logs, system monitoring'),
(2, 'Employee', 'Registrar staff role: manage students, process requests, generate masterlists'),
(3, 'Student', 'Student role: view personal info, request documents, track status');

-- --------------------------------------------------------

--
-- Table structure for table `students`
--

CREATE TABLE `students` (
  `student_id` varchar(255) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `first_name` varchar(255) DEFAULT NULL,
  `last_name` varchar(255) DEFAULT NULL,
  `birthdate` date DEFAULT NULL,
  `gender` varchar(255) DEFAULT NULL,
  `program` varchar(50) NOT NULL,
  `year_level` int(11) DEFAULT NULL,
  `section` int(10) NOT NULL,
  `student_status` varchar(255) DEFAULT NULL,
  `photo_path` text DEFAULT NULL,
  `date_registered` date DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `contact_no` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `students`
--

INSERT INTO `students` (`student_id`, `user_id`, `first_name`, `last_name`, `birthdate`, `gender`, `program`, `year_level`, `section`, `student_status`, `photo_path`, `date_registered`, `email`, `contact_no`) VALUES
('S2025-001', 3, 'Ralph', 'Manalo', '2006-01-15', 'Male', 'BSIT', 1, 11001, 'Enrolled', '../components.img/ids/eh.jpg', '2025-06-01', 'ralph.manalo@bcp.edu.ph', '09170000001'),
('S2025-002', 4, 'Ella', 'Domingo', '2006-02-18', 'Female', 'BSIT', 1, 11002, 'Enrolled', NULL, '2025-06-01', 'ella.domingo@bcp.edu.ph', '09170000002'),
('S2025-003', 5, 'Gino', 'Lopez', '2005-12-02', 'Male', 'BSIT', 1, 11003, 'Enrolled', NULL, '2025-06-01', 'gino.lopez@bcp.edu.ph', '09170000003'),
('S2025-004', 6, 'Clarisse', 'Ferrer', '2006-05-20', 'Female', 'BSIT', 1, 11004, 'Enrolled', NULL, '2025-06-01', 'clarisse.ferrer@bcp.edu.ph', '09170000004'),
('S2025-005', 7, 'Jerome', 'Villanueva', '2005-10-08', 'Male', 'BSIT', 1, 11005, 'Enrolled', NULL, '2025-06-01', 'jerome.villanueva@bcp.edu.ph', '09170000005'),
('S2025-006', 8, 'April', 'Salvador', '2006-03-11', 'Female', 'BSIT', 1, 11006, 'Enrolled', NULL, '2025-06-01', 'april.salvador@bcp.edu.ph', '09170000006'),
('S2025-007', 9, 'Sean', 'Aquino', '2006-07-03', 'Male', 'BSCS', 1, 12001, 'Enrolled', NULL, '2025-06-01', 'sean.aquino@bcp.edu.ph', '09170000007'),
('S2025-008', 10, 'Jasmine', 'Reyes', '2006-09-30', 'Female', 'BSCS', 1, 12002, 'Enrolled', NULL, '2025-06-01', 'jasmine.reyes@bcp.edu.ph', '09170000008'),
('S2025-009', 11, 'Lance', 'Chua', '2005-11-22', 'Male', 'BSCS', 1, 12003, 'Enrolled', NULL, '2025-06-01', 'lance.chua@bcp.edu.ph', '09170000009'),
('S2025-010', 12, 'Hannah', 'Del Rosario', '2006-06-06', 'Female', 'BSCS', 1, 12004, 'Enrolled', NULL, '2025-06-01', 'hannah.delrosario@bcp.edu.ph', '09170000010'),
('S2025-011', 13, 'Kyle', 'Alcantara', '2006-04-17', 'Male', 'BSCS', 1, 12005, 'Enrolled', NULL, '2025-06-01', 'kyle.alcantara@bcp.edu.ph', '09170000011'),
('S2025-012', 14, 'Faith', 'Santiago', '2006-01-27', 'Female', 'BSCS', 1, 12006, 'Enrolled', NULL, '2025-06-01', 'faith.santiago@bcp.edu.ph', '09170000012'),
('S2025-013', 15, 'Patrick', 'Dizon', '2005-09-14', 'Male', 'BSECE', 1, 13001, 'Enrolled', NULL, '2025-06-01', 'patrick.dizon@bcp.edu.ph', '09170000013'),
('S2025-014', 16, 'Shiela', 'Vergara', '2006-08-01', 'Female', 'BSECE', 1, 13002, 'Enrolled', NULL, '2025-06-01', 'shiela.vergara@bcp.edu.ph', '09170000014'),
('S2025-015', 17, 'Marcus', 'Ocampo', '2005-05-09', 'Male', 'BSECE', 1, 13003, 'Enrolled', NULL, '2025-06-01', 'marcus.ocampo@bcp.edu.ph', '09170000015'),
('S2025-016', 18, 'Joanna', 'Ramos', '2006-03-30', 'Female', 'BSECE', 1, 13004, 'Enrolled', NULL, '2025-06-01', 'joanna.ramos@bcp.edu.ph', '09170000016'),
('S2025-017', 19, 'Andre', 'Cruz', '2006-10-25', 'Male', 'BSECE', 1, 13005, 'Enrolled', NULL, '2025-06-01', 'andre.cruz@bcp.edu.ph', '09170000017'),
('S2025-018', 20, 'Sofia', 'Valdez', '2006-12-12', 'Female', 'BSECE', 1, 13006, 'Enrolled', NULL, '2025-06-01', 'sofia.valdez@bcp.edu.ph', '09170000018'),
('S2025-019', 21, 'Jacob', 'Torres', '2006-07-21', 'Male', 'BSHM', 1, 14001, 'Enrolled', NULL, '2025-06-01', 'jacob.torres@bcp.edu.ph', '09170000019'),
('S2025-020', 22, 'Bea', 'Gutierrez', '2006-04-14', 'Female', 'BSHM', 1, 14002, 'Enrolled', NULL, '2025-06-01', 'bea.gutierrez@bcp.edu.ph', '09170000020'),
('S2025-021', 23, 'Nathan', 'Reyes', '2005-12-18', 'Male', 'BSHM', 1, 14003, 'Enrolled', NULL, '2025-06-01', 'nathan.reyes@bcp.edu.ph', '09170000021'),
('S2025-022', 24, 'Lara', 'Gomez', '2006-02-26', 'Female', 'BSHM', 1, 14004, 'Enrolled', NULL, '2025-06-01', 'lara.gomez@bcp.edu.ph', '09170000022'),
('S2025-023', 25, 'Cedric', 'Morales', '2005-11-03', 'Male', 'BSHM', 1, 14005, 'Enrolled', NULL, '2025-06-01', 'cedric.morales@bcp.edu.ph', '09170000023'),
('S2025-024', 26, 'Diana', 'Alvarez', '2006-01-08', 'Female', 'BSHM', 1, 14006, 'Enrolled', NULL, '2025-06-01', 'diana.alvarez@bcp.edu.ph', '09170000024'),
('S2025-025', 27, 'Leo', 'Santos', '2005-10-29', 'Male', 'BSHRM', 1, 15001, 'Enrolled', NULL, '2025-06-01', 'leo.santos@bcp.edu.ph', '09170000025'),
('S2025-026', 28, 'Camille', 'Estrada', '2006-05-27', 'Female', 'BSHRM', 1, 15002, 'Enrolled', NULL, '2025-06-01', 'camille.estrada@bcp.edu.ph', '09170000026'),
('S2025-027', 29, 'Aaron', 'Villarin', '2006-06-04', 'Male', 'BSHRM', 1, 15003, 'Enrolled', NULL, '2025-06-01', 'aaron.villarin@bcp.edu.ph', '09170000027'),
('S2025-028', 30, 'Trisha', 'Soriano', '2006-09-08', 'Female', 'BSHRM', 1, 15004, 'Enrolled', NULL, '2025-06-01', 'trisha.soriano@bcp.edu.ph', '09170000028'),
('S2025-029', 31, 'Dennis', 'Castro', '2005-08-06', 'Male', 'BSHRM', 1, 15005, 'Enrolled', NULL, '2025-06-01', 'dennis.castro@bcp.edu.ph', '09170000029'),
('S2025-030', 32, 'Nicole', 'Pineda', '2006-03-02', 'Female', 'BSHRM', 1, 15006, 'Enrolled', NULL, '2025-06-01', 'nicole.pineda@bcp.edu.ph', '09170000030');

-- --------------------------------------------------------

--
-- Table structure for table `student_ids`
--

CREATE TABLE `student_ids` (
  `id_id` int(11) NOT NULL,
  `student_id` varchar(255) DEFAULT NULL,
  `qr_code` text DEFAULT NULL,
  `issue_date` date DEFAULT NULL,
  `expiry_date` date DEFAULT NULL,
  `printed` tinyint(1) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `student_ids`
--

INSERT INTO `student_ids` (`id_id`, `student_id`, `qr_code`, `issue_date`, `expiry_date`, `printed`) VALUES
(1, 'S2025-001', '../components/img/QR/S2025-001.png', '2025-06-05', '2026-06-05', 1),
(2, 'S2025-002', '../components/img/QR/S2025-002.png', '2025-06-05', '2026-06-05', 1),
(3, 'S2025-003', '../components/img/QR/S2025-003.png', '2025-06-05', '2026-06-05', 1),
(4, 'S2025-004', '../components/img/QR/S2025-004.png', '2025-06-05', '2026-06-05', 1),
(5, 'S2025-005', '../components/img/QR/S2025-005.png', '2025-06-05', '2026-06-05', 1),
(6, 'S2025-006', '../components/img/QR/S2025-006.png', '2025-06-05', '2026-06-05', 1),
(7, 'S2025-007', '../components/img/QR/S2025-007.png', '2025-06-05', '2026-06-05', 1),
(8, 'S2025-008', '../components/img/QR/S2025-008.png', '2025-06-05', '2026-06-05', 1),
(9, 'S2025-009', '../components/img/QR/S2025-009.png', '2025-06-05', '2026-06-05', 1),
(10, 'S2025-010', '../components/img/QR/S2025-010.png', '2025-06-05', '2026-06-05', 1),
(11, 'S2025-011', '../components/img/QR/S2025-011.png', '2025-06-05', '2026-06-05', 1),
(12, 'S2025-012', '../components/img/QR/S2025-012.png', '2025-06-05', '2026-06-05', 1),
(13, 'S2025-013', '../components/img/QR/S2025-013.png', '2025-06-05', '2026-06-05', 1),
(14, 'S2025-014', '../components/img/QR/S2025-014.png', '2025-06-05', '2026-06-05', 1),
(15, 'S2025-015', '../components/img/QR/S2025-015.png', '2025-06-05', '2026-06-05', 1),
(16, 'S2025-016', '../components/img/QR/S2025-016.png', '2025-06-05', '2026-06-05', 1),
(17, 'S2025-017', '../components/img/QR/S2025-017.png', '2025-06-05', '2026-06-05', 1),
(18, 'S2025-018', '../components/img/QR/S2025-018.png', '2025-06-05', '2026-06-05', 1),
(19, 'S2025-019', '../components/img/QR/S2025-019.png', '2025-06-05', '2026-06-05', 1),
(20, 'S2025-020', '../components/img/QR/S2025-020.png', '2025-06-05', '2026-06-05', 1),
(21, 'S2025-021', '../components/img/QR/S2025-021.png', '2025-06-05', '2026-06-05', 1),
(22, 'S2025-022', '../components/img/QR/S2025-022.png', '2025-06-05', '2026-06-05', 1),
(23, 'S2025-023', '../components/img/QR/S2025-023.png', '2025-06-05', '2026-06-05', 1),
(24, 'S2025-024', '../components/img/QR/S2025-024.png', '2025-06-05', '2026-06-05', 1),
(25, 'S2025-025', '../components/img/QR/S2025-025.png', '2025-06-05', '2026-06-05', 1),
(26, 'S2025-026', '../components/img/QR/S2025-026.png', '2025-06-05', '2026-06-05', 1),
(27, 'S2025-027', '../components/img/QR/S2025-027.png', '2025-06-05', '2026-06-05', 1),
(28, 'S2025-028', '../components/img/QR/S2025-028.png', '2025-06-05', '2026-06-05', 1),
(29, 'S2025-029', '../components/img/QR/S2025-029.png', '2025-06-05', '2026-06-05', 1),
(30, 'S2025-030', '../components/img/QR/S2025-030.png', '2025-06-05', '2026-06-05', 1);

-- --------------------------------------------------------

--
-- Table structure for table `student_status_history`
--

CREATE TABLE `student_status_history` (
  `status_id` int(11) NOT NULL,
  `student_id` varchar(255) DEFAULT NULL,
  `status_type` varchar(255) DEFAULT NULL,
  `changed_by` int(11) DEFAULT NULL,
  `timestamp` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `student_status_history`
--

INSERT INTO `student_status_history` (`status_id`, `student_id`, `status_type`, `changed_by`, `timestamp`) VALUES
(1, 'S2025-001', 'Enrolled', 2, '2025-10-08 20:30:21'),
(2, 'S2025-002', 'Enrolled', 2, '2025-10-08 20:30:21');

-- --------------------------------------------------------

--
-- Table structure for table `subjects`
--

CREATE TABLE `subjects` (
  `subject_id` int(11) NOT NULL,
  `subject_name` varchar(255) DEFAULT NULL,
  `units` int(11) DEFAULT NULL,
  `type` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `subjects`
--

INSERT INTO `subjects` (`subject_id`, `subject_name`, `units`, `type`) VALUES
(1, 'Programming Fundamentals', 3, 'Major'),
(2, 'Database Systems', 3, 'Major'),
(3, 'Discrete Mathematics', 3, 'Major'),
(4, 'Physical Education 1', 2, 'Minor');

-- --------------------------------------------------------

--
-- Table structure for table `system_logs`
--

CREATE TABLE `system_logs` (
  `syslog_id` int(11) NOT NULL,
  `level` varchar(255) DEFAULT NULL,
  `message` text DEFAULT NULL,
  `origin` varchar(255) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `timestamp` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `system_logs`
--

INSERT INTO `system_logs` (`syslog_id`, `level`, `message`, `origin`, `user_id`, `timestamp`) VALUES
(1, 'INFO', 'Seed import completed', 'system/seeder', 1, '2025-10-08 20:30:21'),
(2, 'INFO', 'Updated student photo for S2025-011', 'staff/StudentInfo.php', 1, '2025-10-08 20:40:09'),
(3, 'INFO', 'Updated student photo for S2025-024', 'staff/StudentInfo.php', 1, '2025-10-08 20:41:41'),
(4, 'INFO', 'Updated student photo for S2025-024', 'staff/StudentInfo.php', 1, '2025-10-08 20:42:36'),
(5, 'INFO', 'Updated student photo for S2025-024', 'staff/StudentInfo.php', 1, '2025-10-08 20:44:18'),
(6, 'INFO', 'Updated student photo for S2025-011', 'staff/StudentInfo.php', 1, '2025-10-08 20:44:57');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `username` varchar(255) DEFAULT NULL,
  `email` varchar(255) NOT NULL,
  `password_hash` text DEFAULT NULL,
  `role_id` int(11) DEFAULT NULL,
  `active` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `username`, `email`, `password_hash`, `role_id`, `active`) VALUES
(1, 'admin1', 'admin@example.com', '123456', 1, 1),
(2, 'employee1', 'employee@example.com', '123456', 2, 1),
(3, 'ralph.manalo', 'ralph.manalo@bcp.edu.ph', '123456', 3, 1),
(4, 'ella.domingo', 'ella.domingo@bcp.edu.ph', '123456', 3, 1),
(5, 'gino.lopez', 'gino.lopez@bcp.edu.ph', '123456', 3, 1),
(6, 'clarisse.ferrer', 'clarisse.ferrer@bcp.edu.ph', '123456', 3, 1),
(7, 'jerome.villanueva', 'jerome.villanueva@bcp.edu.ph', '123456', 3, 1),
(8, 'april.salvador', 'april.salvador@bcp.edu.ph', '123456', 3, 1),
(9, 'sean.aquino', 'sean.aquino@bcp.edu.ph', '123456', 3, 1),
(10, 'jasmine.reyes', 'jasmine.reyes@bcp.edu.ph', '123456', 3, 1),
(11, 'lance.chua', 'lance.chua@bcp.edu.ph', '123456', 3, 1),
(12, 'hannah.delrosario', 'hannah.delrosario@bcp.edu.ph', '123456', 3, 1),
(13, 'kyle.alcantara', 'kyle.alcantara@bcp.edu.ph', '123456', 3, 1),
(14, 'faith.santiago', 'faith.santiago@bcp.edu.ph', '123456', 3, 1),
(15, 'patrick.dizon', 'patrick.dizon@bcp.edu.ph', '123456', 3, 1),
(16, 'shiela.vergara', 'shiela.vergara@bcp.edu.ph', '123456', 3, 1),
(17, 'marcus.ocampo', 'marcus.ocampo@bcp.edu.ph', '123456', 3, 1),
(18, 'joanna.ramos', 'joanna.ramos@bcp.edu.ph', '123456', 3, 1),
(19, 'andre.cruz', 'andre.cruz@bcp.edu.ph', '123456', 3, 1),
(20, 'sofia.valdez', 'sofia.valdez@bcp.edu.ph', '123456', 3, 1),
(21, 'jacob.torres', 'jacob.torres@bcp.edu.ph', '123456', 3, 1),
(22, 'bea.gutierrez', 'bea.gutierrez@bcp.edu.ph', '123456', 3, 1),
(23, 'nathan.reyes', 'nathan.reyes@bcp.edu.ph', '123456', 3, 1),
(24, 'lara.gomez', 'lara.gomez@bcp.edu.ph', '123456', 3, 1),
(25, 'cedric.morales', 'cedric.morales@bcp.edu.ph', '123456', 3, 1),
(26, 'diana.alvarez', 'diana.alvarez@bcp.edu.ph', '123456', 3, 1),
(27, 'leo.santos', 'leo.santos@bcp.edu.ph', '123456', 3, 1),
(28, 'camille.estrada', 'camille.estrada@bcp.edu.ph', '123456', 3, 1),
(29, 'aaron.villarin', 'aaron.villarin@bcp.edu.ph', '123456', 3, 1),
(30, 'trisha.soriano', 'trisha.soriano@bcp.edu.ph', '123456', 3, 1),
(31, 'dennis.castro', 'dennis.castro@bcp.edu.ph', '123456', 3, 1),
(32, 'nicole.pineda', 'nicole.pineda@bcp.edu.ph', '123456', 3, 1);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `academic_background`
--
ALTER TABLE `academic_background`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_student` (`student_id`);

--
-- Indexes for table `academic_records`
--
ALTER TABLE `academic_records`
  ADD PRIMARY KEY (`record_id`),
  ADD KEY `student_id` (`student_id`),
  ADD KEY `subject_id` (`subject_id`);

--
-- Indexes for table `archived_students`
--
ALTER TABLE `archived_students`
  ADD PRIMARY KEY (`archive_id`),
  ADD KEY `student_id` (`student_id`);

--
-- Indexes for table `document_requests`
--
ALTER TABLE `document_requests`
  ADD PRIMARY KEY (`request_id`),
  ADD KEY `student_id` (`student_id`);

--
-- Indexes for table `file_storage`
--
ALTER TABLE `file_storage`
  ADD PRIMARY KEY (`file_id`),
  ADD KEY `student_id` (`student_id`),
  ADD KEY `uploaded_by` (`uploaded_by`);

--
-- Indexes for table `guardians`
--
ALTER TABLE `guardians`
  ADD PRIMARY KEY (`guardian_id`),
  ADD KEY `student_id` (`student_id`);

--
-- Indexes for table `health_records`
--
ALTER TABLE `health_records`
  ADD PRIMARY KEY (`health_id`),
  ADD KEY `student_id` (`student_id`);

--
-- Indexes for table `masterlists`
--
ALTER TABLE `masterlists`
  ADD PRIMARY KEY (`masterlist_id`),
  ADD KEY `generated_by` (`generated_by`);

--
-- Indexes for table `masterlist_details`
--
ALTER TABLE `masterlist_details`
  ADD PRIMARY KEY (`id`),
  ADD KEY `masterlist_id` (`masterlist_id`),
  ADD KEY `student_id` (`student_id`);

--
-- Indexes for table `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`role_id`);

--
-- Indexes for table `students`
--
ALTER TABLE `students`
  ADD PRIMARY KEY (`student_id`),
  ADD KEY `fk_students_users` (`user_id`),
  ADD KEY `idx_students_program_name` (`program`,`last_name`,`first_name`),
  ADD KEY `idx_students_status` (`student_status`);

--
-- Indexes for table `student_ids`
--
ALTER TABLE `student_ids`
  ADD PRIMARY KEY (`id_id`),
  ADD UNIQUE KEY `student_id` (`student_id`);

--
-- Indexes for table `student_status_history`
--
ALTER TABLE `student_status_history`
  ADD PRIMARY KEY (`status_id`),
  ADD KEY `student_id` (`student_id`),
  ADD KEY `changed_by` (`changed_by`);

--
-- Indexes for table `subjects`
--
ALTER TABLE `subjects`
  ADD PRIMARY KEY (`subject_id`);

--
-- Indexes for table `system_logs`
--
ALTER TABLE `system_logs`
  ADD PRIMARY KEY (`syslog_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `role_id` (`role_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `academic_background`
--
ALTER TABLE `academic_background`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

--
-- AUTO_INCREMENT for table `academic_records`
--
ALTER TABLE `academic_records`
  MODIFY `record_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `archived_students`
--
ALTER TABLE `archived_students`
  MODIFY `archive_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `document_requests`
--
ALTER TABLE `document_requests`
  MODIFY `request_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `file_storage`
--
ALTER TABLE `file_storage`
  MODIFY `file_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `guardians`
--
ALTER TABLE `guardians`
  MODIFY `guardian_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

--
-- AUTO_INCREMENT for table `health_records`
--
ALTER TABLE `health_records`
  MODIFY `health_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `masterlists`
--
ALTER TABLE `masterlists`
  MODIFY `masterlist_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `masterlist_details`
--
ALTER TABLE `masterlist_details`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `roles`
--
ALTER TABLE `roles`
  MODIFY `role_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `student_ids`
--
ALTER TABLE `student_ids`
  MODIFY `id_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

--
-- AUTO_INCREMENT for table `student_status_history`
--
ALTER TABLE `student_status_history`
  MODIFY `status_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `subjects`
--
ALTER TABLE `subjects`
  MODIFY `subject_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `system_logs`
--
ALTER TABLE `system_logs`
  MODIFY `syslog_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=33;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `academic_background`
--
ALTER TABLE `academic_background`
  ADD CONSTRAINT `academic_background_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `students` (`student_id`) ON DELETE CASCADE;

--
-- Constraints for table `academic_records`
--
ALTER TABLE `academic_records`
  ADD CONSTRAINT `academic_records_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `students` (`student_id`),
  ADD CONSTRAINT `academic_records_ibfk_2` FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`subject_id`);

--
-- Constraints for table `document_requests`
--
ALTER TABLE `document_requests`
  ADD CONSTRAINT `document_requests_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `students` (`student_id`);

--
-- Constraints for table `file_storage`
--
ALTER TABLE `file_storage`
  ADD CONSTRAINT `file_storage_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `students` (`student_id`),
  ADD CONSTRAINT `file_storage_ibfk_2` FOREIGN KEY (`uploaded_by`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `guardians`
--
ALTER TABLE `guardians`
  ADD CONSTRAINT `guardians_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `students` (`student_id`);

--
-- Constraints for table `health_records`
--
ALTER TABLE `health_records`
  ADD CONSTRAINT `health_records_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `students` (`student_id`);

--
-- Constraints for table `masterlists`
--
ALTER TABLE `masterlists`
  ADD CONSTRAINT `masterlists_ibfk_1` FOREIGN KEY (`generated_by`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `masterlist_details`
--
ALTER TABLE `masterlist_details`
  ADD CONSTRAINT `masterlist_details_ibfk_1` FOREIGN KEY (`masterlist_id`) REFERENCES `masterlists` (`masterlist_id`),
  ADD CONSTRAINT `masterlist_details_ibfk_2` FOREIGN KEY (`student_id`) REFERENCES `students` (`student_id`);

--
-- Constraints for table `students`
--
ALTER TABLE `students`
  ADD CONSTRAINT `fk_students_users` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `student_ids`
--
ALTER TABLE `student_ids`
  ADD CONSTRAINT `student_ids_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `students` (`student_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `student_status_history`
--
ALTER TABLE `student_status_history`
  ADD CONSTRAINT `student_status_history_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `students` (`student_id`),
  ADD CONSTRAINT `student_status_history_ibfk_2` FOREIGN KEY (`changed_by`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `system_logs`
--
ALTER TABLE `system_logs`
  ADD CONSTRAINT `system_logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `users_ibfk_1` FOREIGN KEY (`role_id`) REFERENCES `roles` (`role_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
