<?php
require_once '../includes/config.php';
requireLogin();

$unreadCount = getUnreadMessagesCount($pdo);
$skills = getSkills($pdo);

// Handle actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'add') {
        $name = trim($_POST['name'] ?? '');
        $icon = trim($_POST['icon'] ?? 'fa-solid fa-code');
        $sortOrder = (int) ($_POST['sort_order'] ?? 0);

        if ($name) {
            $stmt = $pdo->prepare("INSERT INTO skills (name, icon, sort_order) VALUES (?, ?, ?)");
            $stmt->execute([$name, $icon, $sortOrder]);
            setFlash('success', 'Skill added successfully!');
        }
    }

    if ($action === 'edit') {
        $id = (int) ($_POST['id'] ?? 0);
        $name = trim($_POST['name'] ?? '');
        $icon = trim($_POST['icon'] ?? 'fa-solid fa-code');
        $sortOrder = (int) ($_POST['sort_order'] ?? 0);

        if ($id && $name) {
            $stmt = $pdo->prepare("UPDATE skills SET name = ?, icon = ?, sort_order = ? WHERE id = ?");
            $stmt->execute([$name, $icon, $sortOrder, $id]);
            setFlash('success', 'Skill updated successfully!');
        }
    }

    if ($action === 'delete') {
        $id = (int) ($_POST['id'] ?? 0);
        if ($id) {
            $pdo->prepare("DELETE FROM skills WHERE id = ?")->execute([$id]);
            setFlash('success', 'Skill deleted successfully!');
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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/admin.css">
    <style>
        .icon-preview {
            font-size: 20px;
            color: var(--accent);
        }

        .icon-code {
            background: var(--bg);
            padding: 6px 10px;
            border-radius: 4px;
            font-size: 13px;
            font-family: monospace;
            color: var(--text-muted);
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
                <li><a href="skills.php" class="active"><i class="fas fa-code"></i> Skills</a></li>
                <li><a href="projects.php"><i class="fas fa-folder"></i> Projects</a></li>
                <li>
                    <a href="messages.php">
                        <i class="fas fa-envelope"></i> Messages
                        <?php if ($unreadCount > 0): ?>
                            <span class="nav-badge">
                                <?= $unreadCount ?>
                            </span>
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
                    <h1>Skills</h1>
                    <p>Manage your technical skills and expertise</p>
                </div>
                <button class="btn btn-primary" onclick="openAddModal()">
                    <i class="fas fa-plus"></i> Add Skill
                </button>
            </div>

            <?php if ($flash = getFlash()): ?>
                <div class="alert alert-<?= $flash['type'] ?>">
                    <i class="fas fa-check-circle"></i>
                    <?= e($flash['message']) ?>
                </div>
            <?php endif; ?>

            <div class="card">
                <div class="table-wrapper">
                    <table>
                        <thead>
                            <tr>
                                <th style="width: 60px;">Icon</th>
                                <th>Name</th>
                                <th style="width: 100px;">Order</th>
                                <th style="width: 150px;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($skills)): ?>
                                <tr>
                                    <td colspan="4" style="text-align: center; padding: 40px; color: var(--text-muted);">
                                        No skills yet. Click "Add Skill" to create one.
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($skills as $skill): ?>
                                    <tr>
                                        <td class="icon-preview"><i class="<?= e($skill['icon']) ?>"></i></td>
                                        <td>
                                            <?= e($skill['name']) ?>
                                        </td>
                                        <td>
                                            <?= $skill['sort_order'] ?>
                                        </td>
                                        <td>
                                            <button class="btn btn-secondary btn-sm"
                                                onclick="openEditModal(<?= $skill['id'] ?>, '<?= e($skill['name']) ?>', '<?= e($skill['icon']) ?>', <?= $skill['sort_order'] ?>)">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <form method="POST" style="display: inline;"
                                                onsubmit="return confirm('Delete this skill?')">
                                                <input type="hidden" name="action" value="delete">
                                                <input type="hidden" name="id" value="<?= $skill['id'] ?>">
                                                <button type="submit" class="btn btn-danger btn-sm">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h3>Common Icons</h3>
                </div>
                <p style="color: var(--text-muted); font-size: 14px; margin-bottom: 12px;">Copy any of these icon
                    classes:</p>
                <div style="display: flex; flex-wrap: wrap; gap: 12px;">
                    <code class="icon-code">fa-solid fa-code</code>
                    <code class="icon-code">fa-solid fa-paintbrush</code>
                    <code class="icon-code">fa-brands fa-js</code>
                    <code class="icon-code">fa-brands fa-node-js</code>
                    <code class="icon-code">fa-brands fa-react</code>
                    <code class="icon-code">fa-brands fa-python</code>
                    <code class="icon-code">fa-solid fa-database</code>
                    <code class="icon-code">fa-solid fa-server</code>
                    <code class="icon-code">fa-solid fa-mobile-screen</code>
                    <code class="icon-code">fa-solid fa-pen-ruler</code>
                </div>
                <p style="color: var(--text-muted); font-size: 13px; margin-top: 12px;">Browse more at <a
                        href="https://fontawesome.com/icons" target="_blank"
                        style="color: var(--accent);">fontawesome.com/icons</a></p>
            </div>
        </main>
    </div>

    <!-- Add Modal -->
    <div class="modal" id="addModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Add Skill</h3>
                <button class="modal-close" onclick="closeModal('addModal')">&times;</button>
            </div>
            <form method="POST">
                <input type="hidden" name="action" value="add">
                <div class="form-group">
                    <label>Skill Name</label>
                    <input type="text" name="name" required placeholder="e.g., JavaScript">
                </div>
                <div class="form-group">
                    <label>Icon Class</label>
                    <input type="text" name="icon" value="fa-solid fa-code" placeholder="e.g., fa-brands fa-js">
                    <div class="form-hint">FontAwesome class name</div>
                </div>
                <div class="form-group">
                    <label>Sort Order</label>
                    <input type="number" name="sort_order" value="0" min="0">
                    <div class="form-hint">Lower numbers appear first</div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeModal('addModal')">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add Skill</button>
                </div>
            </form>
        </div>
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
                    <label>Icon Class</label>
                    <input type="text" name="icon" id="edit_icon">
                </div>
                <div class="form-group">
                    <label>Sort Order</label>
                    <input type="number" name="sort_order" id="edit_sort" min="0">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeModal('editModal')">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                </div>
            </form>
        </div>
    </div>

    <button class="mobile-toggle" onclick="document.querySelector('.sidebar').classList.toggle('open')">
        <i class="fas fa-bars"></i>
    </button>

    <script>
        function openAddModal() {
            document.getElementById('addModal').classList.add('active');
        }

        function openEditModal(id, name, icon, sort) {
            document.getElementById('edit_id').value = id;
            document.getElementById('edit_name').value = name;
            document.getElementById('edit_icon').value = icon;
            document.getElementById('edit_sort').value = sort;
            document.getElementById('editModal').classList.add('active');
        }

        function closeModal(id) {
            document.getElementById(id).classList.remove('active');
        }

        document.querySelectorAll('.modal').forEach(modal => {
            modal.addEventListener('click', (e) => {
                if (e.target === modal) closeModal(modal.id);
            });
        });
    </script>
</body>

</html>