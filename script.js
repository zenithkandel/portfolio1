// Year
document.getElementById('y').textContent = new Date().getFullYear();

// Animate on scroll (IntersectionObserver)
const revealEls = document.querySelectorAll('.reveal');
const io = new IntersectionObserver((entries)=>{
  entries.forEach(e=>{
    if(e.isIntersecting){ e.target.classList.add('in'); io.unobserve(e.target); }
  });
}, { rootMargin: '0px 0px -5% 0px', threshold: 0.05 });
revealEls.forEach(el=> io.observe(el));

// Custom cursor
const cursor = document.createElement('div');
cursor.className = 'cursor';
document.body.appendChild(cursor);
let raf; let cx = 0, cy = 0, tx = 0, ty = 0;
const lerp = (a,b,t)=> a + (b-a)*t;
const move = (x,y)=>{ tx = x; ty = y; if(!raf) raf = requestAnimationFrame(loop); };
const loop = ()=>{ cx = lerp(cx, tx, 0.2); cy = lerp(cy, ty, 0.2); cursor.style.transform = `translate(${cx}px, ${cy}px)`; raf = requestAnimationFrame(loop); };
window.addEventListener('mousemove', (e)=> move(e.clientX, e.clientY));
window.addEventListener('mousedown', ()=> cursor.classList.add('dot'));
window.addEventListener('mouseup', ()=> cursor.classList.remove('dot'));

// Mobile menu toggle
const mobileMenuBtn = document.querySelector('.mobile-menu-btn');
const mobileMenu = document.querySelector('.mobile-menu');
mobileMenuBtn.addEventListener('click', () => {
  mobileMenu.classList.toggle('active');
  mobileMenuBtn.textContent = mobileMenu.classList.contains('active') ? '✕' : '☰';
});

// Close mobile menu when clicking a link
document.querySelectorAll('.mobile-menu a').forEach(link => {
  link.addEventListener('click', () => {
    mobileMenu.classList.remove('active');
    mobileMenuBtn.textContent = '☰';
  });
});

// Contact form handling
document.getElementById('contact-form').addEventListener('submit', function(e) {
  e.preventDefault();
  const formData = new FormData(this);
  const data = Object.fromEntries(formData);
  
  // Here you would normally send the data to your backend
  console.log('Form submitted:', data);
  
  // Show success message
  alert('Message sent successfully! I will get back to you soon.');
  this.reset();
});

// Particle background animation
const particleCount = Math.floor(Math.random(30*50-1)*30+50); // Reduced from 100
const particles = [];
const container = document.getElementById('particles');

for (let i = 0; i < particleCount; i++) {
  const particle = document.createElement('div');
  particle.classList.add('particle');
  
  // Random initial position
  const x = Math.random() * 100;
  const y = Math.random() * 100;
  
  // Random size (1-2px)
  const size = 1 + Math.random() * 1;
  
  // Random speed (0.05-0.2)
  const speed = 0.05 + Math.random() * 0.15;
  
  // Random direction (0-360 degrees)
  const angle = Math.random() * 360;
  
  particle.style.width = `${size}px`;
  particle.style.height = `${size}px`;
  particle.style.left = `${x}%`;
  particle.style.top = `${y}%`;
  particle.style.opacity = 0.2 + Math.random() * 0.2; // Reduced opacity
  
  container.appendChild(particle);
  
  particles.push({
    element: particle,
    x, y,
    speed,
    angle
  });
}

function updateParticles() {
  const width = window.innerWidth;
  const height = window.innerHeight;
  
  particles.forEach(p => {
    // Update position based on angle and speed
    p.x += Math.cos(p.angle * Math.PI / 180) * p.speed;
    p.y += Math.sin(p.angle * Math.PI / 180) * p.speed;
    
    // Wrap around edges
    if (p.x > 100) p.x = 0;
    if (p.x < 0) p.x = 100;
    if (p.y > 100) p.y = 0;
    if (p.y < 0) p.y = 100;
    
    // Apply new position
    p.element.style.left = `${p.x}%`;
    p.element.style.top = `${p.y}%`;
  });
  
  requestAnimationFrame(updateParticles);
}

