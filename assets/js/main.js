/**
 * ================================================
 * ZENITH PORTFOLIO - Interactive Experience
 * Gamified animations and interactions
 * ================================================
 */

// Wait for DOM to be ready
document.addEventListener('DOMContentLoaded', () => {
    // Initialize all modules
    Loader.init();
    Cursor.init();
    Navigation.init();
    Particles.init();
    Animations.init();
    SkillBars.init();
    Tilt.init();
    TypedText.init();
    SmoothScroll.init();
    FormHandler.init();
});

/**
 * Loading Screen Module
 */
const Loader = {
    init() {
        const loader = document.querySelector('.loader');
        const progress = document.querySelector('.loader-progress');
        const percentage = document.querySelector('.loader-percentage');
        const text = document.querySelector('.loader-text');
        
        if (!loader) return;

        const messages = [
            'Initializing systems...',
            'Loading assets...',
            'Compiling experience...',
            'Rendering interface...',
            'Almost ready...'
        ];

        let currentProgress = 0;
        const targetProgress = 100;
        const duration = 2000;
        const startTime = performance.now();

        const animate = (currentTime) => {
            const elapsed = currentTime - startTime;
            const progress_val = Math.min((elapsed / duration) * targetProgress, targetProgress);
            
            currentProgress = Math.floor(progress_val);
            
            if (progress) progress.style.width = `${currentProgress}%`;
            if (percentage) percentage.textContent = `${currentProgress}%`;
            
            // Update message based on progress
            const messageIndex = Math.min(Math.floor(currentProgress / 25), messages.length - 1);
            if (text) text.textContent = messages[messageIndex];

            if (currentProgress < targetProgress) {
                requestAnimationFrame(animate);
            } else {
                setTimeout(() => {
                    loader.classList.add('loaded');
                    document.body.style.overflow = 'auto';
                    
                    // Trigger entrance animations
                    setTimeout(() => {
                        Animations.triggerEntrance();
                    }, 300);
                }, 500);
            }
        };

        document.body.style.overflow = 'hidden';
        requestAnimationFrame(animate);
    }
};

/**
 * Custom Cursor Module
 */
const Cursor = {
    cursor: null,
    cursorDot: null,
    mouseX: 0,
    mouseY: 0,
    cursorX: 0,
    cursorY: 0,
    dotX: 0,
    dotY: 0,

    init() {
        // Check if device supports hover
        if (window.matchMedia('(pointer: coarse)').matches) return;

        this.cursor = document.querySelector('.cursor');
        this.cursorDot = document.querySelector('.cursor-dot');
        
        if (!this.cursor || !this.cursorDot) return;

        document.addEventListener('mousemove', (e) => {
            this.mouseX = e.clientX;
            this.mouseY = e.clientY;
        });

        document.addEventListener('mousedown', () => {
            this.cursor.classList.add('click');
        });

        document.addEventListener('mouseup', () => {
            this.cursor.classList.remove('click');
        });

        // Add hover effect to interactive elements
        const interactiveElements = document.querySelectorAll('a, button, .skill-card, .project-card, .contact-link, input, textarea');
        
        interactiveElements.forEach(el => {
            el.addEventListener('mouseenter', () => {
                this.cursor.classList.add('hover');
            });
            el.addEventListener('mouseleave', () => {
                this.cursor.classList.remove('hover');
            });
        });

        this.animate();
    },

    animate() {
        // Smooth cursor following with easing
        const ease = 0.15;
        
        this.cursorX += (this.mouseX - this.cursorX) * ease;
        this.cursorY += (this.mouseY - this.cursorY) * ease;
        
        this.dotX += (this.mouseX - this.dotX) * 0.5;
        this.dotY += (this.mouseY - this.dotY) * 0.5;

        if (this.cursor) {
            this.cursor.style.left = `${this.cursorX}px`;
            this.cursor.style.top = `${this.cursorY}px`;
        }
        
        if (this.cursorDot) {
            this.cursorDot.style.left = `${this.dotX}px`;
            this.cursorDot.style.top = `${this.dotY}px`;
        }

        requestAnimationFrame(() => this.animate());
    }
};

/**
 * Navigation Module
 */
