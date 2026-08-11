<div class="topbar">
  <div>
    <p class="eyebrow">Fiche client</p>
    <h1><?= e(client_display_name($client)) ?></h1>
  </div>
  <div class="actions">
    <a class="btn" href="<?= e(url('clients/edit', ['id' => $client['id']])) ?>">Modifier</a>
    <a class="btn btn-primary" href="<?= e(url('devis/create')) ?>">Nouveau devis</a>
    <form method="post" action="<?= e(url('clients/delete')) ?>" onsubmit="return confirm('Supprimer ce client ?')">
      <?= csrf_field() ?>
      <input type="hidden" name="id" value="<?= (int) $client['id'] ?>">
      <button class="btn btn-danger" type="submit">Supprimer</button>
    </form>
  </div>
</div>

<div class="form-grid">
  <div class="panel">
    <h2>Coordonnées</h2>
    <p><strong>Email</strong><br><?= e($client['email'] ?: '—') ?></p>
    <p><strong>Téléphone</strong><br><?= e($client['phone'] ?: '—') ?></p>
    <p><strong>Adresse</strong><br>
      <?= e($client['address'] ?: '') ?><br>
      <?= e(trim(($client['postal_code'] ?? '') . ' ' . ($client['city'] ?? ''))) ?>
    </p>
    <p><span class="badge"><?= e(status_label($client['status'])) ?></span></p>
    <?php if ($client['notes']): ?>
      <p><strong>Notes</strong><br><?= nl2br(e($client['notes'])) ?></p>
    <?php endif; ?>
  </div>
  <div class="stack-lg">
    <div class="panel table-wrap">
      <h2>Devis</h2>
      <table>
        <thead><tr><th>N°</th><th>Total</th><th>Statut</th></tr></thead>
        <tbody>
        <?php foreach ($devis as $d): ?>
          <tr>
            <td><a href="<?= e(url('devis/show', ['id' => $d['id']])) ?>"><?= e($d['number']) ?></a></td>
            <td><?= e(money($d['total'])) ?></td>
            <td><?= e(status_label($d['status'])) ?></td>
          </tr>
        <?php endforeach; ?>
        <?php if (!$devis): ?><tr><td colspan="3" class="muted">Aucun devis</td></tr><?php endif; ?>
        </tbody>
      </table>
    </div>
    <div class="panel table-wrap">
      <h2>Factures</h2>
      <table>
        <thead><tr><th>N°</th><th>Total</th><th>Statut</th></tr></thead>
        <tbody>
        <?php foreach ($factures as $f): ?>
          <tr>
            <td><a href="<?= e(url('factures/show', ['id' => $f['id']])) ?>"><?= e($f['number']) ?></a></td>
            <td><?= e(money($f['total'])) ?></td>
            <td><?= e(status_label($f['status'])) ?></td>
          </tr>
        <?php endforeach; ?>
        <?php if (!$factures): ?><tr><td colspan="3" class="muted">Aucune facture</td></tr><?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
