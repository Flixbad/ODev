export function Footer() {
  return (
    <footer className="border-t border-[var(--line)] bg-mist">
      <div className="mx-auto flex max-w-7xl flex-col gap-10 px-5 py-14 md:flex-row md:items-end md:justify-between md:px-8">
        <div>
          <p className="font-display text-4xl font-extrabold tracking-tight text-ink md:text-5xl">
            O<span className="text-signal">Dev</span>
          </p>
          <p className="mt-3 max-w-sm text-sm text-ink-soft">
            Micro-entreprise de développement web dirigée par Darren
            O&apos;Sullivan. Sites, apps et outils métier — construits pour
            durer.
          </p>
        </div>

        <div className="flex flex-wrap gap-8 text-sm">
          <a href="#services" className="text-ink-soft hover:text-ink">
            Services
          </a>
          <a href="#tarifs" className="text-ink-soft hover:text-ink">
            Tarifs
          </a>
          <a href="#methode" className="text-ink-soft hover:text-ink">
            Méthode
          </a>
          <a href="#realisations" className="text-ink-soft hover:text-ink">
            Réalisations
          </a>
          <a href="#contact" className="text-ink-soft hover:text-ink">
            Contact
          </a>
        </div>
      </div>

      <div className="border-t border-[var(--line)]">
        <div className="mx-auto flex max-w-7xl flex-col gap-2 px-5 py-5 text-xs text-ink-soft sm:flex-row sm:justify-between md:px-8">
          <p>© {new Date().getFullYear()} ODev — Darren O&apos;Sullivan</p>
          <p>Entreprise fictive · Démonstration</p>
        </div>
      </div>
    </footer>
  );
}
