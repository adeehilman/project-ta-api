-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 03, 2023 at 12:18 PM
-- Server version: 10.4.28-MariaDB
-- PHP Version: 8.1.17

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `mysatnusaadmin`
--

-- --------------------------------------------------------

--
-- Table structure for table `tbl_user`
--

CREATE TABLE `tbl_user` (
  `id` int(11) NOT NULL,
  `badge` varchar(6) NOT NULL,
  `username` varchar(30) NOT NULL,
  `password` longtext NOT NULL,
  `section_id` int(11) NOT NULL,
  `rolerepair_id` int(11) DEFAULT NULL,
  `photo` longtext DEFAULT NULL,
  `isactive` varchar(1) NOT NULL,
  `createby` varchar(6) NOT NULL,
  `createdate` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `tbl_user`
--

INSERT INTO `tbl_user` (`id`, `badge`, `username`, `password`, `section_id`, `rolerepair_id`, `photo`, `isactive`, `createby`, `createdate`) VALUES
(1, '111111', 'Nanda', '$2y$10$to4hocHXmMocSYP8RnhcPOU0ZWqMYXAk/xshZ23pZqUV/zexGomMi', 1, 1, NULL, '1', '038720', '2023-04-25 12:17:08'),
(3, '222222', 'Teguh Kurniawan', '$2y$10$to4hocHXmMocSYP8RnhcPOU0ZWqMYXAk/xshZ23pZqUV/zexGomMi', 4, 5, NULL, '1', '038720', '2023-04-27 08:55:53');
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
