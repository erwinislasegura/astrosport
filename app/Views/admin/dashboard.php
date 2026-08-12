<?php
$usedGb = $stats['storage'] / 1073741824;
$storagePercent = min(100, round($usedGb / 50 * 100));
$maxDaily = max([1, ...array_column($daily, 'total')]);
$chartPoints = [];
$chartLabels = [];
foreach ($daily as $index => $day) {
    $x = count($daily) > 1 ? $index * (700 / (count($daily) - 1)) : 350;
    $y = 180 - ((int)$day['total'] / $maxDaily * 150);
    $chartPoints[] = $x . ',' . $y;
    $chartLabels[] = date('d/m', strtotime($day['day']));
}
?>

<div class="content dashboard-workspace">
    <section class="title dashboard-title">
        <div>
            <span class="eyebrow">CENTRO DE OPERACIONES</span>
            <h1>Resumen del negocio</h1>
            <p>Ventas, catálogo y actividad reciente de AstroSport en una sola vista.</p>
        </div>
        <div class="dashboard-title-actions">
            <span><?= date('d/m/Y') ?></span>
            <a class="btn btn-primary" href="<?= url('admin/fotos') ?>">+ NUEVO SET</a>
        </div>
    </section>

    <section class="dashboard-metrics">
        <article>
            <div><span>INGRESOS PAGADOS</span><i>$</i></div>
            <strong><?= money($stats['sales']) ?></strong>
            <small><?= $stats['orders'] ?> pedidos completados</small>
        </article>
        <article>
            <div><span>PEDIDOS</span><i>01</i></div>
            <strong><?= $stats['orders'] ?></strong>
            <small><b><?= $stats['pending'] ?></b> pendientes de gestión</small>
        </article>
        <article>
            <div><span>FOTOS VENDIDAS</span><i>02</i></div>
            <strong><?= $stats['sold'] ?></strong>
            <small><?= $stats['photos'] ?> fotografías disponibles</small>
        </article>
        <article>
            <div><span>TICKET PROMEDIO</span><i>03</i></div>
            <strong><?= money($stats['average']) ?></strong>
            <small>Valor promedio por pedido</small>
        </article>
    </section>

    <section class="dashboard-primary-grid">
        <article class="panel dashboard-sales-card">
            <div class="dashboard-card-head">
                <div><span class="eyebrow">RENDIMIENTO</span><h2>Ingresos recientes</h2><p>Comportamiento diario de las ventas pagadas.</p></div>
                <div class="dashboard-total"><small>TOTAL REGISTRADO</small><strong><?= money($stats['sales']) ?></strong></div>
            </div>
            <div class="dashboard-chart">
                <div class="dashboard-axis"><span><?= money((int)$maxDaily) ?></span><span><?= money((int)($maxDaily / 2)) ?></span><span>$0</span></div>
                <div class="dashboard-plot">
                    <svg viewBox="0 0 700 190" preserveAspectRatio="none" role="img" aria-label="Ingresos diarios">
                        <g class="dashboard-grid-lines"><line x1="0" y1="30" x2="700" y2="30"/><line x1="0" y1="105" x2="700" y2="105"/><line x1="0" y1="180" x2="700" y2="180"/></g>
                        <?php if ($chartPoints): ?><polyline points="<?= implode(' ', $chartPoints) ?>"/><?php endif; ?>
                    </svg>
                    <div class="dashboard-chart-labels"><?php foreach ($chartLabels as $label): ?><span><?= $label ?></span><?php endforeach; ?></div>
                </div>
            </div>
        </article>

        <aside class="dashboard-side-stack">
            <article class="panel dashboard-status-card">
                <div class="dashboard-card-head compact"><div><span class="eyebrow">ESTADO</span><h2>Operación</h2></div><span class="operation-live"><i></i> ACTIVA</span></div>
                <div class="operation-row"><span>Pedidos pendientes</span><strong><?= $stats['pending'] ?></strong></div>
                <div class="operation-row"><span>Fotografías publicadas</span><strong><?= $stats['photos'] ?></strong></div>
                <div class="operation-row"><span>Almacenamiento</span><strong><?= number_format($usedGb, 2, ',', '.') ?> GB</strong></div>
                <div class="storage-progress"><i style="width:<?= $storagePercent ?>%"></i></div>
                <small><?= number_format(max(0, 50 - $usedGb), 1, ',', '.') ?> GB disponibles de 50 GB</small>
            </article>
            <article class="panel dashboard-actions-card">
                <div class="dashboard-card-head compact"><div><span class="eyebrow">ATAJOS</span><h2>Acciones rápidas</h2></div></div>
                <a href="<?= url('admin/fotos') ?>"><span><b>Subir fotografías</b><small>Crear y publicar un nuevo set</small></span><strong>→</strong></a>
                <a href="<?= url('admin/eventos') ?>"><span><b>Gestionar eventos</b><small>Editar fechas y coberturas</small></span><strong>→</strong></a>
                <a href="<?= url('admin/pedidos') ?>"><span><b>Revisar pedidos</b><small>Consultar ventas y estados</small></span><strong>→</strong></a>
            </article>
        </aside>
    </section>

    <section class="dashboard-secondary-grid">
        <article class="panel dashboard-events-card">
            <div class="dashboard-card-head"><div><span class="eyebrow">RENDIMIENTO</span><h2>Eventos con más ventas</h2></div><a href="<?= url('admin/eventos') ?>">VER EVENTOS →</a></div>
            <div class="dashboard-event-list">
                <?php if (!$topEvents): ?><p class="dashboard-empty">Aún no existen ventas asociadas a eventos.</p><?php endif; ?>
                <?php foreach ($topEvents as $index => $event): ?>
                    <article>
                        <span class="event-position"><?= str_pad((string)($index + 1), 2, '0', STR_PAD_LEFT) ?></span>
                        <img src="<?= preview_url($event) ?>" alt="">
                        <div><b><?= htmlspecialchars($event['name']) ?></b><small><?= date('d/m/Y', strtotime($event['event_date'])) ?> · <?= htmlspecialchars($event['sport']) ?></small></div>
                        <span class="event-result"><b><?= money((int)$event['sales']) ?></b><small><?= $event['orders_count'] ?> pedidos</small></span>
                    </article>
                <?php endforeach; ?>
            </div>
        </article>

        <article class="panel dashboard-orders-card">
            <div class="dashboard-card-head"><div><span class="eyebrow">ACTIVIDAD</span><h2>Pedidos recientes</h2></div><a href="<?= url('admin/pedidos') ?>">VER PEDIDOS →</a></div>
            <div class="table-wrap">
                <table>
                    <thead><tr><th>PEDIDO</th><th>CLIENTE</th><th>CONTENIDO</th><th>TOTAL</th><th>ESTADO</th></tr></thead>
                    <tbody>
                        <?php if (!$recent): ?><tr><td colspan="5" class="dashboard-empty">Todavía no existen pedidos registrados.</td></tr><?php endif; ?>
                        <?php foreach ($recent as $order): ?>
                            <tr>
                                <td><b>#AST-<?= str_pad((string)$order['id'], 4, '0', STR_PAD_LEFT) ?></b><small><?= date('d/m H:i', strtotime($order['created_at'])) ?></small></td>
                                <td><?= htmlspecialchars($order['customer_name']) ?></td>
                                <td><?= $order['items'] ?> fotografía(s)</td>
                                <td><b><?= money((int)$order['total']) ?></b></td>
                                <td><span class="status <?= $order['status'] === 'paid' ? 'paid' : 'pending' ?>"><?= $order['status'] === 'paid' ? 'PAGADO' : 'PENDIENTE' ?></span></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </article>
    </section>
</div>
