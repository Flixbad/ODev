"use client";

import { Reveal } from "@/components/ui/Reveal";

const steps = [
  {
    n: "01",
    title: "Brief & cadrage",
    text: "On clarifie le besoin, le public, le budget et les priorités. Une feuille de route réaliste, sans surprise.",
  },
  {
    n: "02",
    title: "Conception",
    text: "Architecture, wireframes et direction visuelle. Vous validez avant que la moindre ligne critique ne parte en production.",
  },
  {
    n: "03",
    title: "Développement",
    text: "Code propre, itérations visibles, feedback continu. React, Node.js et intégrations selon le périmètre.",
  },
  {
    n: "04",
    title: "Mise en ligne",
    text: "Déploiement, checks, formation rapide et documentation. Puis suivi pour que ça reste solide.",
  },
];

export function Process() {
  return (
    <section id="methode" className="bg-paper py-24 md:py-32">
      <div className="mx-auto max-w-7xl px-5 md:px-8">
        <Reveal>
          <p className="text-xs font-semibold uppercase tracking-[0.32em] text-signal">
            Méthode
          </p>
          <h2 className="mt-4 max-w-2xl font-display text-4xl font-bold tracking-tight text-ink md:text-6xl">
            Quatre étapes. Zéro flou.
          </h2>
        </Reveal>

        <ol className="mt-16 grid gap-0 md:grid-cols-2 lg:grid-cols-4">
          {steps.map((step, i) => (
            <Reveal
              key={step.n}
              as="li"
              delay={i * 0.08}
              className="group relative border-t border-[var(--line-strong)] pt-8 md:border-l md:border-t-0 md:px-6 md:pt-0 lg:first:border-l-0 lg:first:pl-0"
            >
              <span className="font-display text-5xl font-extrabold text-mist-deep transition-colors group-hover:text-signal md:text-6xl">
                {step.n}
              </span>
              <h3 className="mt-6 font-display text-2xl font-bold text-ink">
                {step.title}
              </h3>
              <p className="mt-3 text-sm leading-relaxed text-ink-soft md:text-base">
                {step.text}
              </p>
            </Reveal>
          ))}
        </ol>
      </div>
    </section>
  );
}
