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
        $sortOrder = (int)($_POST['sort_order'] ?? 0);
        
        if ($name) {
            $stmt = $pdo->prepare("INSERT INTO skills (name, icon, sort_order) VALUES (?, ?, ?)");
            $stmt->execute([$name, $icon, $sortOrder]);
            setFlash('success', 'Skill added successfully!');
        }
    }
    
    if ($action === 'edit') {
        $id = (int)($_POST['id'] ?? 0);
        $name = trim($_POST['name'] ?? '');
        $icon = trim($_POST['icon'] ?? 'fa-solid fa-code');
        $sortOrder = (int)($_POST['sort_order'] ?? 0);
        
        if ($id && $name) {
            $stmt = $pdo->prepare("UPDATE skills SET name = ?, icon = ?, sort_order = ? WHERE id = ?");
            $stmt->execute([$name, $icon, $sortOrder, $id]);
            setFlash('success', 'Skill updated successfully!');
        }
    }
    
    if ($action === 'delete') {
        $id = (int)($_POST['id'] ?? 0);
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
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        
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
        
        body { font-family: 'Inter', sans-serif; background: var(--bg); color: var(--text); line-height: 1.6; }
        .layout { display: flex; min-height: 100vh; }
        
        .sidebar {
            width: var(--sidebar-width);
            background: #1a1a1a;
            color: white;
            padding: 24px;
            position: fixed;
            height: 100vh;
            overflow-y: auto;
        }
        
        .sidebar-logo { font-size: 20px; font-weight: 700; margin-bottom: 40px; display: flex; align-items: center; gap: 10px; }
        .sidebar-nav { list-style: none; }
        .sidebar-nav li { margin-bottom: 4px; }
        .sidebar-nav a { display: flex; align-items: center; gap: 12px; padding: 12px 16px; color: rgba(255,255,255,0.7); text-decoration: none; border-radius: 8px; font-size: 15px; transition: all 0.2s; }
        .sidebar-nav a:hover, .sidebar-nav a.active { background: rgba(255,255,255,0.1); color: white; }
        .sidebar-nav a i { width: 20px; text-align: center; }
        .sidebar-nav .badge { margin-left: auto; background: #dc2626; color: white; font-size: 11px; font-weight: 600; padding: 2px 8px; border-radius: 10px; }
        .sidebar-footer { position: absolute; bottom: 24px; left: 24px; right: 24px; }
        .sidebar-footer a { display: flex; align-items: center; gap: 10px; padding: 12px 16px; color: rgba(255,255,255,0.5); text-decoration: none; font-size: 14px; border-radius: 8px; transition: all 0.2s; }
        .sidebar-footer a:hover { color: white; background: rgba(255,255,255,0.1); }
        
        .main { flex: 1; margin-left: var(--sidebar-width); padding: 32px; }
        .header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 32px; }
        .header h1 { font-size: 28px; font-weight: 700; }
        
        .btn { display: inline-flex; align-items: center; gap: 8px; padding: 10px 20px; font-size: 14px; font-weight: 600; border-radius: 8px; text-decoration: none; transition: all 0.2s; cursor: pointer; border: none; }
        .btn-primary { background: var(--accent); color: white; }
        .btn-primary:hover { background: var(--accent-hover); }
        .btn-secondary { background: white; color: var(--text); border: 1px solid var(--border); }
        .btn-secondary:hover { border-color: var(--accent); color: var(--accent); }
        .btn-danger { background: #fee2e2; color: #dc2626; }
        .btn-danger:hover { background: #dc2626; color: white; }
        .btn-sm { padding: 8px 14px; font-size: 13px; }
        
        .card { background: var(--card); border-radius: 12px; border: 1px solid var(--border); margin-bottom: 24px; }
        .card-header { display: flex; align-items: center; justify-content: space-between; padding: 20px 24px; border-bottom: 1px solid var(--border); }
        .card-header h2 { font-size: 18px; font-weight: 600; }
        .card-body { padding: 24px; }
        
        .table { width: 100%; border-collapse: collapse; }
        .table th, .table td { padding: 14px 16px; text-align: left; border-bottom: 1px solid var(--border); }
        .table th { font-size: 13px; font-weight: 600; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.5px; }
        .table tr:last-child td { border-bottom: none; }
        .table .icon-preview { font-size: 20px; color: var(--accent); width: 30px; }
        .table .actions { display: flex; gap: 8px; }
        
        .form-group { margin-bottom: 16px; }
        .form-group label { display: block; font-size: 14px; font-weight: 500; margin-bottom: 6px; }
        .form-group input { width: 100%; padding: 10px 12px; border: 1px solid var(--border); border-radius: 8px; font-size: 14px; font-family: inherit; }
        .form-group input:focus { outline: none; border-color: var(--accent); }
        .form-row { display: grid; grid-template-columns: 1fr 1fr 100px; gap: 12px; align-items: end; }
        .form-hint { color: var(--text-muted); font-size: 12px; margin-top: 4px; }
        
        .flash { padding: 16px 20px; border-radius: 8px; margin-bottom: 20px; font-size: 15px; }
        .flash-success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        
        .modal { display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 1000; align-items: center; justify-content: center; }
        .modal.active { display: flex; }
        .modal-content { background: white; border-radius: 12px; padding: 24px; width: 100%; max-width: 500px; margin: 20px; }
        .modal-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 20px; }
        .modal-header h3 { font-size: 20px; font-weight: 600; }
        .modal-close { background: none; border: none; font-size: 24px; cursor: pointer; color: var(--text-muted); }
        .modal-footer { display: flex; gap: 12px; justify-content: flex-end; margin-top: 20px; }
        
        @media (max-width: 900px) {
            .sidebar { display: none; }
            .main { margin-left: 0; }
            .form-row { grid-template-columns: 1fr; }
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
                <li><a href="skills.php" class="active"><i class="fas fa-code"></i> Skills</a></li>
                <li><a href="projects.php"><i class="fas fa-folder"></i> Projects</a></li>
                <li><a href="messages.php"><i class="fas fa-envelope"></i> Messages <?php if ($unreadCount > 0): ?><span class="badge"><?= $unreadCount ?></span><?php endif; ?></a></li>
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
                <h1>Skills</h1>
                <button class="btn btn-primary" onclick="openAddModal()">
                    <i class="fas fa-plus"></i> Add Skill
                </button>
            </div>
            
            <div class="card">
                <div class="card-body" style="padding: 0;">
                    <table class="table">
                        <thead>
                            <tr>
                                <th style="width: 50px;">Icon</th>
                                <th>Name</th>
                                <th style="width: 100px;">Order</th>
                                <th style="width: 150px;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($skills)): ?>
                                <tr><td colspan="4" style="text-align: center; padding: 40px; color: var(--text-muted);">No skills yet. Click "Add Skill" to create one.</td></tr>
                            <?php else: ?>
                                <?php foreach ($skills as $skill): ?>
                                    <tr>
                                        <td class="icon-preview"><i class="<?= e($skill['icon']) ?>"></i></td>
                                        <td><?= e($skill['name']) ?></td>
                                        <td><?= $skill['sort_order'] ?></td>
                                        <td class="actions">
                                            <button class="btn btn-secondary btn-sm" onclick="openEditModal(<?= $skill['id'] ?>, '<?= e($skill['name']) ?>', '<?= e($skill['icon']) ?>', <?= $skill['sort_order'] ?>)">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <form method="POST" style="display: inline;" onsubmit="return confirm('Delete this skill?')">
                                                <input type="hidden" name="action" value="delete">
                                                <input type="hidden" name="id" value="<?= $skill['id'] ?>">
                                                <button type="submit" class="btn btn-danger btn-sm"><i class="fas fa-trash"></i></button>
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
                    <h2>Common Icons</h2>
                </div>
                <div class="card-body">
                    <p style="color: var(--text-muted); font-size: 14px; margin-bottom: 12px;">Copy any of these icon classes:</p>
                    <div style="display: flex; flex-wrap: wrap; gap: 12px;">
                        <code style="background: #f5f5f5; padding: 6px 10px; border-radius: 4px; font-size: 13px;">fa-solid fa-code</code>
                        <code style="background: #f5f5f5; padding: 6px 10px; border-radius: 4px; font-size: 13px;">fa-solid fa-paintbrush</code>
                        <code style="background: #f5f5f5; padding: 6px 10px; border-radius: 4px; font-size: 13px;">fa-brands fa-js</code>
                        <code style="background: #f5f5f5; padding: 6px 10px; border-radius: 4px; font-size: 13px;">fa-brands fa-node-js</code>
                        <code style="background: #f5f5f5; padding: 6px 10px; border-radius: 4px; font-size: 13px;">fa-brands fa-react</code>
                        <code style="background: #f5f5f5; padding: 6px 10px; border-radius: 4px; font-size: 13px;">fa-brands fa-python</code>
                        <code style="background: #f5f5f5; padding: 6px 10px; border-radius: 4px; font-size: 13px;">fa-solid fa-database</code>
                        <code style="background: #f5f5f5; padding: 6px 10px; border-radius: 4px; font-size: 13px;">fa-solid fa-server</code>
                        <code style="background: #f5f5f5; padding: 6px 10px; border-radius: 4px; font-size: 13px;">fa-solid fa-mobile-screen</code>
                        <code style="background: #f5f5f5; padding: 6px 10px; border-radius: 4px; font-size: 13px;">fa-solid fa-pen-ruler</code>
                    </div>
                    <p style="color: var(--text-muted); font-size: 13px; margin-top: 12px;">Browse more at <a href="https://fontawesome.com/icons" target="_blank">fontawesome.com/icons</a></p>
                </div>
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
        
        // Close modal on outside click
        document.querySelectorAll('.modal').forEach(modal => {
            modal.addEventListener('click', (e) => {
                if (e.target === modal) closeModal(modal.id);
            });
        });
    </script>
</body>
</html>
