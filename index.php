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

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= e($settings['site_title'] ?? 'Portfolio') ?></title>
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
  <header class="header">
    <a href="#" class="logo"><?= e(explode(' ', $name)[0]) ?></a>

    <ul class="nav-menu">
      <li><a href="#work">Work</a></li>
      <li><a href="#about">About</a></li>
      <li><a href="#contact">Contact</a></li>
    </ul>

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

  <!-- Hero -->
  <section class="hero">
    <div class="hero-content">
      <p class="hero-intro"><?= e($role) ?></p>

      <h1 class="hero-title">
        <span class="line"><span class="word"><?= e($name) ?></span></span>
      </h1>

      <div class="hero-bottom">
        <div class="hero-cta">
          <a href="#work" class="btn btn-primary">View Work</a>
          <a href="#contact" class="btn btn-outline">Get In Touch</a>
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
  <section id="work">
    <div class="section-label">Selected Work</div>

    <div class="projects-list">
      <?php foreach ($projects as $i => $project):
        $tags = array_filter([$project['tag1'] ?? '', $project['tag2'] ?? '']);
        ?>
        <article class="project-item">
          <div class="project-info">
            <span class="project-number"><?= str_pad($i + 1, 2, '0', STR_PAD_LEFT) ?></span>
            <h2 class="project-title"><?= e($project['title']) ?></h2>
            <div class="project-tags">
              <?php foreach ($tags as $tag): ?>
                <span class="project-tag"><?= e($tag) ?></span>
              <?php endforeach; ?>
            </div>
          </div>

          <div class="project-image">
            <?php if (!empty($project['image'])): ?>
              <img src="<?= e($project['image']) ?>" alt="<?= e($project['title']) ?>">
            <?php else: ?>
              <img src="https://via.placeholder.com/800x500/141414/333?text=<?= urlencode($project['title']) ?>" alt="">
            <?php endif; ?>
          </div>

          <?php if (!empty($project['url'])): ?>
            <a href="<?= e($project['url']) ?>" target="_blank" class="project-link"></a>
          <?php endif; ?>

          <span class="project-arrow">→</span>
        </article>
      <?php endforeach; ?>
    </div>
  </section>

  <!-- About -->
  <section id="about" class="about">
    <div class="about-image reveal-left">
      <img src="me.jpg" alt="<?= e($name) ?>"
        onerror="this.src='https://via.placeholder.com/600x800/141414/333?text=Photo'">
    </div>

    <div class="about-content reveal-right">
      <div class="section-label">About</div>

      <h2 class="section-title"><?= e($name) ?></h2>

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
  <section class="skills-section">
    <div class="skills-inner">
      <div class="section-label">Technologies</div>

      <div class="skills-grid">
        <?php foreach ($skills as $skill): ?>
          <span class="skill-tag"><?= e($skill['name']) ?></span>
        <?php endforeach; ?>
      </div>
    </div>
  </section>

  <!-- Contact -->
  <section id="contact" class="contact-section">
    <h2 class="contact-title">
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

  <!-- Footer -->
  <footer class="footer reveal">
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