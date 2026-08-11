<?php $isEdit = !empty($client); ?>
<div class="topbar">
  <div>
    <p class="eyebrow">Clients</p>
    <h1><?= $isEdit ? 'Modifier' : 'Nouveau client' ?></h1>
  </div>
  <a class="btn" href="<?= e(url('clients')) ?>">Retour</a>
</div>

<form method="post" action="<?= e(url('clients/save')) ?>" class="panel stack">
  <?= csrf_field() ?>
  <?php if ($isEdit): ?><input type="hidden" name="id" value="<?= (int) $client['id'] ?>"><?php endif; ?>
  <div class="form-grid">
    <label>Société <input name="company" value="<?= e($client['company'] ?? '') ?>"></label>
    <label>Statut
      <select name="status">
        <?php foreach (['actif','prospect','archive'] as $s): ?>
          <option value="<?= $s ?>" <?= (($client['status'] ?? 'actif') === $s) ? 'selected' : '' ?>><?= e(status_label($s)) ?></option>
        <?php endforeach; ?>
      </select>
    </label>
    <label>Prénom <input name="first_name" required value="<?= e($client['first_name'] ?? '') ?>"></label>
    <label>Nom <input name="last_name" required value="<?= e($client['last_name'] ?? '') ?>"></label>
    <label>Email <input type="email" name="email" value="<?= e($client['email'] ?? '') ?>"></label>
    <label>Téléphone <input name="phone" value="<?= e($client['phone'] ?? '') ?>"></label>
    <label class="full">Adresse <input name="address" value="<?= e($client['address'] ?? '') ?>"></label>
    <label>Ville <input name="city" value="<?= e($client['city'] ?? '') ?>"></label>
    <label>Code postal <input name="postal_code" value="<?= e($client['postal_code'] ?? '') ?>"></label>
    <label class="full">Notes <textarea name="notes"><?= e($client['notes'] ?? '') ?></textarea></label>
  </div>
  <div class="actions">
    <button class="btn btn-primary" type="submit">Enregistrer</button>
  </div>
</form>
