"use client";

import { useEffect, useState } from "react";
import { motion, AnimatePresence } from "framer-motion";
import { cn } from "@/lib/cn";

const links = [
  { href: "#services", label: "Services" },
  { href: "#tarifs", label: "Tarifs" },
  { href: "#methode", label: "Méthode" },
  { href: "#realisations", label: "Réalisations" },
  { href: "#contact", label: "Contact" },
];

export function Navbar() {
  const [scrolled, setScrolled] = useState(false);
  const [open, setOpen] = useState(false);

  useEffect(() => {
    const onScroll = () => setScrolled(window.scrollY > 24);
    onScroll();
    window.addEventListener("scroll", onScroll, { passive: true });
    return () => window.removeEventListener("scroll", onScroll);
  }, []);

  useEffect(() => {
    document.body.style.overflow = open ? "hidden" : "";
    return () => {
      document.body.style.overflow = "";
    };
  }, [open]);

  return (
    <header
      className={cn(
        "fixed inset-x-0 top-0 z-40 transition-all duration-500",
        scrolled
          ? "border-b border-[var(--line)] bg-paper/80 backdrop-blur-xl"
          : "bg-transparent",
      )}
    >
      <nav className="mx-auto flex h-16 max-w-7xl items-center justify-between px-5 md:h-20 md:px-8">
        <a href="#top" className="group flex items-baseline gap-1">
          <span className="font-display text-2xl font-extrabold tracking-tight text-ink md:text-[1.75rem]">
            O<span className="text-signal">Dev</span>
          </span>
          <span className="hidden text-[10px] uppercase tracking-[0.28em] text-ink-soft sm:inline">
            Studio
          </span>
        </a>

        <ul className="hidden items-center gap-8 md:flex">
          {links.map((link) => (
            <li key={link.href}>
              <a
                href={link.href}
                className="relative text-sm font-medium text-ink-soft transition-colors hover:text-ink after:absolute after:-bottom-1 after:left-0 after:h-[2px] after:w-0 after:bg-signal after:transition-all hover:after:w-full"
              >
                {link.label}
              </a>
            </li>
          ))}
        </ul>

        <div className="hidden items-center gap-3 md:flex">
          <a
            href="/connexion/"
            className="border border-[var(--line-strong)] bg-transparent px-4 py-2 text-xs font-semibold uppercase tracking-[0.16em] text-ink transition-colors hover:border-ink hover:bg-ink hover:text-paper"
          >
            Connexion
          </a>
          <a
            href="#contact"
            className="border border-ink bg-ink px-4 py-2 text-xs font-semibold uppercase tracking-[0.16em] text-paper transition-colors hover:bg-signal hover:border-signal"
          >
            Lancer un projet
          </a>
        </div>

        <button
          type="button"
          aria-label={open ? "Fermer le menu" : "Ouvrir le menu"}
          aria-expanded={open}
          className="relative z-50 flex h-10 w-10 flex-col items-center justify-center gap-1.5 md:hidden"
          onClick={() => setOpen((v) => !v)}
        >
          <span
            className={cn(
              "block h-0.5 w-6 bg-ink transition-transform",
              open && "translate-y-2 rotate-45",
            )}
          />
          <span
            className={cn(
              "block h-0.5 w-6 bg-ink transition-opacity",
              open && "opacity-0",
            )}
          />
          <span
            className={cn(
              "block h-0.5 w-6 bg-ink transition-transform",
              open && "-translate-y-2 -rotate-45",
            )}
          />
        </button>
      </nav>

      <AnimatePresence>
        {open && (
          <motion.div
            initial={{ opacity: 0, y: -12 }}
            animate={{ opacity: 1, y: 0 }}
            exit={{ opacity: 0, y: -12 }}
            className="fixed inset-0 z-40 bg-paper px-6 pt-24 md:hidden"
          >
            <ul className="flex flex-col gap-6">
              {links.map((link, i) => (
                <motion.li
                  key={link.href}
                  initial={{ opacity: 0, x: -16 }}
                  animate={{ opacity: 1, x: 0 }}
                  transition={{ delay: 0.05 * i }}
                >
                  <a
                    href={link.href}
                    onClick={() => setOpen(false)}
                    className="font-display text-4xl font-bold text-ink"
                  >
                    {link.label}
                  </a>
                </motion.li>
              ))}
            </ul>
            <div className="mt-10 flex flex-col gap-3">
              <a
                href="/connexion/"
                onClick={() => setOpen(false)}
                className="inline-flex border border-ink px-5 py-3 text-sm font-semibold uppercase tracking-wider text-ink"
              >
                Connexion
              </a>
              <a
                href="#contact"
                onClick={() => setOpen(false)}
                className="inline-flex border border-ink bg-signal px-5 py-3 text-sm font-semibold uppercase tracking-wider text-white"
              >
                Lancer un projet
              </a>
            </div>
          </motion.div>
        )}
      </AnimatePresence>
    </header>
  );
}
