/* ============================================================
   EDU-CONNECT — Ultra Premium Glassmorphism Interactions v2.0
   Animations: Particles · Cursor · Ripple · Tilt · Counters
   ============================================================ */

document.addEventListener('DOMContentLoaded', () => {

  /* ── PARTICLE CANVAS BACKGROUND ── */
  (function initParticles() {
    const canvas = document.createElement('canvas');
    canvas.id = 'particleCanvas';
    document.body.insertBefore(canvas, document.body.firstChild);
    const ctx = canvas.getContext('2d');
    let W, H, particles = [], animFrame;

    function resize() {
      W = canvas.width  = window.innerWidth;
      H = canvas.height = window.innerHeight;
    }
    resize();
    window.addEventListener('resize', resize, { passive: true });

    const COLORS = [
      'rgba(124,92,252,',
      'rgba(0,245,212,',
      'rgba(255,45,120,',
      'rgba(255,183,0,',
      'rgba(61,158,255,',
    ];

    function Particle() {
      this.reset();
    }
    Particle.prototype.reset = function() {
      this.x = Math.random() * W;
      this.y = Math.random() * H;
      this.r = Math.random() * 1.4 + 0.3;
      this.vx = (Math.random() - 0.5) * 0.25;
      this.vy = (Math.random() - 0.5) * 0.25;
      this.color = COLORS[Math.floor(Math.random() * COLORS.length)];
      this.alpha = Math.random() * 0.6 + 0.2;
      this.life = 0;
      this.maxLife = Math.random() * 300 + 200;
    };
    Particle.prototype.update = function() {
      this.x += this.vx;
      this.y += this.vy;
      this.life++;
      if (this.life > this.maxLife || this.x < 0 || this.x > W || this.y < 0 || this.y > H) {
        this.reset();
        this.x = Math.random() * W;
        this.y = Math.random() * H;
      }
    };
    Particle.prototype.draw = function() {
      ctx.beginPath();
      ctx.arc(this.x, this.y, this.r, 0, Math.PI * 2);
      const fade = Math.sin((this.life / this.maxLife) * Math.PI);
      ctx.fillStyle = this.color + (this.alpha * fade) + ')';
      ctx.fill();
    };

    const COUNT = Math.min(120, Math.floor(W * H / 12000));
    for (let i = 0; i < COUNT; i++) particles.push(new Particle());

    // Connect nearby particles
    function drawConnections() {
      const maxDist = 100;
      for (let i = 0; i < particles.length; i++) {
        for (let j = i + 1; j < particles.length; j++) {
          const dx = particles[i].x - particles[j].x;
          const dy = particles[i].y - particles[j].y;
          const dist = Math.sqrt(dx*dx + dy*dy);
          if (dist < maxDist) {
            ctx.beginPath();
            ctx.moveTo(particles[i].x, particles[i].y);
            ctx.lineTo(particles[j].x, particles[j].y);
            const a = (1 - dist / maxDist) * 0.12;
            ctx.strokeStyle = `rgba(124,92,252,${a})`;
            ctx.lineWidth = 0.5;
            ctx.stroke();
          }
        }
      }
    }

    function animate() {
      ctx.clearRect(0, 0, W, H);
      particles.forEach(p => { p.update(); p.draw(); });
      drawConnections();
      animFrame = requestAnimationFrame(animate);
    }
    animate();
  })();

  /* ── SIDEBAR TOGGLE ── */
  const toggle  = document.getElementById('menuToggle');
  const sidebar = document.getElementById('sidebar');
  const overlay = document.getElementById('sidebarOverlay');
  toggle?.addEventListener('click', () => {
    sidebar.classList.toggle('open');
    overlay.classList.toggle('show');
  });
  overlay?.addEventListener('click', () => {
    sidebar.classList.remove('open');
    overlay.classList.remove('show');
  });

  /* ── AUTO-DISMISS ALERTS ── */
  document.querySelectorAll('.alert').forEach(el => {
    setTimeout(() => {
      el.style.transition = 'opacity .5s, transform .5s';
      el.style.opacity = '0';
      el.style.transform = 'translateY(-10px)';
      setTimeout(() => el.remove(), 500);
    }, 5000);
  });

  /* ── INJECT FLOATING BUBBLE ORBS ── */
  if (!document.querySelector('.bubble-orb')) {
    [1,2,3,4,5].forEach(i => {
      const orb = document.createElement('div');
      orb.className = `bubble-orb bubble-orb-${i}`;
      document.body.appendChild(orb);
    });
  }

  /* ── ADD SCAN LINE TO AUTH BOX ── */
  const authBox = document.querySelector('.auth-box');
  if (authBox) {
    const scan = document.createElement('div');
    scan.className = 'scan-line';
    authBox.appendChild(scan);
  }

  /* ── RIPPLE EFFECT ON BUTTONS ── */
  if (!document.querySelector('#rippleStyle')) {
    const style = document.createElement('style');
    style.id = 'rippleStyle';
    style.textContent = `@keyframes rippleExpand{to{transform:scale(1);opacity:0}}`;
    document.head.appendChild(style);
  }
  document.addEventListener('click', function(e) {
    const btn = e.target.closest('.btn, .action-btn, .tab-btn, .auth-tab');
    if (!btn) return;
    const rect = btn.getBoundingClientRect();
    const ripple = document.createElement('span');
    const size = Math.max(rect.width, rect.height) * 2;
    ripple.style.cssText = `
      position:absolute;
      width:${size}px;height:${size}px;
      left:${e.clientX - rect.left - size/2}px;
      top:${e.clientY - rect.top - size/2}px;
      background:rgba(255,255,255,0.15);
      border-radius:50%;
      transform:scale(0);
      animation:rippleExpand 0.55s ease-out forwards;
      pointer-events:none;z-index:10;
    `;
    btn.style.position = 'relative';
    btn.style.overflow = 'hidden';
    btn.appendChild(ripple);
    setTimeout(() => ripple.remove(), 600);
  });

  /* ── MAGNETIC CURSOR GLOW ── */
  const cursorGlow = document.createElement('div');
  cursorGlow.id = 'cursorGlow';
  cursorGlow.style.cssText = `
    position:fixed;pointer-events:none;z-index:9999;
    width:320px;height:320px;border-radius:50%;
    background:radial-gradient(circle,rgba(124,92,252,0.07) 0%,transparent 70%);
    transform:translate(-50%,-50%);transition:opacity 0.3s,width 0.3s,height 0.3s,background 0.3s;
    mix-blend-mode:screen;
  `;
  document.body.appendChild(cursorGlow);

  let mouseX = 0, mouseY = 0, glowX = 0, glowY = 0;
  document.addEventListener('mousemove', e => { mouseX = e.clientX; mouseY = e.clientY; });
  (function animateGlow() {
    glowX += (mouseX - glowX) * 0.07;
    glowY += (mouseY - glowY) * 0.07;
    cursorGlow.style.left = glowX + 'px';
    cursorGlow.style.top  = glowY + 'px';
    requestAnimationFrame(animateGlow);
  })();

  document.querySelectorAll('.btn,.stat-card,.material-card,.video-card,.notif-btn,.nav-item a,.user-card,.feedback-card').forEach(el => {
    el.addEventListener('mouseenter', () => {
      cursorGlow.style.background = 'radial-gradient(circle,rgba(124,92,252,0.13) 0%,transparent 70%)';
      cursorGlow.style.width = '420px'; cursorGlow.style.height = '420px';
    });
    el.addEventListener('mouseleave', () => {
      cursorGlow.style.background = 'radial-gradient(circle,rgba(124,92,252,0.07) 0%,transparent 70%)';
      cursorGlow.style.width = '320px'; cursorGlow.style.height = '320px';
    });
  });

  /* ── SCROLL REVEAL ── */
  const revealEls = document.querySelectorAll(
    '.stat-card,.material-card,.video-card,.user-card,.notif-item,.feedback-card,.card,.glass-panel'
  );
  revealEls.forEach((el, i) => {
    el.style.opacity = '0';
    el.style.transform = 'translateY(32px) scale(0.97)';
    el.style.transition = `opacity 0.55s ease ${i * 0.04}s, transform 0.55s cubic-bezier(0.34,1.56,0.64,1) ${i * 0.04}s`;
  });
  const observer = new IntersectionObserver(entries => {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        entry.target.style.opacity = '1';
        entry.target.style.transform = 'translateY(0) scale(1)';
        observer.unobserve(entry.target);
      }
    });
  }, { threshold: 0.08, rootMargin: '0px 0px -30px 0px' });
  revealEls.forEach(el => observer.observe(el));

  /* ── 3D TILT ON CARDS ── */
  function addTilt(selector, intensity = 6) {
    document.querySelectorAll(selector).forEach(card => {
      card.style.transformStyle = 'preserve-3d';
      card.addEventListener('mousemove', e => {
        const rect = card.getBoundingClientRect();
        const dx = (e.clientX - rect.left - rect.width/2) / (rect.width/2);
        const dy = (e.clientY - rect.top - rect.height/2) / (rect.height/2);
        card.style.transform = `translateY(-7px) scale(1.02) rotateX(${-dy*intensity}deg) rotateY(${dx*intensity}deg)`;
        card.style.transition = 'transform 0.1s ease';
      });
      card.addEventListener('mouseleave', () => {
        card.style.transform = '';
        card.style.transition = 'all 0.35s cubic-bezier(0.34,1.56,0.64,1)';
      });
    });
  }
  addTilt('.stat-card', 6);
  addTilt('.material-card, .user-card', 4);
  addTilt('.video-card', 3);
  addTilt('.feedback-card', 3);

  /* ── ANIMATED COUNTER FOR STATS ── */
  const counterObs = new IntersectionObserver(entries => {
    entries.forEach(entry => {
      if (!entry.isIntersecting) return;
      const el = entry.target;
      const target = parseInt(el.textContent, 10);
      if (!isNaN(target) && target > 0) {
        const dur = 1100;
        const start = performance.now();
        (function tick(now) {
          const p = Math.min((now - start) / dur, 1);
          const eased = 1 - Math.pow(1 - p, 3);
          el.textContent = Math.round(eased * target);
          if (p < 1) requestAnimationFrame(tick);
        })(performance.now());
      }
      counterObs.unobserve(el);
    });
  }, { threshold: 0.5 });
  document.querySelectorAll('.stat-number').forEach(el => counterObs.observe(el));

  /* ── GLASSMORPHISM SHIMMER ON HOVER ── */
  document.querySelectorAll('.card, .glass-panel').forEach(card => {
    card.addEventListener('mousemove', e => {
      const rect = card.getBoundingClientRect();
      const x = ((e.clientX - rect.left) / rect.width) * 100;
      const y = ((e.clientY - rect.top) / rect.height) * 100;
      card.style.background = `radial-gradient(circle at ${x}% ${y}%, rgba(255,255,255,0.07) 0%, rgba(255,255,255,0.04) 40%, transparent 70%)`;
    });
    card.addEventListener('mouseleave', () => { card.style.background = ''; });
  });

  /* ── NAV ITEM HOVER GLOW ── */
  document.querySelectorAll('.nav-item a').forEach(link => {
    link.addEventListener('mouseenter', function() { this.style.textShadow = '0 0 20px rgba(157,127,255,0.5)'; });
    link.addEventListener('mouseleave', function() { this.style.textShadow = ''; });
  });

  /* ── NOTIFICATION BADGE BOUNCE ── */
  const badge = document.querySelector('.notif-badge');
  if (badge) {
    setInterval(() => {
      badge.style.transform = 'scale(1.3)';
      setTimeout(() => badge.style.transform = '', 200);
    }, 5000);
  }

  /* ── COMMENTS TOGGLE ── */
  document.querySelectorAll('.comments-toggle').forEach(btn => {
    btn.addEventListener('click', function() {
      const body = this.nextElementSibling;
      if (body) body.classList.toggle('open');
    });
  });

  /* ── TOPBAR SCROLL EFFECT ── */
  const topbar = document.querySelector('.topbar');
  if (topbar) {
    window.addEventListener('scroll', () => {
      if (window.scrollY > 20) {
        topbar.style.boxShadow = '0 8px 40px rgba(0,0,0,0.5)';
        topbar.style.borderBottomColor = 'rgba(124,92,252,0.25)';
      } else {
        topbar.style.boxShadow = '';
        topbar.style.borderBottomColor = '';
      }
    }, { passive: true });
  }

  /* ── ACTIVE NAV ICON PULSE ── */
  const activeLink = document.querySelector('.nav-item a.active');
  if (activeLink) {
    const icon = activeLink.querySelector('.icon');
    if (icon) {
      setInterval(() => {
        icon.style.transform = 'scale(1.2)';
        setTimeout(() => icon.style.transform = '', 300);
      }, 4000);
    }
  }

  /* ── INPUT FOCUS DROP-SHADOW ── */
  document.querySelectorAll('.form-control, .comment-input').forEach(input => {
    input.addEventListener('focus', function() {
      this.parentElement.style.filter = 'drop-shadow(0 0 14px rgba(124,92,252,0.35))';
    });
    input.addEventListener('blur', function() {
      this.parentElement.style.filter = '';
    });
  });

  /* ── PAGE LOAD PROGRESS BAR ── */
  const bar = document.createElement('div');
  bar.style.cssText = `
    position:fixed;top:0;left:0;height:2px;z-index:99999;
    background:linear-gradient(90deg,#7c5cfc,#00f5d4,#ff2d78,#ffb700);
    background-size:300% 100%;
    width:0%;transition:width 0.3s ease;
    box-shadow:0 0 12px rgba(124,92,252,0.8),0 0 24px rgba(0,245,212,0.4);
    animation:gradShift 2s linear infinite;
  `;
  document.body.appendChild(bar);
  let p = 0;
  const iv = setInterval(() => {
    p += Math.random() * 18;
    if (p > 88) { clearInterval(iv); p = 88; }
    bar.style.width = p + '%';
  }, 100);
  window.addEventListener('load', () => {
    clearInterval(iv);
    bar.style.width = '100%';
    setTimeout(() => { bar.style.opacity = '0'; }, 350);
    setTimeout(() => bar.remove(), 700);
  });

  /* ── STAGGER NOTIF ITEMS ── */
  document.querySelectorAll('.notif-item').forEach((el, i) => {
    el.style.animationDelay = `${i * 0.06}s`;
  });

  /* ── STAR RATING ── */
  document.querySelectorAll('.star-rating').forEach(wrap => {
    const stars = wrap.querySelectorAll('.star-btn');
    stars.forEach((star, idx) => {
      star.addEventListener('mouseenter', () => {
        stars.forEach((s, i) => s.classList.toggle('active', i <= idx));
      });
      star.addEventListener('mouseleave', () => {
        const checked = wrap.querySelector('input[type=hidden]')?.value;
        stars.forEach((s, i) => s.classList.toggle('active', checked && i < parseInt(checked)));
      });
      star.addEventListener('click', () => {
        const hidden = wrap.querySelector('input[type=hidden]');
        if (hidden) hidden.value = idx + 1;
        stars.forEach((s, i) => s.classList.toggle('active', i <= idx));
      });
    });
  });

  /* ── DELETE CONFIRM ── */
  document.querySelectorAll('.delete-confirm').forEach(a => {
    a.addEventListener('click', e => {
      if (!confirm('Are you sure you want to delete this?')) e.preventDefault();
    });
  });

  /* ── CARD GLOW TRAIL ── */
  document.querySelectorAll('.material-card, .stat-card, .video-card').forEach(card => {
    card.addEventListener('mouseenter', function() {
      this.style.boxShadow = '0 0 40px rgba(124,92,252,0.25), 0 20px 60px rgba(0,0,0,0.5)';
    });
    card.addEventListener('mouseleave', function() {
      this.style.boxShadow = '';
    });
  });

});
