/**
 * Portfolio - Minimal interactions
 */

document.addEventListener('DOMContentLoaded', () => {
    initCursor();
    initReveal();
    initMobileNav();
    initTime();
});

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

// Scroll reveal
function initReveal() {
    const reveals = document.querySelectorAll('.reveal');
    
    const observer = new IntersectionObserver(entries => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('visible');
            }
        });
    }, { threshold: 0.1 });
    
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
