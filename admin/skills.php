<?php
require_once '../includes/config.php';
requireLogin();

$unreadCount = getUnreadMessagesCount($pdo);
$skills = getSkills($pdo);

// Handle AJAX reorder
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'reorder') {
    header('Content-Type: application/json');
    $order = json_decode($_POST['order'] ?? '[]', true);
    if (is_array($order)) {
        $stmt = $pdo->prepare("UPDATE skills SET sort_order = ? WHERE id = ?");
        foreach ($order as $index => $id) {
            $stmt->execute([$index, (int)$id]);
        }
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false]);
    }
    exit;
}

// Handle AJAX quick add
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'quick_add') {
    header('Content-Type: application/json');
    $name = trim($_POST['name'] ?? '');
    if ($name) {
        $stmt = $pdo->query("SELECT MAX(sort_order) as max_order FROM skills");
        $maxOrder = $stmt->fetch()['max_order'] ?? 0;
        $stmt = $pdo->prepare("INSERT INTO skills (name, icon, sort_order) VALUES (?, 'fa-solid fa-code', ?)");
        $stmt->execute([$name, $maxOrder + 1]);
        $id = $pdo->lastInsertId();
        echo json_encode(['success' => true, 'id' => $id, 'name' => $name]);
    } else {
        echo json_encode(['success' => false]);
    }
    exit;
}

// Handle AJAX delete
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'ajax_delete') {
    header('Content-Type: application/json');
    $id = (int)($_POST['id'] ?? 0);
    if ($id) {
        $pdo->prepare("DELETE FROM skills WHERE id = ?")->execute([$id]);
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false]);
    }
    exit;
}

