<?php
$isEdit = !empty($devis);
$tax = $devis['tax_rate'] ?? (app_config()['default_tax_rate'] ?? 0);
?>
<div class="topbar">
  <div>
    <p class="eyebrow">Devis</p>
    <h1><?= $isEdit ? e($devis['number']) : 'Nouveau devis' ?></h1>
  </div>
  <a class="btn" href="<?= e(url('devis')) ?>">Retour</a>
</div>

<form method="post" action="<?= e(url('devis/save')) ?>" class="panel stack" id="doc-form">
  <?= csrf_field() ?>
  <?php if ($isEdit): ?><input type="hidden" name="id" value="<?= (int) $devis['id'] ?>"><?php endif; ?>
  <div class="form-grid">
    <label>Client
      <select name="client_id" required>
        <option value="">— Choisir —</option>
        <?php foreach ($clients as $c): ?>
          <option value="<?= (int) $c['id'] ?>" <?= ((int)($devis['client_id'] ?? 0) === (int)$c['id']) ? 'selected' : '' ?>>
            <?= e(client_display_name($c)) ?>
          </option>
        <?php endforeach; ?>
      </select>
    </label>
    <label>Statut
      <select name="status">
        <?php foreach (['brouillon','envoye','accepte','refuse','expire'] as $s): ?>
          <option value="<?= $s ?>" <?= (($devis['status'] ?? 'brouillon') === $s) ? 'selected' : '' ?>><?= e(status_label($s)) ?></option>
        <?php endforeach; ?>
      </select>
    </label>
    <label class="full">Titre <input name="title" required value="<?= e($devis['title'] ?? '') ?>"></label>
    <label>Date d’émission <input type="date" name="issue_date" required value="<?= e($devis['issue_date'] ?? date('Y-m-d')) ?>"></label>
    <label>Valable jusqu’au <input type="date" name="valid_until" value="<?= e($devis['valid_until'] ?? '') ?>"></label>
    <label>TVA % <input type="number" step="0.01" name="tax_rate" value="<?= e((string) $tax) ?>"></label>
    <label class="full">Notes <textarea name="notes"><?= e($devis['notes'] ?? '') ?></textarea></label>
  </div>

  <h2 class="h-section">Lignes</h2>
  <div class="table-wrap">
    <table class="items-table" id="items-table">
      <thead><tr><th>Description</th><th>Qté</th><th>Prix unit.</th><th></th></tr></thead>
      <tbody>
      <?php foreach ($items as $item): ?>
        <tr>
          <td><input name="item_description[]" value="<?= e($item['description'] ?? '') ?>" required></td>
          <td><input name="item_quantity[]" type="number" step="0.01" value="<?= e((string)($item['quantity'] ?? 1)) ?>"></td>
          <td><input name="item_unit_price[]" type="number" step="0.01" value="<?= e((string)($item['unit_price'] ?? 0)) ?>"></td>
          <td><button type="button" class="btn btn-sm btn-danger" data-remove-row>✕</button></td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
  <div class="actions">
    <button type="button" class="btn" id="add-row">+ Ligne</button>
    <button class="btn btn-primary" type="submit">Enregistrer</button>
  </div>
</form>