updateParticles();

// Smooth internal anchor focus (accessibility)
document.querySelectorAll('a[href^="#"]').forEach(a=>{
  a.addEventListener('click', (e)=>{
    const id = a.getAttribute('href').slice(1);
    const el = document.getElementById(id);
    if(el){ el.setAttribute('tabindex','-1'); el.focus({preventScroll:true}); setTimeout(()=>el.removeAttribute('tabindex'), 600); }
  });
});

// ====== TERMINAL SIMULATION ======
const terminalOutput = document.getElementById('terminal-output');
const commands = [
  { text: '> zenith@dev', type: 'prompt', delay: 0 },
  { text: 'Self-taught Full-Stack Developer', type: 'output', delay: 400 },
  { text: 'Kathmandu, Nepal', type: 'output', delay: 800 },
  { text: '', type: 'output', delay: 1200 },
  { text: '$ cat skills.txt', type: 'prompt', delay: 1600 },
  { text: 'HTML • CSS • JavaScript • PHP', type: 'output', delay: 2000 },
  { text: 'Node.js • MySQL • MongoDB', type: 'output', delay: 2400 },
  { text: '', type: 'output', delay: 2800 },
  { text: '$ echo $PASSION', type: 'prompt', delay: 3200 },
  { text: 'Building minimal, functional experiences', type: 'output', delay: 3600 }
];

function typeTerminal() {
  commands.forEach((cmd, index) => {
    setTimeout(() => {
      const line = document.createElement('div');
      line.className = `terminal-line ${cmd.type}`;
      line.textContent = cmd.text;
      terminalOutput.appendChild(line);
    }, cmd.delay);
  });
}

// Start terminal animation when in view
const terminalObserver = new IntersectionObserver((entries) => {
  entries.forEach(entry => {
    if (entry.isIntersecting) {
      typeTerminal();
      terminalObserver.unobserve(entry.target);
    }
  });
}, { threshold: 0.5 });

const terminal = document.querySelector('.terminal');
if (terminal) {
  terminalObserver.observe(terminal);
}

// ====== STATS COUNTER ANIMATION ======
function animateCounter(element, target) {
  const duration = 2000;
  const step = target / (duration / 16);
  let current = 0;

  const timer = setInterval(() => {
    current += step;
    if (current >= target) {
      element.textContent = target.toLocaleString();
      clearInterval(timer);
    } else {
      element.textContent = Math.floor(current).toLocaleString();
    }
  }, 16);
}

// ====== GITHUB API INTEGRATION WITH CONSOLE LOGGING ======
const GITHUB_USERNAME = 'zenithkandel';

