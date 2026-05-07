# StackCore

Plateforme e-commerce durable et professionnelle, construite avec **Astro + TypeScript** et une API REST intégrée.

## Fonctionnalités
- Authentification JWT (access + refresh), hash bcrypt, validation Zod
- RBAC avancé: **Admin**, **Client**, **Fournisseur**
- Catalogue produits: CRUD, recherche, filtres, pagination
- Commandes: checkout + paiement simulé
- Recyclage: création de demandes, suivi, points fidélité
- Dashboard KPI pour administration
- Design responsive moderne (Tailwind, dark mode)
- SEO: sitemap + robots.txt

## Architecture
```
src/
  components/
  layouts/
  pages/
    api/
  services/
  middlewares/
  lib/
  types/
  utils/
```

## Prérequis
- Node.js >= 22.12
- PostgreSQL 15+

## Installation rapide
```bash
npm install
cp .env.example .env
npm run prisma:generate
npm run prisma:migrate
npm run prisma:seed
npm run dev
```

## Docker
```bash
docker compose up -d
```

## Scripts utiles
- `npm run dev` : développement
- `npm run build` : build production
- `npm run check` : vérification Astro/TS
- `npm run prisma:generate`
- `npm run prisma:migrate`
- `npm run prisma:seed`

## Endpoints principaux
- `POST /api/auth/register`
- `POST /api/auth/login`
- `POST /api/auth/refresh`
- `GET|POST /api/products`
- `GET|POST /api/orders`
- `GET|POST /api/recycling`
- `GET /api/dashboard/kpis`

## Sécurité
- Validation systématique backend (Zod)
- Contrôle d'accès rôle par rôle
- Hash des mots de passe
- JWT signés + rotation via refresh token
- Messages d'erreur API homogènes

## Variables d'environnement
Voir `.env.example`.
