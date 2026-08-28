"use client";

import { Reveal } from "@/components/ui/Reveal";

const plans = [
  {
    id: "01",
    title: "Site vitrine",
    tag: "Présence",
    range: "5 000$ — 8 500$",
    blurb:
      "Landing ou multi-pages pour présenter votre activité, vos services et convertir les visiteurs.",
  },
  {
    id: "02",
    title: "Site catalogue",
    tag: "Catalogue",
    range: "7 500$ — 12 000$",
    blurb:
      "Mise en avant de biens ou produits : fiches, filtres, navigation claire et expérience soignée.",
  },
  {
    id: "03",
    title: "Site SaaS",
    tag: "Produit",
    range: "10 000$ — 15 000$",
    blurb:
      "Application web métier : comptes, tableaux de bord, logique métier et parcours utilisateurs.",
  },
  {
    id: "04",
    title: "Hébergement",
    tag: "Infra",
    range: "5 000$ — 7 500$",
    blurb:
      "Mise en ligne, domaine, SSL et infrastructure stable pour que votre projet reste accessible.",
  },
  {
    id: "05",
    title: "Maintenance",
    tag: "Suivi",
    range: "5 000$ — 9 000$",
    blurb:
      "Corrections, mises à jour, petites évolutions et suivi pour garder le site performant dans le temps.",
  },
];

export function Pricing() {
  return (
    <section id="tarifs" className="relative overflow-hidden bg-ink py-24 text-paper md:py-32">
      <div className="pointer-events-none absolute -left-24 top-20 h-64 w-64 rounded-full bg-signal/15 blur-3xl" />
      <div className="pointer-events-none absolute -right-16 bottom-10 h-56 w-56 rounded-full bg-teal/15 blur-3xl" />

      <div className="relative mx-auto max-w-7xl px-5 md:px-8">
        <Reveal>
          <p className="text-xs font-semibold uppercase tracking-[0.32em] text-signal-hot">
            Tarifs
          </p>
          <h2 className="mt-4 max-w-3xl font-display text-4xl font-bold tracking-tight md:text-6xl">
            Une grille claire. Un plafond net.
          </h2>
          <p className="mt-5 max-w-2xl text-base leading-relaxed text-paper/65 md:text-lg">
            Prix indicatifs selon le périmètre. Chaque devis est calé sur votre
            projet — et{" "}
            <span className="text-paper">plafonné à 15 000$</span> maximum.
          </p>
        </Reveal>

        <div className="mt-14 border-t border-white/10">
          {plans.map((plan, i) => (
            <Reveal key={plan.id} delay={0.06 * i}>
              <article className="group grid gap-4 border-b border-white/10 py-7 transition-colors hover:bg-white/[0.03] md:grid-cols-[4.5rem_1fr_auto] md:items-start md:gap-8 md:py-8">
                <span className="font-mono text-sm text-paper/40">{plan.id}</span>

                <div className="min-w-0">
                  <div className="flex flex-wrap items-baseline gap-3">
                    <h3 className="font-display text-2xl font-bold tracking-tight md:text-3xl">
                      {plan.title}
                    </h3>
                    <span className="text-[10px] font-semibold uppercase tracking-[0.22em] text-teal-glow">
                      {plan.tag}
                    </span>
                  </div>
                  <p className="mt-3 max-w-xl text-sm leading-relaxed text-paper/60 md:text-base">
                    {plan.blurb}
                  </p>
                </div>

                <p className="font-display text-2xl font-extrabold tracking-tight text-signal-hot md:pt-1 md:text-right md:text-3xl">
                  {plan.range}
                </p>
              </article>
            </Reveal>
          ))}
        </div>

        <Reveal delay={0.35}>
          <p className="mt-10 max-w-3xl border-l-2 border-signal pl-5 text-sm leading-relaxed text-paper/55 md:text-base">
            Ces montants sont indicatifs. Le devis final dépend de la complexité,
            du volume de pages, des fonctionnalités et des délais — sans jamais
            dépasser <span className="font-semibold text-paper">15 000$</span>.
          </p>
        </Reveal>
      </div>
    </section>
  );
}
