<?php
/**
 * Dashboard - conteudo exclusivo da pagina.
 * O layout (sidebar, topbar, head, scripts) ja e carregado por
 * partials/header.php e partials/footer.php via View::render().
 */
?>

<style>
.dashboard-charts {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 16px;
    margin-bottom: 24px;
}
.chart-container {
    position: relative;
    height: 260px;
}
@media (max-width: 768px) {
    .dashboard-charts { grid-template-columns: 1fr; }
}
.stats-grid {
    grid-template-columns: repeat(5, 1fr);
}
@media (max-width: 1100px) {
    .stats-grid { grid-template-columns: repeat(3, 1fr); }
}
@media (max-width: 700px) {
    .stats-grid { grid-template-columns: repeat(2, 1fr); }
}
@media (max-width: 480px) {
    .stats-grid { grid-template-columns: 1fr; }
}
</style>

<div class="page-header">
    <div>
        <h1 class="page-title">Bem-vindo, <?= htmlspecialchars($nomeUsuario ?? 'Admin') ?>! &#x1F44B;</h1>
        <p class="page-subtitle">Aqui est&aacute; o resumo geral do sistema.</p>
    </div>
</div>

<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-card-icon" style="background: var(--brand-light); color: var(--brand);">
            <i class="fas fa-egg"></i>
        </div>
        <div class="stat-card-label">Tipos de Ovo</div>
        <div class="stat-card-value"><?= $totalTipoOvo ?? 0 ?></div>
        <?php if (($totalTipoOvoInativos ?? 0) > 0): ?>
            <small style="color: var(--text-muted); font-size: .72rem;"><?= $totalTipoOvoInativos ?> inativos</small>
        <?php endif; ?>
    </div>

    <div class="stat-card">
        <div class="stat-card-icon" style="background: var(--purple-bg); color: var(--purple);">
            <i class="fas fa-box"></i>
        </div>
        <div class="stat-card-label">Produtos</div>
        <div class="stat-card-value"><?= $totalProdutos ?? 0 ?></div>
        <?php if (($totalProdutosInativos ?? 0) > 0): ?>
            <small style="color: var(--text-muted); font-size: .72rem;"><?= $totalProdutosInativos ?> inativos</small>
        <?php endif; ?>
    </div>

    <div class="stat-card">
        <div class="stat-card-icon" style="background: var(--green-bg); color: var(--green);">
            <i class="fas fa-shopping-cart"></i>
        </div>
        <div class="stat-card-label">Vendas (m&ecirc;s)</div>
        <div class="stat-card-value"><?= $vendasMes ?? 0 ?></div>
    </div>

    <div class="stat-card">
        <div class="stat-card-icon" style="background: var(--blue-bg); color: var(--blue);">
            <i class="fas fa-dollar-sign"></i>
        </div>
        <div class="stat-card-label">Faturamento (m&ecirc;s)</div>
        <div class="stat-card-value">R$ <?= number_format($faturamentoMes ?? 0, 2, ',', '.') ?></div>
    </div>

    <div class="stat-card">
        <div class="stat-card-icon" style="background: var(--amber-bg); color: var(--amber);">
            <i class="fas fa-clock"></i>
        </div>
        <div class="stat-card-label">Pedidos Pendentes</div>
        <div class="stat-card-value"><?= $totalPendentes ?? 0 ?></div>
    </div>
</div>

<div class="dashboard-charts">
    <div class="card">
        <div class="card-header">
            <h2><i class="fas fa-chart-bar" style="color: var(--brand);"></i> Vendas por M&ecirc;s</h2>
        </div>
        <div class="card-body">
            <div class="chart-container">
                <canvas id="vendasChart"></canvas>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h2><i class="fas fa-chart-line" style="color: var(--blue);"></i> Pedidos por M&ecirc;s</h2>
        </div>
        <div class="card-body">
            <div class="chart-container">
                <canvas id="pedidosChart"></canvas>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h2><i class="fas fa-box-open" style="color: var(--purple);"></i> &Uacute;ltimos Produtos Cadastrados</h2>
        <a href="/produto/listar" class="btn btn-secondary btn-sm">
            <i class="fas fa-arrow-right"></i> Ver todos
        </a>
    </div>
    <?php if (!empty($ultimosProdutos)): ?>
    <div class="table-wrapper">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nome</th>
                    <th>Pre&ccedil;o</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($ultimosProdutos as $prod): ?>
                <tr>
                    <td class="td-mono">#<?= $prod['id'] ?? '' ?></td>
                    <td><?= htmlspecialchars($prod['nome'] ?? '') ?></td>
                    <td>R$ <?= number_format($prod['preco'] ?? 0, 2, ',', '.') ?></td>
                    <td>
                        <?php if (($prod['excluido_em'] ?? null) === null): ?>
                            <span class="badge badge-green"><i class="fas fa-circle" style="font-size:.45rem;"></i> Ativo</span>
                        <?php else: ?>
                            <span class="badge badge-red"><i class="fas fa-circle" style="font-size:.45rem;"></i> Inativo</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php else: ?>
    <div class="empty-state">
        <i class="fas fa-box-open"></i>
        <p>Nenhum produto cadastrado ainda.</p>
    </div>
    <?php endif; ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4/dist/chart.umd.min.js"></script>
<script>
(function() {
    var brandColor = '#F97316';
    var blueColor  = '#2563EB';

    var vendasCtx = document.getElementById('vendasChart');
    if (vendasCtx) {
        new Chart(vendasCtx, {
            type: 'bar',
            data: {
                labels: <?= json_encode($vendas_chart_labels ?? []) ?>,
                datasets: [{
                    label: 'Vendas',
                    data: <?= json_encode($vendas_chart_data ?? []) ?>,
                    backgroundColor: brandColor + '33',
                    borderColor: brandColor,
                    borderWidth: 2,
                    borderRadius: 6,
                    barPercentage: 0.6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    y: { beginAtZero: true, ticks: { precision: 0 }, grid: { color: '#EDE5DC' } },
                    x: { grid: { display: false } }
                }
            }
        });
    }

    var pedidosCtx = document.getElementById('pedidosChart');
    if (pedidosCtx) {
        new Chart(pedidosCtx, {
            type: 'line',
            data: {
                labels: <?= json_encode($pedidos_chart_labels ?? []) ?>,
                datasets: [{
                    label: 'Pedidos',
                    data: <?= json_encode($pedidos_chart_data ?? []) ?>,
                    borderColor: blueColor,
                    backgroundColor: blueColor + '18',
                    fill: true,
                    tension: 0.35,
                    pointRadius: 4,
                    pointBackgroundColor: blueColor,
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    y: { beginAtZero: true, ticks: { precision: 0 }, grid: { color: '#EDE5DC' } },
                    x: { grid: { display: false } }
                }
            }
        });
    }
})();
</script>
