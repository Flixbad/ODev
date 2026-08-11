/**
 * Génère un PowerPoint (.pptx) du dossier de création ODev.
 * Importable dans Canva.com et Google Slides.
 *
 * Usage :
 *   cd dossier-creation
 *   npm install
 *   npm run slides
 */
import PptxGenJS from "pptxgenjs";
import { mkdirSync } from "node:fs";
import { dirname, join } from "node:path";
import { fileURLToPath } from "node:url";

const __dirname = dirname(fileURLToPath(import.meta.url));
const outDir = join(__dirname, "export");
mkdirSync(outDir, { recursive: true });

const COLORS = {
  ink: "12151C",
  inkSoft: "2A303C",
  paper: "E9ECF1",
  mist: "D6DAE2",
  white: "FFFFFF",
  signal: "E63312",
  teal: "1A7A72",
};

const pptx = new PptxGenJS();
pptx.defineLayout({ name: "WIDE", width: 13.333, height: 7.5 });
pptx.layout = "WIDE";
pptx.author = "Darren O'Sullivan";
pptx.title = "Dossier de création — ODev";
pptx.subject = "Micro-entreprise de développement web";

function addFooter(slide, label) {
  slide.addText("ODev — Dossier de création · ODEV-2026-01", {
    x: 0.6,
    y: 7.05,
    w: 10,
    h: 0.3,
    fontSize: 10,
    fontFace: "Arial",
    color: COLORS.inkSoft,
  });
  slide.addText(label, {
    x: 11.2,
    y: 7.05,
    w: 1.5,
    h: 0.3,
    fontSize: 10,
    fontFace: "Arial",
    color: COLORS.inkSoft,
    align: "right",
  });
}

function eyebrow(slide, text, y = 0.45) {
  slide.addText(text.toUpperCase(), {
    x: 0.7,
    y,
    w: 12,
    h: 0.3,
    fontSize: 11,
    fontFace: "Arial",
    bold: true,
    color: COLORS.signal,
    charSpacing: 4,
  });
}

// —— 1. Couverture ——
{
  const s = pptx.addSlide();
  s.addShape(pptx.shapes.RECTANGLE, {
    x: 0, y: 0, w: 13.333, h: 7.5,
    fill: { color: COLORS.paper },
  });
  s.addShape(pptx.shapes.RECTANGLE, {
    x: 0, y: 0, w: 0.18, h: 7.5,
    fill: { color: COLORS.signal },
  });
  s.addText("DOSSIER DE CRÉATION D'ENTREPRISE", {
    x: 0.8, y: 1.6, w: 11, h: 0.35,
    fontSize: 12, fontFace: "Arial", bold: true, color: COLORS.signal, charSpacing: 3,
  });
  s.addText("ODev", {
    x: 0.8, y: 2.1, w: 11, h: 1.2,
    fontSize: 72, fontFace: "Arial", bold: true, color: COLORS.ink,
  });
  s.addText("Micro-entreprise de conception et développement web\nProposition formelle d'ouverture d'activité", {
    x: 0.8, y: 3.4, w: 10, h: 0.9,
    fontSize: 18, fontFace: "Arial", color: COLORS.inkSoft,
  });
  s.addText([
    { text: "Porteur  ", options: { color: COLORS.inkSoft, fontSize: 12 } },
    { text: "Darren O'Sullivan", options: { color: COLORS.ink, bold: true, fontSize: 14 } },
    { text: "    ·    Forme  ", options: { color: COLORS.inkSoft, fontSize: 12 } },
    { text: "Micro-entreprise", options: { color: COLORS.ink, bold: true, fontSize: 14 } },
    { text: "    ·    Réf.  ", options: { color: COLORS.inkSoft, fontSize: 12 } },
    { text: "ODEV-2026-01", options: { color: COLORS.ink, bold: true, fontSize: 14 } },
  ], { x: 0.8, y: 5.6, w: 11.5, h: 0.4 });
}

