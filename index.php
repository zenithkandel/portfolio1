<?php
require_once 'includes/config.php';

// Fetch all data
$settings = getSettings($pdo);
$skills = getSkills($pdo);
$projects = getProjects($pdo);

// Handle contact form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $subject = trim($_POST['subject'] ?? '');
    $message = trim($_POST['message'] ?? '');
    
    if ($name && $email && $message) {
        $stmt = $pdo->prepare("INSERT INTO messages (name, email, subject, message) VALUES (?, ?, ?, ?)");
        if ($stmt->execute([$name, $email, $subject, $message])) {
            $success = true;
        } else {
            $error = true;
        }
    } else {
        $error = true;
    }
}

// Parse typed roles from site title or use defaults
$roles = ['Full Stack Developer', 'UI/UX Designer', 'Problem Solver', 'Creative Coder'];
$rolesJson = json_encode($roles);

// Calculate stats
$totalSkills = count($skills);
$totalProjects = count($projects);
$yearsExperience = 5; // You can make this dynamic via settings
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="<?= e($settings['hero_subtitle'] ?? 'Portfolio') ?>">
    <title><?= e($settings['site_title'] ?? 'Portfolio') ?></title>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=JetBrains+Mono:wght@400;500;600&family=Space+Grotesk:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    
    <!-- Custom Styles -->
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <!-- Loading Screen -->
    <div class="loader">
        <div class="loader-content">
            <div class="loader-percentage">0%</div>
            <div class="loader-bar">
                <div class="loader-progress"></div>
            </div>
            <div class="loader-text">Initializing systems...</div>
        </div>
    </div>

    <!-- Custom Cursor -->
    <div class="cursor"></div>
    <div class="cursor-dot"></div>

    <!-- Particle Background -->
    <canvas id="particles-canvas"></canvas>

    <!-- Navigation -->
    <nav class="nav">
        <a href="#" class="nav-logo">
            <span class="logo-bracket">&lt;</span>
            <span><?= e(explode(' ', $settings['site_title'] ?? 'Dev')[0]) ?></span>
            <span class="logo-bracket">/&gt;</span>
        </a>
        
        <ul class="nav-links">
            <li><a href="#home" class="nav-link"><span class="link-number">01.</span> Home</a></li>
            <li><a href="#about" class="nav-link"><span class="link-number">02.</span> About</a></li>
            <li><a href="#skills" class="nav-link"><span class="link-number">03.</span> Skills</a></li>
            <li><a href="#projects" class="nav-link"><span class="link-number">04.</span> Projects</a></li>
            <li><a href="#contact" class="nav-link"><span class="link-number">05.</span> Contact</a></li>
        </ul>

        <a href="#contact" class="nav-cta">Let's Talk</a>
        
        <button class="mobile-menu-btn">
            <i class="fas fa-bars"></i>
        </button>
    </nav>

    <!-- Mobile Navigation -->
    <div class="mobile-overlay"></div>
    <div class="mobile-nav">
        <button class="mobile-nav-close">
            <i class="fas fa-times"></i>
        </button>
        <ul class="mobile-nav-links">
            <li><a href="#home" class="mobile-nav-link"><span class="link-number">01.</span> Home</a></li>
            <li><a href="#about" class="mobile-nav-link"><span class="link-number">02.</span> About</a></li>
            <li><a href="#skills" class="mobile-nav-link"><span class="link-number">03.</span> Skills</a></li>
            <li><a href="#projects" class="mobile-nav-link"><span class="link-number">04.</span> Projects</a></li>
            <li><a href="#contact" class="mobile-nav-link"><span class="link-number">05.</span> Contact</a></li>
        </ul>
    </div>

    <!-- Hero Section -->
    <section id="home" class="hero">
        <div class="hero-bg">
            <div class="hero-grid"></div>
            <div class="hero-glow hero-glow-1"></div>
            <div class="hero-glow hero-glow-2"></div>
        </div>
        
        <div class="hero-content">
            <div class="hero-tag reveal">
                <span class="status-dot"></span>
                Available for freelance work
            </div>
            
            <h1 class="hero-title reveal">
                <span class="greeting">Hello, I'm</span>
                <span class="line">
                    <span class="name glitch" data-text="<?= e($settings['hero_title'] ?? 'Developer') ?>">
                        <?= e($settings['hero_title'] ?? 'Developer') ?>
                    </span>
                </span>
                <span class="role">
                    I'm a <span class="typed-text" data-strings='<?= $rolesJson ?>'></span><span class="typed-cursor">|</span>
                </span>
            </h1>
            
            <p class="hero-subtitle reveal">
                <?= e($settings['hero_subtitle'] ?? 'Crafting digital experiences that make an impact.') ?>
            </p>
            
            <div class="hero-cta reveal">
                <a href="#projects" class="btn btn-primary">
                    View My Work
                    <i class="fas fa-arrow-right btn-icon"></i>
                </a>
                <a href="#contact" class="btn btn-secondary">
                    Get In Touch
                    <i class="fas fa-envelope btn-icon"></i>
                </a>
            </div>
            
            <div class="hero-stats reveal">
                <div class="stat">
                    <div class="stat-number" data-target="<?= $yearsExperience ?>">0+</div>
                    <div class="stat-label">Years Experience</div>
                </div>
                <div class="stat">
                    <div class="stat-number" data-target="<?= $totalProjects ?>">0+</div>
                    <div class="stat-label">Projects Completed</div>
                </div>
                <div class="stat">
                    <div class="stat-number" data-target="<?= $totalSkills ?>">0+</div>
                    <div class="stat-label">Technologies</div>
                </div>
            </div>
        </div>
        
        <div class="hero-scroll">
            <span>Scroll Down</span>
            <div class="scroll-line"></div>
        </div>
    </section>

    <!-- About Section -->
    <section id="about" class="about">
        <div class="section-container">
            <div class="about-grid">
                <div class="about-image reveal-left">
                    <div class="about-image-wrapper">
                        <img src="me.jpg" alt="Profile Photo" onerror="this.src='https://via.placeholder.com/500x500/1a1a25/00f0ff?text=Photo'">
                    </div>
                    <div class="about-image-decoration"></div>
                </div>
                
                <div class="about-content reveal-right">
                    <div class="section-header">
                        <span class="section-tag">About Me</span>
                        <h2 class="section-title">
                            Crafting <span class="highlight">Digital</span><br>
                            Experiences
                        </h2>
                    </div>
                    
                    <div class="about-text">
                        <?php 
                        $aboutText = $settings['about_text'] ?? 'I am a passionate developer with expertise in building modern web applications.';
                        $paragraphs = explode("\n", $aboutText);
                        foreach ($paragraphs as $p): 
                            if (trim($p)):
                        ?>
                        <p><?= e($p) ?></p>
                        <?php 
                            endif;
                        endforeach; 
                        ?>
                    </div>
                    
                    <div class="about-highlights">
                        <div class="highlight-item stagger-1">
                            <div class="highlight-icon">
                                <i class="fas fa-code"></i>
                            </div>
                            <span class="highlight-text">Clean Code</span>
                        </div>
                        <div class="highlight-item stagger-2">
                            <div class="highlight-icon">
                                <i class="fas fa-mobile-alt"></i>
                            </div>
                            <span class="highlight-text">Responsive Design</span>
                        </div>
                        <div class="highlight-item stagger-3">
                            <div class="highlight-icon">
                                <i class="fas fa-bolt"></i>
                            </div>
                            <span class="highlight-text">Fast Performance</span>
                        </div>
                        <div class="highlight-item stagger-4">
                            <div class="highlight-icon">
                                <i class="fas fa-shield-alt"></i>
                            </div>
                            <span class="highlight-text">Secure Solutions</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Skills Section -->
    <section id="skills" class="skills">
        <div class="section-container">
            <div class="section-header reveal">
                <span class="section-tag">My Arsenal</span>
                <h2 class="section-title">
                    Skills & <span class="highlight">Technologies</span>
                </h2>
            </div>
            
            <div class="skills-grid">
                <?php foreach ($skills as $index => $skill): 
                    // Generate random proficiency for demo (you can add this to skills table)
                    $proficiency = $skill['proficiency'] ?? rand(70, 98);
                    $level = $proficiency >= 90 ? 'Master' : ($proficiency >= 75 ? 'Advanced' : 'Intermediate');
                ?>
                <div class="skill-card reveal stagger-<?= ($index % 6) + 1 ?>">
                    <div class="skill-header">
                        <div class="skill-icon">
                            <i class="<?= e($skill['icon']) ?>"></i>
                        </div>
                        <div class="skill-info">
                            <h3><?= e($skill['name']) ?></h3>
                            <span class="skill-level"><?= $level ?></span>
                        </div>
                    </div>
                    <div class="skill-bar">
                        <div class="skill-progress" data-progress="<?= $proficiency ?>"></div>
                    </div>
                    <div class="skill-xp">
                        <span>LVL <?= floor($proficiency / 10) ?></span>
                        <span><?= $proficiency ?>% XP</span>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- Projects Section -->
    <section id="projects" class="projects">
        <div class="section-container">
            <div class="section-header reveal">
                <span class="section-tag">Portfolio</span>
                <h2 class="section-title">
                    Featured <span class="highlight">Projects</span>
                </h2>
            </div>
            
            <div class="projects-grid">
                <?php foreach ($projects as $index => $project): 
                    // Parse technologies as tags
                    $tags = array_slice(explode(',', $project['technologies'] ?? ''), 0, 3);
                ?>
                <div class="project-card reveal stagger-<?= ($index % 4) + 1 ?>">
                    <div class="project-image">
                        <?php if ($project['image']): ?>
                        <img src="<?= e($project['image']) ?>" alt="<?= e($project['title']) ?>" onerror="this.src='https://via.placeholder.com/800x500/1a1a25/00f0ff?text=Project'">
                        <?php else: ?>
                        <img src="https://via.placeholder.com/800x500/1a1a25/00f0ff?text=<?= urlencode($project['title']) ?>" alt="<?= e($project['title']) ?>">
                        <?php endif; ?>
                        
                        <?php if ($project['link']): ?>
                        <a href="<?= e($project['link']) ?>" target="_blank" class="project-overlay">
                            <span class="project-overlay-btn">
                                <i class="fas fa-arrow-up-right-from-square"></i>
                            </span>
                        </a>
                        <?php endif; ?>
                    </div>
                    
                    <div class="project-content">
                        <div class="project-tags">
                            <?php foreach ($tags as $tag): ?>
                            <span class="project-tag"><?= e(trim($tag)) ?></span>
                            <?php endforeach; ?>
                        </div>
                        
                        <h3 class="project-title"><?= e($project['title']) ?></h3>
                        <p class="project-description"><?= e($project['description']) ?></p>
                        
                        <?php if ($project['link']): ?>
                        <a href="<?= e($project['link']) ?>" target="_blank" class="project-link">
                            View Project <i class="fas fa-arrow-right"></i>
                        </a>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- Contact Section -->
    <section id="contact" class="contact">
        <div class="section-container">
            <div class="section-header reveal">
                <span class="section-tag">Get In Touch</span>
                <h2 class="section-title">
                    Let's <span class="highlight">Connect</span>
                </h2>
            </div>
            
            <div class="contact-grid">
                <div class="contact-info reveal-left">
                    <p class="contact-text">
                        Have a project in mind or just want to say hello? I'm always open to discussing new opportunities, 
                        creative ideas, or partnerships. Let's build something awesome together!
                    </p>
                    
                    <div class="contact-links">
                        <?php if (!empty($settings['contact_email'])): ?>
                        <a href="mailto:<?= e($settings['contact_email']) ?>" class="contact-link">
                            <div class="contact-link-icon">
                                <i class="fas fa-envelope"></i>
                            </div>
                            <div class="contact-link-text">
                                <span class="contact-link-label">Email</span>
                                <span class="contact-link-value"><?= e($settings['contact_email']) ?></span>
                            </div>
                            <i class="fas fa-arrow-right"></i>
                        </a>
                        <?php endif; ?>
                        
                        <?php if (!empty($settings['contact_phone'])): ?>
                        <a href="tel:<?= e($settings['contact_phone']) ?>" class="contact-link">
                            <div class="contact-link-icon">
                                <i class="fas fa-phone"></i>
                            </div>
                            <div class="contact-link-text">
                                <span class="contact-link-label">Phone</span>
                                <span class="contact-link-value"><?= e($settings['contact_phone']) ?></span>
                            </div>
                            <i class="fas fa-arrow-right"></i>
                        </a>
                        <?php endif; ?>
                        
                        <?php if (!empty($settings['contact_location'])): ?>
                        <div class="contact-link">
                            <div class="contact-link-icon">
                                <i class="fas fa-map-marker-alt"></i>
                            </div>
                            <div class="contact-link-text">
                                <span class="contact-link-label">Location</span>
                                <span class="contact-link-value"><?= e($settings['contact_location']) ?></span>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <div class="contact-form-wrapper reveal-right">
                    <?php if (isset($success)): ?>
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle"></i>
                        Message sent successfully! I'll get back to you soon.
                    </div>
                    <?php endif; ?>
                    
                    <?php if (isset($error)): ?>
                    <div class="alert alert-error">
                        <i class="fas fa-exclamation-circle"></i>
                        Please fill in all required fields.
                    </div>
                    <?php endif; ?>
                    
                    <form class="contact-form" method="POST" action="#contact">
                        <div class="form-row">
                            <div class="form-group">
                                <label class="form-label">
                                    Name <span class="required">*</span>
                                </label>
                                <input type="text" name="name" class="form-input" placeholder="John Doe" required>
                            </div>
                            <div class="form-group">
                                <label class="form-label">
                                    Email <span class="required">*</span>
                                </label>
                                <input type="email" name="email" class="form-input" placeholder="john@example.com" required>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">Subject</label>
                            <input type="text" name="subject" class="form-input" placeholder="Project Inquiry">
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">
                                Message <span class="required">*</span>
                            </label>
                            <textarea name="message" class="form-textarea" placeholder="Tell me about your project..." required></textarea>
                        </div>
                        
                        <button type="submit" class="form-submit">
                            <i class="fas fa-paper-plane"></i>
                            Send Message
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="footer-content">
            <div class="footer-text">
                &copy; <?= date('Y') ?> <?= e($settings['site_title'] ?? 'Portfolio') ?>. Crafted with 
                <i class="fas fa-heart" style="color: var(--accent-tertiary);"></i> 
                and lots of <i class="fas fa-coffee" style="color: var(--accent-warning);"></i>
            </div>
            
            <div class="footer-social">
                <?php if (!empty($settings['social_github'])): ?>
                <a href="<?= e($settings['social_github']) ?>" target="_blank" title="GitHub">
                    <i class="fab fa-github"></i>
                </a>
                <?php endif; ?>
                
                <?php if (!empty($settings['social_linkedin'])): ?>
                <a href="<?= e($settings['social_linkedin']) ?>" target="_blank" title="LinkedIn">
                    <i class="fab fa-linkedin-in"></i>
                </a>
                <?php endif; ?>
                
                <?php if (!empty($settings['social_twitter'])): ?>
                <a href="<?= e($settings['social_twitter']) ?>" target="_blank" title="Twitter">
                    <i class="fab fa-twitter"></i>
                </a>
                <?php endif; ?>
                
                <?php if (!empty($settings['contact_email'])): ?>
                <a href="mailto:<?= e($settings['contact_email']) ?>" title="Email">
                    <i class="fas fa-envelope"></i>
                </a>
                <?php endif; ?>
            </div>
        </div>
    </footer>

    <!-- Scripts -->
    <script src="assets/js/main.js"></script>
</body>
</html>