const Navigation = {
    init() {
        const nav = document.querySelector('.nav');
        const mobileBtn = document.querySelector('.mobile-menu-btn');
        const mobileNav = document.querySelector('.mobile-nav');
        const mobileOverlay = document.querySelector('.mobile-overlay');
        const mobileClose = document.querySelector('.mobile-nav-close');
        const mobileLinks = document.querySelectorAll('.mobile-nav-link');

        // Scroll behavior
        let lastScroll = 0;
        
        window.addEventListener('scroll', () => {
            const currentScroll = window.pageYOffset;
            
            if (currentScroll > 50) {
                nav.classList.add('scrolled');
            } else {
                nav.classList.remove('scrolled');
            }
            
            lastScroll = currentScroll;
        });

        // Mobile menu
        if (mobileBtn && mobileNav && mobileOverlay) {
            mobileBtn.addEventListener('click', () => {
                mobileNav.classList.add('active');
                mobileOverlay.classList.add('active');
                document.body.style.overflow = 'hidden';
            });

            const closeMenu = () => {
                mobileNav.classList.remove('active');
                mobileOverlay.classList.remove('active');
                document.body.style.overflow = '';
            };

            if (mobileClose) mobileClose.addEventListener('click', closeMenu);
            mobileOverlay.addEventListener('click', closeMenu);
            
            mobileLinks.forEach(link => {
                link.addEventListener('click', closeMenu);
            });
        }

        // Active section highlighting
        this.setupActiveLinks();
    },

    setupActiveLinks() {
        const sections = document.querySelectorAll('section[id]');
        const navLinks = document.querySelectorAll('.nav-link');

        window.addEventListener('scroll', () => {
            let current = '';
            
            sections.forEach(section => {
                const sectionTop = section.offsetTop;
                const sectionHeight = section.clientHeight;
                
                if (window.pageYOffset >= sectionTop - 200) {
                    current = section.getAttribute('id');
                }
            });

            navLinks.forEach(link => {
                link.classList.remove('active');
                if (link.getAttribute('href') === `#${current}`) {
                    link.classList.add('active');
                }
            });
        });
    }
};

/**
 * Particles Background Module
 */
const Particles = {
    canvas: null,
    ctx: null,
    particles: [],
    mouse: { x: null, y: null, radius: 150 },

    init() {
        this.canvas = document.getElementById('particles-canvas');
        if (!this.canvas) return;

        this.ctx = this.canvas.getContext('2d');
        this.resize();
        this.createParticles();
        this.animate();

        window.addEventListener('resize', () => this.resize());
        
        document.addEventListener('mousemove', (e) => {
            this.mouse.x = e.clientX;
            this.mouse.y = e.clientY;
        });

        document.addEventListener('mouseout', () => {
            this.mouse.x = null;
            this.mouse.y = null;
        });
    },

    resize() {
        this.canvas.width = window.innerWidth;
        this.canvas.height = window.innerHeight;
    },

    createParticles() {
        const numberOfParticles = Math.floor((window.innerWidth * window.innerHeight) / 15000);
        
        for (let i = 0; i < numberOfParticles; i++) {
            this.particles.push({
                x: Math.random() * this.canvas.width,
                y: Math.random() * this.canvas.height,
                size: Math.random() * 2 + 0.5,
                speedX: (Math.random() - 0.5) * 0.5,
                speedY: (Math.random() - 0.5) * 0.5,
                opacity: Math.random() * 0.5 + 0.2,
                color: Math.random() > 0.5 ? '#00f0ff' : '#7b2dff'
            });
        }
    },

    animate() {
        this.ctx.clearRect(0, 0, this.canvas.width, this.canvas.height);

        this.particles.forEach((particle, index) => {
            // Update position
            particle.x += particle.speedX;
            particle.y += particle.speedY;

            // Mouse interaction
            if (this.mouse.x !== null && this.mouse.y !== null) {
                const dx = this.mouse.x - particle.x;
                const dy = this.mouse.y - particle.y;
                const distance = Math.sqrt(dx * dx + dy * dy);

                if (distance < this.mouse.radius) {
                    const force = (this.mouse.radius - distance) / this.mouse.radius;
                    particle.x -= dx * force * 0.02;
                    particle.y -= dy * force * 0.02;
                }
            }

            // Wrap around edges
            if (particle.x < 0) particle.x = this.canvas.width;
            if (particle.x > this.canvas.width) particle.x = 0;
            if (particle.y < 0) particle.y = this.canvas.height;
            if (particle.y > this.canvas.height) particle.y = 0;

            // Draw particle
            this.ctx.beginPath();
            this.ctx.arc(particle.x, particle.y, particle.size, 0, Math.PI * 2);
            this.ctx.fillStyle = particle.color;
            this.ctx.globalAlpha = particle.opacity;
            this.ctx.fill();

            // Draw connections
            this.particles.slice(index + 1).forEach(other => {
                const dx = particle.x - other.x;
                const dy = particle.y - other.y;
                const distance = Math.sqrt(dx * dx + dy * dy);

                if (distance < 120) {
                    this.ctx.beginPath();
                    this.ctx.moveTo(particle.x, particle.y);
                    this.ctx.lineTo(other.x, other.y);
                    this.ctx.strokeStyle = particle.color;
                    this.ctx.globalAlpha = (120 - distance) / 120 * 0.2;
                    this.ctx.lineWidth = 0.5;
                    this.ctx.stroke();
                }
            });
        });

        this.ctx.globalAlpha = 1;
        requestAnimationFrame(() => this.animate());
    }
};

