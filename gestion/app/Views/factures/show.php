<?php $cfg = app_config(); $reste = max(0, (float)$facture['total'] - (float)$facture['amount_paid']); ?>
<?php if (empty($print)): ?>
<div class="topbar no-print">
  <div>
    <p class="eyebrow">Facture</p>
    <h1><?= e($facture['number']) ?></h1>
  </div>
  <div class="actions">
    <a class="btn" href="<?= e(url('factures/edit', ['id' => $facture['id']])) ?>">Modifier</a>
    <a class="btn" href="<?= e(url('factures/show', ['id' => $facture['id'], 'print' => 1])) ?>" target="_blank">Imprimer / PDF</a>
    <form method="post" action="<?= e(url('factures/delete')) ?>" onsubmit="return confirm('Supprimer cette facture ?')">
      <?= csrf_field() ?>
      <input type="hidden" name="id" value="<?= (int) $facture['id'] ?>">
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
      <div class="eyebrow">Facture</div>
      <h2 style="margin:0"><?= e($facture['number']) ?></h2>
      <p class="muted">Émise le <?= e($facture['issue_date']) ?> · Échéance <?= e($facture['due_date'] ?? '—') ?></p>
      <span class="badge <?= $facture['status'] === 'payee' ? 'badge-ok' : ($facture['status'] === 'en_retard' ? 'badge-danger' : '') ?>"><?= e(status_label($facture['status'])) ?></span>
    </div>
  </div>

  <p><strong>Client</strong><br><?= e(client_display_name($facture)) ?><br>
    <?= e($facture['email'] ?? '') ?>
  </p>
  <h3><?= e($facture['title']) ?></h3>

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
    <div><span>Sous-total</span><span><?= e(money($facture['subtotal'])) ?></span></div>
    <div><span>TVA (<?= e((string) $facture['tax_rate']) ?>%)</span><span><?= e(money($facture['tax_amount'])) ?></span></div>
    <div><span>Payé</span><span><?= e(money($facture['amount_paid'])) ?></span></div>
    <div class="grand"><span>Total</span><span><?= e(money($facture['total'])) ?></span></div>
    <div><span>Reste dû</span><span><?= e(money($reste)) ?></span></div>
  </div>
</article>

<?php if (empty($print)): ?>
<div class="form-grid" style="margin-top:1rem">
  <div class="panel">
    <h2>Enregistrer un paiement</h2>
    <form method="post" action="<?= e(url('factures/payment')) ?>" class="stack">
      <?= csrf_field() ?>
      <input type="hidden" name="facture_id" value="<?= (int) $facture['id'] ?>">
      <label>Montant <input type="number" step="0.01" name="amount" required value="<?= e((string) $reste) ?>"></label>
      <label>Date <input type="date" name="paid_at" value="<?= e(date('Y-m-d')) ?>" required></label>
      <label>Méthode
        <select name="method">
          <?php foreach (['virement','especes','cheque','carte','autre'] as $m): ?>
            <option value="<?= $m ?>"><?= e(ucfirst($m)) ?></option>
          <?php endforeach; ?>
        </select>
      </label>
      <label>Référence <input name="reference"></label>
      <button class="btn btn-primary" type="submit">Ajouter le paiement</button>
    </form>
  </div>
  <div class="panel table-wrap">
    <h2>Historique paiements</h2>
    <table>
      <thead><tr><th>Date</th><th>Montant</th><th>Méthode</th><th></th></tr></thead>
      <tbody>
      <?php foreach ($paiements as $p): ?>
        <tr>
          <td><?= e($p['paid_at']) ?></td>
          <td><?= e(money($p['amount'])) ?></td>
          <td><?= e($p['method']) ?></td>
          <td>
            <form method="post" action="<?= e(url('factures/payment-delete')) ?>" onsubmit="return confirm('Supprimer ?')">
              <?= csrf_field() ?>
              <input type="hidden" name="id" value="<?= (int) $p['id'] ?>">
              <input type="hidden" name="facture_id" value="<?= (int) $facture['id'] ?>">
              <button class="btn btn-sm btn-danger" type="submit">✕</button>
            </form>
          </td>
        </tr>
      <?php endforeach; ?>
      <?php if (!$paiements): ?><tr><td colspan="4" class="muted">Aucun paiement</td></tr><?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
<?php endif; ?>
