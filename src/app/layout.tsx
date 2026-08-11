import type { Metadata } from "next";
import { Syne, Manrope } from "next/font/google";
import { Providers } from "@/components/Providers";
import "./globals.css";

const syne = Syne({
  variable: "--font-syne",
  subsets: ["latin"],
  weight: ["500", "600", "700", "800"],
});

const manrope = Manrope({
  variable: "--font-manrope",
  subsets: ["latin"],
  weight: ["400", "500", "600", "700"],
});

export const metadata: Metadata = {
  title: "ODev — Développement web par Darren O'Sullivan",
  description:
    "ODev est une micro-entreprise de développement web : sites React, Node.js, sites vitrine et outils de gestion d'entreprise.",
  keywords: [
    "ODev",
    "Darren O'Sullivan",
    "développement web",
    "React",
    "Node.js",
    "site vitrine",
    "gestion d'entreprise",
  ],
  openGraph: {
    title: "ODev — Studio de développement web",
    description:
      "Sites, applications et systèmes métier conçus par Darren O'Sullivan.",
    type: "website",
    locale: "fr_FR",
  },
};

export default function RootLayout({ children }: LayoutProps<"/">) {
  return (
    <html
      lang="fr"
      className={`${syne.variable} ${manrope.variable} h-full antialiased`}
    >
      <body className="min-h-full flex flex-col font-sans text-foreground">
        <div className="grain" aria-hidden />
        <Providers>{children}</Providers>
      </body>
    </html>
  );
}