/**
 * Scroll Animations Module
 */
const Animations = {
    init() {
        this.setupScrollReveal();
    },

    setupScrollReveal() {
        const revealElements = document.querySelectorAll('.reveal, .reveal-left, .reveal-right');

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('active');
                }
            });
        }, {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        });

        revealElements.forEach(el => observer.observe(el));
    },

    triggerEntrance() {
        // Trigger initial hero animations
        const heroElements = document.querySelectorAll('.hero .reveal, .hero .reveal-left, .hero .reveal-right');
        heroElements.forEach((el, index) => {
            setTimeout(() => {
                el.classList.add('active');
            }, index * 100);
        });
    }
};

/**
 * Skill Progress Bars Module
 */
const SkillBars = {
    init() {
        const skillCards = document.querySelectorAll('.skill-card');

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const progressBar = entry.target.querySelector('.skill-progress');
                    if (progressBar) {
                        const targetWidth = progressBar.dataset.progress || 0;
                        setTimeout(() => {
                            progressBar.style.width = `${targetWidth}%`;
                        }, 200);
                    }
                    observer.unobserve(entry.target);
                }
            });
        }, {
            threshold: 0.3
        });

        skillCards.forEach(card => observer.observe(card));
    }
};

/**
 * 3D Tilt Effect Module
 */
const Tilt = {
    init() {
        const tiltElements = document.querySelectorAll('.project-card, .skill-card');

        tiltElements.forEach(el => {
            el.addEventListener('mousemove', (e) => this.handleTilt(e, el));
            el.addEventListener('mouseleave', () => this.resetTilt(el));
        });
    },

    handleTilt(e, el) {
        const rect = el.getBoundingClientRect();
        const x = e.clientX - rect.left;
        const y = e.clientY - rect.top;
        
        const centerX = rect.width / 2;
        const centerY = rect.height / 2;
        
        const rotateX = (y - centerY) / 20;
        const rotateY = (centerX - x) / 20;

        el.style.transform = `perspective(1000px) rotateX(${rotateX}deg) rotateY(${rotateY}deg) translateY(-10px)`;
    },

    resetTilt(el) {
        el.style.transform = 'perspective(1000px) rotateX(0) rotateY(0) translateY(0)';
    }
};

/**
 * Typed Text Effect Module
 */
const TypedText = {
    init() {
        const typedElement = document.querySelector('.typed-text');
        if (!typedElement) return;

        const strings = JSON.parse(typedElement.dataset.strings || '[]');
        if (strings.length === 0) return;

        this.element = typedElement;
        this.strings = strings;
        this.stringIndex = 0;
        this.charIndex = 0;
        this.isDeleting = false;
        this.typeSpeed = 100;
        this.deleteSpeed = 50;
        this.pauseTime = 2000;

        this.type();
    },

    type() {
        const currentString = this.strings[this.stringIndex];
        
        if (this.isDeleting) {
            this.element.textContent = currentString.substring(0, this.charIndex - 1);
            this.charIndex--;
        } else {
            this.element.textContent = currentString.substring(0, this.charIndex + 1);
            this.charIndex++;
        }

        let timeout = this.isDeleting ? this.deleteSpeed : this.typeSpeed;

        if (!this.isDeleting && this.charIndex === currentString.length) {
            timeout = this.pauseTime;
            this.isDeleting = true;
        } else if (this.isDeleting && this.charIndex === 0) {
            this.isDeleting = false;
            this.stringIndex = (this.stringIndex + 1) % this.strings.length;
            timeout = 500;
        }

        setTimeout(() => this.type(), timeout);
    }
};

/**
 * Smooth Scroll Module
 */
const SmoothScroll = {
    init() {
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', (e) => {
                e.preventDefault();
                const target = document.querySelector(anchor.getAttribute('href'));
                
                if (target) {
                    const offsetTop = target.offsetTop - 80;
                    
                    window.scrollTo({
                        top: offsetTop,
                        behavior: 'smooth'
                    });
                }
            });
        });
    }
};

/**
 * Form Handler Module
 */
const FormHandler = {
    init() {
        const form = document.querySelector('.contact-form');
        if (!form) return;

        const inputs = form.querySelectorAll('.form-input, .form-textarea');
        
        // Add floating label effect
        inputs.forEach(input => {
            input.addEventListener('focus', () => {
                input.parentElement.classList.add('focused');
            });
            
            input.addEventListener('blur', () => {
                if (!input.value) {
                    input.parentElement.classList.remove('focused');
                }
            });
        });

        // Form submission with animation
        form.addEventListener('submit', (e) => {
            const submitBtn = form.querySelector('.form-submit');
            if (submitBtn) {
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Sending...';
            }
        });
    }
};

