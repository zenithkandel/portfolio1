<?php
require_once 'includes/config.php';


$settings = getSettings($pdo);
$projects = getProjects($pdo);

$name = $settings['hero_title'] ?? 'Developer';
$role = $settings['hero_subtitle'] ?? 'Creative Developer';
?>
<!DOCTYPE html>
<html lang="en">
<script>
    (function () {
        const theme = localStorage.getItem('theme') || 'dark';
        document.documentElement.setAttribute('data-theme', theme);
    })();
</script>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>All Projects – <?= e($name) ?></title>
    <meta name="description" content="Browse all projects by <?= e($name) ?>. <?= e($role) ?>.">
    <meta name="robots" content="index, follow">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Instrument+Serif:ital@0;1&family=Space+Grotesk:wght@300..700&display=swap"
        rel="stylesheet">

    <!-- Icons -->
    <link rel="stylesheet" href="https://site-assets.fontawesome.com/releases/v7.2.0/css/all.css">

    <!-- Styles -->
    <link rel="stylesheet" href="assets/css/style.css">
</head>

<body>
    <!-- Header -->
    <header class="header header-fixed" role="banner">
        <a href="index.php" class="logo" aria-label="<?= e($name) ?> - Home">
            <i class="fas fa-arrow-left"></i> Back
        </a>

        <div class="header-actions">
            <button class="theme-toggle" aria-label="Toggle theme">
                <i class="fas fa-sun"></i>
                <i class="fas fa-moon"></i>
            </button>
        </div>
    </header>

    <main class="projects-page">
        <!-- Page Header -->
        <section class="projects-page-header">
            <h1 class="projects-page-title">All Projects</h1>
            <p class="projects-page-subtitle"><?= count($projects) ?> projects in total</p>

            <!-- Search Bar -->
            <div class="projects-search-wrapper">
                <div class="projects-search">
                    <i class="fas fa-search"></i>
                    <input type="text" id="projectSearch" placeholder="Search projects by name or tag..."
                        autocomplete="off">
                    <button class="search-clear" id="searchClear" aria-label="Clear search">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>
        </section>

        <!-- Projects Grid -->
        <section class="projects-grid-section">
            <div class="projects-grid" id="projectsGrid">
                <?php foreach ($projects as $i => $project):
                    $tags = array_filter([$project['tag1'] ?? '', $project['tag2'] ?? '']);
                    $githubUrl = $project['github_url'] ?? '';
                    $publicUrl = $project['public_url'] ?? '';
                    $hasLinks = !empty($githubUrl) || !empty($publicUrl);
                    $searchTerms = strtolower($project['title'] . ' ' . ($project['tag1'] ?? '') . ' ' . ($project['tag2'] ?? '') . ' ' . ($project['description'] ?? ''));
                    ?>
                    <article class="projects-grid-item" data-search="<?= e($searchTerms) ?>">
                        <div class="projects-grid-image">
                            <?php if (!empty($project['image'])): ?>
                                <img src="<?= e($project['image']) ?>" alt="<?= e($project['title']) ?>" loading="lazy">
                            <?php else: ?>
                                <img src="https://via.placeholder.com/600x400/141414/333?text=<?= urlencode($project['title']) ?>"
                                    alt="" loading="lazy">
                            <?php endif; ?>

                            <div class="projects-grid-overlay">
                                <?php if ($hasLinks): ?>
                                    <div class="projects-grid-links">
                                        <?php if (!empty($githubUrl)): ?>
                                            <a href="<?= e($githubUrl) ?>" target="_blank" class="projects-grid-link"
                                                aria-label="View code on GitHub">
                                                <i class="fab fa-github"></i>
                                            </a>
                                        <?php endif; ?>
                                        <?php if (!empty($publicUrl)): ?>
                                            <a href="<?= e($publicUrl) ?>" target="_blank" class="projects-grid-link"
                                                aria-label="View live demo">
                                                <i class="fas fa-external-link-alt"></i>
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="projects-grid-info">
                            <span class="projects-grid-number"><?= str_pad($i + 1, 2, '0', STR_PAD_LEFT) ?></span>
                            <h2 class="projects-grid-title"><?= e($project['title']) ?></h2>
                            <?php if (!empty($project['description'])): ?>
                                <p class="projects-grid-desc"><?= e($project['description']) ?></p>
                            <?php endif; ?>
                            <div class="projects-grid-tags">
                                <?php foreach ($tags as $tag): ?>
                                    <span class="projects-grid-tag"><?= e($tag) ?></span>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>

            <!-- No Results Message -->
            <div class="projects-no-results" id="noResults" style="display: none;">
                <i class="fas fa-search"></i>
                <h3>No projects found</h3>
                <p>Try a different search term</p>
            </div>
        </section>
    </main>

    <!-- Footer -->
    <footer class="footer projects-footer">
        <a href="index.php" class="back-home">
            <i class="fas fa-arrow-left"></i> Back to Home
        </a>
        <span>© <?= date('Y') ?> <?= e($name) ?></span>
    </footer>

    <script>
        // Theme toggle
        document.querySelector('.theme-toggle')?.addEventListener('click', () => {
            const html = document.documentElement;
            const current = html.getAttribute('data-theme');
            const next = current === 'dark' ? 'light' : 'dark';
            html.setAttribute('data-theme', next);
            localStorage.setItem('theme', next);
        });

        // Project search functionality
        const searchInput = document.getElementById('projectSearch');
        const searchClear = document.getElementById('searchClear');
        const projectsGrid = document.getElementById('projectsGrid');
        const noResults = document.getElementById('noResults');
        const projectItems = document.querySelectorAll('.projects-grid-item');

        function filterProjects(query) {
            const searchTerm = query.toLowerCase().trim();
            let visibleCount = 0;

            projectItems.forEach(item => {
                const searchData = item.dataset.search || '';
                const matches = searchTerm === '' || searchData.includes(searchTerm);

                item.style.display = matches ? '' : 'none';
                if (matches) visibleCount++;
            });

            // Show/hide no results message
            noResults.style.display = visibleCount === 0 ? 'flex' : 'none';

            // Show/hide clear button
            searchClear.style.display = searchTerm ? 'flex' : 'none';
        }

        searchInput?.addEventListener('input', (e) => {
            filterProjects(e.target.value);
        });

        searchClear?.addEventListener('click', () => {
            searchInput.value = '';
            filterProjects('');
            searchInput.focus();
        });

        // Clear on Escape
        searchInput?.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                searchInput.value = '';
                filterProjects('');
            }
        });
    </script>
</body>

</html>