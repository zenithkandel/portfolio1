<?php
require_once 'includes/config.php';

// Get data from database
$settings = getSettings($pdo);
$skills = getSkills($pdo);
$projects = getProjects($pdo);

// Handle contact form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['contact_submit'])) {
  $name = trim($_POST['name'] ?? '');
  $email = trim($_POST['email'] ?? '');
  $subject = trim($_POST['subject'] ?? '');
  $message = trim($_POST['message'] ?? '');

  if ($name && $email && $message) {
    $stmt = $pdo->prepare("INSERT INTO messages (name, email, subject, message) VALUES (?, ?, ?, ?)");
    $stmt->execute([$name, $email, $subject, $message]);
    $formSuccess = true;
  } else {
    $formError = "Please fill in all required fields.";
  }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= e($settings['site_title'] ?? 'Portfolio') ?></title>
  <meta name="description" content="<?= e($settings['site_description'] ?? '') ?>">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
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
      --bg: #fafafa;
      --text: #1a1a1a;
      --text-muted: #666;
      --border: #e5e5e5;
      --accent: #2563eb;
      --accent-hover: #1d4ed8;
      --card-bg: #fff;
      --section-bg: #f5f5f5;
      --max-width: 1100px;
    }

    html {
      scroll-behavior: smooth;
    }

    body {
      font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
      background: var(--bg);
      color: var(--text);
      line-height: 1.6;
      font-size: 16px;
    }

    /* Layout */
    .container {
      max-width: var(--max-width);
      margin: 0 auto;
      padding: 0 24px;
    }

    section {
      padding: 80px 0;
    }

    section:nth-child(even) {
      background: var(--section-bg);
    }

    /* Header */
    header {
      position: sticky;
      top: 0;
      z-index: 100;
      background: rgba(255, 255, 255, 0.95);
      backdrop-filter: blur(10px);
      border-bottom: 1px solid var(--border);
    }

    .nav {
      display: flex;
      align-items: center;
      justify-content: space-between;
      height: 64px;
    }

    .logo {
      font-weight: 700;
      font-size: 20px;
      color: var(--text);
      text-decoration: none;
    }

    .nav-links {
      display: flex;
      gap: 32px;
      list-style: none;
    }

    .nav-links a {
      color: var(--text-muted);
      text-decoration: none;
      font-size: 15px;
      font-weight: 500;
      transition: color 0.2s;
    }

    .nav-links a:hover {
      color: var(--accent);
    }

    .menu-toggle {
      display: none;
      background: none;
      border: none;
      font-size: 24px;
      cursor: pointer;
      color: var(--text);
    }

    /* Hero */
    .hero {
      min-height: calc(100vh - 64px);
      display: flex;
      align-items: center;
      padding: 60px 0;
    }

    .hero-content {
      max-width: 680px;
    }

    .hero-tag {
      display: inline-block;
      background: var(--accent);
      color: white;
      padding: 6px 14px;
      border-radius: 20px;
      font-size: 13px;
      font-weight: 500;
      margin-bottom: 24px;
    }

    .hero h1 {
      font-size: clamp(36px, 5vw, 56px);
      font-weight: 700;
      line-height: 1.1;
      margin-bottom: 20px;
    }

    .hero p {
      color: var(--text-muted);
      font-size: 18px;
      line-height: 1.7;
      margin-bottom: 32px;
    }

    .hero-buttons {
      display: flex;
      gap: 16px;
      flex-wrap: wrap;
    }

    .btn {
      display: inline-flex;
      align-items: center;
      gap: 8px;
      padding: 14px 28px;
      font-size: 15px;
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
      transform: translateY(-2px);
    }

    .btn-secondary {
      background: transparent;
      color: var(--text);
      border: 2px solid var(--border);
    }

    .btn-secondary:hover {
      border-color: var(--accent);
      color: var(--accent);
    }

    /* Section Header */
    .section-header {
      margin-bottom: 48px;
    }

    .section-header h2 {
      font-size: 32px;
      font-weight: 700;
      margin-bottom: 12px;
    }

    .section-header p {
      color: var(--text-muted);
      font-size: 17px;
    }

    /* About */
    .about-grid {
      display: grid;
      grid-template-columns: 1fr 280px;
      gap: 48px;
      align-items: start;
    }

    .about-text p {
      color: var(--text-muted);
      margin-bottom: 16px;
      font-size: 16px;
    }

    .about-photo {
      border-radius: 12px;
      overflow: hidden;
      box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
    }

    .about-photo img {
      width: 100%;
      height: auto;
      display: block;
    }

    /* Skills */
    .skills-grid {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
      gap: 16px;
    }

    .skill-card {
      display: flex;
      align-items: center;
      gap: 14px;
      padding: 20px;
      background: var(--card-bg);
      border: 1px solid var(--border);
      border-radius: 10px;
      transition: all 0.2s;
    }

    .skill-card:hover {
      transform: translateY(-3px);
      box-shadow: 0 8px 24px rgba(0, 0, 0, 0.06);
    }

    .skill-card i {
      font-size: 24px;
      color: var(--accent);
      width: 28px;
      text-align: center;
    }

    .skill-card span {
      font-weight: 500;
      font-size: 15px;
    }

    /* Projects */
    .projects-grid {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
      gap: 24px;
    }

    .project-card {
      background: var(--card-bg);
      border: 1px solid var(--border);
      border-radius: 12px;
      padding: 28px;
      text-decoration: none;
      color: inherit;
      transition: all 0.2s;
      display: block;
    }

    .project-card:hover {
      transform: translateY(-4px);
      box-shadow: 0 12px 32px rgba(0, 0, 0, 0.08);
      border-color: var(--accent);
    }

    .project-tags {
      display: flex;
      gap: 8px;
      margin-bottom: 14px;
    }

    .project-tags span {
      background: var(--section-bg);
      padding: 4px 10px;
      border-radius: 4px;
      font-size: 12px;
      font-weight: 500;
      color: var(--text-muted);
    }

    .project-card h3 {
      font-size: 19px;
      font-weight: 600;
      margin-bottom: 10px;
    }

    .project-card p {
      color: var(--text-muted);
      font-size: 15px;
      line-height: 1.6;
    }

    .project-link {
      display: inline-flex;
      align-items: center;
      gap: 6px;
      margin-top: 16px;
      color: var(--accent);
      font-size: 14px;
      font-weight: 500;
    }

    /* Contact */
    .contact-grid {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 48px;
    }

    .contact-form {
      display: flex;
      flex-direction: column;
      gap: 20px;
    }

    .form-row {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 16px;
    }

    .form-group {
      display: flex;
      flex-direction: column;
      gap: 8px;
    }

    .form-group label {
      font-size: 14px;
      font-weight: 500;
      color: var(--text);
    }

    .form-group input,
    .form-group textarea {
      padding: 14px 16px;
      border: 1px solid var(--border);
      border-radius: 8px;
      font-size: 15px;
      font-family: inherit;
      transition: border-color 0.2s;
      background: var(--card-bg);
    }

    .form-group input:focus,
    .form-group textarea:focus {
      outline: none;
      border-color: var(--accent);
    }

    .form-group textarea {
      resize: vertical;
      min-height: 140px;
    }

    .contact-info {
      display: flex;
      flex-direction: column;
      gap: 16px;
    }

    .contact-link {
      display: flex;
      align-items: center;
      gap: 14px;
      padding: 18px 20px;
      background: var(--card-bg);
      border: 1px solid var(--border);
      border-radius: 10px;
      text-decoration: none;
      color: inherit;
      transition: all 0.2s;
    }

    .contact-link:hover {
      border-color: var(--accent);
      transform: translateX(4px);
    }

    .contact-link i {
      font-size: 20px;
      color: var(--accent);
      width: 24px;
      text-align: center;
    }

    .contact-link span {
      font-size: 15px;
    }

    /* Alert */
    .alert {
      padding: 16px 20px;
      border-radius: 8px;
      margin-bottom: 20px;
      font-size: 15px;
    }

    .alert-success {
      background: #d4edda;
      color: #155724;
      border: 1px solid #c3e6cb;
    }

    .alert-error {
      background: #f8d7da;
      color: #721c24;
      border: 1px solid #f5c6cb;
    }

    /* Footer */
    footer {
      padding: 32px 0;
      border-top: 1px solid var(--border);
      text-align: center;
      color: var(--text-muted);
      font-size: 14px;
    }

    /* Mobile */
    @media (max-width: 768px) {
      .nav-links {
        display: none;
        position: absolute;
        top: 64px;
        left: 0;
        right: 0;
        background: white;
        flex-direction: column;
        padding: 20px;
        gap: 16px;
        border-bottom: 1px solid var(--border);
      }

      .nav-links.active {
        display: flex;
      }

      .menu-toggle {
        display: block;
      }

      section {
        padding: 60px 0;
      }

      .about-grid {
        grid-template-columns: 1fr;
      }

      .about-photo {
        order: -1;
        max-width: 200px;
      }

      .contact-grid {
        grid-template-columns: 1fr;
      }

      .form-row {
        grid-template-columns: 1fr;
      }

      .hero-buttons {
        flex-direction: column;
      }

      .btn {
        justify-content: center;
      }
    }

    /* Animations */
    .fade-in {
      opacity: 0;
      transform: translateY(20px);
      transition: opacity 0.6s ease, transform 0.6s ease;
    }

    .fade-in.visible {
      opacity: 1;
      transform: translateY(0);
    }
  </style>
