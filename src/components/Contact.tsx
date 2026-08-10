"use client";

import { FormEvent, useState } from "react";
import { CheckCircle2, Send } from "lucide-react";
import { Reveal } from "@/components/ui/Reveal";
import { MagneticButton } from "@/components/ui/MagneticButton";

type FormState = {
  name: string;
  email: string;
  company: string;
  service: string;
  message: string;
};

const initial: FormState = {
  name: "",
  email: "",
  company: "",
  service: "Site vitrine",
  message: "",
};

export function Contact() {
  const [form, setForm] = useState<FormState>(initial);
  const [errors, setErrors] = useState<Partial<Record<keyof FormState, string>>>(
    {},
  );
  const [sent, setSent] = useState(false);

  const validate = () => {
    const next: Partial<Record<keyof FormState, string>> = {};
    if (!form.name.trim()) next.name = "Nom requis";
    if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(form.email))
      next.email = "Email invalide";
    if (form.message.trim().length < 12)
      next.message = "Décrivez un peu plus votre besoin";
    setErrors(next);
    return Object.keys(next).length === 0;
  };

  const onSubmit = (e: FormEvent) => {
    e.preventDefault();
    if (!validate()) return;
    setSent(true);
    setForm(initial);
  };

  return (
    <section id="contact" className="relative bg-paper py-24 md:py-32">
      <div className="mx-auto grid max-w-7xl gap-14 px-5 md:grid-cols-12 md:gap-10 md:px-8">
        <Reveal className="md:col-span-5">
          <p className="text-xs font-semibold uppercase tracking-[0.32em] text-signal">
            Contact
          </p>
          <h2 className="mt-4 font-display text-4xl font-bold tracking-tight text-ink md:text-5xl">
            Un projet en tête ?
          </h2>
          <p className="mt-5 text-lg leading-relaxed text-ink-soft">
            Décrivez votre besoin. Darren O&apos;Sullivan vous répond avec une
            proposition claire — délais, périmètre et prochaines étapes.
          </p>

          <div className="mt-10 space-y-4 border-t border-[var(--line-strong)] pt-8 text-sm">
            <p>
              <span className="block text-[10px] uppercase tracking-[0.22em] text-ink-soft">
                Email
              </span>
              <a
                href="mailto:darren@odev.studio"
                className="mt-1 inline-block text-lg font-medium text-ink underline-offset-4 hover:underline"
              >
                darren@odev.studio
              </a>
            </p>
            <p>
              <span className="block text-[10px] uppercase tracking-[0.22em] text-ink-soft">
                Entreprise
              </span>
              <span className="mt-1 block text-lg font-medium text-ink">
                ODev · Micro-entreprise
              </span>
            </p>
          </div>
        </Reveal>

        <Reveal delay={0.12} className="md:col-span-7">
          {sent ? (
            <div className="flex min-h-[420px] flex-col items-start justify-center border border-[var(--line-strong)] bg-mist/50 p-8 md:p-12">
              <CheckCircle2 className="h-10 w-10 text-teal" />
              <h3 className="mt-6 font-display text-3xl font-bold text-ink">
                Message bien reçu
              </h3>
              <p className="mt-3 max-w-md text-ink-soft">
                Merci. Votre demande a été enregistrée localement pour cette
                démo. En conditions réelles, elle arriverait directement chez
                Darren.
              </p>
              <button
                type="button"
                onClick={() => setSent(false)}
                className="mt-8 text-sm font-semibold uppercase tracking-wider text-signal underline-offset-4 hover:underline"
              >
                Envoyer un autre message
              </button>
            </div>
          ) : (
            <form
              onSubmit={onSubmit}
              noValidate
              className="border border-[var(--line-strong)] bg-white/50 p-6 md:p-10"
            >
              <div className="grid gap-5 sm:grid-cols-2">
                <Field
                  label="Nom"
                  error={errors.name}
                  value={form.name}
                  onChange={(v) => setForm((f) => ({ ...f, name: v }))}
                  autoComplete="name"
                />
                <Field
                  label="Email"
                  type="email"
                  error={errors.email}
                  value={form.email}
                  onChange={(v) => setForm((f) => ({ ...f, email: v }))}
                  autoComplete="email"
                />
                <Field
                  label="Entreprise"
                  value={form.company}
                  onChange={(v) => setForm((f) => ({ ...f, company: v }))}
                  autoComplete="organization"
                />
                <label className="block">
                  <span className="text-[10px] font-semibold uppercase tracking-[0.2em] text-ink-soft">
                    Service
                  </span>
                  <select
                    value={form.service}
                    onChange={(e) =>
                      setForm((f) => ({ ...f, service: e.target.value }))
                    }
                    className="mt-2 w-full border-0 border-b border-[var(--line-strong)] bg-transparent py-3 text-ink outline-none focus:border-signal"
                  >
                    <option>Site vitrine</option>
                    <option>Application React</option>
                    <option>Back-end Node.js</option>
                    <option>Gestion d&apos;entreprise</option>
                    <option>Autre / pack complet</option>
                  </select>
                </label>
              </div>

              <label className="mt-5 block">
                <span className="text-[10px] font-semibold uppercase tracking-[0.2em] text-ink-soft">
                  Message
                </span>
                <textarea
                  rows={5}
                  value={form.message}
                  onChange={(e) =>
                    setForm((f) => ({ ...f, message: e.target.value }))
                  }
                  className="mt-2 w-full resize-y border-0 border-b border-[var(--line-strong)] bg-transparent py-3 text-ink outline-none focus:border-signal"
                  placeholder="Objectifs, délais, contraintes…"
                />
                {errors.message && (
                  <span className="mt-1 block text-xs text-signal">
                    {errors.message}
                  </span>
                )}
              </label>

              <div className="mt-8">
                <MagneticButton type="submit" variant="ink">
                  Envoyer
                  <Send className="h-4 w-4" />
                </MagneticButton>
              </div>
            </form>
          )}
        </Reveal>
      </div>
    </section>
  );
}

function Field({
  label,
  value,
  onChange,
  error,
  type = "text",
  autoComplete,
}: {
  label: string;
  value: string;
  onChange: (v: string) => void;
  error?: string;
  type?: string;
  autoComplete?: string;
}) {
  return (
    <label className="block">
      <span className="text-[10px] font-semibold uppercase tracking-[0.2em] text-ink-soft">
        {label}
      </span>
      <input
        type={type}
        value={value}
        autoComplete={autoComplete}
        onChange={(e) => onChange(e.target.value)}
        className="mt-2 w-full border-0 border-b border-[var(--line-strong)] bg-transparent py-3 text-ink outline-none focus:border-signal"
      />
      {error && <span className="mt-1 block text-xs text-signal">{error}</span>}
    </label>
  );
}
