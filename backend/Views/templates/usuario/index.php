<?php
/**
 * View: Listagem de Usuários
 * Variáveis disponíveis: $usuarios, $total_usuarios, $total_ativos, $total_inativos, $paginacao, $tituloPagina
 */
?>

<div class="panel-header" style="margin-bottom:24px;">
  <h2 style="font-size:20px;font-weight:700;color:var(--text);">
    <i class="fas fa-users" style="color:var(--gold);margin-right:8px;"></i> Usuários
  </h2>
  <a href="<?= url('backend/usuario/criar') ?>" class="btn btn-gold">
    <i class="fas fa-plus"></i> Novo Usuário
  </a>
</div>

<!-- Stats -->
<div style="display:grid;grid-template-columns:repeat(3,1fr);gap:16px;margin-bottom:28px;">
  <div style="background:var(--bg-card);border:1px solid var(--border);border-radius:var(--radius-lg);padding:20px 24px;display:flex;align-items:center;gap:16px;">
    <div style="width:44px;height:44px;border-radius:10px;background:rgba(232,168,56,.12);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
      <i class="fas fa-users" style="color:var(--gold);font-size:18px;"></i>
    </div>
    <div>
      <div style="font-size:11px;color:var(--text-dim);text-transform:uppercase;letter-spacing:.8px;font-weight:600;">Total</div>
      <div style="font-size:24px;font-weight:700;color:var(--text);"><?= e($total_usuarios ?? 0) ?></div>
    </div>
  </div>

  <div style="background:var(--bg-card);border:1px solid var(--border);border-radius:var(--radius-lg);padding:20px 24px;display:flex;align-items:center;gap:16px;">
    <div style="width:44px;height:44px;border-radius:10px;background:var(--success-bg);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
      <i class="fas fa-user-check" style="color:var(--success);font-size:18px;"></i>
    </div>
    <div>
      <div style="font-size:11px;color:var(--text-dim);text-transform:uppercase;letter-spacing:.8px;font-weight:600;">Ativos</div>
      <div style="font-size:24px;font-weight:700;color:var(--success);"><?= e($total_ativos ?? 0) ?></div>
    </div>
  </div>

  <div style="background:var(--bg-card);border:1px solid var(--border);border-radius:var(--radius-lg);padding:20px 24px;display:flex;align-items:center;gap:16px;">
    <div style="width:44px;height:44px;border-radius:10px;background:var(--danger-bg);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
      <i class="fas fa-user-slash" style="color:var(--danger);font-size:18px;"></i>
    </div>
    <div>
      <div style="font-size:11px;color:var(--text-dim);text-transform:uppercase;letter-spacing:.8px;font-weight:600;">Inativos</div>
      <div style="font-size:24px;font-weight:700;color:var(--danger);"><?= e($total_inativos ?? 0) ?></div>
    </div>
  </div>
</div>

