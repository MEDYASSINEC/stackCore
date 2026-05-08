# Audit d'écart — StackCore vs documentation "AI BUILD INSTRUCTIONS"

Date d'audit: 2026-05-08 (UTC)

## Résumé exécutif

Le projet actuel est un **MVP Astro + Prisma + JWT + Zustand**, alors que la documentation cible décrit une plateforme **Astro SSR orientée Supabase + Nanostores + Stripe/CMI + Meili/Algolia + Resend**, avec une architecture de pages/composants beaucoup plus large.

➡️ Conclusion: la base actuelle est utilisable, mais **une grande partie de la documentation n'est pas encore appliquée**.

---

## Vérification par bloc

## 1) Stack technique

- **AstroJS SSR `output: 'server'` + adapter Node**: ❌ non appliqué.
  - `astro.config.mjs` ne configure ni `output: 'server'` ni `@astrojs/node`, seulement Tailwind Vite plugin.
- **Supabase (Auth, DB, Storage, RLS)**: ❌ non appliqué.
  - Les dépendances et le code montrent Prisma/JWT, pas de client Supabase.
- **Nanostores**: ❌ non appliqué.
  - Le panier est géré via Zustand persist.
- **DaisyUI**: ❌ non appliqué.
  - Aucune dépendance DaisyUI détectée.
- **Stripe + CMI/PayZone**: ❌ non appliqué.
  - Aucune dépendance Stripe/CMI et aucun service checkout correspondant.
- **MeiliSearch/Algolia**: ❌ non appliqué.
  - Aucune dépendance ou lib search dédiée.
- **Resend**: ❌ non appliqué.
  - Aucune dépendance ou module email transactionnel.

## 2) Architecture fichiers/pages

- **Layouts attendus** (`BaseLayout`, `ShopLayout`, `DashboardLayout`): ❌ non appliqué.
  - Le repo expose `MainLayout.astro` uniquement.
- **Organisation composants par domaines** (`ui/`, `navigation/`, `product/`, `catalog/`, `cart/`, etc.): ❌ non appliqué.
  - Les composants sont plats (`Header`, `Sidebar`, `ProductCard`).
- **Pages demandées** (`/catalogue`, `/produit/[slug]`, `/panier`, `/compte/*`, `/admin/*`, `/blog/*`, etc.): ⚠️ partiellement appliqué.
  - Des pages existent (`/products`, `/checkout`, `/profile`, auth), mais la structure URL demandée n'est pas alignée.

## 3) UI / style computeruniverse

- **Topbar + main nav + menu catégorie**: ✅ partiellement appliqué.
  - `Header.astro` contient déjà une topbar, une recherche centrale, et une barre catégories.
- **Densité compacte stricte (tailles, spacing, radius max 4px, grille 5-6 colonnes)**: ⚠️ partiellement appliqué.
  - Le home utilise encore des sections “premium” et de gros arrondis (`rounded-3xl`) contraires aux règles compactes.
- **Système CSS complet (`variables.css`, `components.css`)**: ❌ non appliqué tel que documenté.
  - Seul `global.css` est présent.

## 4) Données & modèle métier

- **Schéma PostgreSQL Supabase complet (profiles, addresses, suppliers, promotions, blog_posts, notifications, etc.)**: ❌ non appliqué.
  - Le projet utilise Prisma SQLite local (`prisma/dev.db`) avec modèle différent.
- **RLS + auth middleware Supabase**: ❌ non appliqué.
  - Auth basée JWT custom.

## 5) Fonctionnalités critiques

- **Panier Nanostore + sync Supabase**: ❌ non appliqué.
  - Panier Zustand local persist.
- **Checkout Stripe + CMI**: ❌ non appliqué.
- **Recherche avancée Meili/Algolia**: ❌ non appliqué.
- **Emails transactionnels Resend**: ❌ non appliqué.
- **Dropshipping automatique**: ❌ non appliqué.
- **Programme fidélité complet (tiers/règles/notifications)**: ⚠️ partiellement appliqué côté UI/API uniquement.
- **Recyclage avec upload storage et workflow complet**: ⚠️ partiellement appliqué.

## 6) Production

- **SEO/monitoring/performance pipeline (Sentry, image optimization cloud, etc.)**: ❌ majoritairement non appliqué.

---

## Ce qui est déjà en place (positif)

- Base Astro fonctionnelle avec scripts dev/build/check/lint.
- API backend structurée (auth, products, orders, recycling, profile, dashboard).
- RBAC/auth middleware custom existants.
- Header déjà proche du pattern computeruniverse (topbar + search + categories).

---

## Priorités recommandées (ordre pragmatique)

1. **Décision d'architecture**: confirmer officiellement “migration vers Supabase” ou “doc adaptée à Prisma/JWT”.
2. **Alignement SSR**: basculer `astro.config.mjs` vers `output: 'server'` + adapter node.
3. **Refonte structure pages/components**: adopter les routes cibles (`catalogue`, `produit/[slug]`, `compte/*`, `admin/*`).
4. **Système design compact**: remplacer styles premium (gros radius/spacing) par grille dense et cartes compactes.
5. **Panier + auth selon cible**: Nanostores + sessions Supabase.
6. **Paiement & search & email**: Stripe, CMI/PayZone, Meili/Algolia, Resend.
7. **Features avancées**: fidélité complète, dropshipping, blog, notifications.

