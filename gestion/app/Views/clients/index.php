<div class="topbar">
  <div>
    <p class="eyebrow">Carnet</p>
    <h1>Clients</h1>
  </div>
  <a class="btn btn-primary" href="<?= e(url('clients/create')) ?>">Nouveau client</a>
</div>

<form class="panel actions" method="get" action="index.php" style="margin-bottom:1rem">
  <input type="hidden" name="r" value="clients">
  <input type="search" name="q" value="<?= e($q) ?>" placeholder="Rechercher…">
  <select name="status">
    <option value="">Tous statuts</option>
    <?php foreach (['actif','prospect','archive'] as $s): ?>
      <option value="<?= $s ?>" <?= $status === $s ? 'selected' : '' ?>><?= e(status_label($s)) ?></option>
    <?php endforeach; ?>
  </select>
  <button class="btn" type="submit">Filtrer</button>
</form>

<div class="panel table-wrap">
  <table>
    <thead>
      <tr>
        <th>Client</th>
        <th>Contact</th>
        <th>Ville</th>
        <th>Statut</th>
        <th></th>
      </tr>
    </thead>
    <tbody>
    <?php foreach ($clients as $c): ?>
      <tr>
        <td>
          <strong><a href="<?= e(url('clients/show', ['id' => $c['id']])) ?>"><?= e(client_display_name($c)) ?></a></strong>
        </td>
        <td>
          <?= e($c['email'] ?? '—') ?><br>
          <span class="muted"><?= e($c['phone'] ?? '') ?></span>
        </td>
        <td><?= e($c['city'] ?? '—') ?></td>
        <td><span class="badge"><?= e(status_label($c['status'])) ?></span></td>
        <td class="actions">
          <a class="btn btn-sm" href="<?= e(url('clients/edit', ['id' => $c['id']])) ?>">Éditer</a>
        </td>
      </tr>
    <?php endforeach; ?>
    <?php if (!$clients): ?>
      <tr><td colspan="5" class="muted">Aucun client pour le moment.</td></tr>
    <?php endif; ?>
    </tbody>
  </table>
</div>