<!-- Tabela -->
<div class="panel">
  <div class="panel-header">
    <h3 style="font-size:15px;font-weight:600;color:var(--text);">
      <i class="fas fa-list" style="color:var(--text-dim);margin-right:8px;"></i> Lista de Usuários
    </h3>
    <?php if (isset($paginacao) && $paginacao['total'] > 0): ?>
      <span style="font-size:12px;color:var(--text-dim);">
        <?= e($paginacao['de']) ?>–<?= e($paginacao['para']) ?> de <?= e($paginacao['total']) ?>
      </span>
    <?php endif; ?>
  </div>

  <?php if (empty($usuarios)): ?>
    <div class="empty-state">
      <i class="fas fa-users-slash"></i>
      <h3>Nenhum usuário cadastrado</h3>
      <p>Comece adicionando o primeiro usuário ao sistema.</p>
      <a href="<?= url('backend/usuario/criar') ?>" class="btn btn-gold">
        <i class="fas fa-plus"></i> Adicionar Usuário
      </a>
    </div>

  <?php else: ?>
    <div class="table-responsive">
      <table class="data-table">
        <thead>
          <tr>
            <th>#</th>
            <th>Usuário</th>
            <th>E-mail</th>
            <th>Perfil</th>
            <th>Status</th>
            <th>Cadastrado em</th>
            <th style="text-align:right;">Ações</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($usuarios as $u): ?>
            <tr>
              <td style="color:var(--text-dim);font-family:monospace;"><?= e($u['id_usuario']) ?></td>
              <td>
                <div style="display:flex;align-items:center;gap:10px;">
                  <div style="width:34px;height:34px;border-radius:50%;background:linear-gradient(135deg,var(--gold),var(--gold-dim));display:flex;align-items:center;justify-content:center;font-size:13px;font-weight:700;color:#0f0e0b;flex-shrink:0;">
                    <?= strtoupper(substr($u['nome_usuario'], 0, 1)) ?>
                  </div>
                  <span style="font-weight:500;color:var(--text);"><?= e($u['nome_usuario']) ?></span>
                </div>
              </td>
              <td style="color:var(--text-muted);font-family:monospace;font-size:12.5px;"><?= e($u['email_usuario']) ?></td>
              <td>
                <?php
                $badges = [
                    'Admin'       => 'background:rgba(167,139,250,.15);color:#a78bfa;',
                    'Funcionario' => 'background:rgba(96,165,250,.15);color:#60a5fa;',
                    'Motorista'   => 'background:rgba(232,168,56,.15);color:var(--gold);',
                    'Cliente'     => 'background:var(--success-bg);color:var(--success);',
                ];
                $badgeStyle = $badges[$u['tipo_usuario']] ?? 'background:var(--bg-hover);color:var(--text-dim);';
                ?>
                <span class="badge" style="<?= $badgeStyle ?>">
                  <?= e($u['tipo_usuario']) ?>
                </span>
              </td>
              <td>
                <?php if (empty($u['excluido_em'])): ?>
                  <span class="badge badge-success">
                    <i class="fas fa-circle" style="font-size:6px;"></i> Ativo
                  </span>
                <?php else: ?>
                  <span class="badge" style="background:var(--danger-bg);color:var(--danger);">
                    <i class="fas fa-circle" style="font-size:6px;"></i> Inativo
                  </span>
                <?php endif; ?>
              </td>
              <td style="color:var(--text-dim);font-size:12px;">
                <?= isset($u['criado_em']) ? date('d/m/Y', strtotime($u['criado_em'])) : '—' ?>
              </td>
              <td>
                <div class="actions" style="justify-content:flex-end;">
                  <a href="<?= url('backend/usuario/editar/' . $u['id_usuario']) ?>"
                     class="btn btn-outline btn-sm" title="Editar">
                    <i class="fas fa-pen"></i>
                  </a>

                  <?php if (empty($u['excluido_em'])): ?>
                    <a href="<?= url('backend/usuario/deletar/' . $u['id_usuario']) ?>"
                       class="btn btn-danger btn-sm" title="Desativar">
                      <i class="fas fa-ban"></i>
                    </a>
                  <?php else: ?>
                    <form action="<?= url('backend/usuario/ativar') ?>" method="POST" style="display:inline;">
                      <input type="hidden" name="id_usuario" value="<?= e($u['id_usuario']) ?>">
                      <button type="submit" class="btn btn-sm"
                              style="background:var(--success-bg);color:var(--success);border:1px solid rgba(52,211,153,.2);"
                              title="Reativar">
                        <i class="fas fa-rotate-right"></i>
                      </button>
                    </form>
                  <?php endif; ?>
                </div>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>

    <!-- Paginação -->
    <?php if (isset($paginacao) && $paginacao['ultima_pagina'] > 1):
      $atual  = $paginacao['pagina_atual'];
      $ultima = $paginacao['ultima_pagina'];
    ?>
      <div style="display:flex;align-items:center;justify-content:space-between;padding:20px 0 4px;flex-wrap:wrap;gap:12px;">
        <span style="font-size:12px;color:var(--text-dim);">
          <?= e($paginacao['de']) ?>–<?= e($paginacao['para']) ?> de <?= e($paginacao['total']) ?> usuários
        </span>
        <div style="display:flex;gap:4px;">
          <a href="<?= url('backend/usuario/' . max(1, $atual - 1)) ?>"
             class="btn btn-outline btn-sm <?= $atual <= 1 ? 'disabled' : '' ?>"
             style="<?= $atual <= 1 ? 'opacity:.4;pointer-events:none;' : '' ?>">
            <i class="fas fa-chevron-left" style="font-size:10px;"></i>
          </a>

          <?php for ($p = max(1, $atual - 2); $p <= min($ultima, $atual + 2); $p++): ?>
            <a href="<?= url('backend/usuario/' . $p) ?>"
               class="btn btn-sm"
               style="<?= $p === $atual
                 ? 'background:var(--gold);color:#0f0e0b;font-weight:700;'
                 : 'background:var(--bg-input);border:1px solid var(--border);color:var(--text-muted);' ?>">
              <?= $p ?>
            </a>
          <?php endfor; ?>

          <a href="<?= url('backend/usuario/' . min($ultima, $atual + 1)) ?>"
             class="btn btn-outline btn-sm <?= $atual >= $ultima ? 'disabled' : '' ?>"
             style="<?= $atual >= $ultima ? 'opacity:.4;pointer-events:none;' : '' ?>">
            <i class="fas fa-chevron-right" style="font-size:10px;"></i>
          </a>
        </div>
      </div>
    <?php endif; ?>

  <?php endif; ?>
</div>