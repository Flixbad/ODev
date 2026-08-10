import type { NextConfig } from "next";

/**
 * Export statique pour hébergement mutualisé (Hostinger, etc.).
 * `npm run build` génère le dossier `out/` à uploader dans public_html.
 */
const nextConfig: NextConfig = {
  output: "export",
  images: {
    unoptimized: true,
  },
  trailingSlash: true,
};

export default nextConfig;
