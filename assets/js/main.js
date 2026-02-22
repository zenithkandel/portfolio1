/**
 * Portfolio - Minimal interactions
 */

document.addEventListener('DOMContentLoaded', () => {
    initTheme();
    initLoader();
    initCursor();
    initReveal();
    initMobileNav();
    initTime();
    initHeader();
    initContactForm();
    initProjectsSlider();
});

// Projects horizontal slider with navigation and indicators
function initProjectsSlider() {
    const slider = document.querySelector('.projects-list');
    const prevBtn = document.querySelector('.projects-nav-prev');
    const nextBtn = document.querySelector('.projects-nav-next');
    const indicatorsContainer = document.querySelector('.projects-indicators');

    if (!slider) return;

    const items = slider.querySelectorAll('.project-item');
    if (items.length === 0) return;

    // Create indicators
    items.forEach((_, index) => {
        const indicator = document.createElement('button');
        indicator.className = 'projects-indicator' + (index === 0 ? ' active' : '');
        indicator.setAttribute('aria-label', `Go to project ${index + 1}`);
        indicator.addEventListener('click', () => scrollToProject(index));
        indicatorsContainer?.appendChild(indicator);
    });

    const indicators = indicatorsContainer?.querySelectorAll('.projects-indicator');

    // Get card width for scrolling
    function getScrollAmount() {
        const item = items[0];
        if (!item) return 300;
        return item.offsetWidth + parseInt(getComputedStyle(slider).gap) || 16;
    }

    // Scroll to specific project
    function scrollToProject(index) {
        const scrollAmount = getScrollAmount();
        slider.scrollTo({
            left: index * scrollAmount,
            behavior: 'smooth'
        });
    }

    // Update active indicator
    function updateIndicators() {
        const scrollAmount = getScrollAmount();
        const activeIndex = Math.round(slider.scrollLeft / scrollAmount);

        indicators?.forEach((indicator, index) => {
            indicator.classList.toggle('active', index === activeIndex);
        });

        // Update nav button states
        if (prevBtn) prevBtn.disabled = slider.scrollLeft <= 10;
        if (nextBtn) nextBtn.disabled = slider.scrollLeft >= slider.scrollWidth - slider.clientWidth - 10;
    }

    // Navigation buttons
    if (prevBtn) {
        prevBtn.addEventListener('click', () => {
            slider.scrollBy({ left: -getScrollAmount(), behavior: 'smooth' });
        });
    }

    if (nextBtn) {
        nextBtn.addEventListener('click', () => {
            slider.scrollBy({ left: getScrollAmount(), behavior: 'smooth' });
        });
    }

    // Listen for scroll changes
    slider.addEventListener('scroll', updateIndicators, { passive: true });

    // Initial state
    updateIndicators();

    // Touch/swipe support is native via scroll-snap
}

// Theme Toggle
function initTheme() {
    const toggle = document.querySelector('.theme-toggle');
    if (!toggle) return;

    // Check for saved theme preference or default to dark
    const savedTheme = localStorage.getItem('theme') || 'dark';
    document.documentElement.setAttribute('data-theme', savedTheme);

    toggle.addEventListener('click', () => {
        const currentTheme = document.documentElement.getAttribute('data-theme');
        const newTheme = currentTheme === 'dark' ? 'light' : 'dark';

        document.documentElement.setAttribute('data-theme', newTheme);
        localStorage.setItem('theme', newTheme);
    });
}

// Loader
function initLoader() {
    const loader = document.querySelector('.loader');
    if (!loader) return;

    // Wait for everything to load
    window.addEventListener('load', () => {
        setTimeout(() => {
            loader.classList.add('hidden');
            document.body.classList.add('loaded');
        }, 2000);
    });

    // Fallback
    setTimeout(() => {
        loader.classList.add('hidden');
        document.body.classList.add('loaded');
    }, 3500);
}

// Header scroll effect
function initHeader() {
    const header = document.querySelector('.header');
    if (!header) return;

    let lastScroll = 0;

    window.addEventListener('scroll', () => {
        const currentScroll = window.pageYOffset;

        if (currentScroll > 100) {
            header.classList.add('scrolled');
        } else {
            header.classList.remove('scrolled');
        }

        lastScroll = currentScroll;
    });
}

