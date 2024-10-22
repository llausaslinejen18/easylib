-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 19, 2024 at 04:15 PM
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
-- Database: `easylib`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin`
--

CREATE TABLE `admin` (
  `aid` int(9) NOT NULL,
  `name` varchar(255) NOT NULL,
  `contact` varchar(255) NOT NULL,
  `username` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admin`
--

INSERT INTO `admin` (`aid`, `name`, `contact`, `username`, `password`) VALUES
(1, 'Scott Paurom', '12365487', 'admin', 'admin'),
(2, 'Line Llausas', '03694586213', 'admin1', 'admin1'),
(3, 'Arah Fernandez', '03269875896', 'admin2', 'admin2');

-- --------------------------------------------------------

--
-- Table structure for table `assets`
--

CREATE TABLE `assets` (
  `as_id` int(9) NOT NULL,
  `as_name` varchar(99) NOT NULL,
  `mod_num` varchar(99) NOT NULL,
  `ser_num` varchar(99) NOT NULL,
  `p_cost` varchar(99) NOT NULL,
  `p_date` varchar(99) NOT NULL,
  `add_info` varchar(99) NOT NULL,
  `cndtn` varchar(99) NOT NULL,
  `added_by` varchar(99) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `assets`
--

INSERT INTO `assets` (`as_id`, `as_name`, `mod_num`, `ser_num`, `p_cost`, `p_date`, `add_info`, `cndtn`, `added_by`) VALUES
(1, 'Table', 'GHJ-6954', '9865214', '500', '2024-07-06', 'For intern', 'Good', 'admin'),
(4, 'Chair', 'ERT-698', '321542', '250', '2024-07-07', 'For intern', 'Fair', 'admin'),
(5, 'Chair', 'ERT-698', '321542', '250', '2024-07-07', 'For intern', 'Fair', 'admin'),
(7, 'Ladder', 'JKL-3654', '32165898', '200', '2024-07-04', 'Use at own risk', 'Poor', 'admin'),
(8, 'Ladder', 'JKL-3654', '32165898', '200', '2024-07-04', 'Use at own risk', 'Poor', 'admin'),
(10, 'Stand Fan', '21a4598', '7854214', '1000', '2024-07-16', 'N/A', 'New', 'admin'),
(11, 'Stand Fan', '21a4598', '7854214', '1000', '2024-07-16', 'N/A', 'New', 'admin');

-- --------------------------------------------------------

--
-- Table structure for table `chkin`
--

CREATE TABLE `chkin` (
  `id` int(9) NOT NULL,
  `info` varchar(999) NOT NULL,
  `idnum` varchar(255) NOT NULL,
  `user_type` varchar(255) NOT NULL,
  `date` varchar(255) NOT NULL,
  `timein` varchar(999) NOT NULL,
  `timeout` varchar(999) NOT NULL,
  `purpose` varchar(255) NOT NULL,
  `archived` varchar(99) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `chkin`
--

INSERT INTO `chkin` (`id`, `info`, `idnum`, `user_type`, `date`, `timein`, `timeout`, `purpose`, `archived`) VALUES
(17, 'SCOTT MYCKEL D. PAUROM	BSIT	Lower Jasaan, Jasaan, Misamis Oriental', '0', 'Student', '03-03-2024', '1:44:58 PM', '1:50:49 PM', 'Study', ''),
(18, 'SCOTT MYCKEL D. PAUROM	BSIT	Lower Jasaan, Jasaan, Misamis Oriental', '0', 'Faculty', '03-23-2024', '1:14:04 PM', '1:14:39 PM', 'Clearance', 'Yes'),
(19, 'SCOTT MYCKEL D. PAUROM	BSIT	Lower Jasaan, Jasaan, Misamis Oriental', '0', 'Visitor', '04-01-2024', '9:48:10 PM', '9:48:42 PM', 'Printing', ''),
(20, 'SCOTT MYCKEL D. PAUROM	BSIT	Lower Jasaan, Jasaan, Misamis Oriental', '0', 'Faculty', '04-12-2024', '10:24:51 PM', '10:25:29 PM', 'Printing', ''),
(21, 'SCOTT MYCKEL D. PAUROM	BSIT	Lower Jasaan, Jasaan, Misamis Oriental', '0', 'Student', '04-13-2024', '10:30:22 PM', '10:32:47 PM', 'Research', ''),
(23, 'SCOTT MYCKEL D. PAUROM	BSIT	Lower Jasaan, Jasaan, Misamis Oriental', '0', 'Visitor', '04-23-2024', '10:37:52 PM', '10:38:02 PM', 'Clearance', ''),
(24, 'SCOTT MYCKEL D. PAUROM	BSIT	Lower Jasaan, Jasaan, Misamis Oriental', '0', 'Student', '04-30-2024', '10:40:42 PM', '10:40:54 PM', 'Borrow', ''),
(25, 'SCOTT MYCKEL D. PAUROM	BSIT	Lower Jasaan, Jasaan, Misamis Oriental', '0', 'Student', '05-02-2024', '2:18:01 PM', '2:19:43 PM', 'Return', ''),
(26, 'SCOTT MYCKEL D. PAUROM	BSIT	Lower Jasaan, Jasaan, Misamis Oriental', '0', 'Student', '05-03-2024', '2:27:21 PM', '2:28:25 PM', 'Printing', ''),
(27, 'SCOTT MYCKEL D. PAUROM	BSIT	Lower Jasaan, Jasaan, Misamis Oriental', '0', 'Student', '05-15-2024', '9:19:22 PM', '10:30:22 PM', 'Study', ''),
(28, 'SCOTT MYCKEL D. PAUROM	BSIT	Lower Jasaan, Jasaan, Misamis Oriental', '0', 'Staff', '06-02-2024', '12:48:27 PM', '12:49:36 PM', 'Study', ''),
(29, 'SCOTT MYCKEL D. PAUROM	BSIT	Lower Jasaan, Jasaan, Misamis Oriental', '0', 'Student', '06-02-2024', '12:50:41 PM', '12:54:19 PM', 'Study', ''),
(30, 'SCOTT MYCKEL D. PAUROM	BSIT	Lower Jasaan, Jasaan, Misamis Oriental', '0', 'Student', '06-02-2024', '12:55:00 PM', '12:55:12 PM', 'Printing', ''),
(34, 'ARAH U. FERNANDEZ	BSIT	M.E. Mundo St. Brgy. 3 Balingasag, Misamis Oriental', '0', 'Student', '07-26-2024', '12:00:55 PM', '12:04:12 PM', 'Clearance', ''),
(35, 'SCOTT MYCKEL D. PAUROM	BSIT	Lower Jasaan, Jasaan, Misamis Oriental', '0', 'Student', '07-26-2024', '12:01:12 PM', '12:03:30 PM', 'Research', ''),
(44, 'ARAH U. FERNANDEZ	BSIT	M.E. Mundo St. Brgy. 3 Balingasag, Misamis Oriental', '0', 'Student', '07-27-2024', '9:35:25 PM', '9:37:48 PM', 'Research', ''),
(45, 'ARAH U. FERNANDEZ	BSIT	M.E. Mundo St. Brgy. 3 Balingasag, Misamis Oriental', '0', 'Student', '07-28-2024', '1:22:03 PM', '1:29:01 PM', 'Study', ''),
(46, 'SCOTT MYCKEL D. PAUROM	BSIT	Lower Jasaan, Jasaan, Misamis Oriental', '0', 'Student', '07-28-2024', '2:47:09 PM', '2:47:38 PM', 'Clearance', ''),
(47, 'ARAH U. FERNANDEZ	BSIT	M.E. Mundo St. Brgy. 3 Balingasag, Misamis Oriental', '0', 'Student', '08-06-2024', '3:15:19 PM', '3:23:30 PM', 'Clearance', ''),
(55, 'Peter Abacahin BSIT', '0', 'Student', '08-08-2024', '03:55:42 PM', '04:12:19 PM', 'Study', ''),
(57, 'Alex Jacutin BSTCM', '0', 'Visitor', '08-08-2024', '04:08:23 PM', '04:10:39 PM', 'Clearance', ''),
(58, 'Joshua Ken Macapundag BSMET', '0', 'Visitor', '08-08-2024', '04:08:40 PM', '04:14:21 PM', 'Clearance', ''),
(59, 'Mfranz Valledor BSESM Lower', '0', 'Visitor', '08-08-2024', '04:10:08 PM', '08:46:18 PM', 'Clearance', ''),
(61, 'MARION SALVADOR BSNAME Solana', '0', 'Student', '08-08-2024', '08:46:45 PM', '08:47:32 PM', 'Research', ''),
(62, 'MARION SALVADOR BSNAME Solana', '0', 'Student', '08-09-2024', '10:08:10 PM', '10:35:13 PM', 'Study', ''),
(63, 'ARAH U. FERNANDEZ	BSIT	M.E. Mundo St. Brgy. 3 Balingasag, Misamis Oriental', '0', 'Student', '08-11-2024', '2:57:54 PM', '08:44:15 PM', 'Borrow', ''),
(73, 'SCOTT MYCKEL D. PAUROM	BSIT	Lower Jasaan, Jasaan, Misamis Oriental', '0', 'Student', '08-11-2024', '3:32:44 PM', '08:44:23 PM', 'Return', ''),
(78, 'SCOTT MYCKEL D. PAUROM	BSIT	Lower Jasaan, Jasaan, Misamis Oriental', '0', 'Student', '08-11-2024', '4:03:54 PM', '08:33:34 PM', 'Clearance', ''),
(81, 'MARION SALVADOR BSNAME', '0', 'Student', '08-11-2024', '08:37:35 PM', '08:44:00 PM', 'Clearance', ''),
(82, 'MARION SALVADOR BSNAME', '0', 'Student', '08-11-2024', '08:37:49 PM', '', 'Research', ''),
(83, 'Alex Jacutin BSTCM', '0', 'Student', '08-11-2024', '08:38:29 PM', '', 'Clearance', ''),
(84, 'Peter Abacahin BSIT', '0', 'Student', '08-11-2024', '08:39:17 PM', '', 'Research', ''),
(85, 'Irwin Fabela', '0', 'Faculty', '08-11-2024', '08:42:16 PM', '', 'Clearance', ''),
(86, 'Tom tom', '0', 'Staff', '08-11-2024', '08:42:34 PM', '', 'Study', ''),
(87, 'test 1 BSMET', '0', 'Student', '08-11-2024', '08:44:55 PM', '', 'Clearance', ''),
(88, 'Jimvy Salise ', '0', 'Faculty', '08-11-2024', '08:50:27 PM', '', 'Clearance', ''),
(89, 'ARAH U. FERNANDEZ	BSIT	M.E. Mundo St. Brgy. 3 Balingasag, Misamis Oriental', '0', 'Student', '08-13-2024', '10:23:46 AM', '10:53:11 AM', '', 'Yes'),
(90, 'ARAH U. FERNANDEZ	BSIT	M.E. Mundo St. Brgy. 3 Balingasag, Misamis Oriental', '0', 'Student', '08-13-2024', '10:25:56 AM', '10:53:11 AM', 'Study', ''),
(91, 'ALEXANDRA JEWEL C. JACUTIN	BSTCM	Upper Jasaan, Jasaan, Misamis Oriental', '0', 'Student', '08-13-2024', '10:49:51 AM', '10:50:26 AM', 'Study', ''),
(92, 'ARAH U. FERNANDEZ	BSIT	M.E. Mundo St. Brgy. 3 Balingasag, Misamis Oriental', '0', 'Student', '08-13-2024', '10:50:43 AM', '10:53:11 AM', 'Research', ''),
(93, 'ALEXANDRA JEWEL C. JACUTIN	BSTCM	Upper Jasaan, Jasaan, Misamis Oriental', '0', 'Student', '08-13-2024', '10:58:30 AM', '10:59:12 AM', 'Study', ''),
(94, 'ALEXANDRA JEWEL C. JACUTIN	BSTCM	Upper Jasaan, Jasaan, Misamis Oriental', '0', 'Student', '08-13-2024', '10:59:38 AM', '10:59:46 AM', 'Study', ''),
(95, 'ARAH U. FERNANDEZ	BSIT	M.E. Mundo St. Brgy. 3 Balingasag, Misamis Oriental', '0', 'Student', '08-13-2024', '11:01:27 AM', '11:01:53 AM', 'Research', ''),
(96, 'SCOTT MYCKEL D. PAUROM	BSIT	Lower Jasaan, Jasaan, Misamis Oriental', '0', 'Student', '08-13-2024', '11:04:27 AM', '11:05:02 AM', 'Research', ''),
(98, '2020301575	SCOTT MYCKEL	D.	PAUROM	BSEE', '0', 'Student', '08-20-2024', '8:57:27 PM', '8:58:04 PM', 'Clearance', ''),
(99, 'MARION SALVADOR BSNAME', '0', 'Student', '08-22-2024', '01:55:10 PM', '01:55:27 PM', 'Printing', ''),
(100, 'Peter Abacahin BSIT', '0', 'Student', '09-06-2024', '10:35:40 AM', '12:17:16 PM', 'Clearance', ''),
(118, 'LINE JEN S. LLAUSAS	BSIT	Lower Jasaan, Misamis Oriental', '0', 'Student', '09-06-2024', '12:53:26 PM', '12:53:42 PM', 'Study', ''),
(119, 'LINE JEN S. LLAUSAS	BSIT	Lower Jasaan, Misamis Oriental', '0', 'Student', '09-06-2024', '1:02:12 PM', '1:02:31 PM', 'Study', ''),
(120, 'NORMENA P. HADJI MALIC	BSIT	Lower Jasaan, Misamis Oriental', '0', 'Student', '09-06-2024', '1:03:58 PM', '1:04:14 PM', 'Research', ''),
(121, 'ARAH U. FERNANDEZ	BSIT	M.E. Mundo St. Brgy. 3 Balingasag, Misamis Oriental', '0', 'Student', '09-06-2024', '1:05:05 PM', '1:06:13 PM', 'Research', ''),
(122, 'ALEXANDRA JEWEL C. JACUTIN	BSTCM	Upper Jasaan, Jasaan, Misamis Oriental', '0', 'Student', '09-06-2024', '1:05:17 PM', '1:05:49 PM', 'Study', ''),
(123, 'LINE JEN S. LLAUSAS	BSIT	Lower Jasaan, Misamis Oriental', '0', 'Student', '09-06-2024', '1:06:25 PM', '1:07:21 PM', 'Printing', ''),
(124, 'NORMENA P. HADJI MALIC	BSIT	Lower Jasaan, Misamis Oriental', '0', 'Student', '09-06-2024', '1:06:33 PM', '1:07:28 PM', 'Printing', ''),
(125, 'NORMENA P. HADJI MALIC	BSIT	Lower Jasaan, Misamis Oriental', '0', 'Student', '09-06-2024', '3:50:46 PM', '3:52:02 PM', 'Borrow Book(s)', ''),
(126, 'LINE JEN S. LLAUSAS	BSIT	Lower Jasaan, Misamis Oriental', '0', 'Student', '09-06-2024', '3:52:58 PM', '3:59:12 PM', 'Borrow Book(s)', ''),
(128, 'ARAH U. FERNANDEZ	BSIT	M.E. Mundo St. Brgy. 3 Balingasag, Misamis Oriental', '2021304029', 'Student', '10-19-2024', '9:08:29 PM', '9:10:12 PM', 'Study', ''),
(129, 'Peter Abacahin BSIT', '654687', 'Visitor', '10-19-2024', '9:09:15 PM', '09:26:58 PM', 'Clearance', ''),
(130, 'MARION SALVADOR BSNAME', '123123', 'Student', '10-19-2024', '09:22:34 PM', '09:34:25 PM', 'Research', '');

-- --------------------------------------------------------

--
-- Table structure for table `fav`
--

CREATE TABLE `fav` (
  `fid` int(11) NOT NULL,
  `uid` int(11) NOT NULL,
  `bid` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `fav`
--

INSERT INTO `fav` (`fid`, `uid`, `bid`) VALUES
(1, 0, 0),
(2, 1, 1),
(3, 1, 2),
(4, 1, 3),
(5, 1, 4),
(8, 7, 1),
(9, 7, 3),
(10, 7, 2),
(12, 3, 4),
(15, 2, 4),
(16, 2, 7);

-- --------------------------------------------------------

--
-- Table structure for table `inventory`
--

CREATE TABLE `inventory` (
  `bid` int(9) NOT NULL,
  `title` varchar(99) NOT NULL,
  `author` varchar(99) NOT NULL,
  `year` varchar(99) NOT NULL,
  `genre` varchar(255) NOT NULL,
  `dew_num` varchar(99) NOT NULL,
  `ISBN` varchar(99) NOT NULL,
  `shlf_num` varchar(99) NOT NULL,
  `cndtn` varchar(99) NOT NULL,
  `add_info` varchar(500) NOT NULL,
  `status` varchar(255) NOT NULL,
  `added_by` varchar(99) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `inventory`
--

INSERT INTO `inventory` (`bid`, `title`, `author`, `year`, `genre`, `dew_num`, `ISBN`, `shlf_num`, `cndtn`, `add_info`, `status`, `added_by`) VALUES
(1, 'Harry Potter 1', 'J.K. Rowling', '1996', 'Fantasy', '001', '', '2', 'Good', '1st Edition', 'Available', ''),
(2, 'Fellowship Point', 'Alice Elliott Dark', '2001', 'Fiction', '800', '', '', '', 'New York Times Best Seller', 'Overdue', ''),
(3, 'Wuthering Heights', 'Emily Brontë', '', 'Tragedy', '823.8', '', '', '', 'Classic Novel', 'Overdue', ''),
(4, 'Peter and Wendy', 'J. M. Barrie', '2006', 'Fantasy, Fiction', '808.3876', '', '1', 'Poor', 'asdasd', 'Reserved', ''),
(5, 'Summertime Guests', 'Wendy Francis', '', 'Mystery', '813.087208', '', '', '', 'Contemporary Romance', 'Available', ''),
(7, 'Jonah and the Whale', 'GOD', '', 'Fantasy', '999', '0-4596-4626-9', '2', 'Good', 'Bible Story', 'Available', ''),
(8, 'book 1', 'jhon smith', '2001', 'Comedy', '007', '0-7432-4626-8', '3', 'Good', 'N/A', 'Available', 'admin'),
(9, 'book 1', 'jhon smith', '2001', 'Comedy', '007', '0-7432-4626-8', '3', 'Good', 'N/A', 'Available', 'admin');

-- --------------------------------------------------------

--
-- Table structure for table `libr`
--

CREATE TABLE `libr` (
  `aid` int(9) NOT NULL,
  `name` varchar(255) NOT NULL,
  `contact` varchar(255) NOT NULL,
  `username` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `libr`
--

INSERT INTO `libr` (`aid`, `name`, `contact`, `username`, `password`) VALUES
(1, 'Line Jen Llausas', '03695698695', 'Linejen', 'jen');

-- --------------------------------------------------------

--
-- Table structure for table `noise`
--

CREATE TABLE `noise` (
  `n_id` int(9) NOT NULL,
  `tbl_num` int(9) NOT NULL,
  `noise_lvl` int(99) NOT NULL,
  `rmrks` varchar(50) NOT NULL,
  `date` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `ovrd`
--

CREATE TABLE `ovrd` (
  `oid` int(9) NOT NULL,
  `rid` int(9) NOT NULL,
  `uid` int(99) NOT NULL,
  `info` varchar(255) NOT NULL,
  `bid` int(99) NOT NULL,
  `title` varchar(255) NOT NULL,
  `fines` varchar(255) NOT NULL,
  `date_set` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `ovrd`
--

INSERT INTO `ovrd` (`oid`, `rid`, `uid`, `info`, `bid`, `title`, `fines`, `date_set`) VALUES
(19, 68, 0, 'ARAH U. FERNANDEZ	BSIT	M.E. Mundo St. Brgy. 3 Balingasag, Misamis Oriental', 4, 'Peter and Wendy', '3', '2024-08-06'),
(20, 65, 1, 'SCOTT MYCKEL D. PAUROM	BSIT	Lower Jasaan, Jasaan, Misamis Oriental', 1, 'Harry Potter 1', '3', '2024-08-06'),
(48, 72, 2, 'ARAH U. FERNANDEZ	BSIT	M.E. Mundo St. Brgy. 3 Balingasag, Misamis Oriental', 1, 'Harry Potter 1', '9', '2024-08-13'),
(49, 71, 0, 'ARAH U. FERNANDEZ	BSIT	M.E. Mundo St. Brgy. 3 Balingasag, Misamis Oriental', 7, 'Jonah and the Whale', '6', '2024-08-13');

-- --------------------------------------------------------

--
-- Table structure for table `pdf`
--

CREATE TABLE `pdf` (
  `pdf_id` int(9) NOT NULL,
  `title` varchar(255) NOT NULL,
  `author` varchar(255) NOT NULL,
  `year` varchar(50) NOT NULL,
  `genre` varchar(255) NOT NULL,
  `add_info` varchar(255) NOT NULL,
  `link` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `pdf`
--

INSERT INTO `pdf` (`pdf_id`, `title`, `author`, `year`, `genre`, `add_info`, `link`) VALUES
(1, 'USTP Handbook 2023 Edition', 'USTP', '2023', 'Educational', '2023 Edition', 'https://drive.google.com/file/d/1-lIKkolhVZcH9EieN5TZRmR8U5ySClPe/view'),
(2, 'Sound Intensity Measuring Instrument Based on Arduino Board with Data Logger System', 'Intan Nurjannah, Drs. Alex Harijanto, M.Si., Drs. Bambang Supriadi, M.Sc.', '2017', 'Engineering', 'Naa', 'https://www.researchgate.net/profile/Intan-Nurjannah/publication/319905231_Sound_Intensity_Measuring_Instrument_Based_on_Arduino_Board_with_Data_Logger_System/links/5d1ace6a299bf1547c8f8587/Sound-Intensity-Measuring-Instrument-Based-on-Arduino-Board-with-');

-- --------------------------------------------------------

--
-- Table structure for table `rsv`
--

CREATE TABLE `rsv` (
  `rid` int(9) NOT NULL,
  `uid` int(9) NOT NULL,
  `bid` int(9) NOT NULL,
  `info` varchar(255) NOT NULL,
  `contact` varchar(255) NOT NULL,
  `title` varchar(255) NOT NULL,
  `status` varchar(255) NOT NULL,
  `rsv_end` varchar(255) NOT NULL,
  `date_rel` varchar(255) NOT NULL,
  `due_date` varchar(255) NOT NULL,
  `date_ret` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `rsv`
--

INSERT INTO `rsv` (`rid`, `uid`, `bid`, `info`, `contact`, `title`, `status`, `rsv_end`, `date_rel`, `due_date`, `date_ret`) VALUES
(18, 7, 0, '', '', 'Wuthering Heights', 'Rejected', '', '', '', ''),
(20, 7, 0, '', '', 'Summertime Guests', 'Rejected', '', '', '', ''),
(23, 7, 3, '', '', 'Wuthering Heights', 'Returned', '04-09-2024', '04-06-2024', '04-09-2024', '04-06-2024'),
(24, 7, 3, '', '', 'Wuthering Heights', 'Canceled', '04-07-2024', '', '', ''),
(25, 1, 2, '', '', 'Fellowship Point', 'Returned', '04-11-2024', '04-08-2024', '04-11-2024', '04-08-2024'),
(26, 7, 1, '', '', 'Harry Potter 1', 'Rejected', '', '', '', ''),
(27, 7, 3, '', '', 'Wuthering Heights', 'Returned', '04-12-2024', '04-11-2024', '04-13-2024', '04-12-2024'),
(28, 1, 1, '', '', 'Harry Potter 1', 'Settled', '04-11-2024', '04-08-2024', '04-07-2024', ''),
(29, 1, 2, '', '', 'Fellowship Point', 'Overdue', '04-13-2024', '04-12-2024', '04-10-2024', ''),
(34, 1, 3, '', '', 'Wuthering Heights', 'Overdue', '04-18-2024', '04-15-2024', '04-18-2024', ''),
(47, 7, 2, '', '', 'Fellowship Point', 'Canceled', '04-24-2024', '', '', ''),
(48, 3, 1, '', '', 'Harry Potter 1', 'Rejected', '', '', '', ''),
(52, 3, 2, '', '', 'Fellowship Point', 'Rejected', '', '', '', ''),
(57, 3, 6, '', '', 'Pre-Calculus', 'Returned', '05-22-2024', '05-15-2024', '05-18-2024', '05-15-2024'),
(58, 3, 4, '', '', 'Peter and Wendy', 'Returned', '05-22-2024', '05-15-2024', '05-18-2024', '05-15-2024'),
(61, 0, 5, 'SCOTT MYCKEL D. PAUROM	BSIT	Lower Jasaan, Jasaan, Misamis Oriental', '09663695689', '', 'Settled', '', '2024-05-18', '2024-05-15', ''),
(63, 3, 6, '', '', 'Pre-Calculus', 'Settled', '06-05-2024', '06-02-2024', '06-01-2024', ''),
(64, 3, 8, '', '', 'book 1', 'Returned', '07-23-2024', '07-20-2024', '07-22-2024', '07-20-2024'),
(65, 1, 1, '', '', 'Harry Potter 1', 'Settled', '08-09-2024', '08-06-2024', '08-05-2024', ''),
(66, 0, 4, 'ARAH U. FERNANDEZ	BSIT	M.E. Mundo St. Brgy. 3 Balingasag, Misamis Oriental', '233222', '', 'Returned', '', '08-06-2024', '08-12-2024', '08-06-2024	'),
(67, 0, 5, 'ARAH U. FERNANDEZ	BSIT	M.E. Mundo St. Brgy. 3 Balingasag, Misamis Oriental', '09896765454', '', 'Returned', '', '08-06-2024', '08-16-2024', '08-06-2024'),
(68, 0, 4, 'ARAH U. FERNANDEZ	BSIT	M.E. Mundo St. Brgy. 3 Balingasag, Misamis Oriental', '', '', 'Settled', '', '08-06-2024', '08-05-2024', ''),
(69, 2, 1, '', '', 'Harry Potter 1', 'Canceled', '', '', '', ''),
(70, 0, 1, 'ARAH U. FERNANDEZ	BSIT	M.E. Mundo St. Brgy. 3 Balingasag, Misamis Oriental', '', '', 'Returned', '', '08-10-2024', '08-17-2024', '08-10-2024'),
(71, 0, 7, 'ARAH U. FERNANDEZ	BSIT	M.E. Mundo St. Brgy. 3 Balingasag, Misamis Oriental', '036985652', '', 'Settled', '', '08-01-2024', '08-11-2024', ''),
(72, 2, 1, '', '', 'Harry Potter 1', 'Settled', '08-16-2024', '08-13-2024', '08-10-2024', ''),
(73, 0, 4, 'SCOTT MYCKEL D. PAUROM	BSIT	Lower Jasaan, Jasaan, Misamis Oriental', '233222', '', 'Overdue', '', '08-13-2024', '08-23-2024', ''),
(74, 2, 8, '', '', 'book 1', 'Cancelled', '', '', '', '');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `uid` int(9) NOT NULL,
  `contact` varchar(255) NOT NULL,
  `username` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `info` varchar(255) NOT NULL,
  `idnum` varchar(255) NOT NULL,
  `user_type` varchar(99) NOT NULL,
  `profile_image` varchar(99) NOT NULL,
  `status` varchar(99) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`uid`, `contact`, `username`, `password`, `info`, `idnum`, `user_type`, `profile_image`, `status`) VALUES
(1, '66546132', 'life', 'life', 'SCOTT MYCKEL D. PAUROM	BSIT	Lower Jasaan, Jasaan, Misamis Oriental', '2020301575', 'Student', 'uploads/default.jpg', 'Active'),
(2, '09653698745', 'Arah', 'arah', 'ARAH U. FERNANDEZ	BSIT	M.E. Mundo St. Brgy. 3 Balingasag, Misamis Oriental', '2021304029', 'Student', 'uploads/arah.png', 'Active'),
(7, '213123', 'mens', 'mens', 'NORMENA P. HADJI MALIC	BSIT	Lower Jasaan, Misamis Oriental', '0', 'Student', 'uploads/Screenshot 2024-08-21 100736.png', 'Pending'),
(13, '09896765454', 'Line Jen', 'llausas', 'LINE JEN S. LLAUSAS	BSIT	Lower Jasaan, Misamis Oriental', '0', 'Student', 'uploads/Screenshot 2024-08-27 220327.png', 'Active'),
(15, '111111', 'tom', 'tom', '2020301575	SCOTT MYCKEL	D.	PAUROM	BSEE', '0', 'Student', 'uploads/default.jpg', 'Pending'),
(17, '233222', 'mv', 'mv', 'Mfranz Valledor BSMET Bobuntogan', '2020996456', 'Student', 'uploads/default.jpg', 'Pending');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin`
--
ALTER TABLE `admin`
  ADD PRIMARY KEY (`aid`);

--
-- Indexes for table `assets`
--
ALTER TABLE `assets`
  ADD PRIMARY KEY (`as_id`);

--
-- Indexes for table `chkin`
--
ALTER TABLE `chkin`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `fav`
--
ALTER TABLE `fav`
  ADD PRIMARY KEY (`fid`);

--
-- Indexes for table `inventory`
--
ALTER TABLE `inventory`
  ADD PRIMARY KEY (`bid`);

--
-- Indexes for table `libr`
--
ALTER TABLE `libr`
  ADD PRIMARY KEY (`aid`);

--
-- Indexes for table `noise`
--
ALTER TABLE `noise`
  ADD PRIMARY KEY (`n_id`);

--
-- Indexes for table `ovrd`
--
ALTER TABLE `ovrd`
  ADD PRIMARY KEY (`oid`);

--
-- Indexes for table `pdf`
--
ALTER TABLE `pdf`
  ADD PRIMARY KEY (`pdf_id`);

--
-- Indexes for table `rsv`
--
ALTER TABLE `rsv`
  ADD PRIMARY KEY (`rid`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`uid`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admin`
--
ALTER TABLE `admin`
  MODIFY `aid` int(9) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `assets`
--
ALTER TABLE `assets`
  MODIFY `as_id` int(9) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `chkin`
--
ALTER TABLE `chkin`
  MODIFY `id` int(9) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=131;

--
-- AUTO_INCREMENT for table `fav`
--
ALTER TABLE `fav`
  MODIFY `fid` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `inventory`
--
ALTER TABLE `inventory`
  MODIFY `bid` int(9) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `libr`
--
ALTER TABLE `libr`
  MODIFY `aid` int(9) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `noise`
--
ALTER TABLE `noise`
  MODIFY `n_id` int(9) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `ovrd`
--
ALTER TABLE `ovrd`
  MODIFY `oid` int(9) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=50;

--
-- AUTO_INCREMENT for table `pdf`
--
ALTER TABLE `pdf`
  MODIFY `pdf_id` int(9) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `rsv`
--
ALTER TABLE `rsv`
  MODIFY `rid` int(9) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=75;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `uid` int(9) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
