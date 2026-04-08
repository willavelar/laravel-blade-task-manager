# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Environment

All PHP/Artisan/Composer commands run inside Docker — never on the host directly:

```bash
docker compose up -d --build          # build and start containers
docker compose exec app php artisan <cmd>
docker compose run --rm app composer <cmd>
docker compose down
```

The app runs at **http://localhost:8080**. The `app` container uses an entrypoint (`docker/php/entrypoint.sh`) that fixes `storage/`, `bootstrap/cache/`, and `database/` permissions on every startup — this is necessary because bind-mounts create files as root but php-fpm runs as `www-data`.

**Frontend assets** require Node.js 20+ (not in the Docker image). Build on the host or via a one-off container:

```bash
npm run build                          # host (requires Node 20+)
npm run dev                            # host, watch mode

# or inside a container:
docker compose run --rm app bash -c "curl -fsSL https://deb.nodesource.com/setup_20.x | bash - && apt-get install -y nodejs && npm run build"
```

## Common Artisan Commands

```bash
# Database
docker compose exec app php artisan migrate
docker compose exec app php artisan migrate:fresh --seed   # reset + demo data
docker compose exec app php artisan db:seed

# Caches (clear after config/route/view changes)
docker compose exec app php artisan config:clear
docker compose exec app php artisan view:clear
docker compose exec app php artisan route:clear

# Scaffolding
docker compose exec app php artisan make:model ModelName
docker compose exec app php artisan make:controller NameController --resource
docker compose exec app php artisan make:request NameRequest
docker compose exec app php artisan make:migration create_table_name_table
```

## Architecture

### Data flow

`routes/web.php` → Controller → Form Request (validation) → Eloquent Model → Blade view

All routes except auth are inside `Route::middleware('auth')`. The `dashboard` route is an alias that redirects to `tasks.index` (required by Breeze's post-login redirect).

### Ownership enforcement

Controllers use a private `authorizeTask()` / `authorizeCategory()` method with `abort_if($model->user_id !== auth()->id(), 403)`. There are no Policies — the guard is inline. Both Form Requests also use `withValidator()` to verify that a submitted `category_id` belongs to the authenticated user.

### Models

- `Task` — has helper methods: `isCompleted()`, `isPending()`, `isDueToday()`, `isOverdue()`. The `due_date` field is cast to `\Carbon\Carbon` via `$casts`.
- `Category` — scoped to the user via queries; never queried globally.
- `User` — has `categories()` and `tasks()` hasMany relationships.

### Blade components

Custom x-components live in `resources/views/components/`. The app-specific ones are:

| Component | Purpose |
|---|---|
| `x-sidebar` | Fixed left nav; queries the user's categories on every render |
| `x-task-card` | Full task row including the PATCH toggle form |
| `x-badge` | Generic colored badge using inline hex styles (`color` prop) |
| `x-priority-badge` | Alta/Média/Baixa with hardcoded Tailwind color classes |
| `x-button` | `primary`/`secondary`/`danger` variants; renders `<a>` when `href` prop is set |
| `x-form-field` | Label + slot + `@error` display |
| `x-delete-form` | DELETE method form with `confirm()` dialog |

Breeze's shared components (`x-text-input`, `x-input-label`, `x-primary-button`) have been restyled to dark mode in `resources/views/components/`.

### Dark mode

Tailwind uses the `class` strategy (`darkMode: 'class'` in `tailwind.config.js`). The `<html>` element always has `class="dark"` — there is no light/dark toggle. Palette: `bg-slate-900` (page), `bg-slate-800` (cards/sidebar), violet-700 (primary actions).

### Database

SQLite only. File lives at `database/database.sqlite` (gitignored). The entrypoint ensures `www-data` owns this file on every container start. No separate DB container.

### Seeder data (demo)

`php artisan db:seed` creates: 1 user (`demo@taskmanager.com` / `password`), 5 categories (Trabalho, Pessoal, Estudo, Saúde, Finanças), 8 tasks across all categories and priorities.
