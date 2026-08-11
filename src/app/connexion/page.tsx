"use client";

import Link from "next/link";
import { FormEvent, useEffect, useState } from "react";
import { useRouter } from "next/navigation";
import { useAuth } from "@/context/AuthContext";

export default function ConnexionPage() {
  const { user, loading, login } = useAuth();
  const router = useRouter();
  const [email, setEmail] = useState("");
  const [password, setPassword] = useState("");
  const [error, setError] = useState("");
  const [submitting, setSubmitting] = useState(false);

  useEffect(() => {
    if (!loading && user) {
      router.replace("/espace/");
    }
  }, [loading, user, router]);

  async function onSubmit(e: FormEvent) {
    e.preventDefault();
    setError("");
    setSubmitting(true);
    try {
      await login(email.trim(), password);
      router.replace("/espace/");
    } catch (err) {
      setError(err instanceof Error ? err.message : "Connexion impossible");
    } finally {
      setSubmitting(false);
    }
  }

  if (loading || user) {
    return (
      <div className="flex min-h-screen items-center justify-center bg-paper text-ink-soft">
        Chargement…
      </div>
    );
  }

  return (
    <div className="mesh-bg relative flex min-h-screen items-center justify-center px-5 py-16">
      <div className="crm-card w-full max-w-md p-8 md:p-10">
        <Link href="/" className="font-display text-2xl font-extrabold tracking-tight text-ink">
          O<span className="text-signal">Dev</span>
        </Link>
        <p className="mt-2 text-sm text-ink-soft">Espace professionnel — connexion uniquement</p>

        <h1 className="mt-8 font-display text-3xl font-bold tracking-tight text-ink">
          Connexion
        </h1>

        <form onSubmit={onSubmit} className="mt-8 space-y-4">
          <div>
            <label htmlFor="email" className="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-ink-soft">
              E-mail
            </label>
            <input
              id="email"
              type="email"
              autoComplete="email"
              required
              value={email}
              onChange={(e) => setEmail(e.target.value)}
              className="crm-input"
              placeholder="vous@exemple.com"
            />
          </div>
          <div>
            <label htmlFor="password" className="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-ink-soft">
              Mot de passe
            </label>
            <input
              id="password"
              type="password"
              autoComplete="current-password"
              required
              value={password}
              onChange={(e) => setPassword(e.target.value)}
              className="crm-input"
            />
          </div>

          {error ? (
            <p className="border border-signal/30 bg-signal/10 px-3 py-2 text-sm text-signal" role="alert">
              {error}
            </p>
          ) : null}

          <button
            type="submit"
            disabled={submitting}
            className="w-full border border-ink bg-ink px-4 py-3 text-xs font-semibold uppercase tracking-[0.16em] text-paper transition-colors hover:border-signal hover:bg-signal disabled:opacity-60"
          >
            {submitting ? "Connexion…" : "Se connecter"}
          </button>
        </form>

        <p className="mt-6 text-center text-sm text-ink-soft">
          <Link href="/" className="underline decoration-[var(--line-strong)] underline-offset-4 hover:text-ink">
            ← Retour au site
          </Link>
        </p>
      </div>
    </div>
  );
}