// Handle form actions (fallback)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'add') {
        $name = trim($_POST['name'] ?? '');
        $icon = trim($_POST['icon'] ?? 'fa-solid fa-code');
        $stmt = $pdo->query("SELECT MAX(sort_order) as max_order FROM skills");
        $maxOrder = $stmt->fetch()['max_order'] ?? 0;
        if ($name) {
            $stmt = $pdo->prepare("INSERT INTO skills (name, icon, sort_order) VALUES (?, ?, ?)");
            $stmt->execute([$name, $icon, $maxOrder + 1]);
            setFlash('success', 'Skill added!');
        }
    }

    if ($action === 'edit') {
        $id = (int)($_POST['id'] ?? 0);
        $name = trim($_POST['name'] ?? '');
        $icon = trim($_POST['icon'] ?? 'fa-solid fa-code');
        if ($id && $name) {
            $stmt = $pdo->prepare("UPDATE skills SET name = ?, icon = ? WHERE id = ?");
            $stmt->execute([$name, $icon, $id]);
            setFlash('success', 'Skill updated!');
        }
    }

    if ($action === 'delete') {
        $id = (int)($_POST['id'] ?? 0);
        if ($id) {
            $pdo->prepare("DELETE FROM skills WHERE id = ?")->execute([$id]);
            setFlash('success', 'Skill deleted!');
        }
    }

    header('Location: skills.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Skills - Admin</title>
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
        .quick-add { background: var(--card); border-radius: 12px; padding: 20px; margin-bottom: 24px; border: 1px solid var(--border); }
        .quick-add-form { display: flex; gap: 12px; align-items: center; }
        .quick-add-input { flex: 1; background: var(--bg); border: 1px solid var(--border); border-radius: 8px; padding: 12px 16px; color: var(--text); font-size: 14px; }
        .quick-add-input:focus { outline: none; border-color: var(--accent); }
        .quick-add-input::placeholder { color: var(--text-dim); }
        .skills-list { display: flex; flex-direction: column; gap: 8px; }
        .skill-item { display: flex; align-items: center; gap: 12px; background: var(--card); border: 1px solid var(--border); border-radius: 10px; padding: 12px 16px; transition: all 0.2s; }
        .skill-item:hover { border-color: var(--border-light); }
        .skill-item.sortable-ghost { opacity: 0.4; }
        .skill-item.sortable-drag { box-shadow: 0 8px 24px rgba(99,102,241,0.3); }
        .skill-drag { cursor: grab; color: var(--text-dim); padding: 4px; opacity: 0.5; transition: opacity 0.2s; }
        .skill-drag:hover { opacity: 1; }
        .skill-drag:active { cursor: grabbing; }
        .skill-icon { width: 36px; height: 36px; display: flex; align-items: center; justify-content: center; background: var(--accent-dim); border-radius: 8px; color: var(--accent); font-size: 14px; }
        .skill-name { flex: 1; font-weight: 500; color: var(--text); }
        .skill-actions { display: flex; gap: 8px; }
        .skill-actions .btn { padding: 8px 12px; font-size: 12px; }
        .sort-hint { background: var(--accent-dim); color: var(--accent); padding: 12px 16px; border-radius: 8px; margin-bottom: 16px; font-size: 13px; display: flex; align-items: center; gap: 10px; }
        .empty-skills { text-align: center; padding: 60px 20px; color: var(--text-muted); }
        .empty-skills i { font-size: 3rem; margin-bottom: 16px; opacity: 0.3; display: block; }
        .icon-picker { display: grid; grid-template-columns: repeat(5, 1fr); gap: 8px; margin-top: 12px; max-height: 180px; overflow-y: auto; padding: 4px; }
        .icon-option { width: 40px; height: 40px; display: flex; align-items: center; justify-content: center; background: var(--bg); border: 1px solid var(--border); border-radius: 8px; cursor: pointer; color: var(--text-muted); transition: all 0.2s; }
        .icon-option:hover, .icon-option.selected { background: var(--accent-dim); border-color: var(--accent); color: var(--accent); }
        .custom-icon-wrapper { margin-top: 16px; padding-top: 16px; border-top: 1px solid var(--border); }
        .custom-icon-wrapper label { font-size: 12px; color: var(--text-muted); display: block; margin-bottom: 8px; }
        .custom-icon-input-row { display: flex; gap: 12px; align-items: center; }
        .custom-icon-input { flex: 1; background: var(--bg); border: 1px solid var(--border); border-radius: 8px; padding: 10px 14px; color: var(--text); font-size: 13px; font-family: monospace; }
        .custom-icon-input:focus { outline: none; border-color: var(--accent); }
        .custom-icon-input::placeholder { color: var(--text-dim); }
        .custom-icon-preview { width: 44px; height: 44px; display: flex; align-items: center; justify-content: center; background: var(--accent-dim); border: 1px solid var(--accent); border-radius: 8px; color: var(--accent); font-size: 18px; }
        .custom-icon-hint { display: block; margin-top: 8px; font-size: 11px; color: var(--text-dim); }
        .custom-icon-hint a { color: var(--accent); text-decoration: underline; }
        .saving-indicator { position: fixed; bottom: 24px; right: 24px; background: var(--accent); color: white; padding: 12px 20px; border-radius: 8px; font-size: 13px; font-weight: 500; opacity: 0; transform: translateY(10px); transition: all 0.3s; z-index: 1000; }
        .saving-indicator.show { opacity: 1; transform: translateY(0); }
    </style>
</head>
<body>
    <div class="layout">
        <aside class="sidebar">
            <div class="sidebar-logo"><i class="fas fa-terminal"></i> Portfolio</div>
            <ul class="sidebar-nav">
                <li><a href="index.php"><i class="fas fa-home"></i> Dashboard</a></li>
                <li><a href="settings.php"><i class="fas fa-sliders"></i> Settings</a></li>
                <li><a href="skills.php" class="active"><i class="fas fa-code"></i> Skills</a></li>
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
                    <h1>Skills</h1>
                    <p>Manage your technical skills and expertise</p>
                </div>
            </div>

            <?php if ($flash = getFlash()): ?>
                <div class="alert alert-<?= $flash['type'] ?>"><i class="fas fa-check-circle"></i> <?= e($flash['message']) ?></div>
            <?php endif; ?>

            <div class="quick-add">
                <div class="quick-add-form">
                    <input type="text" class="quick-add-input" id="quickAddInput" placeholder="Type a skill and press Enter (e.g., JavaScript, React, Python...)">
                    <button class="btn btn-primary" onclick="quickAddSkill()"><i class="fas fa-plus"></i> Add</button>
                </div>
            </div>

            <?php if (!empty($skills)): ?>
                <div class="sort-hint"><i class="fas fa-grip-vertical"></i> Drag skills to reorder. Click edit to change icons.</div>
            <?php endif; ?>

            <div class="skills-list" id="skillsList">
                <?php if (empty($skills)): ?>
                    <div class="empty-skills" id="emptyState">
                        <i class="fas fa-code"></i>
                        <p>No skills yet. Add your first skill above!</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($skills as $skill): ?>
                        <div class="skill-item" data-id="<?= $skill['id'] ?>">
                            <span class="skill-drag"><i class="fas fa-grip-vertical"></i></span>
                            <span class="skill-icon"><i class="<?= e($skill['icon']) ?>"></i></span>
                            <span class="skill-name"><?= e($skill['name']) ?></span>
                            <div class="skill-actions">
                                <button class="btn btn-secondary btn-sm" onclick="openEditModal(<?= $skill['id'] ?>, '<?= e(addslashes($skill['name'])) ?>', '<?= e($skill['icon']) ?>')"><i class="fas fa-edit"></i></button>
                                <button class="btn btn-danger btn-sm" onclick="deleteSkill(<?= $skill['id'] ?>, this)"><i class="fas fa-trash"></i></button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <div class="saving-indicator" id="savingIndicator"><i class="fas fa-check"></i> Saved!</div>
        </main>
    </div>

    <!-- Edit Modal -->
    <div class="modal" id="editModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Edit Skill</h3>
                <button class="modal-close" onclick="closeModal('editModal')">&times;</button>
            </div>
            <form method="POST">
                <input type="hidden" name="action" value="edit">
                <input type="hidden" name="id" id="edit_id">
                <div class="form-group">
                    <label>Skill Name</label>
                    <input type="text" name="name" id="edit_name" required>
                </div>
                <div class="form-group">
                    <label>Icon</label>
                    <input type="hidden" name="icon" id="edit_icon">
                    <div class="icon-picker">
                        <div class="icon-option" data-icon="fa-solid fa-code"><i class="fa-solid fa-code"></i></div>
                        <div class="icon-option" data-icon="fa-brands fa-js"><i class="fa-brands fa-js"></i></div>
                        <div class="icon-option" data-icon="fa-brands fa-react"><i class="fa-brands fa-react"></i></div>
                        <div class="icon-option" data-icon="fa-brands fa-node-js"><i class="fa-brands fa-node-js"></i></div>
                        <div class="icon-option" data-icon="fa-brands fa-python"><i class="fa-brands fa-python"></i></div>
                        <div class="icon-option" data-icon="fa-brands fa-php"><i class="fa-brands fa-php"></i></div>
                        <div class="icon-option" data-icon="fa-solid fa-database"><i class="fa-solid fa-database"></i></div>
                        <div class="icon-option" data-icon="fa-brands fa-git-alt"><i class="fa-brands fa-git-alt"></i></div>
                        <div class="icon-option" data-icon="fa-solid fa-paintbrush"><i class="fa-solid fa-paintbrush"></i></div>
                        <div class="icon-option" data-icon="fa-solid fa-mobile-screen"><i class="fa-solid fa-mobile-screen"></i></div>
                        <div class="icon-option" data-icon="fa-brands fa-html5"><i class="fa-brands fa-html5"></i></div>
                        <div class="icon-option" data-icon="fa-brands fa-css3-alt"><i class="fa-brands fa-css3-alt"></i></div>
                        <div class="icon-option" data-icon="fa-brands fa-vuejs"><i class="fa-brands fa-vuejs"></i></div>
                        <div class="icon-option" data-icon="fa-brands fa-angular"><i class="fa-brands fa-angular"></i></div>
                        <div class="icon-option" data-icon="fa-brands fa-laravel"><i class="fa-brands fa-laravel"></i></div>
                        <div class="icon-option" data-icon="fa-brands fa-docker"><i class="fa-brands fa-docker"></i></div>
                        <div class="icon-option" data-icon="fa-brands fa-aws"><i class="fa-brands fa-aws"></i></div>
                        <div class="icon-option" data-icon="fa-brands fa-figma"><i class="fa-brands fa-figma"></i></div>
                        <div class="icon-option" data-icon="fa-solid fa-server"><i class="fa-solid fa-server"></i></div>
                        <div class="icon-option" data-icon="fa-solid fa-cloud"><i class="fa-solid fa-cloud"></i></div>
                    </div>
                    <div class="custom-icon-wrapper">
                        <label>Or enter custom FontAwesome class:</label>
                        <div class="custom-icon-input-row">
                            <input type="text" id="custom_icon_input" class="custom-icon-input" placeholder="e.g. fa-solid fa-rocket">
                            <div class="custom-icon-preview" id="custom_icon_preview"><i class="fa-solid fa-code"></i></div>
                        </div>
                        <small class="custom-icon-hint">Browse icons at <a href="https://fontawesome.com/icons" target="_blank">fontawesome.com/icons</a></small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeModal('editModal')">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                </div>
            </form>
        </div>
    </div>

    <button class="mobile-toggle" onclick="document.querySelector('.sidebar').classList.toggle('open')"><i class="fas fa-bars"></i></button>

    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
    <script>
        // Quick add skill
        const quickInput = document.getElementById('quickAddInput');
        quickInput.addEventListener('keypress', (e) => {
            if (e.key === 'Enter') {
                e.preventDefault();
                quickAddSkill();
            }
        });

        function quickAddSkill() {
            const name = quickInput.value.trim();
            if (!name) return;

            const indicator = document.getElementById('savingIndicator');
            indicator.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Adding...';
            indicator.classList.add('show');

            fetch('skills.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'action=quick_add&name=' + encodeURIComponent(name)
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    // Remove empty state if exists
                    const emptyState = document.getElementById('emptyState');
                    if (emptyState) emptyState.remove();

                    // Add new skill to list
                    const list = document.getElementById('skillsList');
                    const newItem = document.createElement('div');
                    newItem.className = 'skill-item';
                    newItem.dataset.id = data.id;
                    newItem.innerHTML = `
                        <span class="skill-drag"><i class="fas fa-grip-vertical"></i></span>
                        <span class="skill-icon"><i class="fa-solid fa-code"></i></span>
                        <span class="skill-name">${escapeHtml(data.name)}</span>
                        <div class="skill-actions">
                            <button class="btn btn-secondary btn-sm" onclick="openEditModal(${data.id}, '${escapeHtml(data.name)}', 'fa-solid fa-code')"><i class="fas fa-edit"></i></button>
                            <button class="btn btn-danger btn-sm" onclick="deleteSkill(${data.id}, this)"><i class="fas fa-trash"></i></button>
                        </div>
                    `;
                    list.appendChild(newItem);

                    quickInput.value = '';
                    indicator.innerHTML = '<i class="fas fa-check"></i> Added!';
                    setTimeout(() => indicator.classList.remove('show'), 2000);
                }
            })
            .catch(() => {
                indicator.innerHTML = '<i class="fas fa-times"></i> Failed';
                indicator.style.background = 'var(--error)';
                setTimeout(() => { indicator.classList.remove('show'); indicator.style.background = ''; }, 2000);
            });
        }

        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }

        // Delete skill
        function deleteSkill(id, btn) {
            if (!confirm('Delete this skill?')) return;

            const item = btn.closest('.skill-item');
            item.style.opacity = '0.5';

            fetch('skills.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'action=ajax_delete&id=' + id
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    item.remove();
                    showIndicator('<i class="fas fa-check"></i> Deleted!');
                }
            });
        }

        // Edit modal
        function openEditModal(id, name, icon) {
            document.getElementById('edit_id').value = id;
            document.getElementById('edit_name').value = name;
            document.getElementById('edit_icon').value = icon;

            document.querySelectorAll('.icon-option').forEach(opt => {
                opt.classList.toggle('selected', opt.dataset.icon === icon);
            });

            document.getElementById('editModal').classList.add('active');
        }

        function closeModal(id) {
            document.getElementById(id).classList.remove('active');
        }

        // Icon picker
        document.querySelectorAll('.icon-option').forEach(opt => {
            opt.addEventListener('click', () => {
                document.querySelectorAll('.icon-option').forEach(o => o.classList.remove('selected'));
                opt.classList.add('selected');
                document.getElementById('edit_icon').value = opt.dataset.icon;
            });
        });

        // Saving indicator helper
        function showIndicator(html) {
            const indicator = document.getElementById('savingIndicator');
            indicator.innerHTML = html;
            indicator.classList.add('show');
            setTimeout(() => indicator.classList.remove('show'), 2000);
        }

        // Modal close on background click
        document.querySelectorAll('.modal').forEach(modal => {
            modal.addEventListener('click', (e) => {
                if (e.target === modal) closeModal(modal.id);
            });
        });

        // Drag and drop sorting
        const list = document.getElementById('skillsList');
        if (list && list.querySelector('.skill-item')) {
            new Sortable(list, {
                animation: 200,
                handle: '.skill-drag',
                ghostClass: 'sortable-ghost',
                dragClass: 'sortable-drag',
                onEnd: function() {
                    const order = Array.from(list.querySelectorAll('.skill-item')).map(el => el.dataset.id);
                    
                    const indicator = document.getElementById('savingIndicator');
                    indicator.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...';
                    indicator.classList.add('show');

                    fetch('skills.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                        body: 'action=reorder&order=' + encodeURIComponent(JSON.stringify(order))
                    })
                    .then(res => res.json())
                    .then(() => {
                        indicator.innerHTML = '<i class="fas fa-check"></i> Order saved!';
                        setTimeout(() => indicator.classList.remove('show'), 2000);
                    });
                }
            });
        }
    </script>
</body>
</html>