/**
 * Number Counter Animation
 */
const CountUp = {
    init() {
        const counters = document.querySelectorAll('.stat-number');
        
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    this.animateCounter(entry.target);
                    observer.unobserve(entry.target);
                }
            });
        }, { threshold: 0.5 });

        counters.forEach(counter => observer.observe(counter));
    },

    animateCounter(element) {
        const target = parseInt(element.dataset.target) || 0;
        const duration = 2000;
        const start = performance.now();

        const update = (currentTime) => {
            const elapsed = currentTime - start;
            const progress = Math.min(elapsed / duration, 1);
            
            // Easing function
            const easeOut = 1 - Math.pow(1 - progress, 3);
            const current = Math.floor(easeOut * target);
            
            element.textContent = current + '+';

            if (progress < 1) {
                requestAnimationFrame(update);
            }
        };

        requestAnimationFrame(update);
    }
};

// Initialize counter after page load
window.addEventListener('load', () => {
    CountUp.init();
});

/**
 * Glitch Effect on Hover
 */
const GlitchEffect = {
    init() {
        const glitchElements = document.querySelectorAll('.glitch');
        
        glitchElements.forEach(el => {
            el.dataset.text = el.textContent;
        });
    }
};

// Initialize glitch effect
document.addEventListener('DOMContentLoaded', () => {
    GlitchEffect.init();
});

/**
 * Parallax Effect
 */
const Parallax = {
    init() {
        const parallaxElements = document.querySelectorAll('[data-parallax]');
        
        window.addEventListener('scroll', () => {
            const scrolled = window.pageYOffset;
            
            parallaxElements.forEach(el => {
                const speed = el.dataset.parallax || 0.5;
                const yPos = -(scrolled * speed);
                el.style.transform = `translateY(${yPos}px)`;
            });
        });
    }
};

// Initialize parallax
document.addEventListener('DOMContentLoaded', () => {
    Parallax.init();
});

/**
 * Magnetic Buttons Effect
 */
const MagneticButtons = {
    init() {
        const buttons = document.querySelectorAll('.btn, .nav-cta');
        
        buttons.forEach(btn => {
            btn.addEventListener('mousemove', (e) => {
                const rect = btn.getBoundingClientRect();
                const x = e.clientX - rect.left - rect.width / 2;
                const y = e.clientY - rect.top - rect.height / 2;
                
                btn.style.transform = `translate(${x * 0.3}px, ${y * 0.3}px)`;
            });
            
            btn.addEventListener('mouseleave', () => {
                btn.style.transform = 'translate(0, 0)';
            });
        });
    }
};

// Initialize magnetic effect
document.addEventListener('DOMContentLoaded', () => {
    MagneticButtons.init();
});

/**
 * Text Scramble Effect (for page transitions or special elements)
 */
class TextScramble {
    constructor(el) {
        this.el = el;
        this.chars = '!<>-_\\/[]{}—=+*^?#________';
        this.update = this.update.bind(this);
    }

    setText(newText) {
        const oldText = this.el.innerText;
        const length = Math.max(oldText.length, newText.length);
        const promise = new Promise((resolve) => this.resolve = resolve);
        this.queue = [];
        
        for (let i = 0; i < length; i++) {
            const from = oldText[i] || '';
            const to = newText[i] || '';
            const start = Math.floor(Math.random() * 40);
            const end = start + Math.floor(Math.random() * 40);
            this.queue.push({ from, to, start, end });
        }
        
        cancelAnimationFrame(this.frameRequest);
        this.frame = 0;
        this.update();
        return promise;
    }

    update() {
        let output = '';
        let complete = 0;
        
        for (let i = 0, n = this.queue.length; i < n; i++) {
            let { from, to, start, end, char } = this.queue[i];
            
            if (this.frame >= end) {
                complete++;
                output += to;
            } else if (this.frame >= start) {
                if (!char || Math.random() < 0.28) {
                    char = this.randomChar();
                    this.queue[i].char = char;
                }
                output += `<span class="scramble-char">${char}</span>`;
            } else {
                output += from;
            }
        }
        
        this.el.innerHTML = output;
        
        if (complete === this.queue.length) {
            this.resolve();
        } else {
            this.frameRequest = requestAnimationFrame(this.update);
            this.frame++;
        }
    }

    randomChar() {
        return this.chars[Math.floor(Math.random() * this.chars.length)];
    }
}

// Export for potential use
window.TextScramble = TextScramble;

console.log('%c ZENITH PORTFOLIO ', 'background: linear-gradient(135deg, #00f0ff, #7b2dff); color: #0a0a0f; font-size: 20px; font-weight: bold; padding: 10px 20px; border-radius: 4px;');
console.log('%c Interactive Experience Loaded ', 'color: #00f0ff; font-size: 12px;');
