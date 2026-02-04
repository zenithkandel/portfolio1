// ====== DOM READY ======
document.addEventListener('DOMContentLoaded', () => {
  initYear();
  initRevealAnimations();
  initMobileMenu();
  initSmoothScroll();
  initContactForm();
  initHeaderScroll();
  fetchGitHubStats();
});

// ====== YEAR ======
function initYear() {
  const yearEl = document.getElementById('year');
  if (yearEl) yearEl.textContent = new Date().getFullYear();
}

// ====== REVEAL ON SCROLL ======
function initRevealAnimations() {
  const reveals = document.querySelectorAll('.reveal');

  const observer = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        entry.target.classList.add('visible');
        observer.unobserve(entry.target);
      }
    });
  }, {
    threshold: 0.1,
    rootMargin: '0px 0px -50px 0px'
  });

  reveals.forEach(el => observer.observe(el));
}

// ====== MOBILE MENU ======
function initMobileMenu() {
  const toggle = document.querySelector('.mobile-toggle');
  const menu = document.querySelector('.mobile-menu');
  const links = document.querySelectorAll('.mobile-link');

  if (!toggle || !menu) return;

  toggle.addEventListener('click', () => {
    menu.classList.toggle('active');
    toggle.classList.toggle('active');
    document.body.style.overflow = menu.classList.contains('active') ? 'hidden' : '';
  });

  links.forEach(link => {
    link.addEventListener('click', () => {
      menu.classList.remove('active');
      toggle.classList.remove('active');
      document.body.style.overflow = '';
    });
  });
}

// ====== SMOOTH SCROLL ======
function initSmoothScroll() {
  document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function (e) {
      e.preventDefault();
      const target = document.querySelector(this.getAttribute('href'));
      if (target) {
        target.scrollIntoView({ behavior: 'smooth' });
      }
    });
  });
}

// ====== HEADER SCROLL EFFECT ======
function initHeaderScroll() {
  const header = document.getElementById('header');
  if (!header) return;

  let lastScroll = 0;

  window.addEventListener('scroll', () => {
    const currentScroll = window.pageYOffset;

    if (currentScroll > 100) {
      header.style.boxShadow = '0 4px 20px rgba(0, 0, 0, 0.3)';
    } else {
      header.style.boxShadow = 'none';
    }

    lastScroll = currentScroll;
  });
}

// ====== CONTACT FORM ======
function initContactForm() {
  const form = document.getElementById('contact-form');
  if (!form) return;

  form.addEventListener('submit', async (e) => {
    e.preventDefault();

    const btn = form.querySelector('button[type="submit"]');
    const originalText = btn.innerHTML;

    // Show loading state
    btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Sending...';
    btn.disabled = true;

    // Simulate form submission (replace with actual backend call)
    await new Promise(resolve => setTimeout(resolve, 1500));

    // Show success
    btn.innerHTML = '<i class="fa-solid fa-check"></i> Message Sent!';
    btn.style.background = 'linear-gradient(135deg, #22c55e 0%, #16a34a 100%)';

    // Reset form
    form.reset();

    // Reset button after 3 seconds
    setTimeout(() => {
      btn.innerHTML = originalText;
      btn.style.background = '';
      btn.disabled = false;
    }, 3000);
  });
}

// ====== GITHUB API ======
const GITHUB_USERNAME = 'zenithkandel';

async function fetchGitHubStats() {
  try {
    // Fetch user data
    const userResponse = await fetch(`https://api.github.com/users/${GITHUB_USERNAME}`);
    const userData = await userResponse.json();

    // Fetch repositories
    const reposResponse = await fetch(`https://api.github.com/users/${GITHUB_USERNAME}/repos?per_page=100`);
    const repos = await reposResponse.json();

    // Calculate stats
    const totalStars = repos.reduce((sum, repo) => sum + repo.stargazers_count, 0);
    const totalForks = repos.reduce((sum, repo) => sum + repo.forks_count, 0);
    const totalCommits = repos.length * 15; // Estimate

    // Update hero stats
    animateValue('stat-repos', 0, userData.public_repos, 1500);
    animateValue('stat-commits', 0, totalCommits, 1500);

    // Update about section floating cards
    const reposEl = document.getElementById('github-repos');
    const starsEl = document.getElementById('github-stars');

    if (reposEl) animateValue('github-repos', 0, userData.public_repos, 1500);
    if (starsEl) animateValue('github-stars', 0, totalStars, 1500);

    console.log('GitHub Stats:', {
      repos: userData.public_repos,
      stars: totalStars,
      forks: totalForks,
      followers: userData.followers
    });

  } catch (error) {
    console.error('Error fetching GitHub stats:', error);
    // Set fallback values
    document.getElementById('stat-repos').textContent = '60+';
    document.getElementById('stat-commits').textContent = '500+';
    document.getElementById('github-repos').textContent = '60+';
    document.getElementById('github-stars').textContent = '10+';
  }
}

// ====== ANIMATE NUMBER VALUE ======
function animateValue(elementId, start, end, duration) {
  const element = document.getElementById(elementId);
  if (!element) return;

  const range = end - start;
  const increment = end > start ? 1 : -1;
  const stepTime = Math.abs(Math.floor(duration / range));
  let current = start;

  const timer = setInterval(() => {
    current += increment;
    element.textContent = current;
    if (current === end) {
      clearInterval(timer);
    }
  }, stepTime);
}

// ====== TYPING EFFECT (optional enhancement) ======
function typeWriter(element, text, speed = 50) {
  let i = 0;
  element.textContent = '';

  function type() {
    if (i < text.length) {
      element.textContent += text.charAt(i);
      i++;
      setTimeout(type, speed);
    }
  }

  type();
}

// ====== PARALLAX EFFECT ON SCROLL ======
window.addEventListener('scroll', () => {
  const scrolled = window.pageYOffset;
  const gradientBg = document.querySelector('.gradient-bg');

  if (gradientBg) {
    gradientBg.style.transform = `translateY(${scrolled * 0.3}px)`;
  }
});

// ====== KEYBOARD NAVIGATION ======
document.addEventListener('keydown', (e) => {
  // Close mobile menu on Escape
  if (e.key === 'Escape') {
    const menu = document.querySelector('.mobile-menu');
    const toggle = document.querySelector('.mobile-toggle');

    if (menu && menu.classList.contains('active')) {
      menu.classList.remove('active');
      toggle.classList.remove('active');
      document.body.style.overflow = '';
    }
  }
});

// ====== PREFERS REDUCED MOTION ======
const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)');

if (prefersReducedMotion.matches) {
  // Disable animations for users who prefer reduced motion
  document.documentElement.style.setProperty('--transition', '0s');
}

// ====== CONSOLE MESSAGE ======
console.log('%c👋 Hey there, curious developer!', 'font-size: 20px; font-weight: bold; color: #6366f1;');
console.log('%cFeel free to check out my code on GitHub: https://github.com/zenithkandel', 'font-size: 14px; color: #a1a1aa;');