</head>

<body>
  <header>
    <div class="container">
      <nav class="nav">
        <a href="#" class="logo">ZENITH</a>
        <ul class="nav-links">
          <li><a href="#about">About</a></li>
          <li><a href="#skills">Skills</a></li>
          <li><a href="#projects">Projects</a></li>
          <li><a href="#contact">Contact</a></li>
        </ul>
        <button class="menu-toggle" aria-label="Menu">
          <i class="fas fa-bars"></i>
        </button>
      </nav>
    </div>
  </header>

  <main>
    <!-- Hero -->
    <section class="hero">
      <div class="container">
        <div class="hero-content">
          <span class="hero-tag"><?= e($settings['hero_tagline'] ?? 'Frontend Developer') ?></span>
          <h1><?= e($settings['hero_title'] ?? 'Hi, I\'m Zenith Kandel.') ?></h1>
          <p><?= e($settings['hero_subtitle'] ?? 'I build clean, fast, and functional web experiences.') ?></p>
          <div class="hero-buttons">
            <a href="#projects" class="btn btn-primary">
              View Projects
              <i class="fas fa-arrow-right"></i>
            </a>
            <a href="#contact" class="btn btn-secondary">Get in Touch</a>
          </div>
        </div>
      </div>
    </section>

    <!-- About -->
    <section id="about">
      <div class="container">
        <div class="section-header fade-in">
          <h2>About Me</h2>
        </div>
        <div class="about-grid">
          <div class="about-text fade-in">
            <p><?= nl2br(e($settings['about_text'] ?? '')) ?></p>
            <?php if (!empty($settings['about_text_2'])): ?>
              <p><?= nl2br(e($settings['about_text_2'])) ?></p>
            <?php endif; ?>
          </div>
          <div class="about-photo fade-in">
            <img src="<?= e($settings['photo_url'] ?? 'me.jpg') ?>" alt="Photo">
          </div>
        </div>
      </div>
    </section>

    <!-- Skills -->
    <section id="skills">
      <div class="container">
        <div class="section-header fade-in">
          <h2>Skills & Technologies</h2>
          <p>Tools and technologies I work with</p>
        </div>
        <div class="skills-grid">
          <?php foreach ($skills as $skill): ?>
            <div class="skill-card fade-in">
              <i class="<?= e($skill['icon']) ?>"></i>
              <span><?= e($skill['name']) ?></span>
            </div>
          <?php endforeach; ?>
        </div>
      </div>
    </section>

    <!-- Projects -->
    <section id="projects">
      <div class="container">
        <div class="section-header fade-in">
          <h2>Projects</h2>
          <p>Some things I've built</p>
        </div>
        <div class="projects-grid">
          <?php foreach ($projects as $project): ?>
            <a href="<?= e($project['url']) ?>" target="_blank" rel="noopener" class="project-card fade-in">
              <div class="project-tags">
                <?php if (!empty($project['tag1'])): ?>
                  <span><?= e($project['tag1']) ?></span>
                <?php endif; ?>
                <?php if (!empty($project['tag2'])): ?>
                  <span><?= e($project['tag2']) ?></span>
                <?php endif; ?>
              </div>
              <h3><?= e($project['title']) ?></h3>
              <p><?= e($project['description']) ?></p>
              <span class="project-link">
                View Project <i class="fas fa-external-link-alt"></i>
              </span>
            </a>
          <?php endforeach; ?>
        </div>
      </div>
    </section>

    <!-- Contact -->
    <section id="contact">
      <div class="container">
        <div class="section-header fade-in">
          <h2>Get in Touch</h2>
          <p>Open to internships, collaborations, and freelance work</p>
        </div>
        <div class="contact-grid">
          <div class="fade-in">
            <?php if (isset($formSuccess)): ?>
              <div class="alert alert-success">
                <i class="fas fa-check-circle"></i> Message sent successfully! I'll get back to you soon.
              </div>
            <?php endif; ?>
            <?php if (isset($formError)): ?>
              <div class="alert alert-error">
                <i class="fas fa-exclamation-circle"></i> <?= e($formError) ?>
              </div>
            <?php endif; ?>
            <form class="contact-form" method="POST" action="#contact">
              <div class="form-row">
                <div class="form-group">
                  <label for="name">Name *</label>
                  <input type="text" id="name" name="name" required>
                </div>
                <div class="form-group">
                  <label for="email">Email *</label>
                  <input type="email" id="email" name="email" required>
                </div>
              </div>
              <div class="form-group">
                <label for="subject">Subject</label>
                <input type="text" id="subject" name="subject">
              </div>
              <div class="form-group">
                <label for="message">Message *</label>
                <textarea id="message" name="message" required></textarea>
              </div>
              <button type="submit" name="contact_submit" class="btn btn-primary">
                Send Message <i class="fas fa-paper-plane"></i>
              </button>
            </form>
          </div>
          <div class="contact-info fade-in">
            <?php if (!empty($settings['email'])): ?>
              <a href="mailto:<?= e($settings['email']) ?>" class="contact-link">
                <i class="fas fa-envelope"></i>
                <span><?= e($settings['email']) ?></span>
              </a>
            <?php endif; ?>
            <?php if (!empty($settings['phone'])): ?>
              <a href="tel:<?= e(preg_replace('/[^0-9+]/', '', $settings['phone'])) ?>" class="contact-link">
                <i class="fas fa-phone"></i>
                <span><?= e($settings['phone']) ?></span>
              </a>
            <?php endif; ?>
            <?php if (!empty($settings['github_url'])): ?>
              <a href="<?= e($settings['github_url']) ?>" target="_blank" rel="noopener" class="contact-link">
                <i class="fab fa-github"></i>
                <span>GitHub</span>
              </a>
            <?php endif; ?>
            <?php if (!empty($settings['linkedin_url'])): ?>
              <a href="<?= e($settings['linkedin_url']) ?>" target="_blank" rel="noopener" class="contact-link">
                <i class="fab fa-linkedin"></i>
                <span>LinkedIn</span>
              </a>
            <?php endif; ?>
            <?php if (!empty($settings['instagram_url'])): ?>
              <a href="<?= e($settings['instagram_url']) ?>" target="_blank" rel="noopener" class="contact-link">
                <i class="fab fa-instagram"></i>
                <span>Instagram</span>
              </a>
            <?php endif; ?>
          </div>
        </div>
      </div>
    </section>
  </main>

  <footer>
    <div class="container">
      <p>&copy; <?= date('Y') ?> Zenith Kandel. All rights reserved.</p>
    </div>
  </footer>

  <script>
    // Mobile menu toggle
    const menuToggle = document.querySelector('.menu-toggle');
    const navLinks = document.querySelector('.nav-links');

    menuToggle.addEventListener('click', () => {
      navLinks.classList.toggle('active');
      const icon = menuToggle.querySelector('i');
      icon.classList.toggle('fa-bars');
      icon.classList.toggle('fa-times');
    });

    // Close menu on link click
    document.querySelectorAll('.nav-links a').forEach(link => {
      link.addEventListener('click', () => {
        navLinks.classList.remove('active');
        const icon = menuToggle.querySelector('i');
        icon.classList.add('fa-bars');
        icon.classList.remove('fa-times');
      });
    });

    // Scroll animations
    const observerOptions = {
      threshold: 0.1,
      rootMargin: '0px 0px -50px 0px'
    };

    const observer = new IntersectionObserver((entries) => {
      entries.forEach(entry => {
        if (entry.isIntersecting) {
          entry.target.classList.add('visible');
          observer.unobserve(entry.target);
        }
      });
    }, observerOptions);

    document.querySelectorAll('.fade-in').forEach(el => observer.observe(el));
  </script>
</body>

</html>