async function fetchGitHubStats() {
  try {
    console.log('=== Starting GitHub API Fetch ===');
    
    // Fetch user data
    console.log('Fetching user data...');
    const userResponse = await fetch(`https://api.github.com/users/${GITHUB_USERNAME}`);
    const userData = await userResponse.json();
    console.log('User Data:', userData);
    
    // Fetch repositories
    console.log('Fetching repositories...');
    const reposResponse = await fetch(`https://api.github.com/users/${GITHUB_USERNAME}/repos?per_page=100`);
    const repos = await reposResponse.json();
    console.log(`Found ${repos.length} repositories:`, repos);
    
    // Calculate total stars and forks
    const totalStars = repos.reduce((sum, repo) => sum + repo.stargazers_count, 0);
    const totalForks = repos.reduce((sum, repo) => sum + repo.forks_count, 0);
    console.log('Total Stars:', totalStars);
    console.log('Total Forks:', totalForks);
    
    // Estimate total commits (repos * 10)
    const totalCommits = repos.length * 10;
    console.log('Estimated Commits (repos × 10):', totalCommits);
    
    // Update main GitHub stats
    document.getElementById('gh-repos').textContent = userData.public_repos;
    document.getElementById('gh-stars').textContent = totalStars;
    document.getElementById('gh-forks').textContent = totalForks;
    document.getElementById('gh-followers').textContent = userData.followers;
    
    // Update Journey section stats with animation
    const journeyRepos = document.getElementById('journey-repos');
    const journeyStars = document.getElementById('journey-stars');
    const journeyCommits = document.getElementById('journey-commits');
    const journeyForks = document.getElementById('journey-forks');
    
    if (journeyRepos) animateCounter(journeyRepos, userData.public_repos);
    if (journeyStars) animateCounter(journeyStars, totalStars);
    if (journeyCommits) animateCounter(journeyCommits, totalCommits);
    if (journeyForks) animateCounter(journeyForks, totalForks);
    
    // Draw custom GitHub stats visualization
    drawGitHubStatsCard(userData, totalStars, totalForks, repos.length);
    
    // Fetch individual repo stats for project cards
    const repoNames = ['STREAMFLIX', 'Javascript-Calculator', 'Random-Color-Generator', 'Css-Login'];
    console.log('Fetching individual repo stats for:', repoNames);
    
    for (const repoName of repoNames) {
      const repo = repos.find(r => r.name === repoName);
      if (repo) {
        console.log(`${repoName}:`, { stars: repo.stargazers_count, forks: repo.forks_count });
        const starsEl = document.querySelector(`.repo-stars[data-repo="${repoName}"]`);
        const forksEl = document.querySelector(`.repo-forks[data-repo="${repoName}"]`);
        if (starsEl) starsEl.textContent = repo.stargazers_count;
        if (forksEl) forksEl.textContent = repo.forks_count;
      } else {
        console.warn(`Repository ${repoName} not found`);
      }
    }
    
    console.log('=== GitHub API Fetch Complete ===');
    
  } catch (error) {
    console.error('=== Error fetching GitHub stats ===', error);
    // Display fallback data
    document.getElementById('gh-repos').textContent = '20+';
    document.getElementById('gh-stars').textContent = '50+';
    document.getElementById('gh-forks').textContent = '15+';
    document.getElementById('gh-followers').textContent = '25+';
  }
}

// Draw custom GitHub stats card matching the minimal theme
function drawGitHubStatsCard(userData, stars, forks, repos) {
  const canvas = document.getElementById('github-stats-canvas');
  if (!canvas) return;
  
  const ctx = canvas.getContext('2d');
  const width = canvas.width;
  const height = canvas.height;
  
  // Clear canvas
  ctx.fillStyle = '#000';
  ctx.fillRect(0, 0, width, height);
  
  // Draw border
  ctx.strokeStyle = '#1a1a1a';
  ctx.lineWidth = 2;
  ctx.strokeRect(1, 1, width - 2, height - 2);
  
  // Set text properties
  ctx.fillStyle = '#fff';
  ctx.font = 'bold 24px ui-monospace, monospace';
  
  // Title
  ctx.fillText(`${userData.name || userData.login}'s GitHub Stats`, 40, 50);
  
  // Divider line
  ctx.strokeStyle = '#1a1a1a';
  ctx.beginPath();
  ctx.moveTo(40, 70);
  ctx.lineTo(width - 40, 70);
  ctx.stroke();
  
  // Stats in grid layout
  const stats = [
    { label: 'Total Stars Earned', value: stars, icon: '★' },
    { label: 'Total Commits', value: repos * 10, icon: '⟳' },
    { label: 'Total Repositories', value: repos, icon: '▢' },
    { label: 'Total Forks', value: forks, icon: '⑂' }
  ];
  
  const startY = 120;
  const rowHeight = 70;
  
  stats.forEach((stat, index) => {
    const y = startY + (index * rowHeight);
    
    // Icon
    ctx.font = 'bold 24px ui-monospace, monospace';
    ctx.fillStyle = '#fff';
    ctx.fillText(stat.icon, 40, y);
    
    // Label
    ctx.font = '16px ui-monospace, monospace';
    ctx.fillStyle = '#7a7a7a';
    ctx.fillText(stat.label, 80, y - 5);
    
    // Value
    ctx.font = 'bold 28px ui-monospace, monospace';
    ctx.fillStyle = '#fff';
    ctx.fillText(stat.value.toLocaleString(), 80, y + 25);
  });
}

// Call the function on page load
fetchGitHubStats();
