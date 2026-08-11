/**
 * Prépare le déploiement Hostinger :
 * - site statique (out/)
 * - API PHP CRM (api/) → à placer dans public_html/api/
 * - zip odev-hostinger.zip
 */
import {
  existsSync,
  rmSync,
  mkdirSync,
  cpSync,
  writeFileSync,
} from "node:fs";
import { join } from "node:path";
import { cwd, platform } from "node:process";
import { execFileSync } from "node:child_process";

const root = cwd();
const outDir = join(root, "out");
const apiDir = join(root, "api");
const deployDir = join(root, "deploy-hostinger");
const zipPath = join(root, "odev-hostinger.zip");

if (!existsSync(outDir) || !existsSync(join(outDir, "index.html"))) {
  console.error("Build invalide. Lance : npm run build");
  process.exit(1);
}
if (!existsSync(apiDir) || !existsSync(join(apiDir, "index.php"))) {
  console.error("Dossier api/ manquant.");
  process.exit(1);
}

if (existsSync(deployDir)) rmSync(deployDir, { recursive: true, force: true });
mkdirSync(deployDir, { recursive: true });

// Contenu du site à la racine public_html
cpSync(outDir, deployDir, { recursive: true });
// API CRM
cpSync(apiDir, join(deployDir, "api"), { recursive: true });

writeFileSync(
  join(deployDir, "LIRE-MOI-DEPLOIEMENT.txt"),
  `ODev — déploiement Hostinger
==============================

1) Uploade TOUT le contenu de ce dossier dans public_html/
   (pas le dossier deploy-hostinger lui-même)

2) Tu dois avoir :
   - index.html          → site vitrine
   - connexion/          → page connexion CRM
   - espace/             → back-office CRM
   - api/                → API PHP + MySQL

3) Crée une base MySQL dans hPanel

4) Ouvre https://TON-DOMAINE/api/setup.php
   Configure MySQL + ton compte admin (UNIQUE, pas d'inscription)

5) Supprime api/setup.php

6) Site : https://TON-DOMAINE/
   Connexion : https://TON-DOMAINE/connexion/
`,
);

if (existsSync(zipPath)) rmSync(zipPath);

try {
  if (platform === "win32") {
    execFileSync(
      "powershell.exe",
      [
        "-NoProfile",
        "-Command",
        `Compress-Archive -Path (Join-Path '${deployDir.replace(/'/g, "''")}' '*') -DestinationPath '${zipPath.replace(/'/g, "''")}' -Force`,
      ],
      { stdio: "inherit" },
    );
  } else {
    execFileSync("zip", ["-r", zipPath, "."], {
      cwd: deployDir,
      stdio: "inherit",
    });
  }
  console.log("OK : deploy-hostinger/ prêt (site + api)");
  console.log(`ZIP : ${zipPath}`);
} catch {
  console.log("OK : deploy-hostinger/ prêt — zip non créé.");
}
