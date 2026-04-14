<?php
/**
 * View: Confirmar Desativação de Usuário
 * Variáveis disponíveis: $usuario (array), $tituloPagina (string)
 */
?>

<div class="panel" style="max-width:560px; margin: 0 auto;">
  <div class="panel-header">
    <h2>
      <i class="fas fa-exclamation-triangle" style="color:var(--danger);margin-right:8px;"></i> Desativar Usuário
    </h2>
    <a href="<?= url('backend/usuario') ?>" class="btn btn-outline btn-sm">
      <i class="fas fa-arrow-left"></i> Voltar
    </a>
  </div>

  <p style="color:var(--text-muted);margin-bottom:20px;font-size:14px;">
    O usuário será <strong style="color:var(--danger);">desativado</strong> e não poderá mais acessar o sistema.
    Seus dados serão preservados e ele poderá ser <strong>reativado</strong> a qualquer momento.
  </p>

  <!-- Preview do usuário -->
  <div style="background:var(--bg-input);border:1px solid var(--border);border-radius:var(--radius);padding:20px;margin-bottom:24px;">
    <div style="display:flex;align-items:center;gap:14px;margin-bottom:16px;padding-bottom:16px;border-bottom:1px solid var(--border);">
      <div style="width:48px;height:48px;border-radius:50%;background:linear-gradient(135deg,var(--gold),var(--gold-dim));display:flex;align-items:center;justify-content:center;font-size:18px;font-weight:700;color:#0f0e0b;flex-shrink:0;">
        <?= strtoupper(substr($usuario['nome_usuario'], 0, 1)) ?>
      </div>
      <div style="flex:1;">
        <div style="font-size:15px;font-weight:600;color:var(--text);"><?= e($usuario['nome_usuario']) ?></div>
        <div style="font-size:12px;color:var(--text-dim);margin-top:2px;font-family:monospace;"><?= e($usuario['email_usuario']) ?></div>
      </div>
      <?php
      $badges = [
          'Admin'       => 'background:rgba(167,139,250,.15);color:#a78bfa;',
          'Funcionario' => 'background:rgba(96,165,250,.15);color:#60a5fa;',
          'Motorista'   => 'background:rgba(232,168,56,.15);color:var(--gold);',
          'Cliente'     => 'background:var(--success-bg);color:var(--success);',
      ];
      $badgeStyle = $badges[$usuario['tipo_usuario']] ?? 'background:var(--bg-hover);color:var(--text-dim);';
      ?>
      <span class="badge" style="<?= $badgeStyle ?>"><?= e($usuario['tipo_usuario']) ?></span>
    </div>

    <div style="display:grid;gap:10px;font-size:13px;">
      <div style="display:flex;justify-content:space-between;align-items:center;">
        <span style="color:var(--text-dim);"><i class="fas fa-hashtag" style="width:16px;"></i> ID</span>
        <span style="color:var(--text);font-family:monospace;">#<?= e($usuario['id_usuario']) ?></span>
      </div>
      <div style="display:flex;justify-content:space-between;align-items:center;">
        <span style="color:var(--text-dim);"><i class="fas fa-calendar" style="width:16px;"></i> Cadastrado</span>
        <span style="color:var(--text);">
          <?= isset($usuario['criado_em']) ? date('d/m/Y \à\s H:i', strtotime($usuario['criado_em'])) : '—' ?>
        </span>
      </div>
      <div style="display:flex;justify-content:space-between;align-items:center;">
        <span style="color:var(--text-dim);"><i class="fas fa-circle-dot" style="width:16px;"></i> Status</span>
        <span class="badge badge-success"><i class="fas fa-circle" style="font-size:6px;"></i> Ativo</span>
      </div>
    </div>
  </div>

  <!-- Ações -->
  <form action="<?= url('backend/usuario/deletar') ?>" method="POST">
    <input type="hidden" name="id_usuario" value="<?= e($usuario['id_usuario']) ?>">

    <div class="form-actions" style="padding-top:0;border-top:none;">
      <button type="submit" class="btn btn-danger">
        <i class="fas fa-ban"></i> Confirmar Desativação
      </button>
      <a href="<?= url('backend/usuario') ?>" class="btn btn-outline">Cancelar</a>
    </div>
  </form>
</div>