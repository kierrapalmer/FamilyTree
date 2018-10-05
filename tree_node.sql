-- phpMyAdmin SQL Dump
-- version 4.8.3
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 04, 2018 at 06:14 PM
-- Server version: 10.1.35-MariaDB
-- PHP Version: 7.2.9

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `familytree`
--

-- --------------------------------------------------------

--
-- Table structure for table `tree_node`
--


--
-- Dumping data for table `tree_node`
--

INSERT INTO `tree_node` (`_id`, `firstName`, `lastName`, `generation`, `spouse`, `parentId`, `parentMarriageId`) VALUES
(1, 'John', 'Winchester', 0, 'Mary  Winchester', NULL, NULL),
(2, 'Dean', 'Winchester', 1, 'Cas Novak', 1, NULL),
(3, 'Ben', 'Winchester', 2, NULL, 2, NULL),
(4, 'Sam', 'Winchester', 1, 'Gabriel Grace', 1, NULL),
(5, 'Claire', 'Nieves', 2, 'Kaia Nieves', 2, NULL),
(6, 'Samandriel', 'Nieves', 3, NULL, 5, NULL);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `tree_node`
--


--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `tree_node`
--


/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
