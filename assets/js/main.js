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
    initParallax();
});

// 3D Parallax Depth Layers
function initParallax() {
    const container = document.querySelector('.parallax-container');
    if (!container) return;
    
    const layers = container.querySelectorAll('.parallax-layer');
    if (layers.length === 0) return;
    
    // Check for reduced motion preference
    if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;
    
    let mouseX = 0;
    let mouseY = 0;
    let currentX = 0;
    let currentY = 0;
    let scrollY = 0;
    let rafId = null;
    
    // Smoothing factor (lower = smoother but slower)
    const smoothing = 0.08;
    
    // Track mouse position
    document.addEventListener('mousemove', (e) => {
        // Normalize coordinates to -1 to 1
        mouseX = (e.clientX / window.innerWidth - 0.5) * 2;
        mouseY = (e.clientY / window.innerHeight - 0.5) * 2;
    });
    
    // Track scroll position
    window.addEventListener('scroll', () => {
        scrollY = window.scrollY;
    }, { passive: true });
    
    // Animation loop with smooth interpolation
    function animate() {
        // Smooth interpolation
        currentX += (mouseX - currentX) * smoothing;
        currentY += (mouseY - currentY) * smoothing;
        
        layers.forEach(layer => {
            const depth = parseFloat(layer.dataset.depth) || 0.1;
            
            // Calculate movement based on depth
            const moveX = currentX * depth * 60;
            const moveY = currentY * depth * 40;
            
            // Add subtle scroll-based vertical shift
            const scrollOffset = scrollY * depth * 0.3;
            
            // Apply transform with slight rotation for depth feel
            const rotateX = currentY * depth * 2;
            const rotateY = currentX * depth * -2;
            
            layer.style.transform = `
                translate3d(${moveX}px, ${moveY - scrollOffset}px, 0)
                rotateX(${rotateX}deg)
                rotateY(${rotateY}deg)
            `;
        });
        
        rafId = requestAnimationFrame(animate);
    }
    
    // Start animation
    animate();
    
    // Pause animation when page is not visible
    document.addEventListener('visibilitychange', () => {
        if (document.hidden) {
            cancelAnimationFrame(rafId);
        } else {
            rafId = requestAnimationFrame(animate);
        }
    });
}

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

// Loader with counter animation
function initLoader() {
    const loader = document.querySelector('.loader');
    const counter = document.querySelector('.loader-counter');
    if (!loader) return;

    let progress = 0;
    const duration = 1800; // Match CSS animation duration
    const startTime = Date.now();

    function updateCounter() {
        const elapsed = Date.now() - startTime;
        progress = Math.min(Math.floor((elapsed / duration) * 100), 100);
        
        if (counter) {
            counter.textContent = progress + '%';
        }

        if (progress < 100) {
            requestAnimationFrame(updateCounter);
        }
    }

    // Start counter animation
    setTimeout(updateCounter, 300); // Match animation delay

    // Wait for everything to load
    window.addEventListener('load', () => {
        setTimeout(() => {
            if (counter) counter.textContent = '100%';
            setTimeout(() => {
                loader.classList.add('hidden');
                document.body.classList.add('loaded');
            }, 200);
        }, 2000);
    });

    // Fallback
    setTimeout(() => {
        if (counter) counter.textContent = '100%';
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

    function openNav() {
        nav.classList.add('active');
        document.body.style.overflow = 'hidden';
    }

    function closeNav() {
        nav.classList.remove('active');
        document.body.style.overflow = '';
    }

    toggle.addEventListener('click', openNav);

    if (close) {
        close.addEventListener('click', closeNav);
    }

    links.forEach(link => {
        link.addEventListener('click', closeNav);
    });

    // Close on escape key
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && nav.classList.contains('active')) {
            closeNav();
        }
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
