/**
 * Node Navigator - Space Explorer Adventure
 * A narrative journey through the portfolio
 */

class NodeNavigator {
    constructor(container, data) {
        this.container = container;
        this.data = data;

        // Planet emojis and stories for each section
        this.sectionMeta = {
            work: {
                emoji: '🪐',
                name: 'Project Nebula',
                story: 'Incoming transmission from Project Nebula... This cosmic archive contains evidence of remarkable creations. Each satellite holds a different artifact.',
                subEmoji: '🛸'
            },
            skills: {
                emoji: '⚡',
                name: 'Tech Station',
                story: 'You\'ve docked at Tech Station - a hub of knowledge and tools. The station\'s database reveals the technologies mastered by the explorer.',
                subEmoji: '💫'
            },
            about: {
                emoji: '🌍',
                name: 'Origin World',
                story: 'Welcome to the Origin World. Here lie the chronicles of the explorer\'s journey - their story, dreams, and philosophy.',
                subEmoji: '📜'
            },
            contact: {
                emoji: '📡',
                name: 'Comm Relay',
                story: 'The Communication Relay is active. Ready to establish a direct link with the explorer...',
                subEmoji: '💬'
            },
            social: {
                emoji: '🌐',
                name: 'Network Hub',
                story: 'You\'ve discovered the Network Hub - portals to other dimensions where the explorer leaves their mark.',
                subEmoji: '🔗'
            }
        };

        this.state = {
            active: false,
            introComplete: false,
            zoom: 1,
            panX: 0,
            panY: 0,
            currentSection: null,
            discovered: new Set(),
            totalNodes: data.meta.totalNodes
        };

        this.elements = {};
        this.nodes = new Map();
        this.rafId = null;
        this.lastTime = 0;
        this.orbitAngles = {};

        // Touch/mouse state
        this.touch = { dragging: false, lastX: 0, lastY: 0, lastDistance: 0 };
        this.mouse = { dragging: false, lastX: 0, lastY: 0 };
    }

    init() {
        this.loadProgress();
        this.buildDOM();
        this.bindEvents();
        this.initKeyboard();
        this.render();
    }

    buildDOM() {
        const canvas = this.container.querySelector('.nn-canvas');

        // Create intro screen
        this.createIntro();

        // Create inner container for transforms
        const inner = document.createElement('div');
        inner.className = 'nn-canvas-inner';
        canvas.appendChild(inner);
        this.elements.inner = inner;

        // Create orbits
        const orbits = document.createElement('div');
        orbits.className = 'nn-orbits';
        orbits.innerHTML = `
            <div class="nn-orbit nn-orbit-1"></div>
            <div class="nn-orbit nn-orbit-2"></div>
        `;
        inner.appendChild(orbits);

        // Create nodes container
        const nodesContainer = document.createElement('div');
        nodesContainer.className = 'nn-nodes';
        inner.appendChild(nodesContainer);
        this.elements.nodes = nodesContainer;

        // Create SVG for connection lines
        const svg = document.createElementNS('http://www.w3.org/2000/svg', 'svg');
        svg.setAttribute('class', 'nn-connections');
        svg.style.cssText = 'position:absolute;top:50%;left:50%;width:600px;height:600px;transform:translate(-50%,-50%);overflow:visible;';
        nodesContainer.appendChild(svg);
        this.elements.svg = svg;

        // Create core node
        this.createCoreNode();

        // Create section nodes
        this.data.sections.forEach(section => {
            this.createSectionNode(section);
        });

        // Create back button
        const backBtn = document.createElement('button');
        backBtn.className = 'nn-back';
        backBtn.innerHTML = '<i class="fas fa-arrow-left"></i> Return';
        this.container.appendChild(backBtn);
        this.elements.backBtn = backBtn;

        // Create hint
        const hint = document.createElement('div');
        hint.className = 'nn-hint';
        hint.textContent = 'Click planets to explore';
        this.container.appendChild(hint);
        this.elements.hint = hint;

        // Update progress display
        this.updateProgressDisplay();
    }

