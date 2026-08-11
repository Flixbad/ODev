<div class="topbar">
  <div>
    <p class="eyebrow">Commercial</p>
    <h1>Devis</h1>
  </div>
  <a class="btn btn-primary" href="<?= e(url('devis/create')) ?>">Nouveau devis</a>
</div>

<form class="panel actions" method="get" action="index.php" style="margin-bottom:1rem">
  <input type="hidden" name="r" value="devis">
  <select name="status">
    <option value="">Tous statuts</option>
    <?php foreach (['brouillon','envoye','accepte','refuse','expire'] as $s): ?>
      <option value="<?= $s ?>" <?= $status === $s ? 'selected' : '' ?>><?= e(status_label($s)) ?></option>
    <?php endforeach; ?>
  </select>
  <button class="btn" type="submit">Filtrer</button>
</form>

<div class="panel table-wrap">
  <table>
    <thead><tr><th>N°</th><th>Client</th><th>Titre</th><th>Date</th><th>Total</th><th>Statut</th><th></th></tr></thead>
    <tbody>
    <?php foreach ($devisList as $d): ?>
      <tr>
        <td><a href="<?= e(url('devis/show', ['id' => $d['id']])) ?>"><?= e($d['number']) ?></a></td>
        <td><?= e(client_display_name($d)) ?></td>
        <td><?= e($d['title']) ?></td>
        <td><?= e($d['issue_date']) ?></td>
        <td><?= e(money($d['total'])) ?></td>
        <td><span class="badge"><?= e(status_label($d['status'])) ?></span></td>
        <td><a class="btn btn-sm" href="<?= e(url('devis/edit', ['id' => $d['id']])) ?>">Éditer</a></td>
      </tr>
    <?php endforeach; ?>
    <?php if (!$devisList): ?><tr><td colspan="7" class="muted">Aucun devis.</td></tr><?php endif; ?>
    </tbody>
  </table>
</div>
