# Audit d'écart — StackCore vs documentation "AI BUILD INSTRUCTIONS"

Date d'audit: 2026-05-08

## Résumé

- **Globalement appliqué partiellement**: le projet contient une base Astro + API + auth JWT/Prisma, mais il est **loin** de la cible décrite (Supabase SSR, nanostores, architecture pages/composants complète, paiements Stripe+CMI, recherche Meili/Algolia, etc.).
- **Constat principal**: l'implémentation actuelle correspond davantage à un MVP REST Astro/Prisma qu'au blueprint e-commerce StackCore complet.

## Écarts majeurs (NON appliqué)

## 1) Stack technique
- Supabase (Auth, DB, Storage, RLS): **non appliqué** (stack actuelle Prisma + JWT).
- Nanostores: **non appliqué** (stores Zustand persist).
- DaisyUI: **non appliqué**.
- Stripe + CMI/PayZone: **non appliqué** (checkout présent mais sans intégration documentée).
- MeiliSearch/Algolia: **non appliqué**.
- Resend: **non appliqué**.
- Adapter SSR Astro `output: 'server'` + `@astrojs/node`: **non appliqué**.

## 2) Architecture Astro attendue
- Layouts attendus `BaseLayout`, `ShopLayout`, `DashboardLayout`: **non appliqué** (seul `MainLayout`).
- Sous-dossiers composants `ui/`, `navigation/`, `product/`, `catalog/`, `cart/`, `home/`, `admin/`: **non appliqué**.
- Routes publiques prévues (`catalogue`, `produit/[slug]`, `panier`, `compte/*`, `admin/*`, `blog/*`, etc.): **majoritairement non appliqué**.

## 3) UI "computeruniverse" demandée
- Topbar + Main Nav + Mega menu 3 niveaux: **non appliqué**.
- Grille catalogue dense 5-6 colonnes desktop + sidebar filtres fixe: **non appliqué**.
- Système de variables CSS proposé (`variables.css`) et conventions visuelles détaillées: **partiellement non appliqué**.

## 4) Base de données attendue
- Schéma Supabase complet (profiles, addresses, categories, suppliers, promotions, blog_posts, notifications, etc.): **non appliqué**.
- Modèle actuel différent (Prisma local `dev.db`) et couverture fonctionnelle inférieure au schéma cible.

## 5) Flux métier avancés
- Middleware Astro avec session Supabase + protection `/admin` & `/compte`: **non appliqué** (middleware custom JWT côté API).
- Recyclage avec upload Storage et workflow complet: **partiellement non appliqué**.
- Dropshipping automatique fournisseur: **non appliqué**.
- Programme fidélité tiers/règles complet: **partiellement non appliqué**.
- Notifications transactionnelles et emails Resend: **non appliqué**.

## 6) Production & observabilité
- Monitoring Sentry: **non appliqué**.
- Optimisation images (Cloudinary/Supabase + sharp pipeline): **non appliqué / partiel**.
- Déploiement documenté Vercel/Netlify Edge: **non appliqué**.

## Ce qui semble appliqué (ou partiellement)

- Astro + Tailwind présents.
- API backend structurée (`auth`, `products`, `orders`, `recycling`, `dashboard`).
- Authentification avec JWT + RBAC (mais différente de Supabase Auth).
- Page de recyclage et section profil/fidélité existantes en base.

## Priorités recommandées pour alignement

1. **Décider la stack source de vérité**: garder Prisma/JWT ou migrer vers Supabase (doc cible = Supabase).
2. Mettre à jour `astro.config.mjs` vers SSR server + adapter node.
3. Introduire l'architecture de dossiers/pages cible (navigation, catalogue, produit, compte, admin, blog).
4. Implémenter d'abord les composants critiques: `Topbar`, `Navbar`, `MegaMenu`, `ProductCard` compacte.
5. Ajouter paiements (Stripe puis CMI), recherche (MeiliSearch), emails (Resend).
6. Finaliser fidélité/recyclage/dropshipping selon le schéma documenté.

