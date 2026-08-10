"use client";

import { useState } from "react";
import { motion, AnimatePresence } from "framer-motion";
import {
  Layout,
  Server,
  Store,
  Building2,
  Database,
  Palette,
} from "lucide-react";
import { Reveal } from "@/components/ui/Reveal";
import { cn } from "@/lib/cn";

const services = [
  {
    id: "react",
    icon: Layout,
    title: "Applications React",
    tag: "Front-end",
    summary:
      "Interfaces fluides, rapides et maintenables. Composants réutilisables, état maîtrisé, expérience utilisateur premium.",
    details: [
      "SPA & apps métier React / Next.js",
      "Design system & composants",
      "Performance & accessibilité",
    ],
  },
  {
    id: "node",
    icon: Server,
    title: "Back-end Node.js",
    tag: "API & Serveur",
    summary:
      "APIs robustes, authentification, logique métier et intégrations. Une base solide pour faire grandir votre activité.",
    details: [
      "API REST & services temps réel",
      "Auth, rôles & sécurité",
      "Intégrations tierces",
    ],
  },
  {
    id: "vitrine",
    icon: Store,
    title: "Sites vitrine",
    tag: "Présence",
    summary:
      "Une vitrine qui marque. Storytelling, animations soignées et conversion — votre entreprise en première ligne.",
    details: [
      "Landing & multi-pages",
      "SEO technique de base",
      "Mise en ligne & hébergement",
    ],
  },
  {
    id: "gestion",
    icon: Building2,
    title: "Gestion d'entreprise",
    tag: "Métier",
    summary:
      "Outils sur mesure pour piloter clients, stocks, factures ou équipes — fini les tableurs qui explosent.",
    details: [
      "CRM & tableaux de bord",
      "Automatisations internes",
      "Workflows métier",
    ],
  },
  {
    id: "data",
    icon: Database,
    title: "Bases de données",
    tag: "Data",
    summary:
      "Modélisation claire, requêtes efficaces et données fiables pour que vos outils restent stables dans le temps.",
    details: [
      "Schémas & migrations",
      "PostgreSQL / MongoDB",
      "Backups & bonnes pratiques",
    ],
  },
  {
    id: "design",
    icon: Palette,
    title: "UX / UI & branding web",
    tag: "Design",
    summary:
      "Identité visuelle web cohérente : typographie, rythme, micro-interactions. Le détail qui fait la différence.",
    details: [
      "Maquettes haute fidélité",
      "Direction artistique web",
      "Prototypes interactifs",
    ],
  },
];

export function Services() {
  const [active, setActive] = useState(services[0].id);
  const current = services.find((s) => s.id === active) ?? services[0];

  return (
    <section id="services" className="relative bg-paper py-24 md:py-32">
      <div className="mx-auto max-w-7xl px-5 md:px-8">
        <Reveal>
          <p className="text-xs font-semibold uppercase tracking-[0.32em] text-signal">
            Services
          </p>
          <h2 className="mt-4 max-w-3xl font-display text-4xl font-bold leading-tight tracking-tight text-ink md:text-6xl">
            Ce que ODev construit pour vous
          </h2>
          <p className="mt-5 max-w-2xl text-lg text-ink-soft">
            Une offre complète pour lancer, moderniser ou industrialiser votre
            présence numérique — du site élégant à l&apos;outil métier critique.
          </p>
        </Reveal>

        <div className="mt-14 grid gap-6 lg:grid-cols-[1.1fr_0.9fr]">
          <ul className="flex flex-col border-t border-[var(--line-strong)]">
            {services.map((service, i) => {
              const Icon = service.icon;
              const isActive = active === service.id;
              return (
                <li key={service.id}>
                  <button
                    type="button"
                    onMouseEnter={() => setActive(service.id)}
                    onFocus={() => setActive(service.id)}
                    onClick={() => setActive(service.id)}
                    className={cn(
                      "group flex w-full items-center gap-4 border-b border-[var(--line)] py-5 text-left transition-colors md:gap-6 md:py-6",
                      isActive ? "bg-mist/40" : "hover:bg-mist/25",
                    )}
                  >
                    <span className="w-10 shrink-0 font-mono text-sm text-ink-soft md:w-12">
                      {String(i + 1).padStart(2, "0")}
                    </span>
                    <Icon
                      className={cn(
                        "h-5 w-5 shrink-0 transition-colors",
                        isActive ? "text-signal" : "text-ink-soft",
                      )}
                    />
                    <div className="min-w-0 flex-1">
                      <div className="flex flex-wrap items-baseline gap-3">
                        <span className="font-display text-xl font-bold text-ink md:text-2xl">
                          {service.title}
                        </span>
                        <span className="text-[10px] uppercase tracking-[0.2em] text-ink-soft">
                          {service.tag}
                        </span>
                      </div>
                    </div>
                    <span
                      className={cn(
                        "mr-2 hidden h-2 w-2 shrink-0 rotate-45 transition-colors sm:block",
                        isActive ? "bg-signal" : "bg-transparent group-hover:bg-ink/20",
                      )}
                    />
                  </button>
                </li>
              );
            })}
          </ul>

          <Reveal delay={0.15} className="relative min-h-[320px] bg-ink p-8 text-paper md:p-10">
            <AnimatePresence mode="wait">
              <motion.div
                key={current.id}
                initial={{ opacity: 0, y: 16 }}
                animate={{ opacity: 1, y: 0 }}
                exit={{ opacity: 0, y: -12 }}
                transition={{ duration: 0.35 }}
              >
                <p className="text-[10px] font-semibold uppercase tracking-[0.28em] text-signal-hot">
                  {current.tag}
                </p>
                <h3 className="mt-3 font-display text-3xl font-bold md:text-4xl">
                  {current.title}
                </h3>
                <p className="mt-5 text-base leading-relaxed text-paper/75">
                  {current.summary}
                </p>
                <ul className="mt-8 space-y-3">
                  {current.details.map((d) => (
                    <li key={d} className="flex items-start gap-3 text-sm text-paper/90">
                      <span className="mt-1.5 h-1.5 w-1.5 shrink-0 bg-teal-glow" />
                      {d}
                    </li>
                  ))}
                </ul>
              </motion.div>
            </AnimatePresence>
            <div className="pointer-events-none absolute -bottom-6 -right-6 h-32 w-32 border border-signal/40" />
          </Reveal>
        </div>
      </div>
    </section>
  );
}
