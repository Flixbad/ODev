"use client";

import { motion, useReducedMotion } from "framer-motion";
import { ArrowDownRight, ArrowUpRight } from "lucide-react";
import { MagneticButton } from "@/components/ui/MagneticButton";

const codeBits = [
  "const craft = true",
  "<Experience />",
  "api.route()",
  "deploy()",
  "React · Node",
];

export function Hero() {
  const reduce = useReducedMotion();

  return (
    <section
      id="top"
      className="relative min-h-[100svh] overflow-hidden mesh-bg pt-24"
    >
      <div className="pointer-events-none absolute inset-0 grid-fade" />

      {/* Full-bleed visual plane */}
      <div className="pointer-events-none absolute inset-0" aria-hidden>
        <div className="absolute -right-[8%] top-[12%] h-[70vmin] w-[70vmin] rounded-full border border-[var(--line-strong)] opacity-60" />
        <div
          className="absolute -right-[2%] top-[18%] h-[55vmin] w-[55vmin] rounded-full border border-signal/30"
          style={{ animation: reduce ? undefined : "pulse-ring 4s ease-out infinite" }}
        />
        <div className="float-slow absolute right-[8%] top-[22%] hidden h-40 w-40 border border-ink/20 bg-ink/5 backdrop-blur-sm lg:block" />
        <div className="float-mid absolute right-[22%] top-[48%] hidden h-24 w-24 bg-signal lg:block" />
        <div className="float-fast absolute bottom-[18%] right-[14%] hidden h-16 w-48 border border-teal bg-teal/10 lg:block" />

        <svg
          className="absolute inset-0 h-full w-full opacity-40"
          viewBox="0 0 1440 900"
          fill="none"
          preserveAspectRatio="xMidYMid slice"
        >
          <motion.path
            d="M920 80 L1280 220 L1180 520 L860 640 L720 360 Z"
            stroke="currentColor"
            strokeWidth="1.2"
            className="text-ink/30"
            initial={reduce ? false : { pathLength: 0 }}
            animate={{ pathLength: 1 }}
            transition={{ duration: 2.2, ease: "easeInOut" }}
          />
          <motion.path
            d="M980 160 L1220 280 L1140 460"
            stroke="currentColor"
            strokeWidth="1.5"
            className="text-signal"
            initial={reduce ? false : { pathLength: 0 }}
            animate={{ pathLength: 1 }}
            transition={{ duration: 1.8, delay: 0.4, ease: "easeInOut" }}
          />
        </svg>

        {codeBits.map((bit, i) => (
          <motion.span
            key={bit}
            className="absolute hidden font-mono text-[11px] tracking-wide text-ink/35 lg:block"
            style={{
              top: `${18 + i * 12}%`,
              right: `${6 + (i % 3) * 9}%`,
            }}
            initial={reduce ? false : { opacity: 0, y: 12 }}
            animate={{ opacity: 1, y: 0 }}
            transition={{ delay: 0.8 + i * 0.12, duration: 0.6 }}
          >
            {bit}
          </motion.span>
        ))}
      </div>

      <div className="relative z-10 mx-auto flex min-h-[calc(100svh-6rem)] max-w-7xl flex-col justify-center px-5 pb-12 pt-8 md:px-8 md:pb-20">
        <motion.p
          className="mb-5 text-xs font-semibold uppercase tracking-[0.32em] text-ink-soft"
          initial={reduce ? false : { opacity: 0, y: 16 }}
          animate={{ opacity: 1, y: 0 }}
          transition={{ duration: 0.55 }}
        >
          Darren O&apos;Sullivan · Micro-entreprise
        </motion.p>

        <motion.h1
          className="font-display text-[clamp(4.25rem,16vw,10.5rem)] leading-[0.82] font-extrabold tracking-tight text-ink"
          initial={reduce ? false : { opacity: 0, y: 28 }}
          animate={{ opacity: 1, y: 0 }}
          transition={{ duration: 0.75, ease: [0.22, 1, 0.36, 1] }}
        >
          O<span className="text-signal">Dev</span>
        </motion.h1>

        <motion.p
          className="mt-6 max-w-xl text-lg leading-relaxed text-ink-soft md:text-xl"
          initial={reduce ? false : { opacity: 0, y: 20 }}
          animate={{ opacity: 1, y: 0 }}
          transition={{ delay: 0.15, duration: 0.6 }}
        >
          Des expériences web taillées sur mesure — React, Node.js et systèmes
          métier qui font gagner du temps à votre entreprise.
        </motion.p>

        <motion.div
          className="mt-10 flex flex-wrap items-center gap-4"
          initial={reduce ? false : { opacity: 0, y: 16 }}
          animate={{ opacity: 1, y: 0 }}
          transition={{ delay: 0.28, duration: 0.55 }}
        >
          <MagneticButton href="#contact" variant="primary">
            Discuter de votre projet
            <ArrowUpRight className="h-4 w-4" />
          </MagneticButton>
          <MagneticButton href="#services" variant="ghost">
            Voir les services
            <ArrowDownRight className="h-4 w-4" />
          </MagneticButton>
        </motion.div>

        <motion.div
          className="mt-14 flex items-end justify-between border-t border-[var(--line-strong)] pt-6 md:mt-16"
          initial={reduce ? false : { opacity: 0 }}
          animate={{ opacity: 1 }}
          transition={{ delay: 0.45 }}
        >
          <p className="max-w-xs text-xs uppercase tracking-[0.2em] text-ink-soft">
            Conception · Développement · Mise en production
          </p>
          <a
            href="#services"
            className="group flex items-center gap-2 text-sm font-medium text-ink"
          >
            Défiler
            <span className="inline-block h-8 w-px origin-top bg-signal transition-transform group-hover:scale-y-125" />
          </a>
        </motion.div>
      </div>
    </section>
  );
}