    createIntro() {
        const intro = document.createElement('div');
        intro.className = 'nn-intro';
        intro.innerHTML = `
            <div class="nn-intro-content">
                <h2 class="nn-intro-title">The Journey Begins</h2>
                <p class="nn-intro-subtitle">Incoming Transmission</p>
                <p class="nn-intro-text">
                    You are <strong>Explorer-1</strong>, drifting through the digital cosmos.
                    Your sensors have detected an uncharted system orbiting a mysterious entity known as <strong>"${this.data.core.name}"</strong>.
                    <br><br>
                    Your mission: Discover the secrets of this system. Visit each planet, decode the transmissions, and piece together the story of this enigmatic developer.
                </p>
                <button class="nn-intro-start">Begin Exploration</button>
            </div>
        `;
        this.container.appendChild(intro);
        this.elements.intro = intro;
    }

    createCoreNode() {
        const core = document.createElement('div');
        core.className = 'nn-node-core';
        core.innerHTML = `
            <div class="nn-node-core-inner"></div>
            <span class="nn-node-core-label">${this.data.core.name}</span>
            <span class="nn-node-core-sublabel">// ${this.data.core.role}</span>
        `;

        this.elements.nodes.appendChild(core);
        this.nodes.set('core', core);

        core.addEventListener('click', () => {
            if (this.state.currentSection) {
                this.collapseToCenter();
            }
        });
    }

    createSectionNode(section) {
        const meta = this.sectionMeta[section.id] || { emoji: '🌑', name: section.label };

        const node = document.createElement('div');
        node.className = 'nn-node-section';
        node.dataset.sectionId = section.id;
        node.innerHTML = `
            <div class="nn-node-section-inner">
                <span class="nn-node-section-emoji">${meta.emoji}</span>
            </div>
            <span class="nn-node-section-label">${meta.name}</span>
        `;

        // Initialize orbit angle
        this.orbitAngles[section.id] = section.angle;

        // Position on orbit
        this.positionOnOrbit(node, section.orbit, section.angle);

        this.elements.nodes.appendChild(node);
        this.nodes.set(section.id, node);

        // Create sub-nodes container
        if (section.children && section.children.length > 0) {
            const subnodes = document.createElement('div');
            subnodes.className = 'nn-subnodes';
            subnodes.dataset.sectionId = section.id;
            this.elements.nodes.appendChild(subnodes);

            section.children.forEach((child, index) => {
                this.createSubNode(subnodes, child, section, index);
            });
        }

        // Click handler
        node.addEventListener('click', () => {
            if (section.action === 'scrollTo') {
                this.showNotification('📡 Opening communication channel...');
                setTimeout(() => {
                    this.deactivate();
                    setTimeout(() => {
                        document.querySelector(section.target)?.scrollIntoView({ behavior: 'smooth' });
                    }, 300);
                }, 800);
            } else {
                this.expandSection(section.id);
            }
        });
    }

    createSubNode(container, childData, section, index) {
        const meta = this.sectionMeta[section.id] || { subEmoji: '⭐' };

        const node = document.createElement('div');
        node.className = 'nn-node-sub';
        node.dataset.nodeId = childData.id;
        node.dataset.sectionId = section.id;

        // Mark as discovered if applicable
        if (this.state.discovered.has(childData.id)) {
            node.classList.add('discovered');
        }

        // Get label based on type
        let label = '';
        if (section.id === 'work') {
            label = childData.title;
        } else if (section.id === 'skills') {
            label = childData.name;
        } else if (section.id === 'about') {
            label = childData.title;
        } else if (section.id === 'social') {
            label = childData.name;
        }

        node.innerHTML = `
            <div class="nn-node-sub-inner">${meta.subEmoji}</div>
            <span class="nn-node-sub-label">${label}</span>
        `;

        // Position in circle around parent
        const total = section.children.length;
        const angleOffset = 360 / total;
        const angle = index * angleOffset - 90;
        const radius = 120;
        const rad = angle * (Math.PI / 180);
        const x = Math.cos(rad) * radius;
        const y = Math.sin(rad) * radius;

        node.dataset.x = x;
        node.dataset.y = y;
        node.style.transitionDelay = `${index * 0.08}s`;

        container.appendChild(node);

        // Click handler
        node.addEventListener('click', (e) => {
            e.stopPropagation();
            this.selectSubNode(childData, section);
        });
    }

