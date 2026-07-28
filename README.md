# Smart Guidance Tool — Monorepo

Luwinga Secondary School career guidance platform, split into a **Laravel backend** and a **Next.js frontend**.

## Project structure

```
SMART-GUIDANCE-TOOL-main/
├── backend/          # Laravel API + legacy Blade portal
│   ├── app/
│   ├── routes/
│   ├── resources/    # Blade views (existing UI)
│   └── ...
└── frontend/         # Next.js app (new UI)
    ├── src/
    └── package.json
```

## Prerequisites

- PHP 8.3+ with Composer
- Node.js 18+ with npm
- MySQL (or your configured database)

## Backend (Laravel)

```bash
cd backend
composer install          # first time only
cp .env.example .env      # first time only
php artisan key:generate  # first time only
php artisan migrate       # first time only
php artisan serve
```

Backend runs at **http://127.0.0.1:8000**

The existing Blade-based portal is still available at the backend URL while pages are migrated to Next.js.

Optional — compile Vite assets for Blade views:

```bash
cd backend
npm install
npm run dev
```

## Frontend (Next.js)

```bash
cd frontend
npm install               # first time only
cp .env.example .env.local
npm run dev
```

Frontend runs at **http://localhost:3000**

## Environment

| Variable | Location | Purpose |
|----------|----------|---------|
| `FRONTEND_URL` | `backend/.env` | CORS origin for Next.js |
| `NEXT_PUBLIC_API_URL` | `frontend/.env.local` | Laravel API base URL |

## API

- Health check: `GET http://127.0.0.1:8000/up` (Laravel built-in)
- JSON health: `GET http://127.0.0.1:8000/api/health`
