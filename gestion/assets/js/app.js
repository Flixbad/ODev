document.addEventListener('click', (e) => {
  const add = e.target.closest('#add-row');
  if (add) {
    const tbody = document.querySelector('#items-table tbody');
    if (!tbody) return;
    const tr = document.createElement('tr');
    tr.innerHTML = `
      <td><input name="item_description[]" required></td>
      <td><input name="item_quantity[]" type="number" step="0.01" value="1"></td>
      <td><input name="item_unit_price[]" type="number" step="0.01" value="0"></td>
      <td><button type="button" class="btn btn-sm btn-danger" data-remove-row>✕</button></td>
    `;
    tbody.appendChild(tr);
  }

  const remove = e.target.closest('[data-remove-row]');
  if (remove) {
    const tbody = remove.closest('tbody');
    if (tbody && tbody.querySelectorAll('tr').length > 1) {
      remove.closest('tr').remove();
    }
  }
});
