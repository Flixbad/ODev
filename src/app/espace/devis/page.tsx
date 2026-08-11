"use client";

import { FormEvent, useCallback, useEffect, useState } from "react";
import {
  api,
  clientName,
  money,
  statusLabel,
  type Client,
  type Devis,
  type LineItem,
} from "@/lib/api";

type ItemDraft = { description: string; quantity: string; unit_price: string };

const emptyItem = (): ItemDraft => ({ description: "", quantity: "1", unit_price: "0" });

function today() {
  return new Date().toISOString().slice(0, 10);
}

export default function DevisPage() {
  const [list, setList] = useState<Devis[]>([]);
  const [clients, setClients] = useState<Client[]>([]);
  const [selectedId, setSelectedId] = useState<number | null>(null);
  const [items, setItems] = useState<LineItem[]>([]);
  const [showForm, setShowForm] = useState(false);
  const [editingId, setEditingId] = useState<number | null>(null);
  const [error, setError] = useState("");
  const [busy, setBusy] = useState(false);

  const [form, setForm] = useState({
    client_id: "",
    title: "",
    status: "brouillon",
    issue_date: today(),
    valid_until: "",
    notes: "",
    tax_rate: "0",
  });
  const [lineItems, setLineItems] = useState<ItemDraft[]>([emptyItem()]);

  const loadList = useCallback(async () => {
    const data = await api.devis.list();
    setList(data.devis);
  }, []);

  useEffect(() => {
    void Promise.all([loadList(), api.clients.list().then((d) => setClients(d.clients))]).catch(
      (err) => setError(err instanceof Error ? err.message : "Erreur"),
    );
  }, [loadList]);

  async function selectDevis(id: number) {
    setSelectedId(id);
    setShowForm(false);
    try {
      const data = await api.devis.get(id);
      setItems(data.items);
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
      valid_until: "",
      notes: "",
      tax_rate: "0",
    });
    setLineItems([emptyItem()]);
    setShowForm(true);
  }

  async function openEdit(d: Devis) {
    setEditingId(d.id);
    setSelectedId(d.id);
    const detail = await api.devis.get(d.id);
    setItems(detail.items);
    setForm({
      client_id: String(d.client_id),
      title: d.title,
      status: d.status,
      issue_date: d.issue_date,
      valid_until: d.valid_until || "",
      notes: d.notes || "",
      tax_rate: String(d.tax_rate),
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
      valid_until: form.valid_until || null,
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
        await api.devis.update(editingId, body);
        await selectDevis(editingId);
      } else {
        const created = await api.devis.create(body);
        await loadList();
        await selectDevis(created.devis.id);
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
    if (!confirm("Supprimer ce devis ?")) return;
    setBusy(true);
    try {
      await api.devis.remove(id);
      if (selectedId === id) {
        setSelectedId(null);
        setItems([]);
      }
      setShowForm(false);
      await loadList();
    } catch (err) {
      setError(err instanceof Error ? err.message : "Erreur");
    } finally {
      setBusy(false);
    }
  }

  async function toFacture(id: number) {
    setBusy(true);
    try {
      await api.devis.toFacture(id);
      alert("Facture créée à partir du devis.");
      await loadList();
    } catch (err) {
      setError(err instanceof Error ? err.message : "Erreur");
    } finally {
      setBusy(false);
    }
  }

  const selected = list.find((d) => d.id === selectedId) || null;

  return (
    <div className="space-y-8">
      <header className="flex flex-wrap items-end justify-between gap-4">
        <div>
          <h1 className="font-display text-3xl font-bold tracking-tight">Devis</h1>
          <p className="mt-2 text-ink-soft">Propositions commerciales et conversion en facture.</p>
        </div>
        <button type="button" onClick={openCreate} className="crm-btn-primary">
          Nouveau devis
        </button>
      </header>

      {error ? <p className="text-sm text-signal">{error}</p> : null}

      {showForm ? (
        <form onSubmit={onSubmit} className="crm-card space-y-4 p-6">
          <h2 className="font-display text-xl font-bold">
            {editingId ? "Modifier le devis" : "Nouveau devis"}
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
                <option value="envoye">Envoyé</option>
                <option value="accepte">Accepté</option>
                <option value="refuse">Refusé</option>
                <option value="expire">Expiré</option>
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
                Valide jusqu&apos;au
              </span>
              <input
                className="crm-input"
                type="date"
                value={form.valid_until}
                onChange={(e) => setForm({ ...form, valid_until: e.target.value })}
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
                  placeholder="Qté"
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
                  placeholder="P.U."
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
                    Aucun devis
                  </td>
                </tr>
              ) : (
                list.map((d) => (
                  <tr
                    key={d.id}
                    className={`cursor-pointer ${selectedId === d.id ? "bg-mist/40" : ""}`}
                    onClick={() => void selectDevis(d.id)}
                  >
                    <td className="font-medium">{d.number}</td>
                    <td>{clientName(d)}</td>
                    <td className="tabular-nums">{money(d.total)}</td>
                    <td>{statusLabel(d.status)}</td>
                  </tr>
                ))
              )}
            </tbody>
          </table>
        </div>

        <div className="crm-card p-6">
          {!selected ? (
            <p className="text-ink-soft">Sélectionnez un devis pour voir le détail.</p>
          ) : (
            <div className="space-y-4">
              <div className="flex flex-wrap items-start justify-between gap-3">
                <div>
                  <h2 className="font-display text-xl font-bold">{selected.number}</h2>
                  <p className="text-sm text-ink-soft">{selected.title}</p>
                  <p className="mt-1 text-sm">{clientName(selected)}</p>
                </div>
                <p className="font-display text-2xl font-bold tabular-nums">{money(selected.total)}</p>
              </div>
              <p className="text-sm text-ink-soft">
                {statusLabel(selected.status)} · {selected.issue_date}
                {selected.valid_until ? ` → ${selected.valid_until}` : ""}
              </p>
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
              <div className="flex flex-wrap gap-3 pt-2">
                <button type="button" className="crm-btn" onClick={() => void openEdit(selected)}>
                  Modifier
                </button>
                <button
                  type="button"
                  className="crm-btn-primary"
                  disabled={busy}
                  onClick={() => void toFacture(selected.id)}
                >
                  Convertir en facture
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
            </div>
          )}
        </div>
      </div>
    </div>
  );
}
