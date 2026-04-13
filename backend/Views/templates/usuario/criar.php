<?php
/**
 * View: Criar novo Usuário
 * Variáveis disponíveis: $tituloPagina (string)
 */
?>

<div class="panel" style="max-width:640px;">
  <div class="panel-header">
    <h2><i class="fas fa-user-plus" style="color:var(--gold);margin-right:8px;"></i> Novo Usuário</h2>
    <a href="<?= url('backend/usuario') ?>" class="btn btn-outline btn-sm">
      <i class="fas fa-arrow-left"></i> Voltar
    </a>
  </div>

  <form action="<?= url('backend/usuario/salvar') ?>" method="POST" novalidate>

    <div class="form-row">
      <div class="form-group" style="grid-column:1/-1;">
        <label for="nome_usuario">Nome completo *</label>
        <input type="text" id="nome_usuario" name="nome_usuario" class="form-control"
               placeholder="Ex: João da Silva"
               value="<?= e($_POST['nome_usuario'] ?? '') ?>"
               required autofocus>
      </div>
    </div>

    <div class="form-group">
      <label for="email_usuario">E-mail *</label>
      <input type="email" id="email_usuario" name="email_usuario" class="form-control"
             placeholder="exemplo@email.com"
             value="<?= e($_POST['email_usuario'] ?? '') ?>"
             required>
    </div>

    <div class="form-row">
      <div class="form-group">
        <label for="senha_usuario">Senha *</label>
        <div style="position:relative;">
          <input type="password" id="senha_usuario" name="senha_usuario" class="form-control"
                 placeholder="Mínimo 6 caracteres"
                 style="padding-right:42px;" required>
          <button type="button" onclick="toggleSenha('senha_usuario', this)"
                  style="position:absolute;right:12px;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;color:var(--text-dim);font-size:14px;">
            <i class="fas fa-eye"></i>
          </button>
        </div>
        <p class="form-hint">Mínimo de 6 caracteres.</p>
      </div>

      <div class="form-group">
        <label for="tipo_usuario">Perfil *</label>
        <select id="tipo_usuario" name="tipo_usuario" class="form-control" required>
          <option value="" disabled selected>Selecione...</option>
          <?php
          $tipos      = ['Cliente', 'Funcionario', 'Motorista', 'Admin'];
          $selecionado = $_POST['tipo_usuario'] ?? '';
          foreach ($tipos as $tipo):
          ?>
            <option value="<?= $tipo ?>" <?= $selecionado === $tipo ? 'selected' : '' ?>>
              <?= $tipo ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>
    </div>

    <div class="form-actions">
      <button type="submit" class="btn btn-gold">
        <i class="fas fa-save"></i> Cadastrar Usuário
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