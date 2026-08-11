"use client";

import { FormEvent, useCallback, useEffect, useState } from "react";
import {
  api,
  clientName,
  money,
  statusLabel,
  type Client,
  type Facture,
  type LineItem,
  type Paiement,
} from "@/lib/api";

type ItemDraft = { description: string; quantity: string; unit_price: string };

const emptyItem = (): ItemDraft => ({ description: "", quantity: "1", unit_price: "0" });

function today() {
  return new Date().toISOString().slice(0, 10);
}

export default function FacturesPage() {
  const [list, setList] = useState<Facture[]>([]);
  const [clients, setClients] = useState<Client[]>([]);
  const [selectedId, setSelectedId] = useState<number | null>(null);
  const [items, setItems] = useState<LineItem[]>([]);
  const [paiements, setPaiements] = useState<Paiement[]>([]);
  const [showForm, setShowForm] = useState(false);
  const [editingId, setEditingId] = useState<number | null>(null);
  const [error, setError] = useState("");
  const [busy, setBusy] = useState(false);

  const [form, setForm] = useState({
    client_id: "",
    title: "",
    status: "brouillon",
    issue_date: today(),
    due_date: "",
    notes: "",
    tax_rate: "0",
  });
  const [lineItems, setLineItems] = useState<ItemDraft[]>([emptyItem()]);
  const [pay, setPay] = useState({
    amount: "",
    paid_at: today(),
    method: "virement",
    reference: "",
  });

  const loadList = useCallback(async () => {
    const data = await api.factures.list();
    setList(data.factures);
  }, []);

  useEffect(() => {
    void Promise.all([loadList(), api.clients.list().then((d) => setClients(d.clients))]).catch(
      (err) => setError(err instanceof Error ? err.message : "Erreur"),
    );
  }, [loadList]);

  async function selectFacture(id: number) {
    setSelectedId(id);
    setShowForm(false);
    try {
      const data = await api.factures.get(id);
      setItems(data.items);
      setPaiements(data.paiements);
      setError("");
    } catch (err) {
      setError(err instanceof Error ? err.message : "Erreur");
    }
  }

  function openCreate() {
    setEditingId(null);
    setSelectedId(null);
    setForm({
      client_id: clients[0] ? String(clients[0].id) : "",
      title: "",
      status: "brouillon",
      issue_date: today(),
      due_date: "",
      notes: "",
      tax_rate: "0",
    });
    setLineItems([emptyItem()]);
    setShowForm(true);
  }

  async function openEdit(f: Facture) {
    setEditingId(f.id);
    setSelectedId(f.id);
    const detail = await api.factures.get(f.id);
    setItems(detail.items);
    setPaiements(detail.paiements);
    setForm({
      client_id: String(f.client_id),
      title: f.title,
      status: f.status,
      issue_date: f.issue_date,
      due_date: f.due_date || "",
      notes: f.notes || "",
      tax_rate: String(f.tax_rate),
    });
    setLineItems(
      detail.items.length
        ? detail.items.map((it) => ({
            description: it.description,
            quantity: String(it.quantity),
            unit_price: String(it.unit_price),
          }))
        : [emptyItem()],
    );
    setShowForm(true);
  }

  function bodyFromForm() {
    return {
      client_id: Number(form.client_id),
      title: form.title,
      status: form.status,
      issue_date: form.issue_date,
      due_date: form.due_date || null,
      notes: form.notes || null,
      tax_rate: Number(form.tax_rate) || 0,
      items: lineItems
        .filter((it) => it.description.trim())
        .map((it) => ({
          description: it.description.trim(),
          quantity: Number(it.quantity) || 0,
          unit_price: Number(it.unit_price) || 0,
        })),
    };
  }

  async function onSubmit(e: FormEvent) {
    e.preventDefault();
    setBusy(true);
    setError("");
    try {
      const body = bodyFromForm();
      if (!body.client_id || !body.title || body.items.length === 0) {
        throw new Error("Client, titre et au moins une ligne sont requis.");
      }
      if (editingId) {
        await api.factures.update(editingId, body);
        await selectFacture(editingId);
      } else {
        const created = await api.factures.create(body);
        await loadList();
        await selectFacture(created.facture.id);
      }
      setShowForm(false);
      await loadList();
    } catch (err) {
      setError(err instanceof Error ? err.message : "Erreur");
    } finally {
      setBusy(false);
    }
  }

  async function onDelete(id: number) {
    if (!confirm("Supprimer cette facture ?")) return;
    setBusy(true);
    try {
      await api.factures.remove(id);
      if (selectedId === id) {
        setSelectedId(null);
        setItems([]);
        setPaiements([]);
      }
      setShowForm(false);
      await loadList();
    } catch (err) {
      setError(err instanceof Error ? err.message : "Erreur");
    } finally {
      setBusy(false);
    }
  }

  async function addPayment(e: FormEvent) {
    e.preventDefault();
    if (!selectedId) return;
    setBusy(true);
    setError("");
    try {
      await api.factures.addPayment(selectedId, {
        amount: Number(pay.amount),
        paid_at: pay.paid_at,
        method: pay.method,
        reference: pay.reference || null,
      });
      setPay({ amount: "", paid_at: today(), method: "virement", reference: "" });
      await selectFacture(selectedId);
      await loadList();
    } catch (err) {
      setError(err instanceof Error ? err.message : "Erreur");
    } finally {
      setBusy(false);
    }
  }

  async function deletePayment(paymentId: number) {
    if (!confirm("Supprimer ce paiement ?")) return;
    setBusy(true);
    try {
      await api.factures.deletePayment(paymentId);
      if (selectedId) await selectFacture(selectedId);
      await loadList();
    } catch (err) {
      setError(err instanceof Error ? err.message : "Erreur");
    } finally {
      setBusy(false);
    }
  }

  const selected = list.find((f) => f.id === selectedId) || null;
  const due = selected ? Number(selected.total) - Number(selected.amount_paid || 0) : 0;

  return (
    <div className="space-y-8">
      <header className="flex flex-wrap items-end justify-between gap-4">
        <div>
          <h1 className="font-display text-3xl font-bold tracking-tight">Factures</h1>
          <p className="mt-2 text-ink-soft">Facturation et suivi des paiements.</p>
        </div>
        <button type="button" onClick={openCreate} className="crm-btn-primary">
          Nouvelle facture
        </button>
      </header>

      {error ? <p className="text-sm text-signal">{error}</p> : null}

      {showForm ? (
        <form onSubmit={onSubmit} className="crm-card space-y-4 p-6">
          <h2 className="font-display text-xl font-bold">
            {editingId ? "Modifier la facture" : "Nouvelle facture"}
          </h2>
          <div className="grid gap-4 sm:grid-cols-2">
            <label className="block sm:col-span-2">
              <span className="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-ink-soft">
                Client *
              </span>
              <select
                className="crm-input"
                required
                value={form.client_id}
                onChange={(e) => setForm({ ...form, client_id: e.target.value })}
              >
                <option value="">Sélectionner…</option>
                {clients.map((c) => (
                  <option key={c.id} value={c.id}>
                    {clientName(c)}
                  </option>
                ))}
              </select>
            </label>
            <label className="block sm:col-span-2">
              <span className="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-ink-soft">
                Titre *
              </span>
              <input
                className="crm-input"
                required
                value={form.title}
                onChange={(e) => setForm({ ...form, title: e.target.value })}
              />
            </label>
            <label className="block">
              <span className="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-ink-soft">
                Statut
              </span>
              <select
                className="crm-input"
                value={form.status}
                onChange={(e) => setForm({ ...form, status: e.target.value })}
              >
                <option value="brouillon">Brouillon</option>
                <option value="envoyee">Envoyée</option>
                <option value="payee">Payée</option>
                <option value="en_retard">En retard</option>
                <option value="annulee">Annulée</option>
              </select>
            </label>
            <label className="block">
              <span className="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-ink-soft">
                Taxe %
              </span>
              <input
                className="crm-input"
                type="number"
                step="0.01"
                value={form.tax_rate}
                onChange={(e) => setForm({ ...form, tax_rate: e.target.value })}
              />
            </label>
            <label className="block">
              <span className="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-ink-soft">
                Date
              </span>
              <input
                className="crm-input"
                type="date"
                value={form.issue_date}
                onChange={(e) => setForm({ ...form, issue_date: e.target.value })}
              />
            </label>
            <label className="block">
              <span className="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-ink-soft">
                Échéance
              </span>
              <input
                className="crm-input"
                type="date"
                value={form.due_date}
                onChange={(e) => setForm({ ...form, due_date: e.target.value })}
              />
            </label>
          </div>

          <div className="space-y-3">
            <div className="flex items-center justify-between">
              <h3 className="text-sm font-semibold uppercase tracking-wider text-ink-soft">Lignes</h3>
              <button
                type="button"
                className="crm-link"
                onClick={() => setLineItems([...lineItems, emptyItem()])}
              >
                + Ligne
              </button>
            </div>
            {lineItems.map((it, idx) => (
              <div key={idx} className="grid gap-2 sm:grid-cols-[1fr_90px_110px_auto]">
                <input
                  className="crm-input"
                  placeholder="Description"
                  value={it.description}
                  onChange={(e) => {
                    const next = [...lineItems];
                    next[idx] = { ...it, description: e.target.value };
                    setLineItems(next);
                  }}
                />
                <input
                  className="crm-input"
                  type="number"
                  step="0.01"
                  value={it.quantity}
                  onChange={(e) => {
                    const next = [...lineItems];
                    next[idx] = { ...it, quantity: e.target.value };
                    setLineItems(next);
                  }}
                />
                <input
                  className="crm-input"
                  type="number"
                  step="0.01"
                  value={it.unit_price}
                  onChange={(e) => {
                    const next = [...lineItems];
                    next[idx] = { ...it, unit_price: e.target.value };
                    setLineItems(next);
                  }}
                />
                <button
                  type="button"
                  className="crm-link text-signal"
                  onClick={() => setLineItems(lineItems.filter((_, i) => i !== idx))}
                  disabled={lineItems.length === 1}
                >
                  Retirer
                </button>
              </div>
            ))}
          </div>

          <label className="block">
            <span className="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-ink-soft">
              Notes
            </span>
            <textarea
              className="crm-input min-h-[72px]"
              value={form.notes}
              onChange={(e) => setForm({ ...form, notes: e.target.value })}
            />
          </label>

          <div className="flex flex-wrap gap-3">
            <button type="submit" disabled={busy} className="crm-btn-primary">
              {busy ? "Enregistrement…" : "Enregistrer"}
            </button>
            <button type="button" className="crm-btn" onClick={() => setShowForm(false)}>
              Annuler
            </button>
          </div>
        </form>
      ) : null}

      <div className="grid gap-6 lg:grid-cols-2">
        <div className="crm-card overflow-x-auto">
          <table className="crm-table">
            <thead>
              <tr>
                <th>N°</th>
                <th>Client</th>
                <th>Total</th>
                <th>Statut</th>
              </tr>
            </thead>
            <tbody>
              {list.length === 0 ? (
                <tr>
                  <td colSpan={4} className="text-ink-soft">
                    Aucune facture
                  </td>
                </tr>
              ) : (
                list.map((f) => (
                  <tr
                    key={f.id}
                    className={`cursor-pointer ${selectedId === f.id ? "bg-mist/40" : ""}`}
                    onClick={() => void selectFacture(f.id)}
                  >
                    <td className="font-medium">{f.number}</td>
                    <td>{clientName(f)}</td>
                    <td className="tabular-nums">{money(f.total)}</td>
                    <td>{statusLabel(f.status)}</td>
                  </tr>
                ))
              )}
            </tbody>
          </table>
        </div>

        <div className="crm-card space-y-5 p-6">
          {!selected ? (
            <p className="text-ink-soft">Sélectionnez une facture pour le détail et les paiements.</p>
          ) : (
            <>
              <div className="flex flex-wrap items-start justify-between gap-3">
                <div>
                  <h2 className="font-display text-xl font-bold">{selected.number}</h2>
                  <p className="text-sm text-ink-soft">{selected.title}</p>
                  <p className="mt-1 text-sm">{clientName(selected)}</p>
                </div>
                <div className="text-right">
                  <p className="font-display text-2xl font-bold tabular-nums">{money(selected.total)}</p>
                  <p className="text-sm text-ink-soft">
                    Payé {money(selected.amount_paid || 0)} · Reste {money(due)}
                  </p>
                </div>
              </div>

              <ul className="divide-y divide-[var(--line)] text-sm">
                {items.map((it, i) => (
                  <li key={it.id ?? i} className="flex justify-between gap-3 py-2">
                    <span>
                      {it.description}{" "}
                      <span className="text-ink-soft">
                        ({it.quantity} × {money(it.unit_price)})
                      </span>
                    </span>
                    <span className="tabular-nums">{money(it.line_total ?? it.quantity * it.unit_price)}</span>
                  </li>
                ))}
              </ul>

              <div className="flex flex-wrap gap-3">
                <button type="button" className="crm-btn" onClick={() => void openEdit(selected)}>
                  Modifier
                </button>
                <button
                  type="button"
                  className="crm-link text-signal"
                  disabled={busy}
                  onClick={() => void onDelete(selected.id)}
                >
                  Supprimer
                </button>
              </div>

              <div className="border-t border-[var(--line)] pt-5">
                <h3 className="font-display text-lg font-bold">Paiements</h3>
                <ul className="mt-3 divide-y divide-[var(--line)] text-sm">
                  {paiements.length === 0 ? (
                    <li className="py-2 text-ink-soft">Aucun paiement</li>
                  ) : (
                    paiements.map((p) => (
                      <li key={p.id} className="flex items-center justify-between gap-3 py-2">
                        <span>
                          {p.paid_at} · {p.method}
                          {p.reference ? ` · ${p.reference}` : ""}
                        </span>
                        <span className="flex items-center gap-3">
                          <span className="tabular-nums font-medium">{money(p.amount)}</span>
                          <button
                            type="button"
                            className="crm-link text-signal"
                            onClick={() => void deletePayment(p.id)}
                          >
                            ×
                          </button>
                        </span>
                      </li>
                    ))
                  )}
                </ul>

                <form onSubmit={addPayment} className="mt-4 grid gap-3 sm:grid-cols-2">
                  <input
                    className="crm-input"
                    type="number"
                    step="0.01"
                    required
                    placeholder="Montant"
                    value={pay.amount}
                    onChange={(e) => setPay({ ...pay, amount: e.target.value })}
                  />
                  <input
                    className="crm-input"
                    type="date"
                    required
                    value={pay.paid_at}
                    onChange={(e) => setPay({ ...pay, paid_at: e.target.value })}
                  />
                  <select
                    className="crm-input"
                    value={pay.method}
                    onChange={(e) => setPay({ ...pay, method: e.target.value })}
                  >
                    <option value="virement">Virement</option>
                    <option value="especes">Espèces</option>
                    <option value="cheque">Chèque</option>
                    <option value="carte">Carte</option>
                    <option value="autre">Autre</option>
                  </select>
                  <input
                    className="crm-input"
                    placeholder="Référence"
                    value={pay.reference}
                    onChange={(e) => setPay({ ...pay, reference: e.target.value })}
                  />
                  <button type="submit" disabled={busy} className="crm-btn-primary sm:col-span-2">
                    Ajouter un paiement
                  </button>
                </form>
              </div>
            </>
          )}
        </div>
      </div>
    </div>
  );
}
