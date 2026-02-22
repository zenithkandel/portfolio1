<?php
require_once '../includes/config.php';
requireLogin();

$settings = getSettings($pdo);
$unreadCount = getUnreadMessagesCount($pdo);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $stmt = $pdo->prepare("
        UPDATE settings SET
            site_title = ?,
            site_description = ?,
            hero_tagline = ?,
            hero_title = ?,
            hero_subtitle = ?,
            about_text = ?,
            about_text_2 = ?,
            photo_url = ?,
            email = ?,
            phone = ?,
            github_url = ?,
            linkedin_url = ?,
            instagram_url = ?
        WHERE id = 1
    ");

    $stmt->execute([
        $_POST['site_title'] ?? '',
        $_POST['site_description'] ?? '',
        $_POST['hero_tagline'] ?? '',
        $_POST['hero_title'] ?? '',
        $_POST['hero_subtitle'] ?? '',
        $_POST['about_text'] ?? '',
        $_POST['about_text_2'] ?? '',
        $_POST['photo_url'] ?? 'me.jpg',
        $_POST['email'] ?? '',
        $_POST['phone'] ?? '',
        $_POST['github_url'] ?? '',
        $_POST['linkedin_url'] ?? '',
        $_POST['instagram_url'] ?? ''
    ]);

    // Handle password change
    if (!empty($_POST['new_password'])) {
        $newPassword = password_hash($_POST['new_password'], PASSWORD_DEFAULT);
        $pdo->prepare("UPDATE settings SET admin_password = ? WHERE id = 1")->execute([$newPassword]);
    }

    setFlash('success', 'Settings updated successfully!');
    header('Location: settings.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Site Settings - Admin</title>
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
            max-width: 900px;
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

        .btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 12px 24px;
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

        .card {
            background: var(--card);
            border-radius: 12px;
            border: 1px solid var(--border);
            margin-bottom: 24px;
        }

        .card-header {
            padding: 20px 24px;
            border-bottom: 1px solid var(--border);
        }

        .card-header h2 {
            font-size: 18px;
            font-weight: 600;
        }

        .card-header p {
            color: var(--text-muted);
            font-size: 14px;
            margin-top: 4px;
        }

        .card-body {
            padding: 24px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            font-size: 14px;
            font-weight: 500;
            margin-bottom: 8px;
            color: var(--text);
        }

        .form-group input,
        .form-group textarea {
            width: 100%;
            padding: 12px 14px;
            border: 1px solid var(--border);
            border-radius: 8px;
            font-size: 15px;
            font-family: inherit;
            transition: border-color 0.2s;
        }

        .form-group input:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: var(--accent);
        }

        .form-group textarea {
            resize: vertical;
            min-height: 100px;
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
        }

        .form-hint {
            color: var(--text-muted);
            font-size: 13px;
            margin-top: 6px;
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

        @media (max-width: 900px) {
            .sidebar {
                display: none;
            }

            .main {
                margin-left: 0;
            }

            .form-row {
                grid-template-columns: 1fr;
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
                <li><a href="index.php"><i class="fas fa-home"></i> Dashboard</a></li>
                <li><a href="settings.php" class="active"><i class="fas fa-sliders"></i> Site Settings</a></li>
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
                <h1>Site Settings</h1>
            </div>

            <form method="POST">
                <!-- Site Info -->
                <div class="card">
                    <div class="card-header">
                        <h2>Site Information</h2>
                        <p>Basic information about your portfolio</p>
                    </div>
                    <div class="card-body">
                        <div class="form-group">
                            <label for="site_title">Site Title</label>
                            <input type="text" id="site_title" name="site_title"
                                value="<?= e($settings['site_title'] ?? '') ?>">
                        </div>
                        <div class="form-group">
                            <label for="site_description">Meta Description</label>
                            <textarea id="site_description" name="site_description"
                                rows="2"><?= e($settings['site_description'] ?? '') ?></textarea>
                            <div class="form-hint">Shown in search engine results</div>
                        </div>
                    </div>
                </div>

                <!-- Hero Section -->
                <div class="card">
                    <div class="card-header">
                        <h2>Hero Section</h2>
                        <p>Main landing content visitors see first</p>
                    </div>
                    <div class="card-body">
                        <div class="form-group">
                            <label for="hero_tagline">Tagline</label>
                            <input type="text" id="hero_tagline" name="hero_tagline"
                                value="<?= e($settings['hero_tagline'] ?? '') ?>"
                                placeholder="e.g., Frontend Developer • Location">
                        </div>
                        <div class="form-group">
                            <label for="hero_title">Main Heading</label>
                            <input type="text" id="hero_title" name="hero_title"
                                value="<?= e($settings['hero_title'] ?? '') ?>"
                                placeholder="e.g., Hi, I'm Zenith Kandel.">
                        </div>
                        <div class="form-group">
                            <label for="hero_subtitle">Subtitle / Description</label>
                            <textarea id="hero_subtitle" name="hero_subtitle"
                                rows="3"><?= e($settings['hero_subtitle'] ?? '') ?></textarea>
                        </div>
                    </div>
                </div>

                <!-- About Section -->
                <div class="card">
                    <div class="card-header">
                        <h2>About Section</h2>
                        <p>Tell visitors about yourself</p>
                    </div>
                    <div class="card-body">
                        <div class="form-group">
                            <label for="about_text">About Text (Paragraph 1)</label>
                            <textarea id="about_text" name="about_text"
                                rows="4"><?= e($settings['about_text'] ?? '') ?></textarea>
                        </div>
                        <div class="form-group">
                            <label for="about_text_2">About Text (Paragraph 2) - Optional</label>
                            <textarea id="about_text_2" name="about_text_2"
                                rows="4"><?= e($settings['about_text_2'] ?? '') ?></textarea>
                        </div>
                        <div class="form-group">
                            <label for="photo_url">Photo Filename</label>
                            <input type="text" id="photo_url" name="photo_url"
                                value="<?= e($settings['photo_url'] ?? 'me.jpg') ?>">
                            <div class="form-hint">Place your photo in the portfolio folder (e.g., me.jpg)</div>
                        </div>
                    </div>
                </div>

                <!-- Contact Info -->
                <div class="card">
                    <div class="card-header">
                        <h2>Contact Information</h2>
                        <p>How visitors can reach you</p>
                    </div>
                    <div class="card-body">
                        <div class="form-row">
                            <div class="form-group">
                                <label for="email">Email</label>
                                <input type="email" id="email" name="email" value="<?= e($settings['email'] ?? '') ?>">
                            </div>
                            <div class="form-group">
                                <label for="phone">Phone</label>
                                <input type="text" id="phone" name="phone" value="<?= e($settings['phone'] ?? '') ?>">
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="github_url">GitHub URL</label>
                            <input type="url" id="github_url" name="github_url"
                                value="<?= e($settings['github_url'] ?? '') ?>">
                        </div>
                        <div class="form-group">
                            <label for="linkedin_url">LinkedIn URL</label>
                            <input type="url" id="linkedin_url" name="linkedin_url"
                                value="<?= e($settings['linkedin_url'] ?? '') ?>">
                        </div>
                        <div class="form-group">
                            <label for="instagram_url">Instagram URL</label>
                            <input type="url" id="instagram_url" name="instagram_url"
                                value="<?= e($settings['instagram_url'] ?? '') ?>">
                        </div>
                    </div>
                </div>

                <!-- Password -->
                <div class="card">
                    <div class="card-header">
                        <h2>Change Password</h2>
                        <p>Update your admin password</p>
                    </div>
                    <div class="card-body">
                        <div class="form-group">
                            <label for="new_password">New Password</label>
                            <input type="password" id="new_password" name="new_password">
                            <div class="form-hint">Leave blank to keep current password</div>
                        </div>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Save Changes
                </button>
            </form>
        </main>
    </div>
</body>

</html>