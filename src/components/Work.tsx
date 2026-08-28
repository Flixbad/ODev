"use client";

import { ArrowUpRight, Tag } from "lucide-react";
import { Reveal } from "@/components/ui/Reveal";
import { MagneticButton } from "@/components/ui/MagneticButton";

const CONTACT_URL =
  "https://life.oren-rp.com/profile/cmshprltu0j3vmj1d4p6lg3yj";

const projects = [
  {
    title: "Cabinet Valet",
    type: "Site vitrine professionnel",
    description:
      "Présence web soignée pour un cabinet : identité claire, parcours client et mise en avant des services — prêt à être repris et exploité.",
    url: "https://cabinet-valet.kodyalabs.fr/",
    host: "cabinet-valet.kodyalabs.fr",
    price: "8 500$",
    accent: "teal" as const,
  },
  {
    title: "Maison Aureum",
    type: "Site atelier de tatouage",
    description:
      "Site immersif pour un atelier de tatouage : manifeste, atlas du corps, artistes, tarifs et prise de rendez-vous — une expérience visuelle premium clé en main.",
    url: "https://tattoo.kodyalabs.fr/",
    host: "tattoo.kodyalabs.fr",
    price: "14 500$",
    accent: "signal" as const,
  },
  {
    title: "Maison Aurèle",
    type: "Site salon de coiffure",
    description:
      "Cut lab premium pour un salon de coiffure : univers femme/homme/couleur, grille tarifaire, équipe et réservation en ligne — prêt à être repris et exploité.",
    url: "https://salon-coiffure.kodyalabs.fr/",
    host: "salon-coiffure.kodyalabs.fr",
    price: "11 500$",
    accent: "teal" as const,
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
            Projets en vente
          </h2>
          <p className="mt-5 max-w-xl text-paper/65">
            Réalisations ODev actuellement proposées à la cession.
          </p>
        </Reveal>

        <div className="mt-14 flex flex-col gap-8">
          {projects.map((project, i) => (
            <Reveal key={project.title} delay={0.08 * i}>
              <article className="relative overflow-hidden border border-white/10 bg-paper/[0.03]">
                <div
                  className={`pointer-events-none absolute -right-16 -top-16 h-56 w-56 rounded-full blur-3xl ${
                    project.accent === "signal" ? "bg-signal/20" : "bg-teal/20"
                  }`}
                />
                <div className="pointer-events-none absolute -bottom-20 left-1/3 h-48 w-48 rounded-full bg-teal/10 blur-3xl" />

                <div className="relative grid gap-0 lg:grid-cols-[1.15fr_0.85fr]">
                  <div className="border-b border-white/10 p-8 md:p-10 lg:border-b-0 lg:border-r">
                    <div
                      className={`inline-flex items-center gap-2 px-3 py-1.5 text-[10px] font-semibold uppercase tracking-[0.22em] ${
                        project.accent === "signal"
                          ? "border border-signal/40 bg-signal/10 text-signal-hot"
                          : "border border-teal-glow/40 bg-teal/15 text-teal-glow"
                      }`}
                    >
                      <Tag className="h-3.5 w-3.5" />
                      En vente
                    </div>

                    <h3 className="mt-6 font-display text-4xl font-bold tracking-tight md:text-5xl">
                      {project.title}
                    </h3>
                    <p className="mt-3 text-sm uppercase tracking-[0.18em] text-paper/45">
                      {project.type}
                    </p>
                    <p className="mt-6 max-w-lg text-base leading-relaxed text-paper/70 md:text-lg">
                      {project.description}
                    </p>

                    <a
                      href={project.url}
                      target="_blank"
                      rel="noopener noreferrer"
                      className="mt-8 inline-flex items-center gap-2 text-sm font-medium text-paper underline-offset-4 transition-colors hover:text-signal-hot hover:underline"
                    >
                      {project.host}
                      <ArrowUpRight className="h-4 w-4" />
                    </a>
                  </div>

                  <div className="flex flex-col justify-between gap-8 p-8 md:p-10">
                    <div>
                      <p className="text-[10px] font-semibold uppercase tracking-[0.22em] text-paper/45">
                        Prix de cession
                      </p>
                      <p
                        className={`mt-3 font-display text-5xl font-extrabold tracking-tight md:text-6xl ${
                          project.accent === "signal"
                            ? "text-signal-hot"
                            : "text-teal-glow"
                        }`}
                      >
                        {project.price}
                      </p>
                      <p className="mt-4 text-sm leading-relaxed text-paper/55">
                        Projet clé en main — me contacter pour discuter de la
                        reprise.
                      </p>
                    </div>

                    <div className="flex flex-col gap-3 sm:flex-row lg:flex-col xl:flex-row">
                      <MagneticButton
                        href={CONTACT_URL}
                        target="_blank"
                        rel="noopener noreferrer"
                        variant="primary"
                        className="w-full sm:w-auto"
                      >
                        Me contacter
                        <ArrowUpRight className="h-4 w-4" />
                      </MagneticButton>
                      <MagneticButton
                        href={project.url}
                        target="_blank"
                        rel="noopener noreferrer"
                        variant="ghost"
                        className="w-full border-white/25 text-paper hover:border-paper hover:bg-paper hover:text-ink sm:w-auto"
                      >
                        Voir le site
                        <ArrowUpRight className="h-4 w-4" />
                      </MagneticButton>
                    </div>
                  </div>
                </div>
              </article>
            </Reveal>
          ))}
        </div>
      </div>
    </section>
  );
}