// —— 2. Identité ——
{
  const s = pptx.addSlide();
  s.addShape(pptx.shapes.RECTANGLE, { x: 0, y: 0, w: 13.333, h: 7.5, fill: { color: COLORS.white } });
  eyebrow(s, "01 — Identité");
  s.addText("Qui est ODev ?", {
    x: 0.7, y: 0.85, w: 12, h: 0.6,
    fontSize: 36, fontFace: "Arial", bold: true, color: COLORS.ink,
  });
  s.addText("Structure légère, professionnelle et autonome, dédiée à la création d'outils numériques utiles pour les entreprises et organisations.", {
    x: 0.7, y: 1.55, w: 11.5, h: 0.7,
    fontSize: 15, fontFace: "Arial", color: COLORS.inkSoft,
  });

  const boxes = [
    ["Raison sociale", "ODev — développement web & solutions digitales sur mesure."],
    ["Dirigeant", "Darren O'Sullivan, fondateur et développeur de la structure."],
    ["Nature", "Services intellectuels : conception, développement, mise en ligne, accompagnement."],
  ];
  boxes.forEach((b, i) => {
    const x = 0.7 + i * 4.1;
    s.addShape(pptx.shapes.RECTANGLE, {
      x, y: 2.5, w: 3.9, h: 2.6,
      fill: { color: COLORS.paper },
      line: { color: "C8CED8", width: 1 },
    });
    s.addText(b[0], {
      x: x + 0.25, y: 2.75, w: 3.4, h: 0.4,
      fontSize: 16, fontFace: "Arial", bold: true, color: COLORS.ink,
    });
    s.addText(b[1], {
      x: x + 0.25, y: 3.3, w: 3.4, h: 1.5,
      fontSize: 13, fontFace: "Arial", color: COLORS.inkSoft,
    });
  });
  addFooter(s, "02 / 08");
}

// —— 3. Motivation ——
{
  const s = pptx.addSlide();
  s.addShape(pptx.shapes.RECTANGLE, { x: 0, y: 0, w: 13.333, h: 7.5, fill: { color: COLORS.mist } });
  eyebrow(s, "02 — Motivation");
  s.addText("Pourquoi ouvrir ODev ?", {
    x: 0.7, y: 0.85, w: 12, h: 0.55,
    fontSize: 34, fontFace: "Arial", bold: true, color: COLORS.ink,
  });

  s.addText("Constat", {
    x: 0.7, y: 1.7, w: 5.5, h: 0.4,
    fontSize: 18, fontFace: "Arial", bold: true, color: COLORS.ink,
  });
  s.addText([
    { text: "De nombreuses structures n'ont pas de vitrine web professionnelle.", options: { breakLine: true } },
    { text: "Les devis d'agences restent souvent hors de portée des petites structures.", options: { breakLine: true } },
    { text: "La gestion (clients, devis, factures) repose encore sur des tableurs fragiles.", options: { breakLine: true } },
    { text: "Le marché demande des interlocuteurs clairs, capables de livrer vite.", options: { breakLine: true } },
  ], {
    x: 0.7, y: 2.2, w: 5.7, h: 3.5,
    fontSize: 14, fontFace: "Arial", color: COLORS.inkSoft, paraSpaceAfter: 10,
    bullet: { code: "25A0" },
  });

  s.addText("Réponse ODev", {
    x: 7, y: 1.7, w: 5.5, h: 0.4,
    fontSize: 18, fontFace: "Arial", bold: true, color: COLORS.ink,
  });
  s.addText([
    { text: "Sites vitrine modernes, rapides et maintenables.", options: { breakLine: true } },
    { text: "Applications web (React, Node.js) adaptées aux besoins réels.", options: { breakLine: true } },
    { text: "Outils de gestion d'entreprise simples et efficaces.", options: { breakLine: true } },
    { text: "Suivi humain, de la conception à la mise en production.", options: { breakLine: true } },
  ], {
    x: 7, y: 2.2, w: 5.7, h: 3.5,
    fontSize: 14, fontFace: "Arial", color: COLORS.inkSoft, paraSpaceAfter: 10,
    bullet: { code: "25A0" },
  });
  addFooter(s, "03 / 08");
}

// —— 4. Valeurs ——
{
  const s = pptx.addSlide();
  s.addShape(pptx.shapes.RECTANGLE, { x: 0, y: 0, w: 13.333, h: 7.5, fill: { color: COLORS.white } });
  eyebrow(s, "02 bis — Principes");
  s.addText("Quatre engagements", {
    x: 0.7, y: 0.85, w: 12, h: 0.55,
    fontSize: 34, fontFace: "Arial", bold: true, color: COLORS.ink,
  });
  const vals = [
    ["Utilité", "Chaque projet sert un usage concret."],
    ["Clarté", "Devis, délais et périmètre sans flou."],
    ["Qualité", "Code propre, design soigné, livraison stable."],
    ["Proximité", "Un interlocuteur unique, engagé."],
  ];
  vals.forEach((v, i) => {
    const x = 0.7 + (i % 4) * 3.1;
    s.addShape(pptx.shapes.RECTANGLE, {
      x, y: 2.2, w: 0.12, h: 2.4, fill: { color: COLORS.signal },
    });
    s.addText(v[0], {
      x: x + 0.3, y: 2.3, w: 2.6, h: 0.5,
      fontSize: 20, fontFace: "Arial", bold: true, color: COLORS.ink,
    });
    s.addText(v[1], {
      x: x + 0.3, y: 3.0, w: 2.6, h: 1.2,
      fontSize: 14, fontFace: "Arial", color: COLORS.inkSoft,
    });
  });
  addFooter(s, "04 / 08");
}

