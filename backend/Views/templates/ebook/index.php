<?php
/**
 * View: Listagem de Ebooks (index)
 * Variáveis disponíveis: $ebooks (array), $tituloPagina (string)
 */
?>

<div class="panel">
  <div class="panel-header">
    <h2><i class="fas fa-book" style="color:var(--gold);margin-right:8px;"></i> Ebooks</h2>
    <a href="<?= url('backend/ebook/criar') ?>" class="btn btn-gold">
      <i class="fas fa-plus"></i> Novo Ebook
    </a>
  </div>

  <!-- Barra de Pesquisa -->
  <?php if (!empty($ebooks)): ?>
  <div class="search-bar" style="margin-bottom:20px;position:relative;">
    <i class="fas fa-search" style="position:absolute;left:14px;top:50%;transform:translateY(-50%);color:var(--text-dim);font-size:14px;pointer-events:none;"></i>
    <input
      type="text"
      id="dashSearchInput"
      class="form-control"
      placeholder="Pesquisar por nome, autor ou selo..."
      autocomplete="off"
      style="padding-left:40px;padding-right:40px;"
    >
    <button
      id="dashClearBtn"
      style="position:absolute;right:12px;top:50%;transform:translateY(-50%);background:none;border:none;color:var(--text-dim);font-size:16px;cursor:pointer;display:none;padding:4px;transition:color .2s;"
      title="Limpar pesquisa"
    >&times;</button>
    <div id="dashSearchCount" style="font-size:11px;color:var(--text-dim);margin-top:6px;display:none;">
    </div>
  </div>
  <?php endif; ?>

  <?php if (empty($ebooks)): ?>

    <div class="empty-state">
      <i class="fas fa-book-open"></i>
      <h3>Nenhum ebook cadastrado</h3>
      <p>Comece adicionando seu primeiro ebook ao catálogo.</p>
      <a href="<?= url('backend/ebook/criar') ?>" class="btn btn-gold">
        <i class="fas fa-plus"></i> Adicionar Ebook
      </a>
    </div>

  <?php else: ?>

    <div class="table-responsive">
      <table class="data-table">
        <thead>
          <tr>
            <th>Capa</th>
            <th>Nome</th>
            <th>Autor</th>
            <th>Preço</th>
            <th>Selo</th>
            <th>Ações</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($ebooks as $ebook): ?>
            <tr>
              <td>
                <img
                  src="<?= e($ebook['img_ebook'] ?? '/img/ebook-placeholder.webp') ?>"
                  alt="<?= e($ebook['nome_ebook']) ?>"
                  class="thumb"
                  loading="lazy"
                >
              </td>
              <td>
                <strong style="color:var(--text);"><?= e($ebook['nome_ebook']) ?></strong>
              </td>
              <td><?= e($ebook['autor_ebook'] ?? '—') ?></td>
              <td>
                <span class="price-current"><?= formatar_preco($ebook['preco_ebook'] ?? 0) ?></span>
                <?php if (!empty($ebook['preco_original'])): ?>
                  <span class="price-original"><?= formatar_preco($ebook['preco_original']) ?></span>
                <?php endif; ?>
              </td>
              <td>
                <?php if (!empty($ebook['selo_ebook'])): ?>
                  <span class="badge <?= $ebook['selo_ebook'] === 'Mais Vendido' ? 'badge-gold' : 'badge-success' ?>">
                    <i class="fas <?= $ebook['selo_ebook'] === 'Mais Vendido' ? 'fa-fire' : 'fa-star' ?>"></i>
                    <?= e($ebook['selo_ebook']) ?>
                  </span>
                <?php else: ?>
                  <span class="badge badge-muted">Sem selo</span>
                <?php endif; ?>
              </td>
              <td>
                <div class="actions">
                  <a href="<?= url('backend/ebook/editar/' . $ebook['id_ebooks']) ?>"
                     class="btn btn-outline btn-sm" title="Editar">
                    <i class="fas fa-pen"></i>
                  </a>
                  <a href="<?= url('backend/ebook/deletar/' . $ebook['id_ebooks']) ?>"
                     class="btn btn-danger btn-sm" title="Excluir">
                    <i class="fas fa-trash"></i>
                  </a>
                </div>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>

    <!-- Paginação -->
    <div id="dashPagination" style="display:flex;align-items:center;justify-content:space-between;margin-top:20px;padding-top:20px;border-top:1px solid var(--border);flex-wrap:wrap;gap:12px;">
      <div id="dashPageInfo" style="font-size:12px;color:var(--text-dim);"></div>
      <div id="dashPageButtons" style="display:flex;align-items:center;gap:6px;"></div>
    </div>

  <?php endif; ?>
</div>

