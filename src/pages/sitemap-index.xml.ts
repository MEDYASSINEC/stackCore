import type { APIRoute } from 'astro';
export const GET: APIRoute = () => {
  const xml = `<?xml version="1.0" encoding="UTF-8"?>\n<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">\n  <sitemap><loc>https://stackcore.example.com/sitemap.xml</loc></sitemap>\n</sitemapindex>`;
  return new Response(xml, { headers: { 'Content-Type': 'application/xml' } });
};
