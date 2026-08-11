<div class="topbar">
  <div>
    <p class="eyebrow">Vue d’ensemble</p>
    <h1>Tableau de bord</h1>
  </div>
  <div class="actions">
    <a class="btn btn-primary" href="<?= e(url('clients/create')) ?>">+ Client</a>
    <a class="btn" href="<?= e(url('devis/create')) ?>">+ Devis</a>
    <a class="btn" href="<?= e(url('factures/create')) ?>">+ Facture</a>
  </div>
</div>

<div class="grid-stats">
  <div class="stat"><div class="label">Clients</div><div class="value"><?= (int) $stats['clients'] ?></div></div>
  <div class="stat"><div class="label">Encaissé ce mois</div><div class="value"><?= e(money($stats['ca_month'])) ?></div></div>
  <div class="stat"><div class="label">Reste à encaisser</div><div class="value"><?= e(money($stats['unpaid'])) ?></div></div>
  <div class="stat"><div class="label">Devis ouverts</div><div class="value"><?= (int) $stats['devis_open'] ?></div></div>
  <div class="stat"><div class="label">Factures en retard</div><div class="value"><?= (int) $stats['factures_late'] ?></div></div>
</div>

<div class="panel">
  <h2>Encaissements <?= date('Y') ?></h2>
  <?php
    $max = max(1, ...array_values($monthly));
  ?>
  <div class="chart">
    <?php for ($m = 1; $m <= 12; $m++): ?>
      <div class="chart-bar" style="height: <?= max(4, ($monthly[$m] / $max) * 130) ?>px" title="<?= e(money($monthly[$m])) ?>"></div>
    <?php endfor; ?>
  </div>
  <div class="chart-labels">
    <?php foreach (['J','F','M','A','M','J','J','A','S','O','N','D'] as $label): ?>
      <span><?= $label ?></span>
    <?php endforeach; ?>
  </div>
</div>

<div class="form-grid" style="margin-top:1rem">
  <div class="panel">
    <h2>Derniers clients</h2>
    <div class="table-wrap">
      <table>
        <thead><tr><th>Nom</th><th>Statut</th></tr></thead>
        <tbody>
        <?php foreach ($recentClients as $c): ?>
          <tr>
            <td><a href="<?= e(url('clients/show', ['id' => $c['id']])) ?>"><?= e(client_display_name($c)) ?></a></td>
            <td><span class="badge"><?= e(status_label($c['status'])) ?></span></td>
          </tr>
        <?php endforeach; ?>
        <?php if (!$recentClients): ?><tr><td colspan="2" class="muted">Aucun client</td></tr><?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
  <div class="panel">
    <h2>Dernières factures</h2>
    <div class="table-wrap">
      <table>
        <thead><tr><th>N°</th><th>Total</th><th>Statut</th></tr></thead>
        <tbody>
        <?php foreach ($recentFactures as $f): ?>
          <tr>
            <td><a href="<?= e(url('factures/show', ['id' => $f['id']])) ?>"><?= e($f['number']) ?></a></td>
            <td><?= e(money($f['total'])) ?></td>
            <td><span class="badge"><?= e(status_label($f['status'])) ?></span></td>
          </tr>
        <?php endforeach; ?>
        <?php if (!$recentFactures): ?><tr><td colspan="3" class="muted">Aucune facture</td></tr><?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