    positionOnOrbit(node, orbitNum, angle) {
        const radius = orbitNum === 1 ? 160 : 260;
        const rad = angle * (Math.PI / 180);
        const x = Math.cos(rad) * radius;
        const y = Math.sin(rad) * radius;

        node.style.left = `${x}px`;
        node.style.top = `${y}px`;
    }

    showNotification(message) {
        // Create a brief notification
        const notif = document.createElement('div');
        notif.style.cssText = `
            position: fixed;
            bottom: 6rem;
            left: 50%;
            transform: translateX(-50%);
            background: rgba(15, 23, 42, 0.95);
            border: 1px solid rgba(110, 231, 183, 0.3);
            padding: 0.75rem 1.5rem;
            border-radius: 100px;
            color: #6ee7b7;
            font-size: 0.8rem;
            z-index: 2000;
            animation: nn-notif 2s ease forwards;
        `;
        notif.textContent = message;
        this.container.appendChild(notif);

        // Add animation
        const style = document.createElement('style');
        style.textContent = `
            @keyframes nn-notif {
                0% { opacity: 0; transform: translateX(-50%) translateY(10px); }
                15% { opacity: 1; transform: translateX(-50%) translateY(0); }
                85% { opacity: 1; transform: translateX(-50%) translateY(0); }
                100% { opacity: 0; transform: translateX(-50%) translateY(-10px); }
            }
        `;
        document.head.appendChild(style);

        setTimeout(() => {
            notif.remove();
            style.remove();
        }, 2000);
    }

    bindEvents() {
        const canvas = this.container.querySelector('.nn-canvas');

        // Intro start button
        this.elements.intro.querySelector('.nn-intro-start').addEventListener('click', () => {
            this.state.introComplete = true;
            this.elements.intro.classList.add('hidden');
            this.showNotification('🚀 Systems online. Exploration mode activated.');
        });

        // Exit button
        const exitBtn = this.container.querySelector('.nn-exit');
        exitBtn?.addEventListener('click', () => this.deactivate());

        // Back button
        this.elements.backBtn.addEventListener('click', () => this.collapseToCenter());

        // Info panel close
        const infoPanel = document.getElementById('nnInfo');
        infoPanel?.addEventListener('click', (e) => {
            if (e.target.closest('.nn-info-close')) {
                this.hideInfo();
            }
        });

        // Mouse drag
        canvas.addEventListener('mousedown', (e) => {
            if (e.target.closest('.nn-node-core, .nn-node-section, .nn-node-sub')) return;
            this.mouse.dragging = true;
            this.mouse.lastX = e.clientX;
            this.mouse.lastY = e.clientY;
            canvas.style.cursor = 'grabbing';
        });

        document.addEventListener('mousemove', (e) => {
            if (!this.mouse.dragging || !this.state.active) return;
            const dx = e.clientX - this.mouse.lastX;
            const dy = e.clientY - this.mouse.lastY;
            this.state.panX += dx;
            this.state.panY += dy;
            this.mouse.lastX = e.clientX;
            this.mouse.lastY = e.clientY;
            this.applyTransform();
        });

        document.addEventListener('mouseup', () => {
            this.mouse.dragging = false;
            canvas.style.cursor = 'grab';
        });

        // Mouse wheel zoom
        canvas.addEventListener('wheel', (e) => {
            if (!this.state.active) return;
            e.preventDefault();
            const delta = e.deltaY > 0 ? 0.9 : 1.1;
            this.state.zoom = Math.max(0.5, Math.min(2, this.state.zoom * delta));
            this.applyTransform();
        }, { passive: false });

        // Touch events
        canvas.addEventListener('touchstart', (e) => {
            if (e.touches.length === 1) {
                this.touch.dragging = true;
                this.touch.lastX = e.touches[0].clientX;
                this.touch.lastY = e.touches[0].clientY;
            } else if (e.touches.length === 2) {
                this.touch.dragging = false;
                this.touch.lastDistance = this.getTouchDistance(e.touches);
            }
        }, { passive: true });

        canvas.addEventListener('touchmove', (e) => {
            if (!this.state.active) return;

            if (e.touches.length === 1 && this.touch.dragging) {
                const dx = e.touches[0].clientX - this.touch.lastX;
                const dy = e.touches[0].clientY - this.touch.lastY;
                this.state.panX += dx;
                this.state.panY += dy;
                this.touch.lastX = e.touches[0].clientX;
                this.touch.lastY = e.touches[0].clientY;
                this.applyTransform();
            } else if (e.touches.length === 2) {
                const distance = this.getTouchDistance(e.touches);
                const scale = distance / this.touch.lastDistance;
                this.state.zoom = Math.max(0.5, Math.min(2, this.state.zoom * scale));
                this.touch.lastDistance = distance;
                this.applyTransform();
            }
        }, { passive: true });

        canvas.addEventListener('touchend', () => {
            this.touch.dragging = false;
        }, { passive: true });
    }

