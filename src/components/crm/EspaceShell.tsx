"use client";

import Link from "next/link";
import { usePathname, useRouter } from "next/navigation";
import { type ReactNode, useEffect } from "react";
import { useAuth } from "@/context/AuthContext";
import { cn } from "@/lib/cn";

const links = [
  { href: "/espace/", label: "Tableau de bord" },
  { href: "/espace/clients/", label: "Clients" },
  { href: "/espace/devis/", label: "Devis" },
  { href: "/espace/factures/", label: "Factures" },
  { href: "/espace/compta/", label: "Compta" },
];

export function EspaceShell({ children }: { children: ReactNode }) {
  const { user, loading, logout } = useAuth();
  const router = useRouter();
  const pathname = usePathname();

  useEffect(() => {
    if (!loading && !user) {
      router.replace("/connexion/");
    }
  }, [loading, user, router]);

  if (loading || !user) {
    return (
      <div className="flex min-h-screen items-center justify-center bg-paper text-ink-soft">
        Chargement de l&apos;espace…
      </div>
    );
  }

  return (
    <div className="min-h-screen bg-paper text-ink">
      <header className="sticky top-0 z-30 border-b border-[var(--line)] bg-paper/90 backdrop-blur-xl">
        <div className="mx-auto flex max-w-7xl items-center justify-between gap-4 px-5 py-4 md:px-8">
          <Link href="/" className="font-display text-xl font-extrabold tracking-tight">
            O<span className="text-signal">Dev</span>
            <span className="ml-2 text-[10px] font-semibold uppercase tracking-[0.22em] text-ink-soft">
              Espace
            </span>
          </Link>
          <div className="flex items-center gap-3 text-sm">
            <span className="hidden text-ink-soft sm:inline">{user.name}</span>
            <button
              type="button"
              onClick={() => void logout().then(() => router.push("/"))}
              className="border border-[var(--line-strong)] px-3 py-1.5 text-xs font-semibold uppercase tracking-wider hover:bg-ink hover:text-paper"
            >
              Déconnexion
            </button>
          </div>
        </div>
        <nav className="mx-auto flex max-w-7xl gap-1 overflow-x-auto px-5 pb-3 md:px-8">
          {links.map((link) => (
            <Link
              key={link.href}
              href={link.href}
              className={cn(
                "whitespace-nowrap px-3 py-2 text-sm font-medium transition-colors",
                pathname === link.href || (link.href !== "/espace/" && pathname?.startsWith(link.href))
                  ? "bg-ink text-paper"
                  : "text-ink-soft hover:text-ink",
              )}
            >
              {link.label}
            </Link>
          ))}
        </nav>
      </header>
      <main className="mx-auto max-w-7xl px-5 py-8 md:px-8 md:py-10">{children}</main>
    </div>
  );
}
