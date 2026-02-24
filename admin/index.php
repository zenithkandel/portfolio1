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
    <!-- FontAwesome Premium -->
    <link rel="stylesheet" href="https://site-assets.fontawesome.com/releases/v7.2.0/css/fontawesome.css">
    <link rel="stylesheet" href="https://site-assets.fontawesome.com/releases/v7.2.0/css/solid.css">
    <link rel="stylesheet" href="https://site-assets.fontawesome.com/releases/v7.2.0/css/regular.css">
    <link rel="stylesheet" href="https://site-assets.fontawesome.com/releases/v7.2.0/css/light.css">
    <link rel="stylesheet" href="https://site-assets.fontawesome.com/releases/v7.2.0/css/brands.css">
    <link rel="stylesheet" href="https://site-assets.fontawesome.com/releases/v7.2.0/css/duotone.css">
    <link rel="stylesheet" href="https://site-assets.fontawesome.com/releases/v7.2.0/css/thin.css">
    <link rel="stylesheet" href="assets/css/admin.css">
</head>

<body>
    <div class="layout">
        <!-- Sidebar -->
        <aside class="sidebar">
            <div class="sidebar-logo">
                <i class="fas fa-terminal"></i>
                Portfolio
            </div>

            <ul class="sidebar-nav">
                <li><a href="index.php" class="active"><i class="fas fa-home"></i> Dashboard</a></li>
                <li><a href="settings.php"><i class="fas fa-sliders"></i> Settings</a></li>
                <li><a href="skills.php"><i class="fas fa-code"></i> Skills</a></li>
                <li><a href="projects.php"><i class="fas fa-folder"></i> Projects</a></li>
                <li>
                    <a href="messages.php">
                        <i class="fas fa-envelope"></i> Messages
                        <?php if ($unreadCount > 0): ?>
                            <span class="nav-badge"><?= $unreadCount ?></span>
                        <?php endif; ?>
                    </a>
                </li>
            </ul>

            <div class="sidebar-divider"></div>

            <ul class="sidebar-nav">
                <li><a href="../index.php" target="_blank"><i class="fas fa-external-link-alt"></i> View Site</a></li>
                <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
            </ul>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <div class="page-header">
                <div>
                    <h1>Dashboard</h1>
                    <p>Welcome back! Here's your portfolio overview.</p>
                </div>
            </div>

            <!-- Stats -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon purple">
                        <i class="fas fa-folder"></i>
                    </div>
                    <div class="stat-value"><?= $projectCount ?></div>
                    <div class="stat-label">Projects</div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon green">
                        <i class="fas fa-code"></i>
                    </div>
                    <div class="stat-value"><?= $skillCount ?></div>
                    <div class="stat-label">Skills</div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon orange">
                        <i class="fas fa-envelope"></i>
                    </div>
                    <div class="stat-value"><?= $unreadCount ?></div>
                    <div class="stat-label">Unread Messages</div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon blue">
                        <i class="fas fa-eye"></i>
                    </div>
                    <div class="stat-value">Live</div>
                    <div class="stat-label">Site Status</div>
                </div>
            </div>

            <!-- Recent Messages -->
            <div class="card">
                <div class="card-header">
                    <h3><i class="fas fa-envelope"></i> Recent Messages</h3>
                    <a href="messages.php" class="btn btn-secondary btn-sm">View All</a>
                </div>
                <?php if (empty($recentMessages)): ?>
                    <div class="empty-state">
                        <i class="fas fa-inbox"></i>
                        <p>No messages yet</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($recentMessages as $msg): ?>
                        <div class="message-item <?= !$msg['is_read'] ? 'unread' : '' ?>">
                            <div class="message-header">
                                <div>
                                    <div class="message-sender"><?= e($msg['name']) ?></div>
                                    <div class="message-email"><?= e($msg['email']) ?></div>
                                </div>
                                <div class="message-time"><?= date('M j, g:i A', strtotime($msg['created_at'])) ?></div>
                            </div>
                            <div class="message-subject"><?= e($msg['subject'] ?: 'No subject') ?></div>
                            <div class="message-body"><?= e(substr($msg['message'], 0, 100)) ?>...</div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </main>
    </div>

    <button class="mobile-toggle" onclick="document.querySelector('.sidebar').classList.toggle('open')">
        <i class="fas fa-bars"></i>
    </button>
</body>

</html>
