<?php
require_once 'includes/config.php';

$settings = getSettings($pdo);
$skills = getSkills($pdo);
$projects = getProjects($pdo);

$name = $settings['hero_title'] ?? 'Developer';
$role = $settings['hero_subtitle'] ?? 'Creative Developer';
$about = $settings['about_text'] ?? '';
$email = $settings['contact_email'] ?? '';
$location = $settings['contact_location'] ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<script>
  // Apply theme before render to prevent flash
  (function () {
    const theme = localStorage.getItem('theme') || 'dark';
    document.documentElement.setAttribute('data-theme', theme);
  })();
</script>

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">

  <!-- SEO: Title Tag - Name + Role + Brand -->
  <title><?= e($name) ?> – <?= e($role) ?> | Portfolio</title>

  <!-- SEO: Meta Description -->
  <meta name="description"
    content="<?= e($name) ?> is a <?= e(strtolower($role)) ?><?= $location ? ' based in ' . e($location) : '' ?>. View portfolio, projects, and get in touch for collaborations.">

  <!-- SEO: Canonical URL -->
  <link rel="canonical" href="<?= e($settings['site_url'] ?? 'https://zenithkandel.com') ?>">

  <!-- SEO: Robots -->
  <meta name="robots" content="index, follow">

  <!-- SEO: Additional Meta -->
  <meta name="author" content="<?= e($name) ?>">
  <meta name="theme-color" content="#0a0a0a">

  <!-- SEO: Open Graph Tags -->
  <meta property="og:type" content="website">
  <meta property="og:title" content="<?= e($name) ?> – <?= e($role) ?>">
  <meta property="og:description"
    content="<?= e($name) ?> is a <?= e(strtolower($role)) ?>. Explore projects, skills, and connect for collaborations.">
  <meta property="og:url" content="<?= e($settings['site_url'] ?? 'https://zenithkandel.com') ?>">
  <meta property="og:image" content="<?= e($settings['site_url'] ?? 'https://zenithkandel.com') ?>/me.jpg">
  <meta property="og:locale" content="en_US">
  <meta property="og:site_name" content="<?= e($name) ?> Portfolio">

  <!-- SEO: Twitter Card -->
  <meta name="twitter:card" content="summary_large_image">
  <meta name="twitter:title" content="<?= e($name) ?> – <?= e($role) ?>">
  <meta name="twitter:description" content="Portfolio showcasing projects and skills. Open to collaborations.">
  <meta name="twitter:image" content="<?= e($settings['site_url'] ?? 'https://zenithkandel.com') ?>/me.jpg">

  <!-- SEO: JSON-LD Structured Data -->
  <script type="application/ld+json">
  {
    "@context": "https://schema.org",
    "@type": "ProfilePage",
    "mainEntity": {
      "@type": "Person",
      "name": "<?= e($name) ?>",
      "jobTitle": "<?= e($role) ?>",
      "description": "<?= e(substr(strip_tags($about), 0, 200)) ?>",
      "url": "<?= e($settings['site_url'] ?? 'https://zenithkandel.com') ?>",
      "image": "<?= e($settings['site_url'] ?? 'https://zenithkandel.com') ?>/me.jpg",
      <?php if ($email): ?>"email": "<?= e($email) ?>",<?php endif; ?>
      <?php if ($location): ?>"address": { "@type": "PostalAddress", "addressLocality": "<?= e($location) ?>" },<?php endif; ?>
      "sameAs": [
        <?php
        $socials = array_filter([
          $settings['github_url'] ?? '',
          $settings['linkedin_url'] ?? '',
          $settings['instagram_url'] ?? '',
          $settings['facebook_url'] ?? ''
        ]);
        echo implode(",\n        ", array_map(fn($s) => '"' . e($s) . '"', $socials));
        ?>
      ],
      "knowsAbout": [<?= implode(', ', array_map(fn($s) => '"' . e($s['name']) . '"', array_slice($skills, 0, 10))) ?>]
    }
  }
  </script>

  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <link rel="stylesheet" href="assets/css/style.css">
</head>