// —— 5. Offre ——
{
  const s = pptx.addSlide();
  s.addShape(pptx.shapes.RECTANGLE, { x: 0, y: 0, w: 13.333, h: 7.5, fill: { color: COLORS.white } });
  eyebrow(s, "03 — Offre de services");
  s.addText("Ce que ODev propose aux entreprises", {
    x: 0.7, y: 0.8, w: 12, h: 0.5,
    fontSize: 30, fontFace: "Arial", bold: true, color: COLORS.ink,
  });

  const services = [
    ["01", "Sites vitrine", "Identité en ligne, pages, contact, mise en production."],
    ["02", "Applications React", "Interfaces modernes pour outils métier et dashboards."],
    ["03", "Back-end Node.js", "API, authentification, logique métier, intégrations."],
    ["04", "Gestion d'entreprise", "Clients, devis, factures, suivi comptable."],
    ["05", "Accompagnement", "Formation, documentation, maintenance, évolutions."],
  ];
  services.forEach((svc, i) => {
    const y = 1.5 + i * 0.95;
    s.addText(svc[0], {
      x: 0.7, y, w: 0.8, h: 0.7,
      fontSize: 20, fontFace: "Arial", bold: true, color: COLORS.signal,
    });
    s.addText(svc[1], {
      x: 1.6, y, w: 4, h: 0.35,
      fontSize: 16, fontFace: "Arial", bold: true, color: COLORS.ink,
    });
    s.addText(svc[2], {
      x: 1.6, y: y + 0.35, w: 10.5, h: 0.35,
      fontSize: 13, fontFace: "Arial", color: COLORS.inkSoft,
    });
  });
  addFooter(s, "05 / 08");
}

// —— 6. Marché ——
{
  const s = pptx.addSlide();
  s.addShape(pptx.shapes.RECTANGLE, { x: 0, y: 0, w: 13.333, h: 7.5, fill: { color: COLORS.ink } });
  s.addText("04 — MARCHÉ & IMPACT", {
    x: 0.7, y: 0.45, w: 12, h: 0.3,
    fontSize: 11, fontFace: "Arial", bold: true, color: COLORS.signal, charSpacing: 3,
  });
  s.addText("À qui s'adresse ODev ?", {
    x: 0.7, y: 0.9, w: 12, h: 0.55,
    fontSize: 34, fontFace: "Arial", bold: true, color: COLORS.white,
  });
  s.addText("Commerces, professions libérales, associations, TPE/PME et organisations qui veulent une présence numérique nette.", {
    x: 0.7, y: 1.55, w: 11.5, h: 0.6,
    fontSize: 15, fontFace: "Arial", color: "A8B0BC",
  });

  const m = [
    ["Clients cibles", "Entreprises locales, indépendants, structures en création ou modernisation digitale."],
    ["Besoin couvert", "Visibilité, conversion, organisation interne et outils sur mesure."],
    ["Impact attendu", "Accès à des services numériques professionnels pour le tissu économique local."],
  ];
  m.forEach((item, i) => {
    const x = 0.7 + i * 4.1;
    s.addShape(pptx.shapes.RECTANGLE, {
      x, y: 2.5, w: 3.9, h: 2.5,
      fill: { color: "1C222C" },
      line: { color: "2A3340", width: 1 },
    });
    s.addText(item[0], {
      x: x + 0.25, y: 2.75, w: 3.4, h: 0.4,
      fontSize: 16, fontFace: "Arial", bold: true, color: COLORS.white,
    });
    s.addText(item[1], {
      x: x + 0.25, y: 3.3, w: 3.4, h: 1.4,
      fontSize: 13, fontFace: "Arial", color: "A8B0BC",
    });
  });
  s.addText("ODev — Dossier de création · ODEV-2026-01", {
    x: 0.6, y: 7.05, w: 10, h: 0.3, fontSize: 10, fontFace: "Arial", color: "6B7380",
  });
  s.addText("06 / 08", {
    x: 11.2, y: 7.05, w: 1.5, h: 0.3, fontSize: 10, fontFace: "Arial", color: "6B7380", align: "right",
  });
}

