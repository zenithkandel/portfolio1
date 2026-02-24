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
    <!-- FontAwesome Premium -->
    <link rel="stylesheet" href="https://site-assets.fontawesome.com/releases/v7.2.0/css/fontawesome.css">
    <link rel="stylesheet" href="https://site-assets.fontawesome.com/releases/v7.2.0/css/solid.css">
    <link rel="stylesheet" href="https://site-assets.fontawesome.com/releases/v7.2.0/css/regular.css">
    <link rel="stylesheet" href="https://site-assets.fontawesome.com/releases/v7.2.0/css/light.css">
    <link rel="stylesheet" href="https://site-assets.fontawesome.com/releases/v7.2.0/css/brands.css">
    <link rel="stylesheet" href="https://site-assets.fontawesome.com/releases/v7.2.0/css/duotone.css">
    <link rel="stylesheet" href="https://site-assets.fontawesome.com/releases/v7.2.0/css/thin.css">
    <link rel="stylesheet" href="assets/css/admin.css">
    <style>
        .settings-layout { display: grid; grid-template-columns: 220px 1fr; gap: 32px; }
        
        .settings-nav { position: sticky; top: 32px; }
        .settings-nav-item { display: flex; align-items: center; gap: 12px; padding: 12px 16px; border-radius: 8px; color: var(--text-muted); cursor: pointer; transition: all 0.2s; margin-bottom: 4px; border: none; background: none; width: 100%; text-align: left; font-size: 14px; }
        .settings-nav-item:hover { background: var(--card); color: var(--text); }
        .settings-nav-item.active { background: var(--accent-dim); color: var(--accent); }
        .settings-nav-item i { width: 18px; }
        
        .settings-section { display: none; }
        .settings-section.active { display: block; }
        
        .section-header { margin-bottom: 24px; padding-bottom: 16px; border-bottom: 1px solid var(--border); }
        .section-header h2 { font-size: 20px; font-weight: 600; margin-bottom: 4px; }
        .section-header p { color: var(--text-muted); font-size: 14px; }
        
        .form-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 20px; }
        .form-grid .form-group.full { grid-column: 1 / -1; }
        
        .photo-upload { display: flex; align-items: flex-start; gap: 20px; }
        .photo-current { width: 120px; height: 120px; border-radius: 12px; overflow: hidden; background: var(--bg); flex-shrink: 0; }
        .photo-current img { width: 100%; height: 100%; object-fit: cover; }
        .photo-actions { flex: 1; }
        .photo-actions p { color: var(--text-muted); font-size: 13px; margin-bottom: 12px; }
        
        .social-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 16px; }
        .social-item { display: flex; align-items: center; gap: 12px; background: var(--bg); padding: 12px 16px; border-radius: 10px; border: 1px solid var(--border); }
        .social-item i { font-size: 18px; color: var(--accent); width: 24px; text-align: center; }
        .social-item input { flex: 1; background: none; border: none; color: var(--text); font-size: 14px; outline: none; }
        .social-item input::placeholder { color: var(--text-dim); }
        
        .save-bar { position: sticky; bottom: 0; background: linear-gradient(transparent, var(--bg) 20%); padding: 24px 0 0; margin-top: 32px; }
        .save-bar .btn { width: 100%; padding: 14px; font-size: 15px; }
        
        @media (max-width: 900px) {
            .settings-layout { grid-template-columns: 1fr; }
            .settings-nav { position: static; display: flex; flex-wrap: wrap; gap: 8px; margin-bottom: 24px; }
            .settings-nav-item { flex: 1; min-width: 100px; justify-content: center; margin-bottom: 0; }
            .form-grid, .social-grid { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>
    <div class="layout">
        <aside class="sidebar">
            <div class="sidebar-logo"><i class="fas fa-terminal"></i> Portfolio</div>
            <ul class="sidebar-nav">
                <li><a href="index.php"><i class="fas fa-home"></i> Dashboard</a></li>
                <li><a href="settings.php" class="active"><i class="fas fa-sliders"></i> Settings</a></li>
                <li><a href="skills.php"><i class="fas fa-code"></i> Skills</a></li>
                <li><a href="projects.php"><i class="fas fa-folder"></i> Projects</a></li>
                <li><a href="messages.php"><i class="fas fa-envelope"></i> Messages <?php if ($unreadCount > 0): ?><span class="nav-badge"><?= $unreadCount ?></span><?php endif; ?></a></li>
            </ul>
            <div class="sidebar-divider"></div>
            <ul class="sidebar-nav">
                <li><a href="../index.php" target="_blank"><i class="fas fa-external-link-alt"></i> View Site</a></li>
                <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
            </ul>
        </aside>

        <main class="main-content">
            <div class="page-header">
                <div>
                    <h1>Settings</h1>
                    <p>Manage your portfolio content and preferences</p>
                </div>
            </div>

            <?php if ($flash = getFlash()): ?>
                <div class="alert alert-<?= $flash['type'] ?>"><i class="fas fa-check-circle"></i> <?= e($flash['message']) ?></div>
            <?php endif; ?>

            <form method="POST">
                <div class="settings-layout">
                    <nav class="settings-nav">
                        <button type="button" class="settings-nav-item active" data-tab="profile"><i class="fas fa-user"></i> Profile</button>
                        <button type="button" class="settings-nav-item" data-tab="about"><i class="fas fa-file-alt"></i> About</button>
                        <button type="button" class="settings-nav-item" data-tab="social"><i class="fas fa-share-alt"></i> Social</button>
                        <button type="button" class="settings-nav-item" data-tab="security"><i class="fas fa-lock"></i> Security</button>
                    </nav>

                    <div class="settings-content">
                        <!-- Profile Tab -->
                        <div class="settings-section active" data-section="profile">
                            <div class="section-header">
                                <h2>Profile & Hero</h2>
                                <p>Your name, title, and hero section content</p>
                            </div>

                            <div class="form-grid">
                                <div class="form-group">
                                    <label>Site Title</label>
                                    <input type="text" name="site_title" value="<?= e($settings['site_title'] ?? '') ?>" placeholder="My Portfolio">
                                </div>
                                <div class="form-group">
                                    <label>Your Name</label>
                                    <input type="text" name="hero_title" value="<?= e($settings['hero_title'] ?? '') ?>" placeholder="John Doe">
                                </div>
                                <div class="form-group full">
                                    <label>Tagline / Role</label>
                                    <input type="text" name="hero_tagline" value="<?= e($settings['hero_tagline'] ?? '') ?>" placeholder="Frontend Developer • Kathmandu, Nepal">
                                </div>
                                <div class="form-group full">
                                    <label>Hero Subtitle</label>
                                    <textarea name="hero_subtitle" rows="2" placeholder="A short introduction..."><?= e($settings['hero_subtitle'] ?? '') ?></textarea>
                                </div>
                                <div class="form-group full">
                                    <label>SEO Description</label>
                                    <textarea name="site_description" rows="2" placeholder="Shown in search engine results"><?= e($settings['site_description'] ?? '') ?></textarea>
                                </div>
                            </div>
                        </div>

                        <!-- About Tab -->
                        <div class="settings-section" data-section="about">
                            <div class="section-header">
                                <h2>About Section</h2>
                                <p>Your bio and profile photo</p>
                            </div>

                            <div class="photo-upload">
                                <div class="photo-current">
                                    <img id="photoPreviewImg" src="../<?= e($settings['photo_url'] ?? 'me.jpg') ?>" alt="Profile" onerror="this.src='https://via.placeholder.com/120/141414/333?text=Photo'">
                                </div>
                                <div class="photo-actions">
                                    <input type="hidden" name="photo_url" id="photo_url" value="<?= e($settings['photo_url'] ?? 'me.jpg') ?>">
                                    <p>Click to upload a new profile photo. Max 5MB.</p>
                                    <input type="file" id="photo_file" accept="image/*" style="display:none">
                                    <button type="button" class="btn btn-secondary" onclick="document.getElementById('photo_file').click()">
                                        <i class="fas fa-upload"></i> Upload Photo
                                    </button>
                                </div>
                            </div>

                            <div class="form-grid" style="margin-top: 24px;">
                                <div class="form-group full">
                                    <label>About Text (Paragraph 1)</label>
                                    <textarea name="about_text" rows="4" placeholder="Tell visitors about yourself..."><?= e($settings['about_text'] ?? '') ?></textarea>
                                </div>
                                <div class="form-group full">
                                    <label>About Text (Paragraph 2) - Optional</label>
                                    <textarea name="about_text_2" rows="4" placeholder="Additional info..."><?= e($settings['about_text_2'] ?? '') ?></textarea>
                                </div>
                            </div>
                        </div>

                        <!-- Social Tab -->
                        <div class="settings-section" data-section="social">
                            <div class="section-header">
                                <h2>Contact & Social Links</h2>
                                <p>How visitors can reach you</p>
                            </div>

                            <div class="form-grid" style="margin-bottom: 24px;">
                                <div class="form-group">
                                    <label>Email</label>
                                    <input type="email" name="email" value="<?= e($settings['email'] ?? '') ?>" placeholder="you@example.com">
                                </div>
                                <div class="form-group">
                                    <label>Phone</label>
                                    <input type="text" name="phone" value="<?= e($settings['phone'] ?? '') ?>" placeholder="+1 234 567 890">
                                </div>
                            </div>

                            <label style="display: block; margin-bottom: 12px; font-weight: 500;">Social Profiles</label>
                            <div class="social-grid">
                                <div class="social-item">
                                    <i class="fab fa-github"></i>
                                    <input type="url" name="github_url" value="<?= e($settings['github_url'] ?? '') ?>" placeholder="https://github.com/username">
                                </div>
                                <div class="social-item">
                                    <i class="fab fa-linkedin"></i>
                                    <input type="url" name="linkedin_url" value="<?= e($settings['linkedin_url'] ?? '') ?>" placeholder="https://linkedin.com/in/username">
                                </div>
                                <div class="social-item">
                                    <i class="fab fa-instagram"></i>
                                    <input type="url" name="instagram_url" value="<?= e($settings['instagram_url'] ?? '') ?>" placeholder="https://instagram.com/username">
                                </div>
                                <div class="social-item">
                                    <i class="fab fa-facebook"></i>
                                    <input type="url" name="facebook_url" value="<?= e($settings['facebook_url'] ?? '') ?>" placeholder="https://facebook.com/username">
                                </div>
                                <div class="social-item">
                                    <i class="fab fa-whatsapp"></i>
                                    <input type="text" name="whatsapp" value="<?= e($settings['whatsapp'] ?? '') ?>" placeholder="9800000000 (without country code)">
                                </div>
                            </div>
                        </div>

                        <!-- Security Tab -->
                        <div class="settings-section" data-section="security">
                            <div class="section-header">
                                <h2>Security</h2>
                                <p>Change your admin password</p>
                            </div>

                            <div class="form-grid">
                                <div class="form-group">
                                    <label>New Password</label>
                                    <input type="password" name="new_password" placeholder="Leave blank to keep current">
                                    <div class="form-hint">Minimum 6 characters recommended</div>
                                </div>
                            </div>

                            <div class="card" style="margin-top: 24px; padding: 16px; background: var(--warning-bg); border-color: var(--warning);">
                                <p style="color: var(--warning); font-size: 13px;"><i class="fas fa-exclamation-triangle"></i> Make sure to remember your password. There's no password recovery.</p>
                            </div>
                        </div>

                        <div class="save-bar">
                            <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Save All Changes</button>
                        </div>
                    </div>
                </div>
            </form>
        </main>
    </div>

    <button class="mobile-toggle" onclick="document.querySelector('.sidebar').classList.toggle('open')"><i class="fas fa-bars"></i></button>

    <script>
        // Tab navigation
        document.querySelectorAll('.settings-nav-item').forEach(btn => {
            btn.addEventListener('click', () => {
                const tab = btn.dataset.tab;
                
                document.querySelectorAll('.settings-nav-item').forEach(b => b.classList.remove('active'));
                document.querySelectorAll('.settings-section').forEach(s => s.classList.remove('active'));
                
                btn.classList.add('active');
                document.querySelector(`[data-section="${tab}"]`).classList.add('active');
            });
        });

        // Photo upload
        const photoInput = document.getElementById('photo_file');
        const photoPreview = document.getElementById('photoPreviewImg');
        const photoUrl = document.getElementById('photo_url');

        photoInput.addEventListener('change', () => {
            if (photoInput.files.length) {
                uploadPhoto(photoInput.files[0]);
            }
        });

        function uploadPhoto(file) {
            const allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
            if (!allowedTypes.includes(file.type)) {
                alert('Invalid file type. Use JPG, PNG, GIF, or WebP.');
                return;
            }
            if (file.size > 5 * 1024 * 1024) {
                alert('File too large. Max 5MB.');
                return;
            }

            const formData = new FormData();
            formData.append('image', file);

            photoPreview.style.opacity = '0.5';

            fetch('upload.php', {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    photoUrl.value = data.path;
                    photoPreview.src = '../' + data.path;
                } else {
                    alert(data.error || 'Upload failed');
                }
                photoPreview.style.opacity = '1';
            })
            .catch(() => {
                alert('Upload failed');
                photoPreview.style.opacity = '1';
            });
        }
    </script>
</body>
</html>
