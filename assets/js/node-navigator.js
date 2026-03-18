/**
 * Node Navigator - Constellation Explorer
 * Gamified portfolio navigation system
 */

class NodeNavigator {
    constructor(container, data) {
        this.container = container;
        this.data = data;

        this.state = {
            active: false,
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

        // Touch state
        this.touch = {
            dragging: false,
            lastX: 0,
            lastY: 0,
            lastDistance: 0
        };

        // Mouse drag state
        this.mouse = {
            dragging: false,
            lastX: 0,
            lastY: 0
        };
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
        backBtn.innerHTML = '<i class="fas fa-arrow-left"></i> Back';
        this.container.appendChild(backBtn);
        this.elements.backBtn = backBtn;

        // Create hint
        const hint = document.createElement('div');
        hint.className = 'nn-hint';
        hint.textContent = 'Click nodes to explore';
        this.container.appendChild(hint);
        this.elements.hint = hint;

        // Update progress display
        this.updateProgressDisplay();
    }

    createCoreNode() {
        const core = document.createElement('div');
        core.className = 'nn-node-core';
        core.innerHTML = `
            <div class="nn-node-core-inner"></div>
            <span class="nn-node-core-label">${this.data.core.label}</span>
            <span class="nn-node-core-sublabel">${this.data.core.role}</span>
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
        const node = document.createElement('div');
        node.className = 'nn-node-section';
        node.dataset.sectionId = section.id;
        node.innerHTML = `
            <div class="nn-node-section-inner">
                <i class="${section.icon} nn-node-section-icon"></i>
            </div>
            <span class="nn-node-section-label">${section.label}</span>
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
                this.deactivate();
                setTimeout(() => {
                    document.querySelector(section.target)?.scrollIntoView({ behavior: 'smooth' });
                }, 300);
            } else {
                this.expandSection(section.id);
            }
        });
    }

    createSubNode(container, childData, section, index) {
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
            <div class="nn-node-sub-inner"></div>
            <span class="nn-node-sub-label">${label}</span>
        `;

        // Position in circle around parent
        const total = section.children.length;
        const angleOffset = 360 / total;
        const angle = index * angleOffset - 90; // Start from top
        const radius = 100;
        const rad = angle * (Math.PI / 180);
        const x = Math.cos(rad) * radius;
        const y = Math.sin(rad) * radius;

        // Store position for later animation
        node.dataset.x = x;
        node.dataset.y = y;
        node.style.transitionDelay = `${index * 0.05}s`;

        container.appendChild(node);

        // Click handler
        node.addEventListener('click', (e) => {
            e.stopPropagation();
            this.selectSubNode(childData, section);
        });
    }

    positionOnOrbit(node, orbitNum, angle) {
        const radius = orbitNum === 1 ? 150 : 240;
        const rad = angle * (Math.PI / 180);
        const x = Math.cos(rad) * radius;
        const y = Math.sin(rad) * radius;

        node.style.left = `${x}px`;
        node.style.top = `${y}px`;
    }

    bindEvents() {
        const canvas = this.container.querySelector('.nn-canvas');

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
                if (this.state.currentSection) {
                    this.collapseToCenter();
                } else {
                    this.hideInfo();
                    if (!document.getElementById('nnInfo')?.classList.contains('active')) {
                        this.deactivate();
                    }
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

        // Start animation loop
        this.startLoop();

        // Hide hint after delay
        setTimeout(() => {
            this.elements.hint?.classList.add('hidden');
        }, 4000);
    }

    deactivate() {
        this.state.active = false;
        this.container.classList.remove('active');
        this.container.setAttribute('aria-hidden', 'true');
        document.body.style.overflow = '';

        // Collapse if expanded
        if (this.state.currentSection) {
            this.collapseToCenter();
        }

        // Hide info panel
        this.hideInfo();

        // Stop animation loop
        this.stopLoop();

        // Update toggle button
        const toggleBtn = document.getElementById('gameToggle');
        if (toggleBtn) {
            toggleBtn.classList.remove('active');
            toggleBtn.querySelector('span').textContent = 'Explore';
        }
    }

    startLoop() {
        const animate = (timestamp) => {
            if (!this.state.active) return;

            const delta = timestamp - this.lastTime;
            this.lastTime = timestamp;

            // Update orbit positions (slow rotation)
            if (!this.state.currentSection) {
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
        // Rotate section nodes slowly
        const speed = 0.005; // degrees per ms

        this.data.sections.forEach(section => {
            this.orbitAngles[section.id] += speed * delta * (section.orbit === 1 ? 1 : -0.7);

            const node = this.nodes.get(section.id);
            if (node) {
                this.positionOnOrbit(node, section.orbit, this.orbitAngles[section.id]);
            }
        });
    }

    expandSection(sectionId) {
        const section = this.data.sections.find(s => s.id === sectionId);
        if (!section || !section.children || section.children.length === 0) return;

        this.state.currentSection = sectionId;

        // Show back button
        this.elements.backBtn.classList.add('visible');

        // Mark section as expanded
        const sectionNode = this.nodes.get(sectionId);
        sectionNode?.classList.add('expanded');

        // Show sub-nodes
        const subnodes = this.container.querySelector(`.nn-subnodes[data-section-id="${sectionId}"]`);
        if (subnodes) {
            // Position sub-nodes relative to section node
            const sectionRect = sectionNode.getBoundingClientRect();
            const containerRect = this.elements.nodes.getBoundingClientRect();
            const offsetX = parseFloat(sectionNode.style.left) || 0;
            const offsetY = parseFloat(sectionNode.style.top) || 0;

            subnodes.style.left = `${offsetX}px`;
            subnodes.style.top = `${offsetY}px`;

            subnodes.classList.add('visible');

            // Position each sub-node
            const children = subnodes.querySelectorAll('.nn-node-sub');
            children.forEach(child => {
                const x = parseFloat(child.dataset.x) || 0;
                const y = parseFloat(child.dataset.y) || 0;
                child.style.left = `${x}px`;
                child.style.top = `${y}px`;
            });
        }

        // Zoom toward section (optional)
        // this.state.zoom = 1.3;
        // this.applyTransform();
    }

    collapseToCenter() {
        if (!this.state.currentSection) return;

        const sectionId = this.state.currentSection;

        // Hide back button
        this.elements.backBtn.classList.remove('visible');

        // Remove expanded state
        const sectionNode = this.nodes.get(sectionId);
        sectionNode?.classList.remove('expanded');

        // Hide sub-nodes
        const subnodes = this.container.querySelector(`.nn-subnodes[data-section-id="${sectionId}"]`);
        subnodes?.classList.remove('visible');

        // Hide info panel
        this.hideInfo();

        // Reset zoom
        this.state.zoom = 1;
        this.applyTransform();

        this.state.currentSection = null;
    }

    selectSubNode(nodeData, section) {
        // Mark as discovered
        this.markDiscovered(nodeData.id);

        // Update node visual
        const node = this.container.querySelector(`.nn-node-sub[data-node-id="${nodeData.id}"]`);
        node?.classList.add('discovered');

        // Show info panel
        this.showInfo(nodeData, section);
    }

    showInfo(nodeData, section) {
        const panel = document.getElementById('nnInfo');
        if (!panel) return;

        let content = '';

        if (section.id === 'work') {
            // Project info
            const tags = nodeData.tags || [];
            content = `
                <div class="nn-info-header">
                    <button class="nn-info-close"><i class="fas fa-times"></i></button>
                    <div class="nn-info-tags">
                        ${tags.map(t => `<span class="nn-info-tag">${t}</span>`).join('')}
                    </div>
                    <h3 class="nn-info-title">${nodeData.title}</h3>
                </div>
                <div class="nn-info-body">
                    ${nodeData.image ? `<img src="${nodeData.image}" alt="${nodeData.title}" class="nn-info-image" onerror="this.style.display='none'">` : ''}
                    <p class="nn-info-description">${nodeData.description || 'No description available.'}</p>
                    <div class="nn-info-links">
                        ${nodeData.github ? `<a href="${nodeData.github}" target="_blank" class="nn-info-link"><i class="fab fa-github"></i> View Code</a>` : ''}
                        ${nodeData.live ? `<a href="${nodeData.live}" target="_blank" class="nn-info-link"><i class="fas fa-external-link-alt"></i> Live Demo</a>` : ''}
                    </div>
                </div>
            `;
        } else if (section.id === 'skills') {
            // Skill info
            content = `
                <div class="nn-info-header">
                    <button class="nn-info-close"><i class="fas fa-times"></i></button>
                    <div class="nn-info-tags">
                        <span class="nn-info-tag">Technology</span>
                    </div>
                    <h3 class="nn-info-title">${nodeData.name}</h3>
                </div>
                <div class="nn-info-body">
                    ${nodeData.icon ? `<i class="${nodeData.icon} nn-info-skill-icon"></i>` : ''}
                    <p class="nn-info-description">Part of my technology stack. I use ${nodeData.name} in various projects.</p>
                </div>
            `;
        } else if (section.id === 'about') {
            // Story fragment
            content = `
                <div class="nn-info-header">
                    <button class="nn-info-close"><i class="fas fa-times"></i></button>
                    <div class="nn-info-tags">
                        <span class="nn-info-tag">Story</span>
                    </div>
                    <h3 class="nn-info-title">${nodeData.title}</h3>
                </div>
                <div class="nn-info-body">
                    <p class="nn-info-story">"${nodeData.fragment}"</p>
                </div>
            `;
        } else if (section.id === 'social') {
            // Social link
            content = `
                <div class="nn-info-header">
                    <button class="nn-info-close"><i class="fas fa-times"></i></button>
                    <div class="nn-info-tags">
                        <span class="nn-info-tag">Social</span>
                    </div>
                    <h3 class="nn-info-title">${nodeData.name}</h3>
                </div>
                <div class="nn-info-body">
                    <a href="${nodeData.url}" target="_blank" class="nn-info-social-link">
                        <i class="${nodeData.icon}"></i>
                        <span>Visit ${nodeData.name}</span>
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
    }

    loadProgress() {
        try {
            const saved = localStorage.getItem('nodeNavigator_progress');
            if (saved) {
                const data = JSON.parse(saved);
                this.state.discovered = new Set(data.discovered || []);
            }
        } catch (e) {
            console.warn('Failed to load progress:', e);
        }
    }

    saveProgress() {
        try {
            const data = {
                discovered: Array.from(this.state.discovered),
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
        // Initial render - apply discovered states
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
            toggleBtn.querySelector('span').textContent = 'Playing';
        }
    });
}

// Auto-initialize if DOM is ready
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initNodeNavigator);
} else {
    initNodeNavigator();
}
