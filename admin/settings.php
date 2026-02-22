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
            instagram_url = ?,
            facebook_url = ?,
            whatsapp = ?
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
        $_POST['instagram_url'] ?? '',
        $_POST['facebook_url'] ?? '',
        $_POST['whatsapp'] ?? ''
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
    <title>Settings - Admin</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
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
                <li><a href="index.php"><i class="fas fa-home"></i> Dashboard</a></li>
                <li><a href="settings.php" class="active"><i class="fas fa-sliders"></i> Settings</a></li>
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
                    <h1>Site Settings</h1>
                    <p>Manage your portfolio content and preferences</p>
                </div>
            </div>

            <?php if ($flash = getFlash()): ?>
                <div class="alert alert-<?= $flash['type'] ?>">
                    <i class="fas fa-check-circle"></i>
                    <?= e($flash['message']) ?>
                </div>
            <?php endif; ?>

            <form method="POST">
                <!-- General Settings -->
                <div class="card">
                    <div class="card-header">
                        <h3>General Settings</h3>
                    </div>
                    <div class="form-group">
                        <label>Site Title</label>
                        <input type="text" name="site_title" value="<?= e($settings['site_title'] ?? '') ?>"
                            placeholder="Portfolio | Your Name">
                    </div>
                    <div class="form-group">
                        <label>Site Description</label>
                        <textarea name="site_description"
                            rows="2"><?= e($settings['site_description'] ?? '') ?></textarea>
                        <div class="form-hint">Shown in search engine results</div>
                    </div>
                </div>

                <!-- Hero Section -->
                <div class="card">
                    <div class="card-header">
                        <h3>Hero Section</h3>
                    </div>
                    <div class="form-group">
                        <label>Tagline</label>
                        <input type="text" name="hero_tagline" value="<?= e($settings['hero_tagline'] ?? '') ?>"
                            placeholder="e.g., Frontend Developer • Location">
                    </div>
                    <div class="form-group">
                        <label>Main Heading (Your Name)</label>
                        <input type="text" name="hero_title" value="<?= e($settings['hero_title'] ?? '') ?>"
                            placeholder="e.g., John Doe">
                    </div>
                    <div class="form-group">
                        <label>Subtitle / Role</label>
                        <textarea name="hero_subtitle" rows="2"><?= e($settings['hero_subtitle'] ?? '') ?></textarea>
                    </div>
                </div>

                <!-- About Section -->
                <div class="card">
                    <div class="card-header">
                        <h3>About Section</h3>
                    </div>
                    <div class="form-group">
                        <label>About Text (Paragraph 1)</label>
                        <textarea name="about_text" rows="4"><?= e($settings['about_text'] ?? '') ?></textarea>
                    </div>
                    <div class="form-group">
                        <label>About Text (Paragraph 2) - Optional</label>
                        <textarea name="about_text_2" rows="4"><?= e($settings['about_text_2'] ?? '') ?></textarea>
                    </div>
                    <div class="form-group">
                        <label>Photo Filename</label>
                        <input type="text" name="photo_url" value="<?= e($settings['photo_url'] ?? 'me.jpg') ?>">
                        <div class="form-hint">Place your photo in the portfolio folder (e.g., me.jpg)</div>
                    </div>
                </div>

                <!-- Contact Info -->
                <div class="card">
                    <div class="card-header">
                        <h3>Contact Information</h3>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label>Email</label>
                            <input type="email" name="email" value="<?= e($settings['email'] ?? '') ?>">
                        </div>
                        <div class="form-group">
                            <label>Phone</label>
                            <input type="text" name="phone" value="<?= e($settings['phone'] ?? '') ?>">
                        </div>
                    </div>
                    <div class="form-group">
                        <label>GitHub URL</label>
                        <input type="url" name="github_url" value="<?= e($settings['github_url'] ?? '') ?>">
                    </div>
                    <div class="form-group">
                        <label>LinkedIn URL</label>
                        <input type="url" name="linkedin_url" value="<?= e($settings['linkedin_url'] ?? '') ?>">
                    </div>
                    <div class="form-group">
                        <label>Instagram URL</label>
                        <input type="url" name="instagram_url" value="<?= e($settings['instagram_url'] ?? '') ?>">
                    </div>
                    <div class="form-group">
                        <label>Facebook URL</label>
                        <input type="url" name="facebook_url" value="<?= e($settings['facebook_url'] ?? '') ?>">
                    </div>
                    <div class="form-group">
                        <label>WhatsApp Number</label>
                        <input type="text" name="whatsapp" value="<?= e($settings['whatsapp'] ?? '') ?>" placeholder="e.g., 9806176120">
                        <div class="form-hint">Just the number, without country code prefix</div>
                    </div>
                </div>

                <!-- Password -->
                <div class="card">
                    <div class="card-header">
                        <h3>Change Password</h3>
                    </div>
                    <div class="form-group">
                        <label>New Password</label>
                        <input type="password" name="new_password">
                        <div class="form-hint">Leave blank to keep current password</div>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Save Changes
                </button>
            </form>
        </main>
    </div>

    <button class="mobile-toggle" onclick="document.querySelector('.sidebar').classList.toggle('open')">
        <i class="fas fa-bars"></i>
    </button>
</body>

</html>