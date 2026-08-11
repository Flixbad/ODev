<?php $cfg = app_config(); ?>
<?php if (empty($print)): ?>
<div class="topbar no-print">
  <div>
    <p class="eyebrow">Devis</p>
    <h1><?= e($devis['number']) ?></h1>
  </div>
  <div class="actions">
    <a class="btn" href="<?= e(url('devis/edit', ['id' => $devis['id']])) ?>">Modifier</a>
    <a class="btn" href="<?= e(url('devis/show', ['id' => $devis['id'], 'print' => 1])) ?>" target="_blank">Imprimer / PDF</a>
    <form method="post" action="<?= e(url('devis/to-invoice')) ?>">
      <?= csrf_field() ?>
      <input type="hidden" name="id" value="<?= (int) $devis['id'] ?>">
      <button class="btn btn-primary" type="submit">Convertir en facture</button>
    </form>
    <form method="post" action="<?= e(url('devis/delete')) ?>" onsubmit="return confirm('Supprimer ce devis ?')">
      <?= csrf_field() ?>
      <input type="hidden" name="id" value="<?= (int) $devis['id'] ?>">
      <button class="btn btn-danger" type="submit">Supprimer</button>
    </form>
  </div>
</div>
<?php endif; ?>

<article class="doc-sheet">
  <div class="doc-head">
    <div>
      <strong><?= e($cfg['company']['name'] ?? 'ODev') ?></strong><br>
      <?= e($cfg['company']['email'] ?? '') ?>
    </div>
    <div style="text-align:right">
      <div class="eyebrow">Devis</div>
      <h2 style="margin:0"><?= e($devis['number']) ?></h2>
      <p class="muted">Émis le <?= e($devis['issue_date']) ?></p>
      <span class="badge"><?= e(status_label($devis['status'])) ?></span>
    </div>
  </div>

  <p><strong>Client</strong><br><?= e(client_display_name($devis)) ?><br>
    <?= e($devis['email'] ?? '') ?><br>
    <?= e(trim(($devis['address'] ?? '') . ' ' . ($devis['postal_code'] ?? '') . ' ' . ($devis['city'] ?? ''))) ?>
  </p>
  <h3><?= e($devis['title']) ?></h3>

  <div class="table-wrap">
    <table>
      <thead><tr><th>Description</th><th>Qté</th><th>P.U.</th><th>Total</th></tr></thead>
      <tbody>
      <?php foreach ($items as $item): ?>
        <tr>
          <td><?= e($item['description']) ?></td>
          <td><?= e((string) $item['quantity']) ?></td>
          <td><?= e(money($item['unit_price'])) ?></td>
          <td><?= e(money($item['line_total'])) ?></td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>

  <div class="doc-totals">
    <div><span>Sous-total</span><span><?= e(money($devis['subtotal'])) ?></span></div>
    <div><span>TVA (<?= e((string) $devis['tax_rate']) ?>%)</span><span><?= e(money($devis['tax_amount'])) ?></span></div>
    <div class="grand"><span>Total</span><span><?= e(money($devis['total'])) ?></span></div>
  </div>

  <?php if (!empty($devis['notes'])): ?>
    <p style="margin-top:2rem"><strong>Notes</strong><br><?= nl2br(e($devis['notes'])) ?></p>
  <?php endif; ?>
</article>
