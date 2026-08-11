"use client";

import { FormEvent, useCallback, useEffect, useState } from "react";
import { api, clientName, statusLabel, type Client } from "@/lib/api";

const emptyForm = {
  company: "",
  first_name: "",
  last_name: "",
  email: "",
  phone: "",
  address: "",
  city: "",
  postal_code: "",
  notes: "",
  status: "actif",
};

export default function ClientsPage() {
  const [clients, setClients] = useState<Client[]>([]);
  const [q, setQ] = useState("");
  const [form, setForm] = useState(emptyForm);
  const [editingId, setEditingId] = useState<number | null>(null);
  const [showForm, setShowForm] = useState(false);
  const [error, setError] = useState("");
  const [busy, setBusy] = useState(false);

  const load = useCallback(async (search = "") => {
    try {
      const data = await api.clients.list(search);
      setClients(data.clients);
      setError("");
    } catch (err) {
      setError(err instanceof Error ? err.message : "Erreur");
    }
  }, []);

  useEffect(() => {
    void load();
  }, [load]);

  function openCreate() {
    setEditingId(null);
    setForm(emptyForm);
    setShowForm(true);
  }

  function openEdit(c: Client) {
    setEditingId(c.id);
    setForm({
      company: c.company || "",
      first_name: c.first_name,
      last_name: c.last_name,
      email: c.email || "",
      phone: c.phone || "",
      address: c.address || "",
      city: c.city || "",
      postal_code: c.postal_code || "",
      notes: c.notes || "",
      status: c.status || "actif",
    });
    setShowForm(true);
  }

  async function onSubmit(e: FormEvent) {
    e.preventDefault();
    setBusy(true);
    setError("");
    try {
      if (editingId) {
        await api.clients.update(editingId, form);
      } else {
        await api.clients.create(form);
      }
      setShowForm(false);
      setForm(emptyForm);
      setEditingId(null);
      await load();
    } catch (err) {
      setError(err instanceof Error ? err.message : "Erreur");
    } finally {
      setBusy(false);
    }
  }

  async function onDelete(id: number) {
    if (!confirm("Supprimer ce client ?")) return;
    setBusy(true);
    try {
      await api.clients.remove(id);
      if (editingId === id) {
        setShowForm(false);
        setEditingId(null);
      }
      await load();
    } catch (err) {
      setError(err instanceof Error ? err.message : "Erreur");
    } finally {
      setBusy(false);
    }
  }

  return (
    <div className="space-y-8">
      <header className="flex flex-wrap items-end justify-between gap-4">
        <div>
          <h1 className="font-display text-3xl font-bold tracking-tight">Clients</h1>
          <p className="mt-2 text-ink-soft">Carnet d&apos;adresses et fiches clients.</p>
        </div>
        <button type="button" onClick={openCreate} className="crm-btn-primary">
          Nouveau client
        </button>
      </header>

      <div className="flex flex-wrap gap-3">
        <input
          className="crm-input max-w-sm"
          placeholder="Rechercher…"
          value={q}
          onChange={(e) => setQ(e.target.value)}
          onKeyDown={(e) => {
            if (e.key === "Enter") void load(q);
          }}
        />
        <button type="button" className="crm-btn" onClick={() => void load(q)}>
          Chercher
        </button>
      </div>

      {error ? <p className="text-sm text-signal">{error}</p> : null}

      {showForm ? (
        <form onSubmit={onSubmit} className="crm-card space-y-4 p-6">
          <h2 className="font-display text-xl font-bold">
            {editingId ? "Modifier le client" : "Nouveau client"}
          </h2>
          <div className="grid gap-4 sm:grid-cols-2">
            <Field label="Société" value={form.company} onChange={(v) => setForm({ ...form, company: v })} />
            <Field
              label="Statut"
              as="select"
              value={form.status}
              onChange={(v) => setForm({ ...form, status: v })}
              options={[
                { value: "actif", label: "Actif" },
                { value: "prospect", label: "Prospect" },
                { value: "archive", label: "Archivé" },
              ]}
            />
            <Field
              label="Prénom *"
              required
              value={form.first_name}
              onChange={(v) => setForm({ ...form, first_name: v })}
            />
            <Field
              label="Nom *"
              required
              value={form.last_name}
              onChange={(v) => setForm({ ...form, last_name: v })}
            />
            <Field label="E-mail" type="email" value={form.email} onChange={(v) => setForm({ ...form, email: v })} />
            <Field label="Téléphone" value={form.phone} onChange={(v) => setForm({ ...form, phone: v })} />
            <Field label="Adresse" value={form.address} onChange={(v) => setForm({ ...form, address: v })} />
            <Field label="Ville" value={form.city} onChange={(v) => setForm({ ...form, city: v })} />
            <Field
              label="Code postal"
              value={form.postal_code}
              onChange={(v) => setForm({ ...form, postal_code: v })}
            />
          </div>
          <Field
            label="Notes"
            as="textarea"
            value={form.notes}
            onChange={(v) => setForm({ ...form, notes: v })}
          />
          <div className="flex flex-wrap gap-3">
            <button type="submit" disabled={busy} className="crm-btn-primary">
              {busy ? "Enregistrement…" : "Enregistrer"}
            </button>
            <button
              type="button"
              className="crm-btn"
              onClick={() => {
                setShowForm(false);
                setEditingId(null);
              }}
            >
              Annuler
            </button>
          </div>
        </form>
      ) : null}

      <div className="crm-card overflow-x-auto">
        <table className="crm-table">
          <thead>
            <tr>
              <th>Client</th>
              <th>Contact</th>
              <th>Statut</th>
              <th />
            </tr>
          </thead>
          <tbody>
            {clients.length === 0 ? (
              <tr>
                <td colSpan={4} className="text-ink-soft">
                  Aucun client
                </td>
              </tr>
            ) : (
              clients.map((c) => (
                <tr key={c.id}>
                  <td>
                    <div className="font-medium">{clientName(c)}</div>
                    {c.city ? <div className="text-xs text-ink-soft">{c.city}</div> : null}
                  </td>
                  <td className="text-sm">
                    {c.email || "—"}
                    {c.phone ? <div className="text-ink-soft">{c.phone}</div> : null}
                  </td>
                  <td>{statusLabel(c.status)}</td>
                  <td className="text-right">
                    <button type="button" className="crm-link" onClick={() => openEdit(c)}>
                      Modifier
                    </button>
                    <button
                      type="button"
                      className="crm-link ml-3 text-signal"
                      onClick={() => void onDelete(c.id)}
                      disabled={busy}
                    >
                      Supprimer
                    </button>
                  </td>
                </tr>
              ))
            )}
          </tbody>
        </table>
      </div>
    </div>
  );
}

function Field({
  label,
  value,
  onChange,
  required,
  type = "text",
  as = "input",
  options,
}: {
  label: string;
  value: string;
  onChange: (v: string) => void;
  required?: boolean;
  type?: string;
  as?: "input" | "textarea" | "select";
  options?: { value: string; label: string }[];
}) {
  return (
    <label className="block">
      <span className="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-ink-soft">
        {label}
      </span>
      {as === "textarea" ? (
        <textarea className="crm-input min-h-[88px]" value={value} onChange={(e) => onChange(e.target.value)} />
      ) : as === "select" ? (
        <select className="crm-input" value={value} onChange={(e) => onChange(e.target.value)} required={required}>
          {options?.map((o) => (
            <option key={o.value} value={o.value}>
              {o.label}
            </option>
          ))}
        </select>
      ) : (
        <input
          className="crm-input"
          type={type}
          value={value}
          required={required}
          onChange={(e) => onChange(e.target.value)}
        />
      )}
    </label>
  );
}
