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
    <link rel="stylesheet" href="assets/css/admin.css">
    <style>
        .messages-grid {
            display: grid;
            grid-template-columns: 350px 1fr;
            gap: 24px;
            height: calc(100vh - 180px);
        }

        .message-list {
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: 12px;
            overflow: hidden;
            display: flex;
            flex-direction: column;
        }

        .message-list-header {
            padding: 16px 20px;
            border-bottom: 1px solid var(--border);
        }

        .message-list-header h2 {
            font-size: 14px;
            font-weight: 600;
            color: var(--text-muted);
        }

        .message-list-items {
            flex: 1;
            overflow-y: auto;
        }

        .msg-item {
            display: flex;
            align-items: flex-start;
            gap: 12px;
            padding: 16px 20px;
            border-bottom: 1px solid var(--border);
            text-decoration: none;
            color: var(--text);
            transition: background 0.2s;
        }

        .msg-item:hover {
            background: var(--bg-elevated);
        }

        .msg-item.active {
            background: var(--accent-dim);
            border-left: 3px solid var(--accent);
        }

        .msg-item.unread .msg-name {
            font-weight: 600;
        }

        .msg-item.unread::before {
            content: '';
            position: absolute;
            left: 8px;
            width: 8px;
            height: 8px;
            background: var(--accent);
            border-radius: 50%;
        }

        .msg-avatar {
            width: 40px;
            height: 40px;
            min-width: 40px;
            border-radius: 50%;
            background: var(--accent-dim);
            color: var(--accent);
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            font-size: 14px;
        }

        .msg-info {
            flex: 1;
            min-width: 0;
        }

        .msg-header {
            display: flex;
            justify-content: space-between;
            margin-bottom: 4px;
        }

        .msg-name {
            font-size: 14px;
        }

        .msg-time {
            font-size: 12px;
            color: var(--text-dim);
        }

        .msg-subject {
            font-size: 13px;
            color: var(--text-muted);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .message-view {
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: 12px;
            display: flex;
            flex-direction: column;
        }

        .message-view-header {
            padding: 24px;
            border-bottom: 1px solid var(--border);
        }

        .message-view-header h2 {
            font-size: 20px;
            margin-bottom: 12px;
        }

        .message-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 16px;
            font-size: 13px;
            color: var(--text-muted);
        }

        .message-meta i {
            margin-right: 6px;
            color: var(--text-dim);
        }

        .message-meta a {
            color: var(--accent);
            text-decoration: none;
        }

        .message-view-body {
            flex: 1;
            padding: 24px;
            overflow-y: auto;
        }

        .message-content {
            font-size: 15px;
            line-height: 1.7;
            color: var(--text);
            white-space: pre-wrap;
        }

        .message-view-actions {
            padding: 20px 24px;
            border-top: 1px solid var(--border);
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
        }

        @media (max-width: 1100px) {
            .messages-grid {
                grid-template-columns: 1fr;
                height: auto;
            }

            .message-view {
                display: <?= $viewMessage ? 'flex' : 'none' ?>;
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
                Portfolio
            </div>

            <ul class="sidebar-nav">
                <li><a href="index.php"><i class="fas fa-home"></i> Dashboard</a></li>
                <li><a href="settings.php"><i class="fas fa-sliders"></i> Settings</a></li>
                <li><a href="skills.php"><i class="fas fa-code"></i> Skills</a></li>
                <li><a href="projects.php"><i class="fas fa-folder"></i> Projects</a></li>
                <li>
                    <a href="messages.php" class="active">
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
                    <h1>Messages</h1>
                    <p>View and manage contact form submissions</p>
                </div>
                <?php if (!empty($messages)): ?>
                    <form method="POST" style="display: inline;">
                        <input type="hidden" name="action" value="mark_all_read">
                        <button type="submit" class="btn btn-secondary">
                            <i class="fas fa-check-double"></i> Mark All Read
                        </button>
                    </form>
                <?php endif; ?>
            </div>

            <?php if ($flash = getFlash()): ?>
                <div class="alert alert-<?= $flash['type'] ?>">
                    <i class="fas fa-check-circle"></i>
                    <?= e($flash['message']) ?>
                </div>
            <?php endif; ?>

            <?php if (empty($messages)): ?>
                <div class="empty-state">
                    <i class="fas fa-inbox"></i>
                    <p>No messages yet. Messages from your contact form will appear here.</p>
                </div>
            <?php else: ?>
                <div class="messages-grid">
                    <!-- Message List -->
                    <div class="message-list">
                        <div class="message-list-header">
                            <h2>All Messages (<?= count($messages) ?>)</h2>
                        </div>
                        <div class="message-list-items">
                            <?php foreach ($messages as $msg): ?>
                                <a href="?view=<?= $msg['id'] ?>" class="msg-item <?= !$msg['is_read'] ? 'unread' : '' ?> <?= ($viewMessage && $viewMessage['id'] == $msg['id']) ? 'active' : '' ?>">
                                    <div class="msg-avatar">
                                        <?= strtoupper(substr($msg['name'], 0, 1)) ?>
                                    </div>
                                    <div class="msg-info">
                                        <div class="msg-header">
                                            <span class="msg-name"><?= e($msg['name']) ?></span>
                                            <span class="msg-time"><?= date('M j', strtotime($msg['created_at'])) ?></span>
                                        </div>
                                        <div class="msg-subject"><?= e($msg['subject'] ?: substr($msg['message'], 0, 50)) ?></div>
                                    </div>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- Message View -->
                    <div class="message-view">
                        <?php if ($viewMessage): ?>
                            <div class="message-view-header">
                                <h2><?= e($viewMessage['subject'] ?: 'No Subject') ?></h2>
                                <div class="message-meta">
                                    <span><i class="fas fa-user"></i> <?= e($viewMessage['name']) ?></span>
                                    <span><i class="fas fa-envelope"></i> <a href="mailto:<?= e($viewMessage['email']) ?>"><?= e($viewMessage['email']) ?></a></span>
                                    <span><i class="fas fa-clock"></i> <?= date('F j, Y \a\t g:i A', strtotime($viewMessage['created_at'])) ?></span>
                                </div>
                            </div>
                            <div class="message-view-body">
                                <div class="message-content"><?= e($viewMessage['message']) ?></div>
                            </div>
                            <div class="message-view-actions">
                                <a href="mailto:<?= e($viewMessage['email']) ?>?subject=Re: <?= e($viewMessage['subject']) ?>" class="btn btn-primary">
                                    <i class="fas fa-reply"></i> Reply
                                </a>
                                <form method="POST" style="display: inline;">
                                    <input type="hidden" name="action" value="<?= $viewMessage['is_read'] ? 'mark_unread' : 'mark_read' ?>">
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
                            <div class="empty-state" style="flex:1; display:flex; flex-direction:column; justify-content:center;">
                                <i class="fas fa-envelope-open-text"></i>
                                <p>Select a message to view</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>
        </main>
    </div>

    <button class="mobile-toggle" onclick="document.querySelector('.sidebar').classList.toggle('open')">
        <i class="fas fa-bars"></i>
    </button>
</body>

</html>
