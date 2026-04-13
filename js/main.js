/* main.js — dados do catálogo e lógica de interação dinâmica */

let EBOOKS = []; // Será populado pela API
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
      <div class="ebook-cover" style="padding: 0; background-color: var(--bg-card); display: flex; align-items: center; justify-content: center; overflow: hidden;">
        <img src="${b.img_ebook}" alt="${b.nome_ebook}" style="width: 100%; height: 100%; object-fit: cover;">
        
        ${b.selo_ebook ? `<span class="ebook-badge" style="z-index: 2;">${b.selo_ebook}</span>` : ''}
        
        <div class="ebook-overlay" style="z-index: 3;">
          <button class="overlay-btn" onclick="openEbookModal(${b.id_ebooks})">Ver Detalhes</button>
        </div>
      </div>
      <div class="ebook-info">
        <h3 class="ebook-title" style="margin-top: 8px;">${b.nome_ebook}</h3>
        <p class="ebook-author">${b.autor_ebook || ''}</p>
        <div class="ebook-footer" style="justify-content: flex-start;">
          <div class="ebook-price">
            ${b.preco_original ? `<span class="old" style="text-decoration: line-through; color: var(--text-dim); margin-right: 6px; font-size: 0.9em;">R$ ${parseFloat(b.preco_original).toFixed(2).replace('.', ',')}</span>` : ''}
            R$ ${parseFloat(b.preco_ebook).toFixed(2).replace('.', ',')}
          </div>
        </div>
      </div>
    </div>
  `).join('');
}

function applyFilters() {
  let result = EBOOKS;
  
  if (searchQuery) {
    const q = searchQuery.toLowerCase();
    result = result.filter(b =>
      (b.nome_ebook && b.nome_ebook.toLowerCase().includes(q)) ||
      (b.autor_ebook && b.autor_ebook.toLowerCase().includes(q))
    );
  }
  renderCards(result);
}

function clearSearch() {
  document.getElementById('searchInput').value = '';
  searchQuery = '';
  document.getElementById('clearBtn').classList.remove('visible');
  applyFilters();
}

function openEbookModal(id) {
  const b = EBOOKS.find(ebook => parseInt(ebook.id_ebooks) === parseInt(id));
  if (!b) return;

  const modalHtml = `
    <div class="ebook-info-modal-overlay active" id="ebookInfoModal" onclick="closeEbookModal(event)">
      <div class="ebook-info-modal">
        <button class="modal-close" onclick="closeEbookModal(event)">&times;</button>
        <div class="modal-content-grid">
          <div class="modal-image-container">
            <img src="${b.img_ebook}" alt="${b.nome_ebook}">
          </div>
          <div class="modal-info-container">
            <h2>${b.nome_ebook}</h2>
            <p class="modal-author">${b.autor_ebook ? 'Por ' + b.autor_ebook : ''}</p>
            <div class="modal-price-box">
              ${b.preco_original ? `<span class="modal-old-price">R$ ${parseFloat(b.preco_original).toFixed(2).replace('.', ',')}</span>` : ''}
              <span class="modal-current-price">R$ ${parseFloat(b.preco_ebook).toFixed(2).replace('.', ',')}</span>
            </div>
            <div class="modal-desc">
              <p>${b.descricao_ebook || 'Nenhuma descrição detalhada disponível.'}</p>
            </div>
            <a href="${b.link_pagamento || '#'}" target="_blank" class="modal-buy-btn">
              Comprar Agora
            </a>
          </div>
        </div>
      </div>
    </div>
  `;

  // Remove existing if any
  const existing = document.getElementById('ebookInfoModal');
  if (existing) existing.remove();

  document.body.insertAdjacentHTML('beforeend', modalHtml);
  document.body.style.overflow = 'hidden'; 
}

function closeEbookModal(e) {
  if (e && e.target.id !== 'ebookInfoModal' && !e.target.classList.contains('modal-close')) {
    return;
  }
  const existing = document.getElementById('ebookInfoModal');
  if (existing) {
    existing.classList.remove('active');
    setTimeout(() => {
      existing.remove();
      document.body.style.overflow = '';
    }, 200);
  }
}

document.getElementById('searchInput').addEventListener('input', function () {
  searchQuery = this.value.trim();
  document.getElementById('clearBtn').classList.toggle('visible', searchQuery.length > 0);
  applyFilters();
});

// Inicializa buscando os dados da API
async function carregarEbooks() {
  try {
    const res = await fetch('/backend/ebook/listar');
    const json = await res.json();
    
    if (json.sucesso && json.dados) {
      EBOOKS = json.dados;
      renderCards(EBOOKS);
    } else {
      console.error('Falha na resposta da API:', json.mensagem);
      renderCards([]);
    }
  } catch (err) {
    console.error('Erro ao buscar ebooks da API:', err);
    renderCards([]);
  }
}

document.addEventListener('DOMContentLoaded', carregarEbooks);