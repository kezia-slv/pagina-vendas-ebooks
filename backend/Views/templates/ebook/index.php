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

  <?php endif; ?>
</div>
