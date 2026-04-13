/* main.js — dados do catálogo e lógica de interação */

const EBOOKS = [
  { id:1,  title:"Do Zero ao Lucro",                   author:"Carlos Mendes",    category:"negócios",               price:29.90, oldPrice:49.90, rating:4.8, reviews:214, badge:"Mais Vendido", theme:"theme-4" },
  { id:2,  title:"Hábitos que Mudam Tudo",             author:"Ana Paula Roque",  category:"desenvolvimento pessoal",price:24.90, oldPrice:null,  rating:4.9, reviews:187, badge:"Novo",         theme:"theme-1" },
  { id:3,  title:"Inteligência Artificial na Prática", author:"Felipe Souza",     category:"tecnologia",             price:34.90, oldPrice:54.90, rating:4.7, reviews:98,  badge:null,           theme:"theme-3" },
  { id:4,  title:"Finanças Pessoais Sem Medo",         author:"Juliana Costa",    category:"finanças",               price:19.90, oldPrice:39.90, rating:4.9, reviews:321, badge:"Mais Vendido", theme:"theme-4" },
  { id:5,  title:"Copywriting Essencial",              author:"Marcos Villela",   category:"marketing",              price:27.90, oldPrice:null,  rating:4.6, reviews:76,  badge:null,           theme:"theme-6" },
  { id:6,  title:"Bem-estar Total",                    author:"Dra. Carla Lima",  category:"saúde",                  price:22.90, oldPrice:34.90, rating:4.8, reviews:143, badge:"Destaque",      theme:"theme-7" },
  { id:7,  title:"Liderança Moderna",                  author:"Roberto Alves",    category:"negócios",               price:31.90, oldPrice:49.90, rating:4.7, reviews:109, badge:null,           theme:"theme-2" },
  { id:8,  title:"Python para Iniciantes",             author:"Diego Fernandes",  category:"tecnologia",             price:29.90, oldPrice:null,  rating:4.8, reviews:256, badge:"Novo",         theme:"theme-5" },
  { id:9,  title:"Mente Milionária",                   author:"Sandra Nogueira",  category:"finanças",               price:24.90, oldPrice:44.90, rating:4.9, reviews:388, badge:"Mais Vendido", theme:"theme-4" },
  { id:10, title:"Marketing Digital do Zero",          author:"Bruno Carvalho",   category:"marketing",              price:26.90, oldPrice:39.90, rating:4.6, reviews:82,  badge:null,           theme:"theme-8" },
  { id:11, title:"Mindfulness Diário",                 author:"Priya Nair",       category:"saúde",                  price:18.90, oldPrice:null,  rating:4.7, reviews:195, badge:"Destaque",      theme:"theme-1" },
  { id:12, title:"A Arte de Negociar",                 author:"Luisa Bettini",    category:"desenvolvimento pessoal",price:23.90, oldPrice:35.90, rating:4.8, reviews:134, badge:null,           theme:"theme-6" },
];

const ICONS = {
  "negócios": `<svg class="cover-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"><rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 7V5a2 2 0 0 0-4 0v2M8 7V5a2 2 0 0 0-4 0v2"/></svg>`,
  "desenvolvimento pessoal": `<svg class="cover-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"><circle cx="12" cy="8" r="4"/><path d="M4 20c0-4 3.6-7 8-7s8 3 8 7"/></svg>`,
  "tecnologia": `<svg class="cover-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"><rect x="2" y="3" width="20" height="14" rx="2"/><path d="M8 21h8M12 17v4"/></svg>`,
  "finanças": `<svg class="cover-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"><path d="M12 2v20M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>`,
  "marketing": `<svg class="cover-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg>`,
  "saúde": `<svg class="cover-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg>`,
};

let activeTag = 'all';
let searchQuery = '';

function renderCards(list) {
  const grid = document.getElementById('ebooksGrid');
  const count = document.getElementById('catalogCount');

  if (!list.length) {
    grid.innerHTML = `
      <div class="no-results">
        <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="#8a8370" stroke-width="1.5">
          <circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/>
        </svg>
        <p>Nenhum ebook encontrado. Tente outra busca.</p>
      </div>`;
    count.textContent = '0 ebooks';
    return;
  }

  count.textContent = list.length + (list.length === 1 ? ' ebook' : ' ebooks');

  grid.innerHTML = list.map((b, i) => `
    <div class="ebook-card" style="animation-delay:${i * 0.05}s">
      <div class="ebook-cover">
        <div class="ebook-cover-inner ${b.theme}">
          ${ICONS[b.category] || ''}
          <div class="cover-title">${b.title}</div>
          <div class="cover-author">${b.author}</div>
        </div>
        ${b.badge ? `<span class="ebook-badge">${b.badge}</span>` : ''}
        <div class="ebook-overlay">
          <button class="overlay-btn" onclick="addToCart('${b.title}')">Comprar agora</button>
        </div>
      </div>
      <div class="ebook-info">
        <div class="ebook-category">${b.category}</div>
        <h3 class="ebook-title">${b.title}</h3>
        <p class="ebook-author">${b.author}</p>
        <div class="ebook-footer">
          <div class="ebook-price">
            ${b.oldPrice ? `<span class="old">R$ ${b.oldPrice.toFixed(2).replace('.', ',')}</span>` : ''}
            R$ ${b.price.toFixed(2).replace('.', ',')}
          </div>
          <div class="ebook-rating"><span>★</span> ${b.rating} (${b.reviews})</div>
        </div>
      </div>
    </div>
  `).join('');
}

function applyFilters() {
  let result = EBOOKS;
  if (activeTag !== 'all') {
    result = result.filter(b => b.category === activeTag);
  }
  if (searchQuery) {
    const q = searchQuery.toLowerCase();
    result = result.filter(b =>
      b.title.toLowerCase().includes(q) ||
      b.author.toLowerCase().includes(q) ||
      b.category.toLowerCase().includes(q)
    );
  }
  renderCards(result);
}

function filterTag(el, tag) {
  document.querySelectorAll('.tag').forEach(t => t.classList.remove('active'));
  el.classList.add('active');
  activeTag = tag;
  applyFilters();
}

function clearSearch() {
  document.getElementById('searchInput').value = '';
  searchQuery = '';
  document.getElementById('clearBtn').classList.remove('visible');
  applyFilters();
}

function addToCart(title) {
  alert('✓ "' + title + '" adicionado ao carrinho!');
}

document.getElementById('searchInput').addEventListener('input', function () {
  searchQuery = this.value.trim();
  document.getElementById('clearBtn').classList.toggle('visible', searchQuery.length > 0);
  applyFilters();
});

// Inicializa
renderCards(EBOOKS);