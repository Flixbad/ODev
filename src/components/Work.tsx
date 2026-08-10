"use client";

import { motion } from "framer-motion";
import { ArrowUpRight } from "lucide-react";
import { Reveal } from "@/components/ui/Reveal";

const projects = [
  {
    title: "Nova Retail",
    type: "Site vitrine + catalogue",
    stack: "Next.js · CMS",
    year: "2025",
    accent: "bg-signal",
  },
  {
    title: "Horizon Fleet",
    type: "Gestion d'entreprise",
    stack: "React · Node.js",
    year: "2025",
    accent: "bg-teal",
  },
  {
    title: "Atelier Meridian",
    type: "Dashboard métier",
    stack: "React · API",
    year: "2024",
    accent: "bg-ink",
  },
  {
    title: "Pulse Clinic",
    type: "Prise de rendez-vous",
    stack: "Node.js · UI",
    year: "2024",
    accent: "bg-signal-hot",
  },
];

export function Work() {
  return (
    <section id="realisations" className="bg-ink py-24 text-paper md:py-32">
      <div className="mx-auto max-w-7xl px-5 md:px-8">
        <Reveal>
          <p className="text-xs font-semibold uppercase tracking-[0.32em] text-signal-hot">
            Réalisations
          </p>
          <h2 className="mt-4 max-w-3xl font-display text-4xl font-bold tracking-tight md:text-6xl">
            Projets fictifs, exigence réelle.
          </h2>
          <p className="mt-5 max-w-xl text-paper/65">
            Une sélection représentative du type de livrables ODev — du site
            d&apos;image à l&apos;outil opérationnel.
          </p>
        </Reveal>

        <ul className="mt-14 divide-y divide-white/10 border-y border-white/10">
          {projects.map((project, i) => (
            <Reveal key={project.title} as="li" delay={i * 0.06}>
              <motion.article
                whileHover={{ x: 6 }}
                className="group flex flex-col gap-4 py-8 md:flex-row md:items-center md:justify-between md:gap-8 md:py-10"
              >
                <div className="flex items-start gap-5 md:items-center md:gap-8">
                  <span
                    className={`mt-1.5 h-3 w-3 shrink-0 rotate-45 ${project.accent} md:mt-0`}
                  />
                  <div>
                    <h3 className="font-display text-3xl font-bold tracking-tight md:text-4xl">
                      {project.title}
                    </h3>
                    <p className="mt-2 text-sm text-paper/55 md:text-base">
                      {project.type}
                    </p>
                  </div>
                </div>

                <div className="flex items-center justify-between gap-6 pl-8 md:pl-0">
                  <span className="font-mono text-xs text-paper/45 md:text-sm">
                    {project.stack}
                  </span>
                  <span className="flex items-center gap-3 text-sm text-paper/70">
                    {project.year}
                    <ArrowUpRight className="h-4 w-4 text-signal-hot opacity-0 transition-opacity group-hover:opacity-100" />
                  </span>
                </div>
              </motion.article>
            </Reveal>
          ))}
        </ul>
      </div>
    </section>
  );
}
