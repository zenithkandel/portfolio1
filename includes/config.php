<?php
/**
 * Database Configuration
 * Update these settings for your XAMPP environment
 */

define('DB_HOST', 'localhost');
define('DB_NAME', 'portfolio_db');
define('DB_USER', 'root');
define('DB_PASS', '');

// Site configuration
define('SITE_URL', 'http://localhost/codes/portfolio');
define('ADMIN_URL', SITE_URL . '/admin');

// Session configuration
session_start();

// Error reporting (disable in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database connection
try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ]
    );
} catch (PDOException $e) {
    die("Database connection failed. Please run setup.php first. Error: " . $e->getMessage());
}

/**
 * Get site settings
 */
function getSettings($pdo)
{
    $stmt = $pdo->query("SELECT * FROM settings WHERE id = 1");
    return $stmt->fetch() ?: [];
}

/**
 * Get all skills
 */
function getSkills($pdo)
{
    $stmt = $pdo->query("SELECT * FROM skills ORDER BY sort_order ASC");
    return $stmt->fetchAll();
}

/**
 * Get all projects
 */
function getProjects($pdo)
{
    $stmt = $pdo->query("SELECT * FROM projects ORDER BY sort_order ASC");
    return $stmt->fetchAll();
}

/**
 * Get unread messages count
 */
function getUnreadMessagesCount($pdo)
{
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM messages WHERE is_read = 0");
    $result = $stmt->fetch();
    return $result['count'];
}

/**
 * Sanitize output
 */
function e($string)
{
    return htmlspecialchars($string ?? '', ENT_QUOTES, 'UTF-8');
}

/**
 * Check if user is logged in
 */
function isLoggedIn()
{
    return isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;
}

/**
 * Require login for admin pages
 */
function requireLogin()
{
    if (!isLoggedIn()) {
        header('Location: login.php');
        exit;
    }
}

/**
 * Flash messages
 */
function setFlash($type, $message)
{
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function getFlash()
{
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}
