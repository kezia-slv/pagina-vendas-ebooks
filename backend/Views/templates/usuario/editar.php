<?php
/**
 * View: Editar Usuário
 * Variáveis disponíveis: $usuario (array), $tituloPagina (string)
 */
?>

<div class="panel" style="max-width:640px; margin: 0 auto;">
  <div class="panel-header">
    <h2>
      <i class="fas fa-user-pen" style="color:var(--gold);margin-right:8px;"></i> Editar Usuário
      <span style="font-size:13px;color:var(--text-dim);font-weight:400;margin-left:8px;font-family:monospace;">
        #<?= e($usuario['id_usuario']) ?>
      </span>
    </h2>
    <a href="<?= url('backend/usuario') ?>" class="btn btn-outline btn-sm">
      <i class="fas fa-arrow-left"></i> Voltar
    </a>
  </div>

  <!-- Status badge -->
  <div style="margin-bottom:20px;">
    <?php if (empty($usuario['excluido_em'])): ?>
      <span class="badge badge-success"><i class="fas fa-circle" style="font-size:6px;"></i> Ativo</span>
    <?php else: ?>
      <span class="badge" style="background:var(--danger-bg);color:var(--danger);">
        <i class="fas fa-circle" style="font-size:6px;"></i> Inativo
      </span>
    <?php endif; ?>
  </div>

  <form action="<?= url('backend/usuario/atualizar') ?>" method="POST" novalidate>
    <input type="hidden" name="id_usuario" value="<?= e($usuario['id_usuario']) ?>">

    <div class="form-group">
      <label for="nome_usuario">Nome completo *</label>
      <input type="text" id="nome_usuario" name="nome_usuario" class="form-control"
             value="<?= e($usuario['nome_usuario']) ?>"
             required autofocus>
    </div>

    <div class="form-group">
      <label for="email_usuario">E-mail *</label>
      <input type="email" id="email_usuario" name="email_usuario" class="form-control"
             value="<?= e($usuario['email_usuario']) ?>"
             required>
    </div>

    <div class="form-row">
      <div class="form-group">
        <label for="senha_usuario">Nova Senha</label>
        <div style="position:relative;">
          <input type="password" id="senha_usuario" name="senha_usuario" class="form-control"
                 placeholder="Deixe em branco para manter"
                 style="padding-right:42px;">
          <button type="button" onclick="toggleSenha('senha_usuario', this)"
                  style="position:absolute;right:12px;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;color:var(--text-dim);font-size:14px;">
            <i class="fas fa-eye"></i>
          </button>
        </div>
        <p class="form-hint">Deixe em branco para manter a senha atual.</p>
      </div>

      <div class="form-group">
        <label for="tipo_usuario">Perfil *</label>
        <select id="tipo_usuario" name="tipo_usuario" class="form-control" required>
          <?php
          $tipos = ['Cliente', 'Funcionario', 'Motorista', 'Admin'];
          foreach ($tipos as $tipo):
          ?>
            <option value="<?= $tipo ?>" <?= ($usuario['tipo_usuario'] ?? '') === $tipo ? 'selected' : '' ?>>
              <?= $tipo ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>
    </div>

    <!-- Datas informativas -->
    <div style="background:var(--bg-input);border:1px solid var(--border);border-radius:var(--radius);padding:14px 16px;margin-bottom:4px;display:flex;flex-wrap:wrap;gap:20px;font-size:12px;color:var(--text-dim);">
      <?php if (!empty($usuario['criado_em'])): ?>
        <span><i class="fas fa-calendar-plus" style="margin-right:5px;"></i>
          Criado em: <strong style="color:var(--text-muted);"><?= date('d/m/Y H:i', strtotime($usuario['criado_em'])) ?></strong>
        </span>
      <?php endif; ?>
      <?php if (!empty($usuario['atualizado_em'])): ?>
        <span><i class="fas fa-calendar-check" style="margin-right:5px;"></i>
          Atualizado: <strong style="color:var(--text-muted);"><?= date('d/m/Y H:i', strtotime($usuario['atualizado_em'])) ?></strong>
        </span>
      <?php endif; ?>
    </div>

    <div class="form-actions">
      <button type="submit" class="btn btn-gold">
        <i class="fas fa-save"></i> Salvar Alterações
      </button>
      <a href="<?= url('backend/usuario') ?>" class="btn btn-outline">Cancelar</a>
    </div>

  </form>
</div>

<script>
function toggleSenha(inputId, btn) {
  const input = document.getElementById(inputId);
  const icon  = btn.querySelector('i');
  if (input.type === 'password') {
    input.type    = 'text';
    icon.className = 'fas fa-eye-slash';
  } else {
    input.type    = 'password';
    icon.className = 'fas fa-eye';
  }
}
</script>