# StackCore

Application e-commerce professionnelle Astro + API REST intégrée.

## Stack
- Astro + TypeScript + TailwindCSS
- PostgreSQL + Prisma ORM
- JWT + bcrypt + Zod
- RBAC (Admin, Client, Supplier)

## Structure
- `src/components`
- `src/layouts`
- `src/pages`
- `src/pages/api`
- `src/services`
- `src/middlewares`
- `src/lib`
- `src/types`
- `src/utils`

## Installation
```bash
npm install
cp .env.example .env
npm run prisma:generate
npm run prisma:migrate
npm run prisma:seed
npm run dev
```

## Docker (ready)
Exemple de `DATABASE_URL` pointant vers un service postgres docker.

## Endpoints principaux
- `POST /api/auth/register`
- `GET/POST /api/products`
- `GET /api/orders`
- `POST /api/recycling`
- `GET /api/dashboard/kpis`

## Sécurité incluse
- Hash mot de passe bcrypt
- Validation Zod
- Middleware auth JWT
- Middleware RBAC
- Isolation des routes API sensibles

## Notes
- Stripe peut être intégré via `STRIPE_SECRET_KEY` (mock par défaut).
- Upload images: prévoir adaptateur S3/Cloudinary en production.