    getTouchDistance(touches) {
        const dx = touches[0].clientX - touches[1].clientX;
        const dy = touches[0].clientY - touches[1].clientY;
        return Math.sqrt(dx * dx + dy * dy);
    }

    initKeyboard() {
        document.addEventListener('keydown', (e) => {
            if (!this.state.active) return;

            if (e.key === 'Escape') {
                if (document.getElementById('nnInfo')?.classList.contains('active')) {
                    this.hideInfo();
                } else if (this.state.currentSection) {
                    this.collapseToCenter();
                } else {
                    this.deactivate();
                }
            }
        });
    }

    applyTransform() {
        if (!this.elements.inner) return;
        this.elements.inner.style.transform = `
            translate(${this.state.panX}px, ${this.state.panY}px)
            scale(${this.state.zoom})
        `;
    }

    activate() {
        this.state.active = true;
        this.container.classList.add('active');
        this.container.setAttribute('aria-hidden', 'false');
        document.body.style.overflow = 'hidden';

        // Reset view
        this.state.panX = 0;
        this.state.panY = 0;
        this.state.zoom = 1;
        this.applyTransform();

        // Show intro if first time
        if (!this.state.introComplete) {
            this.elements.intro.classList.remove('hidden');
        }

        // Start animation loop
        this.startLoop();

        // Hide hint after delay
        setTimeout(() => {
            this.elements.hint?.classList.add('hidden');
        }, 5000);
    }

    deactivate() {
        this.state.active = false;
        this.container.classList.remove('active');
        this.container.setAttribute('aria-hidden', 'true');
        document.body.style.overflow = '';

        if (this.state.currentSection) {
            this.collapseToCenter();
        }

        this.hideInfo();
        this.stopLoop();

        const toggleBtn = document.getElementById('gameToggle');
        if (toggleBtn) {
            toggleBtn.classList.remove('active');
            const span = toggleBtn.querySelector('span');
            if (span) span.textContent = 'Explore';
        }
    }

    startLoop() {
        const animate = (timestamp) => {
            if (!this.state.active) return;

            const delta = timestamp - this.lastTime;
            this.lastTime = timestamp;

            if (!this.state.currentSection && this.state.introComplete) {
                this.updateOrbits(delta);
            }

            this.rafId = requestAnimationFrame(animate);
        };

        this.lastTime = performance.now();
        this.rafId = requestAnimationFrame(animate);
    }

    stopLoop() {
        if (this.rafId) {
            cancelAnimationFrame(this.rafId);
            this.rafId = null;
        }
    }

    updateOrbits(delta) {
        const speed = 0.003;

        this.data.sections.forEach(section => {
            this.orbitAngles[section.id] += speed * delta * (section.orbit === 1 ? 1 : -0.6);

            const node = this.nodes.get(section.id);
            if (node) {
                this.positionOnOrbit(node, section.orbit, this.orbitAngles[section.id]);
            }
        });
    }

    expandSection(sectionId) {
        const section = this.data.sections.find(s => s.id === sectionId);
        if (!section || !section.children || section.children.length === 0) return;

        const meta = this.sectionMeta[sectionId];
        this.showNotification(`🔭 Scanning ${meta.name}...`);

        this.state.currentSection = sectionId;
        this.elements.backBtn.classList.add('visible');

        const sectionNode = this.nodes.get(sectionId);
        sectionNode?.classList.add('expanded');

        const subnodes = this.container.querySelector(`.nn-subnodes[data-section-id="${sectionId}"]`);
        if (subnodes) {
            const offsetX = parseFloat(sectionNode.style.left) || 0;
            const offsetY = parseFloat(sectionNode.style.top) || 0;

            subnodes.style.left = `${offsetX}px`;
            subnodes.style.top = `${offsetY}px`;

            subnodes.classList.add('visible');

            const children = subnodes.querySelectorAll('.nn-node-sub');
            children.forEach(child => {
                const x = parseFloat(child.dataset.x) || 0;
                const y = parseFloat(child.dataset.y) || 0;
                child.style.left = `${x}px`;
                child.style.top = `${y}px`;
            });
        }
    }

