"use client";

import Link from "next/link";
import { useEffect, useState } from "react";
import {
  api,
  clientName,
  money,
  statusLabel,
  type Client,
  type Facture,
} from "@/lib/api";

const MONTHS = ["Jan", "Fév", "Mar", "Avr", "Mai", "Jun", "Jul", "Aoû", "Sep", "Oct", "Nov", "Déc"];

type Dash = Awaited<ReturnType<typeof api.dashboard>>;

export default function EspaceDashboardPage() {
  const [data, setData] = useState<Dash | null>(null);
  const [error, setError] = useState("");

  useEffect(() => {
    void api
      .dashboard()
      .then(setData)
      .catch((err) => setError(err instanceof Error ? err.message : "Erreur"));
  }, []);

  if (error) {
    return <p className="text-signal">{error}</p>;
  }

  if (!data) {
    return <p className="text-ink-soft">Chargement du tableau de bord…</p>;
  }

  const { stats, monthly, recent_clients, recent_factures } = data;
  const values = MONTHS.map((_, i) => Number(monthly[String(i + 1)] ?? monthly[i + 1] ?? 0));
  const max = Math.max(...values, 1);

  const cards = [
    { label: "Clients", value: String(stats.clients), href: "/espace/clients/" },
    { label: "CA du mois", value: money(stats.ca_month), href: "/espace/compta/" },
    { label: "Impayés", value: money(stats.unpaid), href: "/espace/factures/" },
    { label: "Devis ouverts", value: String(stats.devis_open), href: "/espace/devis/" },
    { label: "Factures en retard", value: String(stats.factures_late), href: "/espace/factures/" },
  ];

  return (
    <div className="space-y-10">
      <header>
        <h1 className="font-display text-3xl font-bold tracking-tight md:text-4xl">
          Tableau de bord
        </h1>
        <p className="mt-2 text-ink-soft">Vue d&apos;ensemble de votre activité ODev.</p>
      </header>

      <section className="grid gap-4 sm:grid-cols-2 lg:grid-cols-5">
        {cards.map((card) => (
          <Link key={card.label} href={card.href} className="crm-card block p-5 transition-colors hover:border-ink">
            <p className="text-[10px] font-semibold uppercase tracking-[0.18em] text-ink-soft">
              {card.label}
            </p>
            <p className="mt-3 font-display text-2xl font-bold tracking-tight">{card.value}</p>
          </Link>
        ))}
      </section>

      <section className="crm-card p-6">
        <h2 className="font-display text-xl font-bold">Encaissements {new Date().getFullYear()}</h2>
        <div className="mt-6 flex h-40 items-end gap-2">
          {values.map((v, i) => (
            <div key={MONTHS[i]} className="flex flex-1 flex-col items-center gap-2">
              <div
                className="w-full min-h-[4px] bg-ink transition-all"
                style={{ height: `${Math.max((v / max) * 100, v > 0 ? 8 : 4)}%` }}
                title={money(v)}
              />
              <span className="text-[10px] uppercase tracking-wider text-ink-soft">{MONTHS[i]}</span>
            </div>
          ))}
        </div>
      </section>

      <div className="grid gap-6 lg:grid-cols-2">
        <section className="crm-card p-6">
          <div className="flex items-center justify-between gap-3">
            <h2 className="font-display text-xl font-bold">Clients récents</h2>
            <Link href="/espace/clients/" className="text-sm text-teal hover:underline">
              Voir tout
            </Link>
          </div>
          <ul className="mt-4 divide-y divide-[var(--line)]">
            {recent_clients.length === 0 ? (
              <li className="py-3 text-sm text-ink-soft">Aucun client</li>
            ) : (
              recent_clients.map((c: Client) => (
                <li key={c.id} className="flex items-center justify-between gap-3 py-3 text-sm">
                  <span className="font-medium">{clientName(c)}</span>
                  <span className="text-ink-soft">{statusLabel(c.status)}</span>
                </li>
              ))
            )}
          </ul>
        </section>

        <section className="crm-card p-6">
          <div className="flex items-center justify-between gap-3">
            <h2 className="font-display text-xl font-bold">Factures récentes</h2>
            <Link href="/espace/factures/" className="text-sm text-teal hover:underline">
              Voir tout
            </Link>
          </div>
          <ul className="mt-4 divide-y divide-[var(--line)]">
            {recent_factures.length === 0 ? (
              <li className="py-3 text-sm text-ink-soft">Aucune facture</li>
            ) : (
              recent_factures.map((f: Facture) => (
                <li key={f.id} className="flex items-center justify-between gap-3 py-3 text-sm">
                  <span>
                    <span className="font-medium">{f.number}</span>
                    <span className="ml-2 text-ink-soft">{clientName(f)}</span>
                  </span>
                  <span className="tabular-nums">{money(f.total)}</span>
                </li>
              ))
            )}
          </ul>
        </section>
      </div>
    </div>
  );
}