// —— 7. Modèle + Porteur ——
{
  const s = pptx.addSlide();
  s.addShape(pptx.shapes.RECTANGLE, { x: 0, y: 0, w: 13.333, h: 7.5, fill: { color: COLORS.white } });
  eyebrow(s, "05 — Modèle & porteur");
  s.addText("Fonctionnement & responsabilité", {
    x: 0.7, y: 0.85, w: 12, h: 0.5,
    fontSize: 30, fontFace: "Arial", bold: true, color: COLORS.ink,
  });

  const steps = [
    ["01", "Brief", "Écoute, cadrage, devis"],
    ["02", "Conception", "Architecture & validation"],
    ["03", "Développement", "Production itérative"],
    ["04", "Livraison", "Mise en ligne & suivi"],
  ];
  steps.forEach((st, i) => {
    const x = 0.7 + i * 3.1;
    s.addShape(pptx.shapes.RECTANGLE, {
      x, y: 1.6, w: 2.95, h: 0.08, fill: { color: COLORS.signal },
    });
    s.addText(st[0], {
      x, y: 1.85, w: 2.95, h: 0.35,
      fontSize: 14, fontFace: "Arial", bold: true, color: COLORS.signal,
    });
    s.addText(st[1], {
      x, y: 2.2, w: 2.95, h: 0.35,
      fontSize: 16, fontFace: "Arial", bold: true, color: COLORS.ink,
    });
    s.addText(st[2], {
      x, y: 2.55, w: 2.95, h: 0.35,
      fontSize: 12, fontFace: "Arial", color: COLORS.inkSoft,
    });
  });

  s.addShape(pptx.shapes.RECTANGLE, {
    x: 0.7, y: 3.4, w: 12, h: 2.8,
    fill: { color: COLORS.paper },
  });
  s.addText("Darren O'Sullivan — Fondateur", {
    x: 1, y: 3.7, w: 11, h: 0.4,
    fontSize: 20, fontFace: "Arial", bold: true, color: COLORS.ink,
  });
  s.addText("Développeur web orienté produit : interfaces, architectures React / Node.js, livraison d'outils utiles aux entreprises.\nCompétences : React, Node.js, sites vitrine, UX/UI, bases de données.\nPosture : micro-entreprise agile, responsabilité totale sur les livrables.", {
    x: 1, y: 4.25, w: 11, h: 1.6,
    fontSize: 14, fontFace: "Arial", color: COLORS.inkSoft,
  });
  addFooter(s, "07 / 08");
}

// —— 8. Demande ——
{
  const s = pptx.addSlide();
  s.addShape(pptx.shapes.RECTANGLE, { x: 0, y: 0, w: 13.333, h: 7.5, fill: { color: COLORS.paper } });
  s.addShape(pptx.shapes.RECTANGLE, {
    x: 0, y: 0, w: 13.333, h: 0.12, fill: { color: COLORS.signal },
  });
  eyebrow(s, "07 — Demande");
  s.addText("Demande d'ouverture", {
    x: 0.7, y: 0.9, w: 12, h: 0.55,
    fontSize: 34, fontFace: "Arial", bold: true, color: COLORS.ink,
  });
  s.addText("Par le présent dossier, Darren O'Sullivan sollicite l'enregistrement et la reconnaissance de l'activité ODev en tant que micro-entreprise de services numériques, afin d'exercer légalement les prestations décrites au profit des entreprises et organisations du territoire.", {
    x: 0.7, y: 1.65, w: 11.8, h: 1.3,
    fontSize: 16, fontFace: "Arial", color: COLORS.inkSoft,
  });

  s.addShape(pptx.shapes.RECTANGLE, {
    x: 0.7, y: 3.2, w: 11.9, h: 2.6,
    fill: { color: COLORS.white },
    line: { color: "C8CED8", width: 1 },
  });
  s.addText("ODev s'engage à exercer dans le respect des règles applicables, avec sérieux, transparence et contribution positive à l'écosystème économique local.", {
    x: 1.1, y: 3.5, w: 11, h: 0.9,
    fontSize: 15, fontFace: "Arial", color: COLORS.inkSoft,
  });
  s.addText("Darren O'Sullivan", {
    x: 1.1, y: 4.55, w: 6, h: 0.35,
    fontSize: 18, fontFace: "Arial", bold: true, color: COLORS.ink,
  });
  s.addText("Fondateur — ODev    ·    darren@odev.studio", {
    x: 1.1, y: 4.95, w: 8, h: 0.3,
    fontSize: 13, fontFace: "Arial", color: COLORS.inkSoft,
  });
  addFooter(s, "08 / 08");
}

const outFile = join(outDir, "ODev-Dossier-Creation.pptx");
await pptx.writeFile({ fileName: outFile });
console.log("OK → " + outFile);
console.log("");
console.log("Canva : Créer → Importer un fichier → choisir le .pptx");
console.log("Google Slides : Fichier → Importer des diapositives → Uploader le .pptx");
