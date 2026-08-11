<div class="topbar">
  <div>
    <p class="eyebrow">Suivi</p>
    <h1>Comptabilité</h1>
  </div>
</div>

<form class="panel actions" method="get" action="index.php" style="margin-bottom:1rem">
  <input type="hidden" name="r" value="compta">
  <label style="text-transform:none;letter-spacing:0">Du <input type="date" name="from" value="<?= e($from) ?>"></label>
  <label style="text-transform:none;letter-spacing:0">Au <input type="date" name="to" value="<?= e($to) ?>"></label>
  <label style="text-transform:none;letter-spacing:0">Année graphique <input type="number" name="year" value="<?= (int) $year ?>"></label>
  <button class="btn btn-primary" type="submit">Actualiser</button>
</form>

<div class="grid-stats">
  <div class="stat"><div class="label">Facturé (période)</div><div class="value"><?= e(money($facture)) ?></div></div>
  <div class="stat"><div class="label">Encaissé (période)</div><div class="value"><?= e(money($encaisse)) ?></div></div>
  <div class="stat"><div class="label">Reste à encaisser</div><div class="value"><?= e(money($due)) ?></div></div>
</div>

<div class="panel">
  <h2>Encaissements <?= (int) $year ?></h2>
  <?php $max = max(1, ...array_values($monthly)); ?>
  <div class="chart">
    <?php for ($m = 1; $m <= 12; $m++): ?>
      <div class="chart-bar" style="height: <?= max(4, ($monthly[$m] / $max) * 130) ?>px" title="<?= e(money($monthly[$m])) ?>"></div>
    <?php endfor; ?>
  </div>
  <div class="chart-labels">
    <?php foreach (['J','F','M','A','M','J','J','A','S','O','N','D'] as $label): ?><span><?= $label ?></span><?php endforeach; ?>
  </div>
</div>

<div class="panel table-wrap" style="margin-top:1rem">
  <h2>Paiements de la période</h2>
  <table>
    <thead><tr><th>Date</th><th>Facture</th><th>Client</th><th>Méthode</th><th>Montant</th></tr></thead>
    <tbody>
    <?php foreach ($paiements as $p): ?>
      <tr>
        <td><?= e($p['paid_at']) ?></td>
        <td><a href="<?= e(url('factures/show', ['id' => $p['facture_id']])) ?>"><?= e($p['facture_number']) ?></a></td>
        <td><?= e(client_display_name($p)) ?></td>
        <td><?= e($p['method']) ?></td>
        <td><?= e(money($p['amount'])) ?></td>
      </tr>
    <?php endforeach; ?>
    <?php if (!$paiements): ?><tr><td colspan="5" class="muted">Aucun paiement sur la période.</td></tr><?php endif; ?>
    </tbody>
  </table>
</div>
