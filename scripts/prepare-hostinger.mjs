/**
 * Vérifie le build statique et crée odev-hostinger.zip (contenu de out/).
 */
import { existsSync, rmSync } from "node:fs";
import { join } from "node:path";
import { cwd, platform } from "node:process";
import { execFileSync } from "node:child_process";

const root = cwd();
const outDir = join(root, "out");
const zipPath = join(root, "odev-hostinger.zip");

if (!existsSync(outDir)) {
  console.error("Dossier `out/` introuvable. Lance d'abord : npm run build");
  process.exit(1);
}

if (!existsSync(join(outDir, ".htaccess"))) {
  console.warn("Attention : `.htaccess` absent de `out/`. Vérifie public/.htaccess");
}

if (!existsSync(join(outDir, "index.html"))) {
  console.error("index.html manquant dans out/ — build invalide.");
  process.exit(1);
}

if (existsSync(zipPath)) rmSync(zipPath);

try {
  if (platform === "win32") {
    execFileSync(
      "powershell.exe",
      [
        "-NoProfile",
        "-Command",
        `Compress-Archive -Path (Join-Path '${outDir.replace(/'/g, "''")}' '*') -DestinationPath '${zipPath.replace(/'/g, "''")}' -Force`,
      ],
      { stdio: "inherit" },
    );
  } else {
    execFileSync("zip", ["-r", zipPath, "."], { cwd: outDir, stdio: "inherit" });
  }
  console.log("OK : out/ prêt pour Hostinger (uploader dans public_html)");
  console.log(`ZIP : ${zipPath}`);
} catch {
  console.log("OK : out/ prêt — zip non créé (uploade le dossier out/ manuellement).");
}