<body>

  <!-- Loader -->
  <div class="loader">
    <div class="loader-inner">
      <div class="loader-text">
        <span>L</span><span>o</span><span>a</span><span>d</span><span>i</span><span>n</span><span>g</span>
      </div>
      <div class="loader-line"></div>
    </div>
  </div>

  <!-- Header -->
  <header class="header" role="banner">
    <a href="#" class="logo" aria-label="<?= e($name) ?> - Home"><?= e(explode(' ', $name)[0]) ?></a>

    <nav aria-label="Main navigation">
      <ul class="nav-menu">
        <li><a href="#work">Work</a></li>
        <li><a href="#about">About</a></li>
        <li><a href="#contact">Contact</a></li>
      </ul>
    </nav>

    <div class="header-actions">
      <button class="theme-toggle" aria-label="Toggle theme">
        <i class="fas fa-sun"></i>
        <i class="fas fa-moon"></i>
      </button>
      <span class="header-time"></span>
    </div>

    <button class="menu-toggle">Menu</button>
  </header>

  <!-- Mobile Nav -->
  <nav class="mobile-nav">
    <button class="mobile-close">Close</button>
    <a href="#work">Work</a>
    <a href="#about">About</a>
    <a href="#contact">Contact</a>
  </nav>

  <main id="main-content">
    <!-- Hero -->
    <section class="hero" aria-labelledby="hero-title">
      <div class="hero-content">
        <p class="hero-intro"><?= e($role) ?></p>

        <h1 class="hero-title" id="hero-title">
          <span class="line"><span class="word"><?= e($name) ?></span></span>
        </h1>

        <div class="hero-bottom">
          <div class="hero-cta">
            <a href="#contact" class="btn btn-primary">Get In Touch</a>
            <a href="cv.html" target="_blank" class="btn btn-outline"><i class="fas fa-download"></i> Download CV</a>
          </div>

          <div class="hero-scroll">
            <span>Scroll</span>
            <div class="arrow"></div>
          </div>
        </div>
      </div>
    </section>

    <!-- Marquee -->
    <div class="marquee">
      <div class="marquee-inner">
        <?php foreach ($skills as $skill): ?>
          <span class="marquee-item"><?= e($skill['name']) ?></span>
        <?php endforeach; ?>
        <?php foreach ($skills as $skill): ?>
          <span class="marquee-item"><?= e($skill['name']) ?></span>
        <?php endforeach; ?>
      </div>
    </div>

    <!-- Work -->
    <section id="work" aria-labelledby="work-heading">
      <h2 id="work-heading" class="section-label">Selected Work</h2>

      <div class="projects-wrapper">
        <button class="projects-nav projects-nav-prev" aria-label="Previous">
          <i class="fas fa-chevron-left"></i>
        </button>

        <div class="projects-list">
          <?php foreach ($projects as $i => $project):
            $tags = array_filter([$project['tag1'] ?? '', $project['tag2'] ?? '']);
            $githubUrl = $project['github_url'] ?? '';
            $publicUrl = $project['public_url'] ?? '';
            $hasLinks = !empty($githubUrl) || !empty($publicUrl);
            ?>
            <article class="project-item">
              <div class="project-image">
                <?php if (!empty($project['image'])): ?>
                  <img src="<?= e($project['image']) ?>" alt="<?= e($project['title']) ?>">
                <?php else: ?>
                  <img src="https://via.placeholder.com/800x600/141414/333?text=<?= urlencode($project['title']) ?>" alt="">
                <?php endif; ?>
              </div>

              <div class="project-info">
                <span class="project-number"><?= str_pad($i + 1, 2, '0', STR_PAD_LEFT) ?></span>
                <h3 class="project-title"><?= e($project['title']) ?></h3>
                <div class="project-tags">
                  <?php foreach ($tags as $tag): ?>
                    <span class="project-tag"><?= e($tag) ?></span>
                  <?php endforeach; ?>
                </div>
              </div>

              <?php if ($hasLinks): ?>
                <div class="project-ctas">
                  <?php if (!empty($githubUrl)): ?>
                    <a href="<?= e($githubUrl) ?>" target="_blank" class="project-cta">
                      <i class="fab fa-github"></i> Code
                    </a>
                  <?php endif; ?>
                  <?php if (!empty($publicUrl)): ?>
                    <a href="<?= e($publicUrl) ?>" target="_blank" class="project-cta">
                      <i class="fas fa-external-link-alt"></i> Live
                    </a>
                  <?php endif; ?>
                </div>
              <?php endif; ?>
            </article>
          <?php endforeach; ?>
        </div>

        <button class="projects-nav projects-nav-next" aria-label="Next">
          <i class="fas fa-chevron-right"></i>
        </button>

        <div class="projects-indicators"></div>
      </div>
    </section>

    <!-- About -->
    <section id="about" class="about" aria-labelledby="about-heading">
      <div class="about-image reveal-left">
        <img src="me.jpg" alt="<?= e($name) ?>"
          onerror="this.src='https://via.placeholder.com/600x800/141414/333?text=Photo'">
      </div>

      <div class="about-content reveal-right">
        <h2 id="about-heading" class="section-label">About</h2>

        <p class="section-title"><?= e($name) ?></p>

        <div class="about-text">
          <?php
          $paragraphs = array_filter(array_map('trim', explode("\n", $about)));
          foreach ($paragraphs as $p):
            ?>
            <p><?= e($p) ?></p>
          <?php endforeach; ?>
        </div>

        <div class="about-details">
          <?php if ($location): ?>
            <div class="detail-item">
              <span class="detail-label">Based in</span>
              <span class="detail-value"><?= e($location) ?></span>
            </div>
          <?php endif; ?>

          <div class="detail-item">
            <span class="detail-label">Experience</span>
            <span class="detail-value"><?= count($skills) ?>+ Technologies</span>
          </div>
        </div>
      </div>
    </section>

    <!-- Skills -->
    <section class="skills-section" aria-labelledby="skills-heading">
      <div class="skills-inner">
        <h2 id="skills-heading" class="section-label">Technologies</h2>

        <div class="skills-grid">
          <?php foreach ($skills as $skill): ?>
            <span class="skill-tag"><?= e($skill['name']) ?></span>
          <?php endforeach; ?>
        </div>
      </div>
    </section>

    <!-- Contact -->
    <section id="contact" class="contact-section" aria-labelledby="contact-heading">
      <h2 id="contact-heading" class="contact-title">
        Let's work<br><em>together</em>
      </h2>

      <?php if ($email): ?>
        <a href="mailto:<?= e($email) ?>" class="contact-email"><?= e($email) ?></a>
      <?php endif; ?>

      <form class="contact-form" id="contactForm">
        <div class="alert alert-success" id="formSuccess" style="display:none">Message sent successfully!</div>
        <div class="alert alert-error" id="formError" style="display:none"></div>

        <div class="form-group">
          <label class="form-label">Name</label>
          <input type="text" name="name" class="form-input" required>
        </div>

        <div class="form-group">
          <label class="form-label">Email</label>
          <input type="email" name="email" class="form-input" required>
        </div>

        <div class="form-group">
          <label class="form-label">Message</label>
          <textarea name="message" class="form-textarea" required></textarea>
        </div>

        <button type="submit" class="form-submit" id="submitBtn">Send Message</button>
      </form>

      <div class="contact-links">
        <?php if (!empty($settings['github_url'])): ?>
          <a href="<?= e($settings['github_url']) ?>" target="_blank"><i class="fab fa-github"></i> GitHub</a>
        <?php endif; ?>

        <?php if (!empty($settings['linkedin_url'])): ?>
          <a href="<?= e($settings['linkedin_url']) ?>" target="_blank"><i class="fab fa-linkedin"></i> LinkedIn</a>
        <?php endif; ?>

        <?php if (!empty($settings['instagram_url'])): ?>
          <a href="<?= e($settings['instagram_url']) ?>" target="_blank"><i class="fab fa-instagram"></i> Instagram</a>
        <?php endif; ?>

        <?php if (!empty($settings['facebook_url'])): ?>
          <a href="<?= e($settings['facebook_url']) ?>" target="_blank"><i class="fab fa-facebook"></i> Facebook</a>
        <?php endif; ?>

        <?php if (!empty($settings['whatsapp'])): ?>
          <a href="https://wa.me/977<?= e($settings['whatsapp']) ?>" target="_blank"><i class="fab fa-whatsapp"></i>
            WhatsApp</a>
        <?php endif; ?>
      </div>
    </section>
  </main>

  <!-- Footer -->
  <footer class="footer reveal" role="contentinfo">
    <span>© <?= date('Y') ?> <?= e($name) ?></span>

    <div class="footer-social">
      <?php if (!empty($settings['github_url'])): ?>
        <a href="<?= e($settings['github_url']) ?>" target="_blank" aria-label="GitHub"><i class="fab fa-github"></i></a>
      <?php endif; ?>
      <?php if (!empty($settings['linkedin_url'])): ?>
        <a href="<?= e($settings['linkedin_url']) ?>" target="_blank" aria-label="LinkedIn"><i
            class="fab fa-linkedin"></i></a>
      <?php endif; ?>
      <?php if (!empty($settings['instagram_url'])): ?>
        <a href="<?= e($settings['instagram_url']) ?>" target="_blank" aria-label="Instagram"><i
            class="fab fa-instagram"></i></a>
      <?php endif; ?>
      <?php if (!empty($settings['facebook_url'])): ?>
        <a href="<?= e($settings['facebook_url']) ?>" target="_blank" aria-label="Facebook"><i
            class="fab fa-facebook"></i></a>
      <?php endif; ?>
      <?php if (!empty($settings['whatsapp'])): ?>
        <a href="https://wa.me/977<?= e($settings['whatsapp']) ?>" target="_blank" aria-label="WhatsApp"><i
            class="fab fa-whatsapp"></i></a>
      <?php endif; ?>
    </div>

    <div class="footer-links">
      <a href="#work">Work</a>
      <a href="#about">About</a>
      <a href="#contact">Contact</a>
    </div>
  </footer>

  <script src="assets/js/main.js"></script>
</body>

</html>