/* hero-interactive.js — partículas de luz, parallax e ciclo de livros */

(function () {

  /* ══════════════════════════════
     1. PARTÍCULAS DE LUZ ÂMBAR
  ══════════════════════════════ */
  const canvas = document.getElementById('heroParticles');
  if (!canvas) return;

  const ctx = canvas.getContext('2d');
  let particles = [];
  let raf;

  function resizeCanvas() {
    canvas.width  = canvas.offsetWidth;
    canvas.height = canvas.offsetHeight;
  }

  function createParticle() {
    const x = Math.random() * canvas.width;
    return {
      x,
      y: canvas.height + 10,
      size:    Math.random() * 2.5 + 0.8,
      speedY:  Math.random() * 0.8 + 0.4,
      speedX:  (Math.random() - 0.5) * 0.4,
      opacity: Math.random() * 0.6 + 0.2,
      life:    0,
      maxLife: Math.random() * 180 + 100,
    };
  }

  function drawParticles() {
    ctx.clearRect(0, 0, canvas.width, canvas.height);

    /* Adiciona partículas */
    if (particles.length < 55) {
      particles.push(createParticle());
    }

    particles.forEach((p, i) => {
      p.life++;
      p.x += p.speedX + Math.sin(p.life * 0.03) * 0.3;
      p.y -= p.speedY;

      const progress = p.life / p.maxLife;
      const alpha = p.opacity * Math.sin(progress * Math.PI);

      ctx.beginPath();
      ctx.arc(p.x, p.y, p.size, 0, Math.PI * 2);

      /* Núcleo branco + halo âmbar */
      const grad = ctx.createRadialGradient(p.x, p.y, 0, p.x, p.y, p.size * 2.5);
      grad.addColorStop(0,   `rgba(255, 240, 200, ${alpha})`);
      grad.addColorStop(0.5, `rgba(232, 168, 56, ${alpha * 0.7})`);
      grad.addColorStop(1,   `rgba(232, 168, 56, 0)`);

      ctx.fillStyle = grad;
      ctx.fill();

      if (p.life >= p.maxLife) {
        particles.splice(i, 1);
      }
    });

    raf = requestAnimationFrame(drawParticles);
  }

  resizeCanvas();
  drawParticles();

  const ro = new ResizeObserver(() => {
    resizeCanvas();
    particles = [];
  });
  ro.observe(canvas);


  /* ══════════════════════════════
     2. PARALLAX NO MOUSE
  ══════════════════════════════ */
  const booksEl = document.getElementById('heroBooks');

  if (booksEl) {
    let targetX = 0, targetY = 0;
    let currentX = 0, currentY = 0;

    document.addEventListener('mousemove', (e) => {
      const rect = document.querySelector('.hero').getBoundingClientRect();
      const cx = rect.left + rect.width  / 2;
      const cy = rect.top  + rect.height / 2;
      const dx = (e.clientX - cx) / (rect.width  / 2); /* -1 → 1 */
      const dy = (e.clientY - cy) / (rect.height / 2);

      targetX = dx * 18;
      targetY = dy * 10;
    });

    /* Parallax mais suave em cada layer */
    const layers = [
      { el: document.querySelector('.book-main'), factor: 0.6 },
      { el: document.querySelector('.book-tl'),   factor: 1.2 },
      { el: document.querySelector('.book-br'),   factor: 1.4 },
      { el: document.querySelector('.book-back'), factor: 1.8 },
    ].filter(l => l.el);

    function animateParallax() {
      currentX += (targetX - currentX) * 0.08;
      currentY += (targetY - currentY) * 0.08;

      layers.forEach(({ el, factor }) => {
        const tx = currentX * factor;
        const ty = currentY * factor;
        el.style.willChange = 'transform';
        /* Mantém a transformação CSS original + offset de parallax via filter */
        el.dataset.px = tx;
        el.dataset.py = ty;
      });

      requestAnimationFrame(animateParallax);
    }
    animateParallax();
  }


  /* ══════════════════════════════
     3. CICLO DE LIVROS EM DESTAQUE
  ══════════════════════════════ */
  const CYCLE_DATA = [
    {
      emoji: '📖',
      title: 'Fé que Move Montanhas',
      author: 'Pastor André Lima',
      theme: 'bc-crimson',
      badge: '⭐ Destaque',
    },
    {
      emoji: '🕊️',
      title: 'Graça para Cada Dia',
      author: 'Dra. Maria Santos',
      theme: 'bc-navy',
      badge: '🔥 Mais Lido',
    },
    {
      emoji: '🙏',
      title: 'Orações que Transformam',
      author: 'Pr. João Oliveira',
      theme: 'bc-forest',
      badge: '✨ Novo',
    },
    {
      emoji: '✝️',
      title: 'A Cruz e a Vitória',
      author: 'Bispa Tereza Alves',
      theme: 'bc-gold',
      badge: '💛 Favorito',
    },
  ];

  const mainBook = document.querySelector('.book-main .book-cover');
  const mainBadge = document.querySelector('.book-main .book-badge');
  const mainEmoji = mainBook ? mainBook.querySelector('.book-emoji') : null;
  const mainTitle = mainBook ? mainBook.querySelector('.book-title') : null;
  const mainAuthor = mainBook ? mainBook.querySelector('.book-author') : null;

  if (mainEmoji && mainTitle && mainAuthor) {
    let current = 0;

    function cycleBook() {
      current = (current + 1) % CYCLE_DATA.length;
      const data = CYCLE_DATA[current];
      const bookEl = document.querySelector('.book-main');

      /* Fade out */
      bookEl.style.opacity = '0';
      bookEl.style.transform = 'translate(-50%, -50%) rotate(-2deg) scale(0.95)';

      setTimeout(() => {
        /* Atualiza conteúdo */
        mainEmoji.textContent  = data.emoji;
        mainTitle.textContent  = data.title;
        mainAuthor.textContent = data.author;
        if (mainBadge) mainBadge.textContent = data.badge;

        /* Troca tema */
        const themes = ['bc-crimson', 'bc-navy', 'bc-forest', 'bc-gold'];
        mainBook.classList.remove(...themes);
        mainBook.classList.add(data.theme);

        /* Fade in */
        bookEl.style.opacity   = '1';
        bookEl.style.transform = 'translate(-50%, -50%) rotate(-2deg) scale(1)';
      }, 380);
    }

    setInterval(cycleBook, 4500);

    /* Clique no livro principal → avança */
    const mainBookEl = document.querySelector('.book-main');
    if (mainBookEl) {
      mainBookEl.addEventListener('click', () => {
        cycleBook();
      });
    }
  }


  /* ══════════════════════════════
     4. CONTADOR ANIMADO NAS STATS
  ══════════════════════════════ */
  function animateCounter(el, from, to, duration, suffix) {
    const start = performance.now();
    function update(now) {
      const t = Math.min((now - start) / duration, 1);
      const ease = 1 - Math.pow(1 - t, 3);
      el.textContent = Math.round(from + (to - from) * ease) + (suffix || '');
      if (t < 1) requestAnimationFrame(update);
    }
    requestAnimationFrame(update);
  }

  /* Observa quando a seção fica visível */
  const statsEls = document.querySelectorAll('.stat-num[data-count]');
  if (statsEls.length) {
    const obs = new IntersectionObserver((entries) => {
      entries.forEach(entry => {
        if (entry.isIntersecting) {
          const el = entry.target;
          animateCounter(el, 0, parseInt(el.dataset.count), 1200, el.dataset.suffix || '');
          obs.unobserve(el);
        }
      });
    }, { threshold: 0.5 });

    statsEls.forEach(el => obs.observe(el));
  }

})();