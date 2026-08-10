"use client";

import { Reveal } from "@/components/ui/Reveal";

export function About() {
  return (
    <section id="apropos" className="relative overflow-hidden bg-mist py-24 md:py-32">
      <div className="pointer-events-none absolute -left-20 top-10 font-display text-[22vw] font-extrabold leading-none text-ink/[0.04]">
        ODev
      </div>

      <div className="relative mx-auto grid max-w-7xl gap-12 px-5 md:grid-cols-12 md:gap-8 md:px-8">
        <Reveal className="md:col-span-5">
          <p className="text-xs font-semibold uppercase tracking-[0.32em] text-teal">
            À propos
          </p>
          <h2 className="mt-4 font-display text-4xl font-bold tracking-tight text-ink md:text-5xl">
            Darren O&apos;Sullivan
          </h2>
          <p className="mt-2 text-sm uppercase tracking-[0.2em] text-ink-soft">
            Fondateur & Développeur
          </p>
        </Reveal>

        <Reveal delay={0.12} className="md:col-span-7">
          <p className="text-xl leading-relaxed text-ink md:text-2xl md:leading-relaxed">
            ODev est une micro-entreprise dédiée à la création d&apos;outils
            numériques utiles — pas de blabla, des livrables qui tournent.
          </p>
          <div className="mt-8 space-y-5 text-base leading-relaxed text-ink-soft md:text-lg">
            <p>
              Je conçois et développe des sites web, des applications React et
              des back-ends Node.js pour des entreprises qui veulent une présence
              nette et des process digitalisés.
            </p>
            <p>
              Chaque projet démarre par une écoute réelle de votre activité :
              objectifs, contraintes, utilisateurs. Ensuite, on construit —
              itération après itération — jusqu&apos;à une solution stable et
              élégante.
            </p>
          </div>

          <dl className="mt-12 grid grid-cols-2 gap-6 border-t border-[var(--line-strong)] pt-8 sm:grid-cols-3">
            {[
              { label: "Focus", value: "Sur mesure" },
              { label: "Stack", value: "React · Node" },
              { label: "Approche", value: "Directe" },
            ].map((item) => (
              <div key={item.label}>
                <dt className="text-[10px] uppercase tracking-[0.22em] text-ink-soft">
                  {item.label}
                </dt>
                <dd className="mt-2 font-display text-xl font-bold text-ink">
                  {item.value}
                </dd>
              </div>
            ))}
          </dl>
        </Reveal>
      </div>
    </section>
  );
}
