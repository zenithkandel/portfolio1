/**
 * Portfolio - Minimal interactions
 */

document.addEventListener('DOMContentLoaded', () => {
    initLoader();
    initCursor();
    initReveal();
    initMobileNav();
    initTime();
    initHeader();
    initContactForm();
});

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

// Subtle cursor
function initCursor() {
    if (window.matchMedia('(pointer: coarse)').matches) return;

    const cursor = document.createElement('div');
    cursor.className = 'cursor';
    document.body.appendChild(cursor);

    let x = 0, y = 0;
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
        cursor.style.left = x + 'px';
        cursor.style.top = y + 'px';
        requestAnimationFrame(animate);
    }
    animate();
}

// Scroll reveal - handles all reveal types
function initReveal() {
    const reveals = document.querySelectorAll('.reveal, .reveal-left, .reveal-right, .reveal-scale, .reveal-fade, .section-label, .skills-grid, .contact-title, .contact-section, .project-item');

    const observer = new IntersectionObserver(entries => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('visible');
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
