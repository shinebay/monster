-- phpMyAdmin SQL Dump
-- version 4.2.2
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: 2016-04-20 22:06:49
-- 服务器版本： 5.7.4-m14
-- PHP Version: 5.5.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `monster`
--

-- --------------------------------------------------------

--
-- 表的结构 `ms_thread`
--

CREATE TABLE IF NOT EXISTS `ms_thread` (
`thread_id` int(10) unsigned NOT NULL,
  `thread_keywords` text NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- 表的结构 `ms_user`
--

CREATE TABLE IF NOT EXISTS `ms_user` (
`uid` int(10) unsigned NOT NULL,
  `username` varchar(100) NOT NULL,
  `email` text NOT NULL,
  `pwd` text NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `ms_thread`
--
ALTER TABLE `ms_thread`
 ADD PRIMARY KEY (`thread_id`), ADD FULLTEXT KEY `thread_keywords` (`thread_keywords`);

--
-- Indexes for table `ms_user`
--
ALTER TABLE `ms_user`
 ADD PRIMARY KEY (`uid`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `ms_thread`
--
ALTER TABLE `ms_thread`
MODIFY `thread_id` int(10) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `ms_user`
--
ALTER TABLE `ms_user`
MODIFY `uid` int(10) unsigned NOT NULL AUTO_INCREMENT;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
