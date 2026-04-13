<?php
/**
 * View: Criar novo Ebook
 * Variáveis disponíveis: $tituloPagina (string)
 */
?>

<div class="panel">
  <div class="panel-header">
    <h2><i class="fas fa-plus-circle" style="color:var(--gold);margin-right:8px;"></i> Novo Ebook</h2>
    <a href="<?= url('backend/ebook') ?>" class="btn btn-outline btn-sm">
      <i class="fas fa-arrow-left"></i> Voltar
    </a>
  </div>

  <form action="<?= url('backend/ebook/salvar') ?>" method="POST" enctype="multipart/form-data" id="formCriarEbook">

    <div class="form-row">
      <div class="form-group">
        <label for="nome_ebook">Nome do Ebook *</label>
        <input type="text" id="nome_ebook" name="nome_ebook" class="form-control"
               placeholder="Ex: Ainda Há Perdão" required>
      </div>

      <div class="form-group">
        <label for="autor_ebook">Autor</label>
        <input type="text" id="autor_ebook" name="autor_ebook" class="form-control"
               placeholder="Ex: João Silva">
      </div>
    </div>

    <div class="form-group">
      <label for="descricao_ebook">Descrição *</label>
      <textarea id="descricao_ebook" name="descricao_ebook" class="form-control"
                placeholder="Descreva o conteúdo do ebook..." rows="4" required></textarea>
    </div>

    <div class="form-group">
      <label>Capa do Ebook *</label>
      <label for="img_ebook" class="file-upload-area" id="uploadArea">
        <i class="fas fa-cloud-upload-alt"></i>
        <p id="uploadText">Clique ou arraste a imagem da capa</p>
        <p style="font-size:10px;color:var(--text-dim);margin-top:4px;">JPEG, PNG ou WebP — Máx. 3MB</p>
      </label>
      <input type="file" id="img_ebook" name="img_ebook" accept="image/jpeg,image/png,image/webp"
             style="display:none" required>
      <div id="previewContainer" style="display:none;margin-top:12px;">
        <img id="previewImg" src="" alt="Preview"
             style="max-height:120px;border-radius:8px;border:1px solid var(--border);">
      </div>
    </div>

    <div class="form-row">
      <div class="form-group">
        <label for="preco_ebook">Preço (R$) *</label>
        <input type="number" id="preco_ebook" name="preco_ebook" class="form-control"
               placeholder="29.90" step="0.01" min="0" required>
      </div>

      <div class="form-group">
        <label for="preco_original">Preço Original (R$)</label>
        <input type="number" id="preco_original" name="preco_original" class="form-control"
               placeholder="49.90" step="0.01" min="0">
        <p class="form-hint">Deixe vazio se não houver promoção</p>
      </div>
    </div>

    <div class="form-row">
      <div class="form-group">
        <label for="link_pagamento">Link de Pagamento *</label>
        <input type="url" id="link_pagamento" name="link_pagamento" class="form-control"
               placeholder="https://pay.exemplo.com/..." required>
      </div>

      <div class="form-group">
        <label for="selo_ebook">Selo</label>
        <select id="selo_ebook" name="selo_ebook" class="form-control">
          <option value="">Sem selo</option>
          <option value="Mais Vendido">🔥 Mais Vendido</option>
          <option value="Novidade">⭐ Novidade</option>
        </select>
      </div>
    </div>

    <div class="form-actions">
      <button type="submit" class="btn btn-gold">
        <i class="fas fa-save"></i> Cadastrar Ebook
      </button>
      <a href="<?= url('backend/ebook') ?>" class="btn btn-outline">Cancelar</a>
    </div>
  </form>
</div>

<script>
  // ── File Upload Preview ──────────────────────
  const fileInput = document.getElementById('img_ebook');
  const uploadArea = document.getElementById('uploadArea');
  const uploadText = document.getElementById('uploadText');
  const previewContainer = document.getElementById('previewContainer');
  const previewImg = document.getElementById('previewImg');

  fileInput.addEventListener('change', function () {
    const file = this.files[0];
    if (file) {
      uploadText.textContent = file.name;
      const reader = new FileReader();
      reader.onload = e => {
        previewImg.src = e.target.result;
        previewContainer.style.display = 'block';
      };
      reader.readAsDataURL(file);
    }
  });
</script>
