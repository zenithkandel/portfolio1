<?php
require_once '../includes/config.php';
requireLogin();

$settings = getSettings($pdo);
$skillCount = count(getSkills($pdo));
$projectCount = count(getProjects($pdo));
$unreadCount = getUnreadMessagesCount($pdo);

// Get recent messages
$stmt = $pdo->query("SELECT * FROM messages ORDER BY created_at DESC LIMIT 5");
$recentMessages = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin Dashboard</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        *,
        *::before,
        *::after {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        :root {
            --sidebar-width: 260px;
            --accent: #2563eb;
            --accent-hover: #1d4ed8;
            --text: #1a1a1a;
            --text-muted: #666;
            --border: #e5e5e5;
            --bg: #f5f5f5;
            --card: #fff;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: var(--bg);
            color: var(--text);
            line-height: 1.6;
        }

        .layout {
            display: flex;
            min-height: 100vh;
        }

        /* Sidebar */
        .sidebar {
            width: var(--sidebar-width);
            background: #1a1a1a;
            color: white;
            padding: 24px;
            position: fixed;
            height: 100vh;
            overflow-y: auto;
        }

        .sidebar-logo {
            font-size: 20px;
            font-weight: 700;
            margin-bottom: 40px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .sidebar-nav {
            list-style: none;
        }

        .sidebar-nav li {
            margin-bottom: 4px;
        }

        .sidebar-nav a {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 16px;
            color: rgba(255, 255, 255, 0.7);
            text-decoration: none;
            border-radius: 8px;
            font-size: 15px;
            transition: all 0.2s;
        }

        .sidebar-nav a:hover,
        .sidebar-nav a.active {
            background: rgba(255, 255, 255, 0.1);
            color: white;
        }

        .sidebar-nav a i {
            width: 20px;
            text-align: center;
        }

        .sidebar-nav .badge {
            margin-left: auto;
            background: #dc2626;
            color: white;
            font-size: 11px;
            font-weight: 600;
            padding: 2px 8px;
            border-radius: 10px;
        }

        .sidebar-footer {
            position: absolute;
            bottom: 24px;
            left: 24px;
            right: 24px;
        }

        .sidebar-footer a {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 12px 16px;
            color: rgba(255, 255, 255, 0.5);
            text-decoration: none;
            font-size: 14px;
            border-radius: 8px;
            transition: all 0.2s;
        }

        .sidebar-footer a:hover {
            color: white;
            background: rgba(255, 255, 255, 0.1);
        }

        /* Main Content */
        .main {
            flex: 1;
            margin-left: var(--sidebar-width);
            padding: 32px;
        }

        .header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 32px;
        }

        .header h1 {
            font-size: 28px;
            font-weight: 700;
        }

        .header-actions {
            display: flex;
            gap: 12px;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 20px;
            font-size: 14px;
            font-weight: 600;
            border-radius: 8px;
            text-decoration: none;
            transition: all 0.2s;
            cursor: pointer;
            border: none;
        }

        .btn-primary {
            background: var(--accent);
            color: white;
        }

        .btn-primary:hover {
            background: var(--accent-hover);
        }

        .btn-secondary {
            background: white;
            color: var(--text);
            border: 1px solid var(--border);
        }

        .btn-secondary:hover {
            border-color: var(--accent);
            color: var(--accent);
        }

        /* Stats Grid */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 32px;
        }

        .stat-card {
            background: var(--card);
            border-radius: 12px;
            padding: 24px;
            border: 1px solid var(--border);
        }

        .stat-card .icon {
            width: 48px;
            height: 48px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            margin-bottom: 16px;
        }

        .stat-card .icon.blue {
            background: #dbeafe;
            color: #2563eb;
        }

        .stat-card .icon.green {
            background: #dcfce7;
            color: #16a34a;
        }

        .stat-card .icon.purple {
            background: #f3e8ff;
            color: #9333ea;
        }

        .stat-card .icon.orange {
            background: #ffedd5;
            color: #ea580c;
        }

        .stat-card h3 {
            font-size: 32px;
            font-weight: 700;
            margin-bottom: 4px;
        }

        .stat-card p {
            color: var(--text-muted);
            font-size: 14px;
        }

        /* Card */
        .card {
            background: var(--card);
            border-radius: 12px;
            border: 1px solid var(--border);
            margin-bottom: 24px;
        }

        .card-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 20px 24px;
            border-bottom: 1px solid var(--border);
        }

        .card-header h2 {
            font-size: 18px;
            font-weight: 600;
        }

        .card-body {
            padding: 24px;
        }

        /* Messages List */
        .message-item {
            display: flex;
            gap: 16px;
            padding: 16px 0;
            border-bottom: 1px solid var(--border);
        }

        .message-item:last-child {
            border-bottom: none;
        }

        .message-avatar {
            width: 40px;
            height: 40px;
            background: var(--accent);
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            font-size: 14px;
            flex-shrink: 0;
        }

        .message-content {
            flex: 1;
            min-width: 0;
        }

        .message-header {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 4px;
        }

        .message-name {
            font-weight: 600;
            font-size: 15px;
        }

        .message-time {
            color: var(--text-muted);
            font-size: 13px;
        }

        .message-subject {
            color: var(--text-muted);
            font-size: 14px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .unread-badge {
            display: inline-block;
            width: 8px;
            height: 8px;
            background: var(--accent);
            border-radius: 50%;
            margin-left: 8px;
        }

        .empty-state {
            text-align: center;
            padding: 40px;
            color: var(--text-muted);
        }

        .empty-state i {
            font-size: 48px;
            margin-bottom: 16px;
            opacity: 0.3;
        }

        /* Flash Messages */
        .flash {
            padding: 16px 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 15px;
        }

        .flash-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .flash-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        /* Responsive */
        @media (max-width: 900px) {
            .sidebar {
                display: none;
            }

            .main {
                margin-left: 0;
            }
        }
    </style>
</head>

<body>
    <div class="layout">
        <!-- Sidebar -->
        <aside class="sidebar">
            <div class="sidebar-logo">
                <i class="fas fa-terminal"></i>
                Portfolio Admin
            </div>

            <ul class="sidebar-nav">
                <li><a href="index.php" class="active"><i class="fas fa-home"></i> Dashboard</a></li>
                <li><a href="settings.php"><i class="fas fa-sliders"></i> Site Settings</a></li>
                <li><a href="skills.php"><i class="fas fa-code"></i> Skills</a></li>
                <li><a href="projects.php"><i class="fas fa-folder"></i> Projects</a></li>
                <li>
                    <a href="messages.php">
                        <i class="fas fa-envelope"></i> Messages
                        <?php if ($unreadCount > 0): ?>
                            <span class="badge"><?= $unreadCount ?></span>
                        <?php endif; ?>
                    </a>
                </li>
            </ul>

            <div class="sidebar-footer">
                <a href="../index.php" target="_blank"><i class="fas fa-external-link-alt"></i> View Site</a>
                <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </div>
        </aside>

        <!-- Main Content -->
        <main class="main">
            <?php if ($flash = getFlash()): ?>
                <div class="flash flash-<?= $flash['type'] ?>"><?= e($flash['message']) ?></div>
            <?php endif; ?>

            <div class="header">
                <h1>Dashboard</h1>
                <div class="header-actions">
                    <a href="settings.php" class="btn btn-primary">
                        <i class="fas fa-edit"></i> Edit Profile
                    </a>
                </div>
            </div>

            <!-- Stats -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="icon blue"><i class="fas fa-code"></i></div>
                    <h3><?= $skillCount ?></h3>
                    <p>Skills</p>
                </div>
                <div class="stat-card">
                    <div class="icon green"><i class="fas fa-folder"></i></div>
                    <h3><?= $projectCount ?></h3>
                    <p>Projects</p>
                </div>
                <div class="stat-card">
                    <div class="icon purple"><i class="fas fa-envelope"></i></div>
                    <h3><?= $unreadCount ?></h3>
                    <p>Unread Messages</p>
                </div>
            </div>

            <!-- Recent Messages -->
            <div class="card">
                <div class="card-header">
                    <h2>Recent Messages</h2>
                    <a href="messages.php" class="btn btn-secondary">View All</a>
                </div>
                <div class="card-body">
                    <?php if (empty($recentMessages)): ?>
                        <div class="empty-state">
                            <i class="fas fa-inbox"></i>
                            <p>No messages yet</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($recentMessages as $msg): ?>
                            <div class="message-item">
                                <div class="message-avatar">
                                    <?= strtoupper(substr($msg['name'], 0, 1)) ?>
                                </div>
                                <div class="message-content">
                                    <div class="message-header">
                                        <span class="message-name">
                                            <?= e($msg['name']) ?>
                                            <?php if (!$msg['is_read']): ?>
                                                <span class="unread-badge"></span>
                                            <?php endif; ?>
                                        </span>
                                        <span class="message-time">
                                            <?= date('M j, g:i A', strtotime($msg['created_at'])) ?>
                                        </span>
                                    </div>
                                    <div class="message-subject">
                                        <?= e($msg['subject'] ?: 'No subject') ?> — <?= e(substr($msg['message'], 0, 60)) ?>...
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>
</body>

</html>