    collapseToCenter() {
        if (!this.state.currentSection) return;

        const sectionId = this.state.currentSection;

        this.elements.backBtn.classList.remove('visible');

        const sectionNode = this.nodes.get(sectionId);
        sectionNode?.classList.remove('expanded');

        const subnodes = this.container.querySelector(`.nn-subnodes[data-section-id="${sectionId}"]`);
        subnodes?.classList.remove('visible');

        this.hideInfo();

        this.state.zoom = 1;
        this.applyTransform();

        this.state.currentSection = null;
    }

    selectSubNode(nodeData, section) {
        this.markDiscovered(nodeData.id);

        const node = this.container.querySelector(`.nn-node-sub[data-node-id="${nodeData.id}"]`);
        node?.classList.add('discovered');

        this.showInfo(nodeData, section);
    }

    showInfo(nodeData, section) {
        const panel = document.getElementById('nnInfo');
        if (!panel) return;

        const meta = this.sectionMeta[section.id];
        let content = '';

        if (section.id === 'work') {
            const tags = nodeData.tags || [];
            content = `
                <div class="nn-info-header">
                    <button class="nn-info-close"><i class="fas fa-times"></i></button>
                    <div class="nn-info-transmission">Signal Decoded</div>
                    <div class="nn-info-tags">
                        ${tags.map(t => `<span class="nn-info-tag">${t}</span>`).join('')}
                    </div>
                    <h3 class="nn-info-title">${nodeData.title}</h3>
                </div>
                <div class="nn-info-body">
                    <p class="nn-info-story">"This artifact was discovered in the ${meta.name} archives. It appears to be a significant creation..."</p>
                    ${nodeData.image ? `<img src="${nodeData.image}" alt="${nodeData.title}" class="nn-info-image" onerror="this.style.display='none'">` : ''}
                    <p class="nn-info-description">${nodeData.description || 'Data logs corrupted. No additional information available.'}</p>
                    <div class="nn-info-links">
                        ${nodeData.github ? `<a href="${nodeData.github}" target="_blank" class="nn-info-link"><i class="fab fa-github"></i> Source Code</a>` : ''}
                        ${nodeData.live ? `<a href="${nodeData.live}" target="_blank" class="nn-info-link"><i class="fas fa-external-link-alt"></i> Visit Site</a>` : ''}
                    </div>
                </div>
            `;
        } else if (section.id === 'skills') {
            content = `
                <div class="nn-info-header">
                    <button class="nn-info-close"><i class="fas fa-times"></i></button>
                    <div class="nn-info-transmission">Tech Database Access</div>
                    <div class="nn-info-tags">
                        <span class="nn-info-tag">Technology</span>
                    </div>
                    <h3 class="nn-info-title">${nodeData.name}</h3>
                </div>
                <div class="nn-info-body">
                    ${nodeData.icon ? `<i class="${nodeData.icon} nn-info-skill-icon"></i>` : ''}
                    <p class="nn-info-story">"Tech Station records indicate this technology has been mastered by the explorer."</p>
                    <p class="nn-info-description">
                        <strong>${nodeData.name}</strong> is part of the explorer's arsenal.
                        It has been deployed across multiple missions and proven invaluable in the creation of digital artifacts.
                    </p>
                </div>
            `;
        } else if (section.id === 'about') {
            content = `
                <div class="nn-info-header">
                    <button class="nn-info-close"><i class="fas fa-times"></i></button>
                    <div class="nn-info-transmission">Personal Log Entry</div>
                    <div class="nn-info-tags">
                        <span class="nn-info-tag">${nodeData.title}</span>
                    </div>
                    <h3 class="nn-info-title">Explorer's Chronicle</h3>
                </div>
                <div class="nn-info-body">
                    <p class="nn-info-story">"${nodeData.fragment}"</p>
                    <p class="nn-info-description" style="margin-top: 1.5rem; font-size: 0.85rem; opacity: 0.7;">
                        — Excerpt from the personal logs of ${this.data.core.name}
                    </p>
                </div>
            `;
        } else if (section.id === 'social') {
            content = `
                <div class="nn-info-header">
                    <button class="nn-info-close"><i class="fas fa-times"></i></button>
                    <div class="nn-info-transmission">Portal Located</div>
                    <div class="nn-info-tags">
                        <span class="nn-info-tag">Dimension Gate</span>
                    </div>
                    <h3 class="nn-info-title">${nodeData.name} Portal</h3>
                </div>
                <div class="nn-info-body">
                    <p class="nn-info-story">"A stable wormhole to the ${nodeData.name} dimension has been detected. The explorer maintains an active presence there."</p>
                    <a href="${nodeData.url}" target="_blank" class="nn-info-social-link">
                        <i class="${nodeData.icon}"></i>
                        <span>Enter ${nodeData.name} Dimension</span>
                    </a>
                </div>
            `;
        }

        panel.innerHTML = content;
        panel.classList.add('active');
    }

