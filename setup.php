<?php
/**
 * Database Setup Script
 * Run this once to create the database and tables
 */

$host = 'localhost';
$user = 'root';
$pass = '';
$dbname = 'portfolio_db';

try {
    // Connect without database first
    $pdo = new PDO("mysql:host=$host;charset=utf8mb4", $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);

    // Create database
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `$dbname` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    $pdo->exec("USE `$dbname`");

    // Create settings table
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS settings (
            id INT PRIMARY KEY DEFAULT 1,
            site_title VARCHAR(255) DEFAULT 'Zenith Kandel — Portfolio',
            site_description TEXT,
            hero_tagline VARCHAR(255),
            hero_title VARCHAR(255),
            hero_subtitle TEXT,
            about_text TEXT,
            about_text_2 TEXT,
            photo_url VARCHAR(255) DEFAULT 'me.jpg',
            email VARCHAR(255),
            phone VARCHAR(50),
            github_url VARCHAR(255),
            linkedin_url VARCHAR(255),
            instagram_url VARCHAR(255),
            admin_password VARCHAR(255),
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            CONSTRAINT single_row CHECK (id = 1)
        ) ENGINE=InnoDB
    ");

    // Create skills table
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS skills (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(100) NOT NULL,
            icon VARCHAR(100) DEFAULT 'fa-solid fa-code',
            sort_order INT DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB
    ");

    // Create projects table
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS projects (
            id INT AUTO_INCREMENT PRIMARY KEY,
            title VARCHAR(255) NOT NULL,
            description TEXT,
            image VARCHAR(255),
            tag1 VARCHAR(50),
            tag2 VARCHAR(50),
            url VARCHAR(255),
            sort_order INT DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB
    ");

    // Create messages table
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS messages (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(255) NOT NULL,
            email VARCHAR(255) NOT NULL,
            subject VARCHAR(255),
            message TEXT NOT NULL,
            is_read TINYINT(1) DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB
    ");

    // Insert default settings
    $defaultPassword = password_hash('admin123', PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("
        INSERT IGNORE INTO settings (
            id, site_title, site_description, hero_tagline, hero_title, hero_subtitle,
            about_text, about_text_2, email, phone, github_url, linkedin_url, instagram_url, admin_password
        ) VALUES (
            1,
            'Zenith Kandel — Portfolio',
            'Zenith Kandel — self-taught frontend developer & designer.',
            'Self-taught Frontend Developer • Kathmandu, Nepal',
            'Hi, I''m Zenith Kandel.',
            'I build clean, fast, and functional web experiences with HTML, CSS, and JavaScript. I love minimal UI and shipping pragmatic solutions.',
            'I''m a Grade 11 student and a self-taught web developer/designer from Nepal. I enjoy frontend craft, quick prototyping, and turning ideas into simple UIs. Currently exploring Node.js, PHP, MongoDB, and building portfolio projects while staying open to internships and collaborations.',
            'When not coding, I sketch interfaces, tweak micro-interactions, and learn by doing. I prefer minimal code, no frameworks when possible, and strong fundamentals.',
            'zenithkandel0@gmail.com',
            '+977 9806176120',
            'https://github.com/zenithkandel',
            'https://www.linkedin.com/in/zenithkandel',
            'https://instagram.com/kandel.zenith',
            ?
        )
    ");
    $stmt->execute([$defaultPassword]);

    // Insert default skills
    $skills = [
        ['HTML', 'fa-solid fa-code', 1],
        ['CSS', 'fa-solid fa-paintbrush', 2],
        ['JavaScript', 'fa-brands fa-js', 3],
        ['Node.js', 'fa-brands fa-node-js', 4],
        ['PHP', 'fa-solid fa-server', 5],
        ['MySQL', 'fa-solid fa-database', 6],
        ['MongoDB', 'fa-solid fa-leaf', 7],
        ['UI/UX', 'fa-solid fa-pen-ruler', 8],
        ['Responsive Design', 'fa-solid fa-mobile-screen', 9]
    ];

    $stmt = $pdo->prepare("INSERT IGNORE INTO skills (name, icon, sort_order) VALUES (?, ?, ?)");
    foreach ($skills as $skill) {
        $stmt->execute($skill);
    }

    // Insert default projects
    $projects = [
        ['STREAMFLIX', 'Movie streaming platform with chunked uploads, subtitles, and HLS processing.', 'PHP', 'Full-stack', 'https://github.com/zenithkandel/STREAMFLIX', 1],
        ['Kushma Art Project', 'Modern bilingual art site with gallery, events, and donation flows.', 'Showcase', 'Frontend', 'https://github.com/zenithkandel', 2],
        ['Rageni Agro Resort', 'Single-page resort website with parallax and theme switch experiments.', 'Landing', 'SPA', 'https://github.com/zenithkandel', 3],
        ['JavaScript Calculator', 'Compact, dependency-free calculator built with just HTML/CSS/JS.', 'HTML', 'JS', 'https://github.com/zenithkandel/Javascript-Calculator', 4],
        ['Random Color Generator', 'Generates random colors for design/dev workflows—simple and handy.', 'Utility', 'JS', 'https://github.com/zenithkandel/Random-Color-Generator', 5],
        ['CSS Login', 'Minimal login interface with basic client-side validation.', 'CSS', 'UI', 'https://github.com/zenithkandel/Css-Login', 6]
    ];

    $stmt = $pdo->prepare("INSERT IGNORE INTO projects (title, description, tag1, tag2, url, sort_order) VALUES (?, ?, ?, ?, ?, ?)");
    foreach ($projects as $project) {
        $stmt->execute($project);
    }

    echo "<!DOCTYPE html>
    <html>
    <head>
        <title>Setup Complete</title>
        <style>
            body { font-family: system-ui, sans-serif; max-width: 600px; margin: 100px auto; padding: 20px; }
            .success { background: #d4edda; color: #155724; padding: 20px; border-radius: 8px; margin-bottom: 20px; }
            .info { background: #e7f3ff; color: #004085; padding: 15px; border-radius: 8px; margin-bottom: 15px; }
            a { color: #007bff; }
            code { background: #f4f4f4; padding: 2px 6px; border-radius: 4px; }
        </style>
    </head>
    <body>
        <div class='success'>
            <h2>✓ Setup Complete!</h2>
            <p>Database and tables created successfully.</p>
        </div>
        <div class='info'>
            <strong>Admin Login:</strong><br>
            Password: <code>admin123</code><br>
            <small>Change this after first login!</small>
        </div>
        <p><a href='index.php'>View Portfolio →</a></p>
        <p><a href='admin/login.php'>Go to Admin →</a></p>
        <p style='color: #666; margin-top: 30px;'><strong>Security:</strong> Delete this file (setup.php) after setup.</p>
    </body>
    </html>";

} catch (PDOException $e) {
    die("<h1>Setup Failed</h1><p>Error: " . $e->getMessage() . "</p>");
}
