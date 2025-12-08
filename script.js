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
