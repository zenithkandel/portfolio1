-- ============================================
-- Portfolio Database Schema
-- MySQL 5.7+ / MariaDB 10.2+
-- ============================================

-- Create database
CREATE DATABASE IF NOT EXISTS `portfolio_db` 
CHARACTER SET utf8mb4 
COLLATE utf8mb4_unicode_ci;

USE `portfolio_db`;

-- ============================================
-- Settings Table
-- Single-row configuration store
-- ============================================
CREATE TABLE IF NOT EXISTS `settings` (
    `id` INT PRIMARY KEY DEFAULT 1,
    
    -- Site Meta
    `site_title` VARCHAR(255) DEFAULT 'Portfolio',
    `site_description` TEXT,
    
    -- Hero Section
    `hero_tagline` VARCHAR(255),
    `hero_title` VARCHAR(255),
    `hero_subtitle` TEXT,
    
    -- About Section
    `about_text` TEXT,
    `about_text_2` TEXT,
    `photo_url` VARCHAR(255) DEFAULT 'me.jpg',
    
    -- Contact Information
    `email` VARCHAR(255),
    `contact_email` VARCHAR(255),
    `contact_location` VARCHAR(255),
    `phone` VARCHAR(50),
    
    -- Social Links
    `github_url` VARCHAR(255),
    `linkedin_url` VARCHAR(255),
    `instagram_url` VARCHAR(255),
    `facebook_url` VARCHAR(255),
    `whatsapp` VARCHAR(50),
    
    -- Security
    `admin_password` VARCHAR(255),
    
    -- Timestamps
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    -- Ensure only one row exists
    CONSTRAINT `single_row` CHECK (`id` = 1)
) ENGINE=InnoDB;

-- ============================================
-- Skills Table
-- Technology/skill tags displayed on portfolio
-- ============================================
CREATE TABLE IF NOT EXISTS `skills` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(100) NOT NULL,
    `icon` VARCHAR(100) DEFAULT 'fa-solid fa-code',
    `sort_order` INT DEFAULT 0,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    INDEX `idx_sort_order` (`sort_order`)
) ENGINE=InnoDB;

-- ============================================
-- Projects Table
-- Portfolio project showcase
-- ============================================
CREATE TABLE IF NOT EXISTS `projects` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `title` VARCHAR(255) NOT NULL,
    `description` TEXT,
    `image` VARCHAR(255),
    `tag1` VARCHAR(50),
    `tag2` VARCHAR(50),
    `url` VARCHAR(255),
    `github_url` VARCHAR(255),
    `public_url` VARCHAR(255),
    `sort_order` INT DEFAULT 0,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    INDEX `idx_sort_order` (`sort_order`)
) ENGINE=InnoDB;

-- ============================================
-- Messages Table
-- Contact form submissions
-- ============================================
CREATE TABLE IF NOT EXISTS `messages` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(255) NOT NULL,
    `email` VARCHAR(255) NOT NULL,
    `subject` VARCHAR(255),
    `message` TEXT NOT NULL,
    `is_read` TINYINT(1) DEFAULT 0,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    INDEX `idx_is_read` (`is_read`),
    INDEX `idx_created_at` (`created_at` DESC)
) ENGINE=InnoDB;

-- ============================================
-- Default Data
-- ============================================

-- Insert default settings (password: admin123)
INSERT INTO `settings` (
    `id`, 
    `site_title`, 
    `hero_title`, 
    `hero_subtitle`,
    `admin_password`
) VALUES (
    1,
    'My Portfolio',
    'Hello, I''m Developer',
    'I build modern web experiences',
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'
) ON DUPLICATE KEY UPDATE `id` = `id`;

-- Sample skills
INSERT INTO `skills` (`name`, `icon`, `sort_order`) VALUES
    ('HTML', 'fa-solid fa-code', 1),
    ('CSS', 'fa-solid fa-paintbrush', 2),
    ('JavaScript', 'fa-brands fa-js', 3),
    ('PHP', 'fa-solid fa-server', 4),
    ('MySQL', 'fa-solid fa-database', 5)
ON DUPLICATE KEY UPDATE `name` = VALUES(`name`);