    hideInfo() {
        const panel = document.getElementById('nnInfo');
        panel?.classList.remove('active');
    }

    markDiscovered(nodeId) {
        if (this.state.discovered.has(nodeId)) return;

        this.state.discovered.add(nodeId);
        this.saveProgress();
        this.updateProgressDisplay();

        // Check for milestones
        const count = this.state.discovered.size;
        const total = this.state.totalNodes;
        const percent = Math.round((count / total) * 100);

        if (percent === 100) {
            setTimeout(() => {
                this.showNotification('🎉 Mission Complete! All secrets discovered!');
            }, 500);
        } else if (count === 1) {
            this.showNotification('✨ First discovery logged!');
        } else if (percent === 50) {
            this.showNotification('🌟 Halfway there! Keep exploring!');
        }
    }

    loadProgress() {
        try {
            const saved = localStorage.getItem('nodeNavigator_progress');
            if (saved) {
                const data = JSON.parse(saved);
                this.state.discovered = new Set(data.discovered || []);
                this.state.introComplete = data.introComplete || false;
            }
        } catch (e) {
            console.warn('Failed to load progress:', e);
        }
    }

    saveProgress() {
        try {
            const data = {
                discovered: Array.from(this.state.discovered),
                introComplete: this.state.introComplete,
                lastUpdated: Date.now()
            };
            localStorage.setItem('nodeNavigator_progress', JSON.stringify(data));
        } catch (e) {
            console.warn('Failed to save progress:', e);
        }
    }

    updateProgressDisplay() {
        const count = this.state.discovered.size;
        const total = this.state.totalNodes;
        const percent = total > 0 ? Math.round((count / total) * 100) : 0;

        const progressText = this.container.querySelector('.nn-progress-text');
        const progressFill = this.container.querySelector('.nn-progress-fill');

        if (progressText) {
            progressText.textContent = `${count}/${total} discovered`;
        }

        if (progressFill) {
            progressFill.style.width = `${percent}%`;
        }
    }

    render() {
        this.state.discovered.forEach(nodeId => {
            const node = this.container.querySelector(`.nn-node-sub[data-node-id="${nodeId}"]`);
            node?.classList.add('discovered');
        });

        this.updateProgressDisplay();
    }
}

// Initialize when DOM is ready
function initNodeNavigator() {
    const container = document.getElementById('nodeNavigator');
    const toggleBtn = document.getElementById('gameToggle');

    if (!container || !toggleBtn || !window.GAME_DATA) return;

    const game = new NodeNavigator(container, window.GAME_DATA);
    game.init();

    toggleBtn.addEventListener('click', () => {
        if (game.state.active) {
            game.deactivate();
        } else {
            game.activate();
            toggleBtn.classList.add('active');
            const span = toggleBtn.querySelector('span');
            if (span) span.textContent = 'Playing';
        }
    });
}

// Auto-initialize
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initNodeNavigator);
} else {
    initNodeNavigator();
}
