<?php
require_once '../includes/config.php';
requireLogin();

$unreadCount = getUnreadMessagesCount($pdo);
$projects = getProjects($pdo);

// Handle AJAX reorder
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'reorder') {
    header('Content-Type: application/json');
    $order = json_decode($_POST['order'] ?? '[]', true);
    if (is_array($order)) {
        $stmt = $pdo->prepare("UPDATE projects SET sort_order = ? WHERE id = ?");
        foreach ($order as $index => $id) {
            $stmt->execute([$index, (int) $id]);
        }
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false]);
    }
    exit;
}

// Handle actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'add') {
        $title = trim($_POST['title'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $image = trim($_POST['image'] ?? '');
        $tag1 = trim($_POST['tag1'] ?? '');
        $tag2 = trim($_POST['tag2'] ?? '');
        $github_url = trim($_POST['github_url'] ?? '');
        $public_url = trim($_POST['public_url'] ?? '');

        // Auto-assign sort order (add to end)
        $stmt = $pdo->query("SELECT MAX(sort_order) as max_order FROM projects");
        $maxOrder = $stmt->fetch()['max_order'] ?? 0;
        $sortOrder = $maxOrder + 1;

        if ($title) {
            $stmt = $pdo->prepare("INSERT INTO projects (title, description, image, tag1, tag2, github_url, public_url, sort_order) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$title, $description, $image, $tag1, $tag2, $github_url, $public_url, $sortOrder]);
            setFlash('success', 'Project added successfully!');
        }
    }

    if ($action === 'edit') {
        $id = (int) ($_POST['id'] ?? 0);
        $title = trim($_POST['title'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $image = trim($_POST['image'] ?? '');
        $tag1 = trim($_POST['tag1'] ?? '');
        $tag2 = trim($_POST['tag2'] ?? '');
        $github_url = trim($_POST['github_url'] ?? '');
        $public_url = trim($_POST['public_url'] ?? '');

        if ($id && $title) {
            $stmt = $pdo->prepare("UPDATE projects SET title = ?, description = ?, image = ?, tag1 = ?, tag2 = ?, github_url = ?, public_url = ? WHERE id = ?");
            $stmt->execute([$title, $description, $image, $tag1, $tag2, $github_url, $public_url, $id]);
            setFlash('success', 'Project updated successfully!');
        }
    }

    if ($action === 'delete') {
        $id = (int) ($_POST['id'] ?? 0);
        if ($id) {
            $pdo->prepare("DELETE FROM projects WHERE id = ?")->execute([$id]);
            setFlash('success', 'Project deleted successfully!');
        }
    }

    header('Location: projects.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Projects - Admin</title>
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
        .projects-sortable {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 20px;
        }

        .project-card {
            background: var(--card);
            border-radius: 12px;
            overflow: hidden;
            border: 1px solid var(--border);
            transition: transform 0.2s, box-shadow 0.2s;
        }

        .project-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.3);
        }

        .project-card.sortable-ghost {
            opacity: 0.4;
        }

        .project-card.sortable-drag {
            box-shadow: 0 12px 40px rgba(99, 102, 241, 0.3);
        }

        .project-card-image {
            aspect-ratio: 16/10;
            background: var(--bg);
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
        }

        .project-card-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .project-card-image i {
            font-size: 2rem;
            color: var(--text-dim);
        }

        .project-card-body {
            padding: 16px;
        }

        .project-card-header {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 8px;
            margin-bottom: 8px;
        }

        .project-card-title {
            font-size: 16px;
            font-weight: 600;
            color: var(--text);
            line-height: 1.3;
        }

        .project-card-drag {
            cursor: grab;
            color: var(--text-dim);
            padding: 4px;
            opacity: 0.5;
            transition: opacity 0.2s;
        }

        .project-card-drag:hover {
            opacity: 1;
        }

        .project-card-drag:active {
            cursor: grabbing;
        }

        .project-card-desc {
            font-size: 13px;
            color: var(--text-muted);
            margin-bottom: 12px;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .project-card-tags {
            display: flex;
            gap: 6px;
            flex-wrap: wrap;
            margin-bottom: 12px;
        }

        .project-card-tags .badge {
            font-size: 11px;
            padding: 4px 8px;
        }

        .project-card-actions {
            display: flex;
            gap: 8px;
            padding-top: 12px;
            border-top: 1px solid var(--border);
        }

        .project-card-actions .btn {
            flex: 1;
            font-size: 12px;
            padding: 8px;
        }

        .sort-hint {
            background: var(--accent-dim);
            color: var(--accent);
            padding: 12px 16px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 13px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .sort-hint i {
            font-size: 16px;
        }

        .saving-indicator {
            position: fixed;
            bottom: 24px;
            right: 24px;
            background: var(--accent);
            color: white;
            padding: 12px 20px;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 500;
            opacity: 0;
            transform: translateY(10px);
            transition: all 0.3s;
            z-index: 1000;
        }

        .saving-indicator.show {
            opacity: 1;
            transform: translateY(0);
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
                <li><a href="projects.php" class="active"><i class="fas fa-folder"></i> Projects</a></li>
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
                    <h1>Projects</h1>
                    <p>Manage your portfolio projects</p>
                </div>
                <button class="btn btn-primary" onclick="openAddModal()">
                    <i class="fas fa-plus"></i> Add Project
                </button>
            </div>

            <?php if ($flash = getFlash()): ?>
                <div class="alert alert-<?= $flash['type'] ?>">
                    <i class="fas fa-check-circle"></i>
                    <?= e($flash['message']) ?>
                </div>
            <?php endif; ?>

            <?php if (empty($projects)): ?>
                <div class="empty-state">
                    <i class="fas fa-folder-open"></i>
                    <p>No projects yet. Click "Add Project" to create one.</p>
                </div>
            <?php else: ?>
                <div class="sort-hint">
                    <i class="fas fa-grip-vertical"></i>
                    Drag projects to reorder. Changes save automatically.
                </div>
                <div class="projects-sortable" id="projectsGrid">
                    <?php foreach ($projects as $project): ?>
                        <div class="project-card" data-id="<?= $project['id'] ?>">
                            <div class="project-card-image">
                                <?php if ($project['image']): ?>
                                    <img src="../<?= e($project['image']) ?>" alt="<?= e($project['title']) ?>">
                                <?php else: ?>
                                    <i class="fas fa-image"></i>
                                <?php endif; ?>
                            </div>
                            <div class="project-card-body">
                                <div class="project-card-header">
                                    <span class="project-card-title"><?= e($project['title']) ?></span>
                                    <span class="project-card-drag" title="Drag to reorder"><i
                                            class="fas fa-grip-vertical"></i></span>
                                </div>
                                <?php if ($project['description']): ?>
                                    <p class="project-card-desc"><?= e($project['description']) ?></p>
                                <?php endif; ?>
                                <div class="project-card-tags">
                                    <?php if ($project['tag1']): ?>
                                        <span class="badge"><?= e($project['tag1']) ?></span>
                                    <?php endif; ?>
                                    <?php if ($project['tag2']): ?>
                                        <span class="badge"><?= e($project['tag2']) ?></span>
                                    <?php endif; ?>
                                </div>
                                <div class="project-card-actions">
                                    <button class="btn btn-secondary btn-sm"
                                        onclick="openEditModal(<?= htmlspecialchars(json_encode($project), ENT_QUOTES, 'UTF-8') ?>)">
                                        <i class="fas fa-edit"></i> Edit
                                    </button>
                                    <form method="POST" style="flex:1;display:flex;"
                                        onsubmit="return confirm('Delete this project?')">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="id" value="<?= $project['id'] ?>">
                                        <button type="submit" class="btn btn-danger btn-sm" style="width:100%">
                                            <i class="fas fa-trash"></i> Delete
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <div class="saving-indicator" id="savingIndicator">
                <i class="fas fa-check"></i> Order saved!
            </div>
        </main>
    </div>

    <!-- Add Modal -->
    <div class="modal" id="addModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Add Project</h3>
                <button class="modal-close" onclick="closeModal('addModal')">&times;</button>
            </div>
            <form method="POST" id="addForm">
                <input type="hidden" name="action" value="add">
                <input type="hidden" name="image" id="add_image_path">
                <div class="form-group">
                    <label>Project Title</label>
                    <input type="text" name="title" required placeholder="e.g., My Awesome Project">
                </div>
                <div class="form-group">
                    <label>Description</label>
                    <textarea name="description" placeholder="Brief description of the project..."></textarea>
                </div>
                <div class="form-group">
                    <label>Project Image</label>
                    <div class="upload-area" id="addUploadArea">
                        <input type="file" id="add_image_file" accept="image/*" style="display:none">
                        <div class="upload-placeholder" id="addPlaceholder">
                            <i class="fas fa-cloud-upload-alt"></i>
                            <span>Click to upload or drag image here</span>
                            <small>JPG, PNG, GIF, WebP (Max 5MB)</small>
                        </div>
                        <div class="upload-preview" id="addPreview" style="display:none">
                            <img id="addPreviewImg" src="" alt="Preview">
                            <button type="button" class="remove-image" onclick="removeImage('add')">&times;</button>
                        </div>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Tag 1</label>
                        <input type="text" name="tag1" placeholder="e.g., React">
                    </div>
                    <div class="form-group">
                        <label>Tag 2</label>
                        <input type="text" name="tag2" placeholder="e.g., Full-stack">
                    </div>
                </div>
                <div class="form-group">
                    <label>GitHub URL</label>
                    <input type="url" name="github_url" placeholder="https://github.com/...">
                </div>
                <div class="form-group">
                    <label>Live/Public URL</label>
                    <input type="url" name="public_url" placeholder="https://example.com/...">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeModal('addModal')">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add Project</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit Modal -->
    <div class="modal" id="editModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Edit Project</h3>
                <button class="modal-close" onclick="closeModal('editModal')">&times;</button>
            </div>
            <form method="POST" id="editForm">
                <input type="hidden" name="action" value="edit">
                <input type="hidden" name="id" id="edit_id">
                <input type="hidden" name="image" id="edit_image">
                <div class="form-group">
                    <label>Project Title</label>
                    <input type="text" name="title" id="edit_title" required>
                </div>
                <div class="form-group">
                    <label>Description</label>
                    <textarea name="description" id="edit_description"></textarea>
                </div>
                <div class="form-group">
                    <label>Project Image</label>
                    <div class="upload-area" id="editUploadArea">
                        <input type="file" id="edit_image_file" accept="image/*" style="display:none">
                        <div class="upload-placeholder" id="editPlaceholder">
                            <i class="fas fa-cloud-upload-alt"></i>
                            <span>Click to upload or drag image here</span>
                            <small>JPG, PNG, GIF, WebP (Max 5MB)</small>
                        </div>
                        <div class="upload-preview" id="editPreview" style="display:none">
                            <img id="editPreviewImg" src="" alt="Preview">
                            <button type="button" class="remove-image" onclick="removeImage('edit')">&times;</button>
                        </div>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Tag 1</label>
                        <input type="text" name="tag1" id="edit_tag1">
                    </div>
                    <div class="form-group">
                        <label>Tag 2</label>
                        <input type="text" name="tag2" id="edit_tag2">
                    </div>
                </div>
                <div class="form-group">
                    <label>GitHub URL</label>
                    <input type="url" name="github_url" id="edit_github_url">
                </div>
                <div class="form-group">
                    <label>Live/Public URL</label>
                    <input type="url" name="public_url" id="edit_public_url">
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
            document.getElementById('addForm').reset();
            document.getElementById('add_image_path').value = '';
            document.getElementById('addPlaceholder').style.display = 'flex';
            document.getElementById('addPreview').style.display = 'none';
            document.getElementById('addModal').classList.add('active');
        }

        function openEditModal(project) {
            document.getElementById('edit_id').value = project.id;
            document.getElementById('edit_title').value = project.title || '';
            document.getElementById('edit_description').value = project.description || '';
            document.getElementById('edit_image').value = project.image || '';
            document.getElementById('edit_tag1').value = project.tag1 || '';
            document.getElementById('edit_tag2').value = project.tag2 || '';
            document.getElementById('edit_github_url').value = project.github_url || '';
            document.getElementById('edit_public_url').value = project.public_url || '';

            if (project.image) {
                document.getElementById('editPlaceholder').style.display = 'none';
                document.getElementById('editPreview').style.display = 'block';
                document.getElementById('editPreviewImg').src = '../' + project.image;
            } else {
                document.getElementById('editPlaceholder').style.display = 'flex';
                document.getElementById('editPreview').style.display = 'none';
            }

            document.getElementById('editModal').classList.add('active');
        }

        function closeModal(id) {
            document.getElementById(id).classList.remove('active');
        }

        function removeImage(prefix) {
            const pathInput = prefix === 'add' ?
                document.getElementById('add_image_path') :
                document.getElementById('edit_image');
            pathInput.value = '';
            document.getElementById(prefix + 'Placeholder').style.display = 'flex';
            document.getElementById(prefix + 'Preview').style.display = 'none';
        }

        function setupUploadArea(prefix) {
            const area = document.getElementById(prefix + 'UploadArea');
            const fileInput = document.getElementById(prefix + '_image_file');

            area.addEventListener('click', () => fileInput.click());

            area.addEventListener('dragover', (e) => {
                e.preventDefault();
                area.classList.add('dragover');
            });

            area.addEventListener('dragleave', () => {
                area.classList.remove('dragover');
            });

            area.addEventListener('drop', (e) => {
                e.preventDefault();
                area.classList.remove('dragover');
                if (e.dataTransfer.files.length) {
                    handleFile(e.dataTransfer.files[0], prefix);
                }
            });

            fileInput.addEventListener('change', () => {
                if (fileInput.files.length) {
                    handleFile(fileInput.files[0], prefix);
                }
            });
        }

        function handleFile(file, prefix) {
            const allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
            const maxSize = 5 * 1024 * 1024;

            if (!allowedTypes.includes(file.type)) {
                alert('Invalid file type. Please upload JPG, PNG, GIF, or WebP.');
                return;
            }

            if (file.size > maxSize) {
                alert('File too large. Maximum size is 5MB.');
                return;
            }

            const placeholder = document.getElementById(prefix + 'Placeholder');
            const preview = document.getElementById(prefix + 'Preview');
            const previewImg = document.getElementById(prefix + 'PreviewImg');
            const pathInput = prefix === 'add' ?
                document.getElementById('add_image_path') :
                document.getElementById('edit_image');

            placeholder.innerHTML = '<div class="upload-loading active"><div class="spinner"></div><span>Uploading...</span></div>';

            const formData = new FormData();
            formData.append('image', file);

            fetch('upload.php', {
                method: 'POST',
                body: formData
            })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        pathInput.value = data.path;
                        previewImg.src = '../' + data.path;
                        placeholder.innerHTML = '<i class="fas fa-cloud-upload-alt"></i><span>Click to upload or drag image here</span><small>JPG, PNG, GIF, WebP (Max 5MB)</small>';
                        placeholder.style.display = 'none';
                        preview.style.display = 'block';
                    } else {
                        alert(data.error || 'Upload failed');
                        placeholder.innerHTML = '<i class="fas fa-cloud-upload-alt"></i><span>Click to upload or drag image here</span><small>JPG, PNG, GIF, WebP (Max 5MB)</small>';
                    }
                })
                .catch(err => {
                    alert('Upload failed. Please try again.');
                    placeholder.innerHTML = '<i class="fas fa-cloud-upload-alt"></i><span>Click to upload or drag image here</span><small>JPG, PNG, GIF, WebP (Max 5MB)</small>';
                });
        }

        setupUploadArea('add');
        setupUploadArea('edit');

        // Clipboard paste support
        document.addEventListener('paste', (e) => {
            // Check if we're in a modal that has an upload area
            const addModal = document.getElementById('addModal');
            const editModal = document.getElementById('editModal');

            let prefix = null;
            if (addModal && addModal.classList.contains('active')) {
                prefix = 'add';
            } else if (editModal && editModal.classList.contains('active')) {
                prefix = 'edit';
            }

            if (!prefix) return;

            const items = e.clipboardData?.items;
            if (!items) return;

            for (const item of items) {
                if (item.type.startsWith('image/')) {
                    e.preventDefault();
                    const file = item.getAsFile();
                    if (file) {
                        handleFile(file, prefix);
                    }
                    break;
                }
            }
        });

        document.querySelectorAll('.modal').forEach(modal => {
            modal.addEventListener('click', (e) => {
                if (e.target === modal) closeModal(modal.id);
            });
        });
    </script>

    <!-- SortableJS for drag and drop -->
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
    <script>
        const grid = document.getElementById('projectsGrid');
        if (grid) {
            new Sortable(grid, {
                animation: 200,
                handle: '.project-card-drag',
                ghostClass: 'sortable-ghost',
                dragClass: 'sortable-drag',
                onEnd: function () {
                    const order = Array.from(grid.querySelectorAll('.project-card')).map(el => el.dataset.id);

                    // Show saving indicator
                    const indicator = document.getElementById('savingIndicator');
                    indicator.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...';
                    indicator.classList.add('show');

                    // Save order via AJAX
                    fetch('projects.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                        body: 'action=reorder&order=' + encodeURIComponent(JSON.stringify(order))
                    })
                        .then(res => res.json())
                        .then(data => {
                            indicator.innerHTML = '<i class="fas fa-check"></i> Order saved!';
                            setTimeout(() => indicator.classList.remove('show'), 2000);
                        })
                        .catch(() => {
                            indicator.innerHTML = '<i class="fas fa-times"></i> Save failed';
                            indicator.style.background = 'var(--error)';
                            setTimeout(() => {
                                indicator.classList.remove('show');
                                indicator.style.background = '';
                            }, 2000);
                        });
                }
            });
        }
    </script>
</body>

</html>