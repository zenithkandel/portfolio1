<?php
/**
 * Migration: Add image column to projects table
 */

$host = 'localhost';
$user = 'root';
$pass = '';
$dbname = 'portfolio_db';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
    
    // Check if column already exists
    $stmt = $pdo->query("SHOW COLUMNS FROM projects LIKE 'image'");
    if ($stmt->rowCount() == 0) {
        $pdo->exec("ALTER TABLE projects ADD COLUMN image VARCHAR(255) DEFAULT NULL AFTER description");
        echo "SUCCESS: Added 'image' column to projects table.\n";
    } else {
        echo "INFO: 'image' column already exists.\n";
    }
    
    echo "\nCurrent projects table structure:\n";
    $cols = $pdo->query("DESCRIBE projects")->fetchAll(PDO::FETCH_ASSOC);
    foreach ($cols as $col) {
        echo "  - " . $col['Field'] . " (" . $col['Type'] . ")\n";
    }
    
} catch (PDOException $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
