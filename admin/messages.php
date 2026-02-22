<?php
require_once '../includes/config.php';
requireLogin();

$unreadCount = getUnreadMessagesCount($pdo);

// Handle actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $id = (int) ($_POST['id'] ?? 0);

    if ($action === 'mark_read' && $id) {
        $pdo->prepare("UPDATE messages SET is_read = 1 WHERE id = ?")->execute([$id]);
    }

    if ($action === 'mark_unread' && $id) {
        $pdo->prepare("UPDATE messages SET is_read = 0 WHERE id = ?")->execute([$id]);
    }

    if ($action === 'delete' && $id) {
        $pdo->prepare("DELETE FROM messages WHERE id = ?")->execute([$id]);
        setFlash('success', 'Message deleted successfully!');
    }

    if ($action === 'mark_all_read') {
        $pdo->exec("UPDATE messages SET is_read = 1");
        setFlash('success', 'All messages marked as read!');
    }

    header('Location: messages.php');
    exit;
}

// Get all messages
$stmt = $pdo->query("SELECT * FROM messages ORDER BY created_at DESC");
$messages = $stmt->fetchAll();

// View single message
$viewMessage = null;
if (isset($_GET['view'])) {
    $id = (int) $_GET['view'];
    $stmt = $pdo->prepare("SELECT * FROM messages WHERE id = ?");
    $stmt->execute([$id]);
    $viewMessage = $stmt->fetch();

    // Mark as read
    if ($viewMessage && !$viewMessage['is_read']) {
        $pdo->prepare("UPDATE messages SET is_read = 1 WHERE id = ?")->execute([$id]);
        $viewMessage['is_read'] = 1;
        $unreadCount = max(0, $unreadCount - 1);
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Messages - Admin</title>
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
            flex-wrap: wrap;
            gap: 16px;
        }

        .header h1 {
            font-size: 28px;
            font-weight: 700;
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

        .btn-danger {
            background: #fee2e2;
            color: #dc2626;
        }

        .btn-danger:hover {
            background: #dc2626;
            color: white;
        }

        .btn-sm {
            padding: 8px 14px;
            font-size: 13px;
        }

        .messages-grid {
            display: grid;
            grid-template-columns: 400px 1fr;
            gap: 24px;
        }

        .message-list {
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: 12px;
            overflow: hidden;
        }

        .message-list-header {
            padding: 16px 20px;
            border-bottom: 1px solid var(--border);
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .message-list-header h2 {
            font-size: 16px;
            font-weight: 600;
        }

        .message-item {
            display: flex;
            gap: 14px;
            padding: 16px 20px;
            border-bottom: 1px solid var(--border);
            cursor: pointer;
            transition: background 0.2s;
            text-decoration: none;
            color: inherit;
        }

        .message-item:hover {
            background: var(--bg);
        }

        .message-item.active {
            background: #eff6ff;
            border-left: 3px solid var(--accent);
        }

        .message-item.unread {
            background: #fefce8;
        }

        .message-item.unread .message-name {
            font-weight: 700;
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

        .message-preview {
            flex: 1;
            min-width: 0;
        }

        .message-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 4px;
        }

        .message-name {
            font-size: 15px;
        }

        .message-time {
            color: var(--text-muted);
            font-size: 12px;
        }

        .message-subject {
            color: var(--text-muted);
            font-size: 13px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .message-view {
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: 12px;
        }

        .message-view-header {
            padding: 24px;
            border-bottom: 1px solid var(--border);
        }

        .message-view-header h2 {
            font-size: 20px;
            font-weight: 600;
            margin-bottom: 16px;
        }

        .message-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            color: var(--text-muted);
            font-size: 14px;
        }

        .message-meta span {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .message-view-body {
            padding: 24px;
        }

        .message-content {
            white-space: pre-wrap;
            line-height: 1.7;
            font-size: 15px;
        }

        .message-view-actions {
            padding: 20px 24px;
            border-top: 1px solid var(--border);
            display: flex;
            gap: 12px;
        }

        .empty-state {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 60px;
            text-align: center;
        }

        .empty-state i {
            font-size: 48px;
            color: var(--border);
            margin-bottom: 16px;
        }

        .empty-state p {
            color: var(--text-muted);
        }

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

        @media (max-width: 1100px) {
            .messages-grid {
                grid-template-columns: 1fr;
            }

            .message-view {
                display:
                    <?= $viewMessage ? 'block' : 'none' ?>
                ;
            }
        }

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
        <aside class="sidebar">
            <div class="sidebar-logo"><i class="fas fa-terminal"></i> Portfolio Admin</div>
            <ul class="sidebar-nav">
                <li><a href="index.php"><i class="fas fa-home"></i> Dashboard</a></li>
                <li><a href="settings.php"><i class="fas fa-sliders"></i> Site Settings</a></li>
                <li><a href="skills.php"><i class="fas fa-code"></i> Skills</a></li>
                <li><a href="projects.php"><i class="fas fa-folder"></i> Projects</a></li>
                <li><a href="messages.php" class="active"><i class="fas fa-envelope"></i> Messages
                        <?php if ($unreadCount > 0): ?><span class="badge"><?= $unreadCount ?></span><?php endif; ?></a>
                </li>
            </ul>
            <div class="sidebar-footer">
                <a href="../index.php" target="_blank"><i class="fas fa-external-link-alt"></i> View Site</a>
                <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </div>
        </aside>

        <main class="main">
            <?php if ($flash = getFlash()): ?>
                <div class="flash flash-<?= $flash['type'] ?>"><?= e($flash['message']) ?></div>
            <?php endif; ?>

            <div class="header">
                <h1>Messages</h1>
                <?php if (!empty($messages)): ?>
                    <form method="POST" style="display: inline;">
                        <input type="hidden" name="action" value="mark_all_read">
                        <button type="submit" class="btn btn-secondary">
                            <i class="fas fa-check-double"></i> Mark All Read
                        </button>
                    </form>
                <?php endif; ?>
            </div>

            <?php if (empty($messages)): ?>
                <div class="message-list">
                    <div class="empty-state">
                        <i class="fas fa-inbox"></i>
                        <p>No messages yet. Messages from your contact form will appear here.</p>
                    </div>
                </div>
            <?php else: ?>
                <div class="messages-grid">
                    <!-- Message List -->
                    <div class="message-list">
                        <div class="message-list-header">
                            <h2>All Messages (<?= count($messages) ?>)</h2>
                        </div>
                        <?php foreach ($messages as $msg): ?>
                            <a href="?view=<?= $msg['id'] ?>"
                                class="message-item <?= !$msg['is_read'] ? 'unread' : '' ?> <?= ($viewMessage && $viewMessage['id'] == $msg['id']) ? 'active' : '' ?>">
                                <div class="message-avatar">
                                    <?= strtoupper(substr($msg['name'], 0, 1)) ?>
                                </div>
                                <div class="message-preview">
                                    <div class="message-header">
                                        <span class="message-name"><?= e($msg['name']) ?></span>
                                        <span class="message-time"><?= date('M j', strtotime($msg['created_at'])) ?></span>
                                    </div>
                                    <div class="message-subject"><?= e($msg['subject'] ?: substr($msg['message'], 0, 50)) ?>
                                    </div>
                                </div>
                            </a>
                        <?php endforeach; ?>
                    </div>

                    <!-- Message View -->
                    <div class="message-view">
                        <?php if ($viewMessage): ?>
                            <div class="message-view-header">
                                <h2><?= e($viewMessage['subject'] ?: 'No Subject') ?></h2>
                                <div class="message-meta">
                                    <span><i class="fas fa-user"></i> <?= e($viewMessage['name']) ?></span>
                                    <span><i class="fas fa-envelope"></i> <a
                                            href="mailto:<?= e($viewMessage['email']) ?>"><?= e($viewMessage['email']) ?></a></span>
                                    <span><i class="fas fa-clock"></i>
                                        <?= date('F j, Y \a\t g:i A', strtotime($viewMessage['created_at'])) ?></span>
                                </div>
                            </div>
                            <div class="message-view-body">
                                <div class="message-content"><?= e($viewMessage['message']) ?></div>
                            </div>
                            <div class="message-view-actions">
                                <a href="mailto:<?= e($viewMessage['email']) ?>?subject=Re: <?= e($viewMessage['subject']) ?>"
                                    class="btn btn-primary">
                                    <i class="fas fa-reply"></i> Reply
                                </a>
                                <form method="POST" style="display: inline;">
                                    <input type="hidden" name="action"
                                        value="<?= $viewMessage['is_read'] ? 'mark_unread' : 'mark_read' ?>">
                                    <input type="hidden" name="id" value="<?= $viewMessage['id'] ?>">
                                    <button type="submit" class="btn btn-secondary">
                                        <i class="fas fa-<?= $viewMessage['is_read'] ? 'envelope' : 'envelope-open' ?>"></i>
                                        Mark as <?= $viewMessage['is_read'] ? 'Unread' : 'Read' ?>
                                    </button>
                                </form>
                                <form method="POST" style="display: inline;" onsubmit="return confirm('Delete this message?')">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="id" value="<?= $viewMessage['id'] ?>">
                                    <button type="submit" class="btn btn-danger">
                                        <i class="fas fa-trash"></i> Delete
                                    </button>
                                </form>
                            </div>
                        <?php else: ?>
                            <div class="empty-state">
                                <i class="fas fa-envelope-open-text"></i>
                                <p>Select a message to view</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>
        </main>
    </div>
</body>

</html>