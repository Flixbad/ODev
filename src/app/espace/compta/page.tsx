"use client";

import { FormEvent, useCallback, useEffect, useState } from "react";
import { api, clientName, money, type Paiement } from "@/lib/api";

const MONTHS = ["Jan", "Fév", "Mar", "Avr", "Mai", "Jun", "Jul", "Aoû", "Sep", "Oct", "Nov", "Déc"];

function monthStart() {
  const d = new Date();
  return `${d.getFullYear()}-${String(d.getMonth() + 1).padStart(2, "0")}-01`;
}

function monthEnd() {
  const d = new Date();
  const last = new Date(d.getFullYear(), d.getMonth() + 1, 0);
  return last.toISOString().slice(0, 10);
}

type ComptaData = Awaited<ReturnType<typeof api.compta>>;

export default function ComptaPage() {
  const [from, setFrom] = useState(monthStart);
  const [to, setTo] = useState(monthEnd);
  const [year, setYear] = useState(new Date().getFullYear());
  const [data, setData] = useState<ComptaData | null>(null);
  const [error, setError] = useState("");
  const [loading, setLoading] = useState(true);

  const load = useCallback(async (f = from, t = to, y = year) => {
    setLoading(true);
    setError("");
    try {
      const res = await api.compta(f, t, y);
      setData(res);
    } catch (err) {
      setError(err instanceof Error ? err.message : "Erreur");
    } finally {
      setLoading(false);
    }
  }, [from, to, year]);

  useEffect(() => {
    void load();
  }, [load]);

  function onFilter(e: FormEvent) {
    e.preventDefault();
    void load(from, to, year);
  }

  const values = data
    ? MONTHS.map((_, i) => Number(data.monthly[String(i + 1)] ?? data.monthly[i + 1] ?? 0))
    : [];
  const max = Math.max(...values, 1);

  return (
    <div className="space-y-8">
      <header>
        <h1 className="font-display text-3xl font-bold tracking-tight">Compta</h1>
        <p className="mt-2 text-ink-soft">Encaissements, facturé et reste dû.</p>
      </header>

      <form onSubmit={onFilter} className="crm-card flex flex-wrap items-end gap-4 p-5">
        <label className="block">
          <span className="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-ink-soft">
            Du
          </span>
          <input
            className="crm-input"
            type="date"
            value={from}
            onChange={(e) => setFrom(e.target.value)}
          />
        </label>
        <label className="block">
          <span className="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-ink-soft">
            Au
          </span>
          <input className="crm-input" type="date" value={to} onChange={(e) => setTo(e.target.value)} />
        </label>
        <label className="block">
          <span className="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-ink-soft">
            Année (graphique)
          </span>
          <input
            className="crm-input w-28"
            type="number"
            value={year}
            onChange={(e) => setYear(Number(e.target.value) || new Date().getFullYear())}
          />
        </label>
        <button type="submit" className="crm-btn-primary">
          Filtrer
        </button>
      </form>

      {error ? <p className="text-sm text-signal">{error}</p> : null}
      {loading && !data ? <p className="text-ink-soft">Chargement…</p> : null}

      {data ? (
        <>
          <section className="grid gap-4 sm:grid-cols-3">
            <div className="crm-card p-5">
              <p className="text-[10px] font-semibold uppercase tracking-[0.18em] text-ink-soft">
                Encaissé
              </p>
              <p className="mt-3 font-display text-2xl font-bold tabular-nums">
                {money(data.stats.encaisse)}
              </p>
            </div>
            <div className="crm-card p-5">
              <p className="text-[10px] font-semibold uppercase tracking-[0.18em] text-ink-soft">
                Facturé
              </p>
              <p className="mt-3 font-display text-2xl font-bold tabular-nums">
                {money(data.stats.facture)}
              </p>
            </div>
            <div className="crm-card p-5">
              <p className="text-[10px] font-semibold uppercase tracking-[0.18em] text-ink-soft">
                Reste dû
              </p>
              <p className="mt-3 font-display text-2xl font-bold tabular-nums">
                {money(data.stats.due)}
              </p>
            </div>
          </section>

          <section className="crm-card p-6">
            <h2 className="font-display text-xl font-bold">Encaissements {data.year}</h2>
            <div className="mt-6 flex h-40 items-end gap-2">
              {values.map((v, i) => (
                <div key={MONTHS[i]} className="flex flex-1 flex-col items-center gap-2">
                  <div
                    className="w-full min-h-[4px] bg-teal transition-all"
                    style={{ height: `${Math.max((v / max) * 100, v > 0 ? 8 : 4)}%` }}
                    title={money(v)}
                  />
                  <span className="text-[10px] uppercase tracking-wider text-ink-soft">
                    {MONTHS[i]}
                  </span>
                </div>
              ))}
            </div>
          </section>

          <section className="crm-card overflow-x-auto">
            <div className="border-b border-[var(--line)] px-5 py-4">
              <h2 className="font-display text-xl font-bold">Paiements sur la période</h2>
            </div>
            <table className="crm-table">
              <thead>
                <tr>
                  <th>Date</th>
                  <th>Facture</th>
                  <th>Client</th>
                  <th>Méthode</th>
                  <th>Montant</th>
                </tr>
              </thead>
              <tbody>
                {data.paiements.length === 0 ? (
                  <tr>
                    <td colSpan={5} className="text-ink-soft">
                      Aucun paiement
                    </td>
                  </tr>
                ) : (
                  data.paiements.map((p: Paiement) => (
                    <tr key={p.id}>
                      <td>{p.paid_at}</td>
                      <td className="font-medium">{p.facture_number || `#${p.facture_id}`}</td>
                      <td>{clientName(p)}</td>
                      <td>{p.method}</td>
                      <td className="tabular-nums font-medium">{money(p.amount)}</td>
                    </tr>
                  ))
                )}
              </tbody>
            </table>
          </section>
        </>
      ) : null}
    </div>
  );
}
