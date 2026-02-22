<?php
require_once '../includes/config.php';
requireLogin();

$unreadCount = getUnreadMessagesCount($pdo);
$projects = getProjects($pdo);

// Handle actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'add') {
        $title = trim($_POST['title'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $image = trim($_POST['image'] ?? '');
        $tag1 = trim($_POST['tag1'] ?? '');
        $tag2 = trim($_POST['tag2'] ?? '');
        $url = trim($_POST['url'] ?? '');
        $sortOrder = (int) ($_POST['sort_order'] ?? 0);

        if ($title) {
            $stmt = $pdo->prepare("INSERT INTO projects (title, description, image, tag1, tag2, url, sort_order) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$title, $description, $image, $tag1, $tag2, $url, $sortOrder]);
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
        $url = trim($_POST['url'] ?? '');
        $sortOrder = (int) ($_POST['sort_order'] ?? 0);

        if ($id && $title) {
            $stmt = $pdo->prepare("UPDATE projects SET title = ?, description = ?, image = ?, tag1 = ?, tag2 = ?, url = ?, sort_order = ? WHERE id = ?");
            $stmt->execute([$title, $description, $image, $tag1, $tag2, $url, $sortOrder, $id]);
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

        .projects-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(340px, 1fr));
            gap: 20px;
        }

        .project-card {
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: 24px;
        }

        .project-card h3 {
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 8px;
        }

        .project-card p {
            color: var(--text-muted);
            font-size: 14px;
            margin-bottom: 12px;
            line-height: 1.5;
        }

        .project-tags {
            display: flex;
            gap: 8px;
            margin-bottom: 16px;
        }

        .project-tags span {
            background: var(--bg);
            padding: 4px 10px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 500;
            color: var(--text-muted);
        }

        .project-url {
            font-size: 13px;
            color: var(--accent);
            text-decoration: none;
            display: block;
            margin-bottom: 16px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .project-actions {
            display: flex;
            gap: 8px;
            padding-top: 16px;
            border-top: 1px solid var(--border);
        }

        .form-group {
            margin-bottom: 16px;
        }

        .form-group label {
            display: block;
            font-size: 14px;
            font-weight: 500;
            margin-bottom: 6px;
        }

        .form-group input,
        .form-group textarea {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid var(--border);
            border-radius: 8px;
            font-size: 14px;
            font-family: inherit;
        }

        .form-group input:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: var(--accent);
        }

        .form-group textarea {
            resize: vertical;
            min-height: 80px;
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
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

        .modal {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.5);
            z-index: 1000;
            align-items: center;
            justify-content: center;
            overflow-y: auto;
        }

        .modal.active {
            display: flex;
        }

        .modal-content {
            background: white;
            border-radius: 12px;
            padding: 24px;
            width: 100%;
            max-width: 550px;
            margin: 20px;
        }

        .modal-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 20px;
        }

        .modal-header h3 {
            font-size: 20px;
            font-weight: 600;
        }

        .modal-close {
            background: none;
            border: none;
            font-size: 24px;
            cursor: pointer;
            color: var(--text-muted);
        }

        .modal-footer {
            display: flex;
            gap: 12px;
            justify-content: flex-end;
            margin-top: 20px;
        }

        .upload-area {
            border: 2px dashed var(--border);
            border-radius: 8px;
            padding: 20px;
            text-align: center;
            cursor: pointer;
            transition: all 0.2s;
            background: #fafafa;
        }

        .upload-area:hover,
        .upload-area.dragover {
            border-color: var(--accent);
            background: #f0f7ff;
        }

        .upload-placeholder {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 8px;
            color: var(--text-muted);
        }

        .upload-placeholder i {
            font-size: 32px;
            color: var(--border);
        }

        .upload-placeholder small {
            font-size: 12px;
            opacity: 0.7;
        }

        .upload-preview {
            position: relative;
            display: inline-block;
        }

        .upload-preview img {
            max-width: 100%;
            max-height: 200px;
            border-radius: 4px;
        }

        .remove-image {
            position: absolute;
            top: -8px;
            right: -8px;
            width: 24px;
            height: 24px;
            border-radius: 50%;
            background: #e53935;
            color: white;
            border: none;
            cursor: pointer;
            font-size: 16px;
            line-height: 1;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .remove-image:hover {
            background: #c62828;
        }

        .upload-loading {
            display: none;
            align-items: center;
            justify-content: center;
            gap: 10px;
            padding: 20px;
            color: var(--text-muted);
        }

        .upload-loading.active {
            display: flex;
        }

        .upload-loading .spinner {
            width: 20px;
            height: 20px;
            border: 2px solid var(--border);
            border-top-color: var(--accent);
            border-radius: 50%;
            animation: spin 0.8s linear infinite;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        .empty-state {
            text-align: center;
            padding: 60px 40px;
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: 12px;
        }

        .empty-state i {
            font-size: 48px;
            color: var(--border);
            margin-bottom: 16px;
        }

        .empty-state p {
            color: var(--text-muted);
        }

        @media (max-width: 900px) {
            .sidebar {
                display: none;
            }

            .main {
                margin-left: 0;
            }

            .projects-grid {
                grid-template-columns: 1fr;
            }

            .form-row {
                grid-template-columns: 1fr;
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
                <li><a href="projects.php" class="active"><i class="fas fa-folder"></i> Projects</a></li>
                <li><a href="messages.php"><i class="fas fa-envelope"></i> Messages <?php if ($unreadCount > 0): ?><span
                                class="badge"><?= $unreadCount ?></span><?php endif; ?></a></li>
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
                <h1>Projects</h1>
                <button class="btn btn-primary" onclick="openAddModal()">
                    <i class="fas fa-plus"></i> Add Project
                </button>
            </div>

            <?php if (empty($projects)): ?>
                <div class="empty-state">
                    <i class="fas fa-folder-open"></i>
                    <p>No projects yet. Click "Add Project" to create one.</p>
                </div>
            <?php else: ?>
                <div class="projects-grid">
                    <?php foreach ($projects as $project): ?>
                        <div class="project-card">
                            <div class="project-tags">
                                <?php if ($project['tag1']): ?><span><?= e($project['tag1']) ?></span><?php endif; ?>
                                <?php if ($project['tag2']): ?><span><?= e($project['tag2']) ?></span><?php endif; ?>
                            </div>
                            <h3><?= e($project['title']) ?></h3>
                            <p><?= e($project['description']) ?></p>
                            <?php if ($project['url']): ?>
                                <a href="<?= e($project['url']) ?>" target="_blank"
                                    class="project-url"><?= e($project['url']) ?></a>
                            <?php endif; ?>
                            <div class="project-actions">
                                <button class="btn btn-secondary btn-sm"
                                    onclick="openEditModal(<?= htmlspecialchars(json_encode($project), ENT_QUOTES, 'UTF-8') ?>)">
                                    <i class="fas fa-edit"></i> Edit
                                </button>
                                <form method="POST" style="display: inline;" onsubmit="return confirm('Delete this project?')">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="id" value="<?= $project['id'] ?>">
                                    <button type="submit" class="btn btn-danger btn-sm"><i class="fas fa-trash"></i>
                                        Delete</button>
                                </form>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
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
                    <label>Project URL</label>
                    <input type="url" name="url" placeholder="https://github.com/...">
                </div>
                <div class="form-group">
                    <label>Sort Order</label>
                    <input type="number" name="sort_order" value="0" min="0">
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
                    <label>Project URL</label>
                    <input type="url" name="url" id="edit_url">
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
            // Reset the form
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
            document.getElementById('edit_url').value = project.url || '';
            document.getElementById('edit_sort').value = project.sort_order || 0;
            
            // Show existing image if any
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
            document.getElementById(prefix + '_image_path') ? 
                document.getElementById(prefix + '_image_path').value = '' :
                document.getElementById(prefix + '_image').value = '';
            document.getElementById(prefix + 'Placeholder').style.display = 'flex';
            document.getElementById(prefix + 'Preview').style.display = 'none';
        }

        function setupUploadArea(prefix) {
            const area = document.getElementById(prefix + 'UploadArea');
            const fileInput = document.getElementById(prefix + '_image_file');
            const placeholder = document.getElementById(prefix + 'Placeholder');
            const preview = document.getElementById(prefix + 'Preview');
            const previewImg = document.getElementById(prefix + 'PreviewImg');
            const pathInput = prefix === 'add' ? 
                document.getElementById('add_image_path') : 
                document.getElementById('edit_image');

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

        // Initialize upload areas
        setupUploadArea('add');
        setupUploadArea('edit');

        document.querySelectorAll('.modal').forEach(modal => {
            modal.addEventListener('click', (e) => {
                if (e.target === modal) closeModal(modal.id);
            });
        });
    </script>
</body>

</html>