// Subtle cursor with glow
function initCursor() {
    if (window.matchMedia('(pointer: coarse)').matches) return;

    const cursor = document.createElement('div');
    cursor.className = 'cursor';
    document.body.appendChild(cursor);

    // Glow element that follows mouse
    const glow = document.createElement('div');
    glow.className = 'cursor-glow';
    glow.style.cssText = `
        position: fixed;
        width: 300px;
        height: 300px;
        background: radial-gradient(circle, rgba(255, 255, 255, 0.04) 0%, transparent 70%);
        pointer-events: none;
        z-index: -1;
        transform: translate(-50%, -50%);
        transition: opacity 0.3s;
    `;
    document.body.appendChild(glow);

    let x = 0, y = 0;
    let glowX = 0, glowY = 0;
    let targetX = 0, targetY = 0;

    document.addEventListener('mousemove', e => {
        targetX = e.clientX;
        targetY = e.clientY;
    });

    const interactives = document.querySelectorAll('a, button');
    interactives.forEach(el => {
        el.addEventListener('mouseenter', () => cursor.classList.add('hover'));
        el.addEventListener('mouseleave', () => cursor.classList.remove('hover'));
    });

    function animate() {
        x += (targetX - x) * 0.15;
        y += (targetY - y) * 0.15;
        glowX += (targetX - glowX) * 0.05;
        glowY += (targetY - glowY) * 0.05;

        cursor.style.left = x + 'px';
        cursor.style.top = y + 'px';
        glow.style.left = glowX + 'px';
        glow.style.top = glowY + 'px';
        requestAnimationFrame(animate);
    }
    animate();
}

// Scroll reveal - handles all reveal types with stagger
function initReveal() {
    const reveals = document.querySelectorAll('.reveal, .reveal-left, .reveal-right, .reveal-scale, .reveal-fade, .section-label, .skills-grid, .contact-title, .contact-section, .project-item, .about-section');

    const observer = new IntersectionObserver(entries => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('visible');

                // Stagger children for skills-grid
                if (entry.target.classList.contains('skills-grid')) {
                    const tags = entry.target.querySelectorAll('.skill-tag');
                    tags.forEach((tag, i) => {
                        tag.style.transitionDelay = `${i * 0.05}s`;
                    });
                }
            }
        });
    }, {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    });

    reveals.forEach(el => observer.observe(el));
}

// Mobile navigation
function initMobileNav() {
    const toggle = document.querySelector('.menu-toggle');
    const nav = document.querySelector('.mobile-nav');
    const close = document.querySelector('.mobile-close');
    const links = document.querySelectorAll('.mobile-nav a');

    if (!toggle || !nav) return;

    toggle.addEventListener('click', () => nav.classList.add('active'));

    if (close) {
        close.addEventListener('click', () => nav.classList.remove('active'));
    }

    links.forEach(link => {
        link.addEventListener('click', () => nav.classList.remove('active'));
    });
}

// Live time display
function initTime() {
    const timeEl = document.querySelector('.header-time');
    if (!timeEl) return;

    function update() {
        const now = new Date();
        const options = {
            hour: '2-digit',
            minute: '2-digit',
            timeZoneName: 'short'
        };
        timeEl.textContent = now.toLocaleTimeString('en-US', options);
    }

    update();
    setInterval(update, 1000);
}

// Smooth scroll
document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', e => {
        e.preventDefault();
        const target = document.querySelector(anchor.getAttribute('href'));
        if (target) {
            target.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
    });
});

// Contact form AJAX
function initContactForm() {
    const form = document.getElementById('contactForm');
    if (!form) return;

    const successAlert = document.getElementById('formSuccess');
    const errorAlert = document.getElementById('formError');
    const submitBtn = document.getElementById('submitBtn');

    form.addEventListener('submit', async (e) => {
        e.preventDefault();

        // Hide alerts
        successAlert.style.display = 'none';
        errorAlert.style.display = 'none';

        // Disable button
        submitBtn.disabled = true;
        submitBtn.textContent = 'Sending...';

        const formData = new FormData(form);

        try {
            const response = await fetch('handle_message.php', {
                method: 'POST',
                body: formData
            });

            const data = await response.json();

            if (data.success) {
                successAlert.style.display = 'block';
                form.reset();
            } else {
                errorAlert.textContent = data.errors ? data.errors.join(', ') : (data.error || 'Something went wrong');
                errorAlert.style.display = 'block';
            }
        } catch (err) {
            errorAlert.textContent = 'Failed to send message. Please try again.';
            errorAlert.style.display = 'block';
        } finally {
            submitBtn.disabled = false;
            submitBtn.textContent = 'Send Message';
        }
    });
}
