<?php
/**
 * View: Confirmar exclusão de Ebook
 * Variáveis disponíveis: $ebook (array), $tituloPagina (string)
 */
?>

<div class="panel" style="max-width:640px;">
  <div class="panel-header">
    <h2><i class="fas fa-exclamation-triangle" style="color:var(--danger);margin-right:8px;"></i> Confirmar Exclusão</h2>
    <a href="<?= url('backend/ebook') ?>" class="btn btn-outline btn-sm">
      <i class="fas fa-arrow-left"></i> Voltar
    </a>
  </div>

  <p style="color:var(--text-muted);margin-bottom:20px;font-size:14px;">
    Você está prestes a excluir permanentemente o ebook abaixo. Esta ação <strong style="color:var(--danger);">não pode ser desfeita</strong>.
  </p>

  <div class="delete-preview">
    <img src="<?= e($ebook['img_ebook'] ?? '/img/ebook-placeholder.webp') ?>"
         alt="<?= e($ebook['nome_ebook']) ?>">
    <div class="delete-preview-info">
      <h3><?= e($ebook['nome_ebook']) ?></h3>
      <p><i class="fas fa-user" style="width:16px;"></i> <?= e($ebook['autor_ebook'] ?? 'Autor não informado') ?></p>
      <p><i class="fas fa-tag" style="width:16px;"></i> <?= formatar_preco($ebook['preco_ebook'] ?? 0) ?></p>
      <?php if (!empty($ebook['selo_ebook'])): ?>
        <p>
          <span class="badge <?= $ebook['selo_ebook'] === 'Mais Vendido' ? 'badge-gold' : 'badge-success' ?>">
            <?= e($ebook['selo_ebook']) ?>
          </span>
        </p>
      <?php endif; ?>
    </div>
  </div>

  <form action="<?= url('backend/ebook/deletar') ?>" method="POST">
    <input type="hidden" name="id_ebooks" value="<?= e($ebook['id_ebooks']) ?>">

    <div class="form-actions">
      <button type="submit" class="btn btn-danger">
        <i class="fas fa-trash"></i> Sim, Excluir Ebook
      </button>
      <a href="<?= url('backend/ebook') ?>" class="btn btn-outline">Cancelar</a>
    </div>
  </form>
</div>
