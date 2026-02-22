-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Feb 22, 2026 at 02:47 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `portfolio_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `messages`
--

CREATE TABLE `messages` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `subject` varchar(255) DEFAULT NULL,
  `message` text NOT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `messages`
--

INSERT INTO `messages` (`id`, `name`, `email`, `subject`, `message`, `is_read`, `created_at`) VALUES
(1, 'zenith kandel', 'kandelze123@gmail.com', '', '838', 1, '2026-02-22 12:30:22');

-- --------------------------------------------------------

--
-- Table structure for table `projects`
--

CREATE TABLE `projects` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `tag1` varchar(50) DEFAULT NULL,
  `tag2` varchar(50) DEFAULT NULL,
  `url` varchar(255) DEFAULT NULL,
  `sort_order` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `github_url` varchar(500) DEFAULT NULL,
  `public_url` varchar(500) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `projects`
--

INSERT INTO `projects` (`id`, `title`, `description`, `image`, `tag1`, `tag2`, `url`, `sort_order`, `created_at`, `github_url`, `public_url`) VALUES
(1, 'STREAMFLIX', 'Movie streaming platform with chunked uploads, subtitles, and HLS processing.', 'uploads/projects/project_699afdd8be7283.04044409.png', 'PHP', 'Full-stack', 'https://github.com/zenithkandel/STREAMFLIX', 6, '2026-02-22 12:04:35', 'https://github.com/zenithkandel/STREAMFLIX', 'https://englishlab.rageniresort.com.np'),
(2, 'Kushma Art Project', 'Modern bilingual art site with gallery, events, and donation flows.', 'uploads/projects/project_699afc469d11d3.14587543.png', 'Showcase', 'Frontend', 'https://github.com/zenithkandel', 0, '2026-02-22 12:04:35', 'https://github.com/zenithkandel', 'https://kushmaartproject.com.np'),
(3, 'Rageni Agro Resort', 'Single-page resort website with parallax and theme switch experiments.', 'uploads/projects/project_699afdd01531d1.19317452.png', 'Landing', 'SPA', 'https://github.com/zenithkandel', 2, '2026-02-22 12:04:35', NULL, 'https://github.com/zenithkandel'),
(4, 'LIFELINE', 'Wireless emergency signal transmission.', 'uploads/projects/project_699afdbd812be5.07929802.png', 'HTML', 'JS', 'https://github.com/zenithkandel/Javascript-Calculator', 1, '2026-02-22 12:04:35', 'https://github.com/zenithkandel/gss-home', 'https://zenithkandel.com.np/lifeline'),
(5, 'Sawari', 'nepali bus tracking system', 'uploads/projects/project_699afe1d35cf13.52946836.png', 'Utility', 'JS', 'https://github.com/zenithkandel/Random-Color-Generator', 3, '2026-02-22 12:04:35', NULL, 'https://github.com/zenithkandel/Random-Color-Generator'),
(6, 'Edu Track Pro', 'smart digital attendance system', 'uploads/projects/project_699afe2c52b4f0.69786178.png', 'CSS', 'UI', 'https://github.com/zenithkandel/Css-Login', 4, '2026-02-22 12:04:35', NULL, 'https://github.com/zenithkandel/Css-Login'),
(7, 'Agropan', 'agriculture monitoring tool', 'uploads/projects/project_699b0551d36697.68219393.png', '', '', NULL, 5, '2026-02-22 13:32:32', 'https://github.com/esp-sakshyam/Renhackathon-spark', 'https://zenithkandel.com.np/agropan');

-- --------------------------------------------------------

--
-- Table structure for table `settings`
--

CREATE TABLE `settings` (
  `id` int(11) NOT NULL DEFAULT 1,
  `site_title` varchar(255) DEFAULT 'Zenith Kandel — Portfolio',
  `site_description` text DEFAULT NULL,
  `hero_tagline` varchar(255) DEFAULT NULL,
  `hero_title` varchar(255) DEFAULT NULL,
  `hero_subtitle` text DEFAULT NULL,
  `about_text` text DEFAULT NULL,
  `about_text_2` text DEFAULT NULL,
  `photo_url` varchar(255) DEFAULT 'me.jpg',
  `email` varchar(255) DEFAULT NULL,
  `phone` varchar(50) DEFAULT NULL,
  `github_url` varchar(255) DEFAULT NULL,
  `linkedin_url` varchar(255) DEFAULT NULL,
  `instagram_url` varchar(255) DEFAULT NULL,
  `facebook_url` varchar(255) DEFAULT NULL,
  `whatsapp` varchar(50) DEFAULT NULL,
  `admin_password` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ;

--
-- Dumping data for table `settings`
--

INSERT INTO `settings` (`id`, `site_title`, `site_description`, `hero_tagline`, `hero_title`, `hero_subtitle`, `about_text`, `about_text_2`, `photo_url`, `email`, `phone`, `github_url`, `linkedin_url`, `instagram_url`, `facebook_url`, `whatsapp`, `admin_password`, `created_at`, `updated_at`) VALUES
(1, 'Zenith Kandel — Portfolio', 'Zenith Kandel — self-taught frontend developer & designer.', 'Self-taught Frontend Developer • Kathmandu, Nepal', 'Hi, I\'m Zenith Kandel.', 'I build clean, fast, and functional web experiences. I love minimal UI and shipping pragmatic solutions.', 'I\'m a Grade 11 student and a self-taught web developer/designer from Nepal. I enjoy frontend craft, quick prototyping, and turning ideas into simple UIs. Currently exploring Node.js, PHP, MongoDB, and building portfolio projects while staying open to internships and collaborations.', 'When not coding, I sketch interfaces, tweak micro-interactions, and learn by doing. I prefer minimal code, no frameworks when possible, and strong fundamentals.', 'me.jpg', 'zenithkandel0@gmail.com', '+977 9806176120', 'https://github.com/zenithkandel', 'https://www.linkedin.com/in/zenithkandel/', 'https://www.instagram.com/kandel.zenith/', 'https://www.facebook.com/kandel.zenith', '9806176120', '$2y$12$BBRDhMN1ZWM5O2n8GkitKuF1KXf236lpOw8uQH5MdBn22YCU2BExu', '2026-02-22 12:04:35', '2026-02-22 13:17:01');

-- --------------------------------------------------------

--
-- Table structure for table `skills`
--

CREATE TABLE `skills` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `icon` varchar(100) DEFAULT 'fa-solid fa-code',
  `sort_order` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `skills`
--

INSERT INTO `skills` (`id`, `name`, `icon`, `sort_order`, `created_at`) VALUES
(1, 'HTML', 'fa-solid fa-code', 1, '2026-02-22 12:04:35'),
(2, 'CSS', 'fa-solid fa-paintbrush', 2, '2026-02-22 12:04:35'),
(3, 'JavaScript', 'fa-brands fa-js', 3, '2026-02-22 12:04:35'),
(4, 'Node.js', 'fa-brands fa-node-js', 4, '2026-02-22 12:04:35'),
(5, 'PHP', 'fa-solid fa-server', 5, '2026-02-22 12:04:35'),
(6, 'MySQL', 'fa-solid fa-database', 6, '2026-02-22 12:04:35'),
(7, 'MongoDB', 'fa-solid fa-leaf', 7, '2026-02-22 12:04:35'),
(8, 'UI/UX', 'fa-solid fa-pen-ruler', 8, '2026-02-22 12:04:35'),
(9, 'Responsive Design', 'fa-solid fa-mobile-screen', 9, '2026-02-22 12:04:35');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `messages`
--
ALTER TABLE `messages`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `projects`
--
ALTER TABLE `projects`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `settings`
--
ALTER TABLE `settings`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `skills`
--
ALTER TABLE `skills`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `messages`
--
ALTER TABLE `messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `projects`
--
ALTER TABLE `projects`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `skills`
--
ALTER TABLE `skills`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
