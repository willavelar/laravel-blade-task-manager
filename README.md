# Laravel Blade Task Manager

A personal task manager built with **Laravel 13** and **Blade** as a portfolio project demonstrating clean MVC architecture, Eloquent relationships, and Docker containerization.

**Live demo:** [laravel-blade-task-manager.onrender.com](https://laravel-blade-task-manager.onrender.com)

```
Email:    user@example.com
Password: user
```

![Laravel](https://img.shields.io/badge/Laravel-13.4-FF2D20?style=flat&logo=laravel&logoColor=white)
![PHP](https://img.shields.io/badge/PHP-8.3-777BB4?style=flat&logo=php&logoColor=white)
![Docker](https://img.shields.io/badge/Docker-ready-2496ED?style=flat&logo=docker&logoColor=white)
![License](https://img.shields.io/badge/license-MIT-green?style=flat)

---

## Features

- **Full CRUD** for tasks and categories
- **Eloquent relationships** — tasks belong to categories, both belong to the authenticated user
- **Form Request validation** with ownership checks (users only access their own data)
- **Authentication** via Laravel Breeze (session-based)
- **Reusable Blade x-components** — sidebar, badges, buttons, form fields, task cards
- **Task filters** — filter by status (pending/completed) and priority (low/medium/high)
- **Quick toggle** — mark tasks as done/pending without leaving the list
- **Dark mode UI** with Tailwind CSS (violet accent, slate palette)
- **Docker** — PHP 8.3 FPM + Nginx, SQLite, zero host dependencies

## Tech Stack

| Layer | Technology |
|---|---|
| Framework | Laravel 13.4 |
| Language | PHP 8.3 |
| Frontend | Blade + Tailwind CSS |
| Authentication | Laravel Breeze |
| Database | SQLite |
| Container | Docker (php-fpm + nginx:alpine) |
| Asset bundler | Vite 8 |

## Requirements

- Docker & Docker Compose

That's it. Node.js and PHP are not required on the host.

## Getting Started

### 1. Clone the repository

```bash
git clone git@github.com:willavelar/laravel-blade-task-manager.git
cd laravel-blade-task-manager
```

### 2. Configure environment

```bash
cp .env.example .env
```

The default `.env.example` is already configured for SQLite. No changes needed for local development.

### 3. Build and start containers

```bash
docker compose up -d --build
```

### 4. Install dependencies and set up the application

```bash
docker compose exec app composer install
docker compose exec app php artisan key:generate
docker compose exec app php artisan migrate --seed
```

The `--seed` flag creates a demo user and sample data.

### 5. Build frontend assets

```bash
# Requires Node.js 20+ on host
npm install && npm run build

# Or build inside the container (no Node required on host)
docker compose run --rm app bash -c "curl -fsSL https://deb.nodesource.com/setup_20.x | bash - && apt-get install -y nodejs && npm install && npm run build"
```

### 6. Access the application

Open **http://localhost:8080** in your browser.

**Demo credentials:**
```
Email:    user@example.com
Password: user
```

## Project Structure

```
app/
├── Http/
│   ├── Controllers/
│   │   ├── TaskController.php       # CRUD + toggle + filters
│   │   └── CategoryController.php   # CRUD
│   └── Requests/
│       ├── StoreTaskRequest.php
│       ├── UpdateTaskRequest.php
│       ├── StoreCategoryRequest.php
│       └── UpdateCategoryRequest.php
└── Models/
    ├── Task.php
    ├── Category.php
    └── User.php

resources/views/
├── components/
│   ├── sidebar.blade.php
│   ├── task-card.blade.php
│   ├── badge.blade.php
│   ├── priority-badge.blade.php
│   ├── button.blade.php
│   ├── form-field.blade.php
│   └── delete-form.blade.php
├── tasks/
│   ├── index.blade.php
│   ├── create.blade.php
│   └── edit.blade.php
└── categories/
    ├── index.blade.php
    ├── create.blade.php
    └── edit.blade.php

docker/
├── php/
│   ├── Dockerfile
│   └── entrypoint.sh
└── nginx/
    └── default.conf
```

## Database Schema

```
users
  └── id, name, email, password

categories
  └── id, user_id, name, color (#hex), icon (emoji)

tasks
  └── id, user_id, category_id, title, description,
      priority (low|medium|high), status (pending|completed), due_date
```

Relationships:
- `User` → hasMany → `Category`
- `User` → hasMany → `Task`
- `Category` → hasMany → `Task`
- `Task` → belongsTo → `Category`

## Useful Commands

```bash
# Stop containers
docker compose down

# Reset database with fresh seed data
docker compose exec app php artisan migrate:fresh --seed

# View application logs
docker compose logs -f app

# Run artisan commands
docker compose exec app php artisan <command>
```

## License

This project is open-sourced under the [MIT license](https://opensource.org/licenses/MIT).
