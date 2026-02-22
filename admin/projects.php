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
        $github_url = trim($_POST['github_url'] ?? '');
        $public_url = trim($_POST['public_url'] ?? '');
        $sortOrder = (int) ($_POST['sort_order'] ?? 0);

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
        $sortOrder = (int) ($_POST['sort_order'] ?? 0);

        if ($id && $title) {
            $stmt = $pdo->prepare("UPDATE projects SET title = ?, description = ?, image = ?, tag1 = ?, tag2 = ?, github_url = ?, public_url = ?, sort_order = ? WHERE id = ?");
            $stmt->execute([$title, $description, $image, $tag1, $tag2, $github_url, $public_url, $sortOrder, $id]);
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
                <div class="items-grid">
                    <?php foreach ($projects as $project): ?>
                        <div class="item-card">
                            <?php if ($project['image']): ?>
                                <img src="../<?= e($project['image']) ?>" alt="<?= e($project['title']) ?>" class="project-preview">
                            <?php else: ?>
                                <div class="project-preview-placeholder">
                                    <i class="fas fa-image"></i>
                                </div>
                            <?php endif; ?>

                            <h4><?= e($project['title']) ?></h4>
                            <p><?= e($project['description']) ?></p>

                            <div class="item-meta">
                                <?php if ($project['tag1']): ?>
                                    <span class="badge"><?= e($project['tag1']) ?></span>
                                <?php endif; ?>
                                <?php if ($project['tag2']): ?>
                                    <span class="badge"><?= e($project['tag2']) ?></span>
                                <?php endif; ?>
                            </div>

                            <div class="item-actions">
                                <button class="btn btn-secondary btn-sm"
                                    onclick="openEditModal(<?= htmlspecialchars(json_encode($project), ENT_QUOTES, 'UTF-8') ?>)">
                                    <i class="fas fa-edit"></i> Edit
                                </button>
                                <form method="POST" style="display: inline;" onsubmit="return confirm('Delete this project?')">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="id" value="<?= $project['id'] ?>">
                                    <button type="submit" class="btn btn-danger btn-sm">
                                        <i class="fas fa-trash"></i> Delete
                                    </button>
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
                    <label>GitHub URL</label>
                    <input type="url" name="github_url" placeholder="https://github.com/...">
                </div>
                <div class="form-group">
                    <label>Live/Public URL</label>
                    <input type="url" name="public_url" placeholder="https://example.com/...">
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
                    <label>GitHub URL</label>
                    <input type="url" name="github_url" id="edit_github_url">
                </div>
                <div class="form-group">
                    <label>Live/Public URL</label>
                    <input type="url" name="public_url" id="edit_public_url">
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
            document.getElementById('edit_sort').value = project.sort_order || 0;

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
</body>

</html>