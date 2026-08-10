const items = [
  "React",
  "Next.js",
  "Node.js",
  "TypeScript",
  "Sites vitrine",
  "Gestion d'entreprise",
  "API REST",
  "Dashboards",
  "UX / UI",
  "Déploiement",
];

export function Marquee() {
  const doubled = [...items, ...items];

  return (
    <section
      aria-label="Technologies et expertises"
      className="relative overflow-hidden border-y border-[var(--line)] bg-ink py-5 text-paper"
    >
      <div className="marquee-track flex w-max gap-10 whitespace-nowrap px-4">
        {doubled.map((item, i) => (
          <span
            key={`${item}-${i}`}
            className="flex items-center gap-10 font-display text-lg font-semibold tracking-wide md:text-2xl"
          >
            {item}
            <span className="inline-block h-2 w-2 rotate-45 bg-signal" aria-hidden />
          </span>
        ))}
      </div>
    </section>
  );
}