<!-- Script de pesquisa + paginação do dashboard -->
<?php if (!empty($ebooks)): ?>
<script>
(function() {
  const input      = document.getElementById('dashSearchInput');
  const clearBtn   = document.getElementById('dashClearBtn');
  const counter    = document.getElementById('dashSearchCount');
  const table      = document.querySelector('.data-table');
  const pageInfo   = document.getElementById('dashPageInfo');
  const pageButtons = document.getElementById('dashPageButtons');
  const pagination = document.getElementById('dashPagination');
  if (!input || !table) return;

  const allRows     = Array.from(table.querySelectorAll('tbody tr'));
  const totalRows   = allRows.length;
  const perPage     = 8;
  let currentPage   = 1;
  let filteredRows  = allRows.slice();

  // ── Filtragem ──
  function filterRows() {
    const query = input.value.trim().toLowerCase();
    if (!query) {
      filteredRows = allRows.slice();
    } else {
      filteredRows = allRows.filter(function(row) {
        const cells = row.querySelectorAll('td');
        const nome  = (cells[1] ? cells[1].textContent : '').toLowerCase();
        const autor = (cells[2] ? cells[2].textContent : '').toLowerCase();
        const selo  = (cells[4] ? cells[4].textContent : '').toLowerCase();
        return nome.includes(query) || autor.includes(query) || selo.includes(query);
      });
    }
  }

  // ── Renderizar página ──
  function renderPage() {
    const totalFiltered = filteredRows.length;
    const totalPages    = Math.ceil(totalFiltered / perPage) || 1;
    if (currentPage > totalPages) currentPage = totalPages;
    if (currentPage < 1) currentPage = 1;

    const start = (currentPage - 1) * perPage;
    const end   = start + perPage;

    // Esconder todas as linhas, então mostrar apenas as da página atual
    allRows.forEach(function(row) { row.style.display = 'none'; });
    filteredRows.forEach(function(row, i) {
      row.style.display = (i >= start && i < end) ? '' : 'none';
    });

    // Info de paginação
    if (totalFiltered === 0) {
      pageInfo.textContent = 'Nenhum resultado';
    } else {
      var showStart = start + 1;
      var showEnd   = Math.min(end, totalFiltered);
      pageInfo.textContent = 'Mostrando ' + showStart + '-' + showEnd + ' de ' + totalFiltered + ' ebook' + (totalFiltered > 1 ? 's' : '');
    }

    // Botões de paginação
    var html = '';
    var btnStyle = 'display:inline-flex;align-items:center;justify-content:center;width:36px;height:36px;border-radius:50%;font-size:13px;font-weight:500;cursor:pointer;transition:all .2s;font-family:inherit;';
    var normalStyle = btnStyle + 'background:var(--bg-input);border:1px solid var(--border);color:var(--text-muted);';
    var activeStyle = btnStyle + 'background:linear-gradient(135deg,var(--gold),var(--gold-dim));border:1px solid var(--gold);color:#0f0e0b;';
    var disabledStyle = btnStyle + 'background:transparent;border:1px solid var(--border);color:var(--text-dim);opacity:.4;cursor:not-allowed;';

    if (totalPages > 1) {
      // Botão anterior
      html += '<button onclick="dashGoPage(' + (currentPage - 1) + ')" style="' + (currentPage === 1 ? disabledStyle : normalStyle) + '"' + (currentPage === 1 ? ' disabled' : '') + '>&laquo;</button>';

      // Números de página (com elipsis)
      var pages = buildPageNumbers(currentPage, totalPages);
      pages.forEach(function(p) {
        if (p === '...') {
          html += '<span style="color:var(--text-dim);font-size:13px;padding:0 4px;">…</span>';
        } else {
          html += '<button onclick="dashGoPage(' + p + ')" style="' + (p === currentPage ? activeStyle : normalStyle) + '">' + p + '</button>';
        }
      });

      // Botão próximo
      html += '<button onclick="dashGoPage(' + (currentPage + 1) + ')" style="' + (currentPage === totalPages ? disabledStyle : normalStyle) + '"' + (currentPage === totalPages ? ' disabled' : '') + '>&raquo;</button>';
    }

    pageButtons.innerHTML = html;
    pagination.style.display = totalFiltered > 0 ? 'flex' : 'none';

    // Contador de pesquisa
    var query = input.value.trim();
    if (query.length > 0) {
      counter.style.display = 'block';
      counter.textContent = totalFiltered === 0
        ? 'Nenhum ebook encontrado'
        : totalFiltered + ' de ' + totalRows + ' ebook' + (totalRows > 1 ? 's' : '');
    } else {
      counter.style.display = 'none';
    }
  }

  // ── Gerar números de página com elipsis ──
  function buildPageNumbers(current, total) {
    if (total <= 7) {
      var arr = [];
      for (var i = 1; i <= total; i++) arr.push(i);
      return arr;
    }
    var pages = [1];
    if (current > 3) pages.push('...');
    var rangeStart = Math.max(2, current - 1);
    var rangeEnd   = Math.min(total - 1, current + 1);
    for (var i = rangeStart; i <= rangeEnd; i++) pages.push(i);
    if (current < total - 2) pages.push('...');
    pages.push(total);
    return pages;
  }

  // ── Navegação global ──
  window.dashGoPage = function(page) {
    currentPage = page;
    renderPage();
    // Scroll suave ao topo do painel
    document.querySelector('.panel').scrollIntoView({ behavior: 'smooth', block: 'start' });
  };

  // ── Evento de pesquisa ──
  input.addEventListener('input', function() {
    var query = this.value.trim();
    clearBtn.style.display = query.length > 0 ? 'block' : 'none';
    currentPage = 1;
    filterRows();
    renderPage();
  });

  clearBtn.addEventListener('click', function() {
    input.value = '';
    input.dispatchEvent(new Event('input'));
    input.focus();
  });

  // Atalho: Ctrl+K ou / para focar na pesquisa
  document.addEventListener('keydown', function(e) {
    if ((e.ctrlKey && e.key === 'k') || (e.key === '/' && document.activeElement.tagName !== 'INPUT')) {
      e.preventDefault();
      input.focus();
      input.select();
    }
  });

  // Renderizar a primeira página
  filterRows();
  renderPage();
})();
</script>
<?php endif; ?>
