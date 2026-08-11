<div class="topbar">
  <div>
    <p class="eyebrow">Facturation</p>
    <h1>Factures</h1>
  </div>
  <a class="btn btn-primary" href="<?= e(url('factures/create')) ?>">Nouvelle facture</a>
</div>

<form class="panel actions" method="get" action="index.php" style="margin-bottom:1rem">
  <input type="hidden" name="r" value="factures">
  <select name="status">
    <option value="">Tous statuts</option>
    <?php foreach (['brouillon','envoyee','payee','en_retard','annulee'] as $s): ?>
      <option value="<?= $s ?>" <?= $status === $s ? 'selected' : '' ?>><?= e(status_label($s)) ?></option>
    <?php endforeach; ?>
  </select>
  <button class="btn" type="submit">Filtrer</button>
</form>

<div class="panel table-wrap">
  <table>
    <thead><tr><th>N°</th><th>Client</th><th>Échéance</th><th>Total</th><th>Payé</th><th>Statut</th><th></th></tr></thead>
    <tbody>
    <?php foreach ($factures as $f): ?>
      <tr>
        <td><a href="<?= e(url('factures/show', ['id' => $f['id']])) ?>"><?= e($f['number']) ?></a></td>
        <td><?= e(client_display_name($f)) ?></td>
        <td><?= e($f['due_date'] ?? '—') ?></td>
        <td><?= e(money($f['total'])) ?></td>
        <td><?= e(money($f['amount_paid'])) ?></td>
        <td><span class="badge <?= $f['status'] === 'payee' ? 'badge-ok' : ($f['status'] === 'en_retard' ? 'badge-danger' : '') ?>"><?= e(status_label($f['status'])) ?></span></td>
        <td><a class="btn btn-sm" href="<?= e(url('factures/edit', ['id' => $f['id']])) ?>">Éditer</a></td>
      </tr>
    <?php endforeach; ?>
    <?php if (!$factures): ?><tr><td colspan="7" class="muted">Aucune facture.</td></tr><?php endif; ?>
    </tbody>
  </table>
</div>
