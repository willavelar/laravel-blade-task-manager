# Task Manager — Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Build a personal task manager (to-do with categories) in Laravel 13.4.0 + Blade, containerized with Docker, as a portfolio project demonstrating CRUD, Eloquent relationships, Form Requests, Breeze auth, and x-components.

**Architecture:** Two Resource Controllers (TaskController, CategoryController) own all CRUD logic. UI is composed from reusable Blade x-components on top of a dark-mode sidebar layout. All data is scoped to the authenticated user via Eloquent query scoping.

**Tech Stack:** Laravel 13.4.0, PHP 8.3, Blade x-components, Tailwind CSS (via Breeze), SQLite, Docker (php-fpm + nginx), Laravel Breeze

---

## File Map

```
# Infrastructure
docker-compose.yml
docker/php/Dockerfile
docker/nginx/default.conf

# Config
.env.example                                        (modify: SQLite paths)
tailwind.config.js                                  (modify: dark mode class strategy)
vite.config.js                                      (already correct from Breeze)

# Migrations
database/migrations/xxxx_create_categories_table.php
database/migrations/xxxx_create_tasks_table.php

# Models
app/Models/Category.php
app/Models/Task.php
app/Models/User.php                                 (modify: add relationships)

# Seeders
database/seeders/UserSeeder.php
database/seeders/CategorySeeder.php
database/seeders/TaskSeeder.php
database/seeders/DatabaseSeeder.php                 (modify)

# Form Requests
app/Http/Requests/StoreTaskRequest.php
app/Http/Requests/UpdateTaskRequest.php
app/Http/Requests/StoreCategoryRequest.php
app/Http/Requests/UpdateCategoryRequest.php

# Controllers
app/Http/Controllers/TaskController.php
app/Http/Controllers/CategoryController.php

# Routes
routes/web.php                                      (modify)

# Blade Components
resources/views/components/sidebar.blade.php
resources/views/components/task-card.blade.php
resources/views/components/badge.blade.php
resources/views/components/priority-badge.blade.php
resources/views/components/form-field.blade.php
resources/views/components/button.blade.php
resources/views/components/delete-form.blade.php

# Layouts
resources/views/layouts/app.blade.php               (modify: dark sidebar layout)

# Task Views
resources/views/tasks/index.blade.php
resources/views/tasks/create.blade.php
resources/views/tasks/edit.blade.php

# Category Views
resources/views/categories/index.blade.php
resources/views/categories/create.blade.php
resources/views/categories/edit.blade.php

# Auth Views (dark theme overrides)
resources/views/auth/login.blade.php                (modify)
resources/views/auth/register.blade.php             (modify)
```

---

## Task 1: Docker Infrastructure

**Files:**
- Create: `docker-compose.yml`
- Create: `docker/php/Dockerfile`
- Create: `docker/nginx/default.conf`

- [ ] **Step 1: Create PHP Dockerfile**

```dockerfile
# docker/php/Dockerfile
FROM php:8.3-fpm

RUN apt-get update && apt-get install -y \
    git curl zip unzip libzip-dev libpng-dev libonig-dev libxml2-dev \
    && docker-php-ext-install pdo pdo_mysql zip mbstring exif pcntl bcmath \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

RUN chown -R www-data:www-data /var/www/html
```

- [ ] **Step 2: Create nginx config**

```nginx
# docker/nginx/default.conf
server {
    listen 80;
    server_name localhost;
    root /var/www/html/public;
    index index.php index.html;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass app:9000;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

- [ ] **Step 3: Create docker-compose.yml**

```yaml
# docker-compose.yml
services:
  app:
    build:
      context: .
      dockerfile: docker/php/Dockerfile
    container_name: taskmanager_app
    volumes:
      - .:/var/www/html
    environment:
      - APP_ENV=local
    networks:
      - taskmanager

  nginx:
    image: nginx:alpine
    container_name: taskmanager_nginx
    ports:
      - "8080:80"
    volumes:
      - .:/var/www/html
      - ./docker/nginx/default.conf:/etc/nginx/conf.d/default.conf
    depends_on:
      - app
    networks:
      - taskmanager

networks:
  taskmanager:
    driver: bridge
```

- [ ] **Step 4: Build the app image**

```bash
docker compose build
```

Expected: image `taskmanager_app` built successfully, no errors.

- [ ] **Step 5: Commit**

```bash
git init
git add docker-compose.yml docker/
git commit -m "feat: add Docker infrastructure (php-fpm 8.3 + nginx)"
```

---

## Task 2: Scaffold Laravel + Breeze

**Files:**
- Create: all Laravel scaffold files (via composer)
- Modify: `.env` for SQLite

- [ ] **Step 1: Scaffold Laravel 13 project**

```bash
docker compose run --rm app composer create-project laravel/laravel:^13.0 /tmp/laravel_tmp
docker compose run --rm app bash -c "cp -r /tmp/laravel_tmp/. /var/www/html/ && rm -rf /tmp/laravel_tmp"
```

If that fails due to non-empty directory, use:
```bash
docker compose run --rm app composer create-project laravel/laravel:^13.0 . --no-interaction
```

- [ ] **Step 2: Configure SQLite in .env**

Edit `.env` (file will exist now):
```env
APP_NAME="Task Manager"
APP_ENV=local
APP_KEY=         # will be generated
APP_DEBUG=true
APP_URL=http://localhost:8080

DB_CONNECTION=sqlite
# Remove all other DB_* lines
```

Also edit `.env.example` with the same DB block.

Create the SQLite file:
```bash
docker compose run --rm app touch database/database.sqlite
```

- [ ] **Step 3: Generate app key**

```bash
docker compose run --rm app php artisan key:generate
```

Expected: `Application key set successfully.`

- [ ] **Step 4: Install Breeze**

```bash
docker compose run --rm app composer require laravel/breeze --dev
docker compose run --rm app php artisan breeze:install blade --dark
```

When prompted for dark mode: select yes (or use `--dark` flag).

- [ ] **Step 5: Install Node dependencies and build assets**

```bash
docker compose run --rm -w /var/www/html app bash -c "curl -fsSL https://deb.nodesource.com/setup_20.x | bash - && apt-get install -y nodejs && npm install && npm run build"
```

Or if Node is available on host:
```bash
npm install && npm run build
```

- [ ] **Step 6: Run initial migrations**

```bash
docker compose run --rm app php artisan migrate
```

Expected: migrations for `users`, `password_reset_tokens`, `sessions`, `cache`, `jobs` run OK.

- [ ] **Step 7: Start containers and verify**

```bash
docker compose up -d
```

Open `http://localhost:8080` — Laravel welcome page should appear.

- [ ] **Step 8: Commit**

```bash
git add .
git commit -m "feat: scaffold Laravel 13 with Breeze (Blade + dark mode)"
```

---

## Task 3: Dark Theme + Tailwind Config

**Files:**
- Modify: `tailwind.config.js`
- Modify: `resources/views/layouts/app.blade.php`
- Modify: `resources/css/app.css`

- [ ] **Step 1: Enable Tailwind dark class strategy**

Edit `tailwind.config.js`:
```js
import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    darkMode: 'class',
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
    ],
    theme: {
        extend: {
            fontFamily: {
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
            },
        },
    },
    plugins: [forms],
};
```

- [ ] **Step 2: Add dark class to html element and global dark background**

Edit `resources/views/layouts/app.blade.php`.

Replace the `<html>` opening tag with:
```html
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
```

Replace `<body class="...">` with:
```html
<body class="font-sans antialiased bg-slate-900 text-slate-100">
```

- [ ] **Step 3: Add base dark styles to app.css**

Edit `resources/css/app.css`:
```css
@import 'tailwindcss/base';
@import 'tailwindcss/components';
@import 'tailwindcss/utilities';

/* Ensure select dropdowns respect dark bg */
@layer base {
    select option {
        background-color: #1e293b;
        color: #e2e8f0;
    }
}
```

- [ ] **Step 4: Rebuild assets**

```bash
docker compose run --rm app npm run build
```

Or if Node on host: `npm run build`

- [ ] **Step 5: Commit**

```bash
git add tailwind.config.js resources/css/app.css resources/views/layouts/
git commit -m "feat: configure Tailwind dark mode (class strategy)"
```

---

## Task 4: Migrations

**Files:**
- Create: `database/migrations/xxxx_create_categories_table.php`
- Create: `database/migrations/xxxx_create_tasks_table.php`

- [ ] **Step 1: Create categories migration**

```bash
docker compose run --rm app php artisan make:migration create_categories_table
```

Edit the generated file in `database/migrations/`:
```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('name', 100);
            $table->string('color', 7)->default('#7c3aed'); // hex color
            $table->string('icon', 10)->default('📁');      // emoji
            $table->timestamps();

            $table->unique(['user_id', 'name']); // name unique per user
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('categories');
    }
};
```

- [ ] **Step 2: Create tasks migration**

```bash
docker compose run --rm app php artisan make:migration create_tasks_table
```

Edit the generated file:
```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('category_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->enum('priority', ['low', 'medium', 'high'])->default('medium');
            $table->enum('status', ['pending', 'completed'])->default('pending');
            $table->date('due_date')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tasks');
    }
};
```

- [ ] **Step 3: Run migrations**

```bash
docker compose run --rm app php artisan migrate
```

Expected output:
```
  categories ..... 0.xx ms DONE
  tasks .......... 0.xx ms DONE
```

- [ ] **Step 4: Commit**

```bash
git add database/migrations/
git commit -m "feat: add categories and tasks migrations"
```

---

## Task 5: Models

**Files:**
- Create: `app/Models/Category.php`
- Create: `app/Models/Task.php`
- Modify: `app/Models/User.php`

- [ ] **Step 1: Create Category model**

```bash
docker compose run --rm app php artisan make:model Category
```

Replace `app/Models/Category.php`:
```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'name', 'color', 'icon'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class);
    }
}
```

- [ ] **Step 2: Create Task model**

```bash
docker compose run --rm app php artisan make:model Task
```

Replace `app/Models/Task.php`:
```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Task extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'category_id',
        'title',
        'description',
        'priority',
        'status',
        'due_date',
    ];

    protected $casts = [
        'due_date' => 'date',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isDueToday(): bool
    {
        return $this->due_date?->isToday() ?? false;
    }

    public function isOverdue(): bool
    {
        return $this->due_date?->isPast() && $this->isPending() ?? false;
    }
}
```

- [ ] **Step 3: Add relationships to User model**

Open `app/Models/User.php`. Add these two methods inside the class (after the existing `$hidden` property):
```php
use Illuminate\Database\Eloquent\Relations\HasMany;

public function categories(): HasMany
{
    return $this->hasMany(Category::class);
}

public function tasks(): HasMany
{
    return $this->hasMany(Task::class);
}
```

Also add the `use HasMany;` import at the top of the file if not already present.

- [ ] **Step 4: Commit**

```bash
git add app/Models/
git commit -m "feat: add Category and Task models with relationships"
```

---

## Task 6: Seeders

**Files:**
- Create: `database/seeders/UserSeeder.php`
- Create: `database/seeders/CategorySeeder.php`
- Create: `database/seeders/TaskSeeder.php`
- Modify: `database/seeders/DatabaseSeeder.php`

- [ ] **Step 1: Create UserSeeder**

```bash
docker compose run --rm app php artisan make:seeder UserSeeder
```

Edit `database/seeders/UserSeeder.php`:
```php
<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::create([
            'name' => 'Demo User',
            'email' => 'demo@taskmanager.com',
            'password' => Hash::make('password'),
        ]);
    }
}
```

- [ ] **Step 2: Create CategorySeeder**

```bash
docker compose run --rm app php artisan make:seeder CategorySeeder
```

Edit `database/seeders/CategorySeeder.php`:
```php
<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\User;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $user = User::first();

        $categories = [
            ['name' => 'Trabalho',  'color' => '#3b82f6', 'icon' => '💼'],
            ['name' => 'Pessoal',   'color' => '#22c55e', 'icon' => '🏠'],
            ['name' => 'Estudo',    'color' => '#eab308', 'icon' => '📚'],
            ['name' => 'Saúde',     'color' => '#ef4444', 'icon' => '❤️'],
            ['name' => 'Finanças',  'color' => '#8b5cf6', 'icon' => '💰'],
        ];

        foreach ($categories as $category) {
            $user->categories()->create($category);
        }
    }
}
```

- [ ] **Step 3: Create TaskSeeder**

```bash
docker compose run --rm app php artisan make:seeder TaskSeeder
```

Edit `database/seeders/TaskSeeder.php`:
```php
<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Seeder;

class TaskSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::first();
        $categories = $user->categories()->pluck('id', 'name');

        $tasks = [
            [
                'title' => 'Revisar relatório mensal',
                'description' => 'Verificar números de março e preparar apresentação para o gerente.',
                'category_id' => $categories['Trabalho'],
                'priority' => 'high',
                'status' => 'pending',
                'due_date' => now()->addDays(2)->toDateString(),
            ],
            [
                'title' => 'Responder e-mails pendentes',
                'description' => null,
                'category_id' => $categories['Trabalho'],
                'priority' => 'medium',
                'status' => 'pending',
                'due_date' => now()->addDay()->toDateString(),
            ],
            [
                'title' => 'Estudar para certificação Laravel',
                'description' => 'Cobrir os capítulos de Queues e Events.',
                'category_id' => $categories['Estudo'],
                'priority' => 'medium',
                'status' => 'pending',
                'due_date' => now()->addDays(7)->toDateString(),
            ],
            [
                'title' => 'Ler livro Clean Code',
                'description' => null,
                'category_id' => $categories['Estudo'],
                'priority' => 'low',
                'status' => 'pending',
                'due_date' => null,
            ],
            [
                'title' => 'Comprar mantimentos',
                'description' => 'Arroz, feijão, azeite, ovos.',
                'category_id' => $categories['Pessoal'],
                'priority' => 'low',
                'status' => 'completed',
                'due_date' => now()->subDay()->toDateString(),
            ],
            [
                'title' => 'Academia — treino de peito',
                'description' => null,
                'category_id' => $categories['Saúde'],
                'priority' => 'medium',
                'status' => 'pending',
                'due_date' => now()->toDateString(),
            ],
            [
                'title' => 'Pagar fatura do cartão',
                'description' => null,
                'category_id' => $categories['Finanças'],
                'priority' => 'high',
                'status' => 'pending',
                'due_date' => now()->addDays(3)->toDateString(),
            ],
            [
                'title' => 'Configurar backup automático do PC',
                'description' => null,
                'category_id' => $categories['Pessoal'],
                'priority' => 'low',
                'status' => 'completed',
                'due_date' => null,
            ],
        ];

        foreach ($tasks as $task) {
            $user->tasks()->create($task);
        }
    }
}
```

- [ ] **Step 4: Update DatabaseSeeder**

Edit `database/seeders/DatabaseSeeder.php`:
```php
<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            UserSeeder::class,
            CategorySeeder::class,
            TaskSeeder::class,
        ]);
    }
}
```

- [ ] **Step 5: Run seeders**

```bash
docker compose run --rm app php artisan db:seed
```

Expected:
```
  UserSeeder ........ RUNNING
  UserSeeder ........ DONE
  CategorySeeder .... RUNNING
  CategorySeeder .... DONE
  TaskSeeder ........ RUNNING
  TaskSeeder ........ DONE
```

- [ ] **Step 6: Commit**

```bash
git add database/seeders/
git commit -m "feat: add seeders with demo user, 5 categories, and 8 tasks"
```

---

## Task 7: Form Requests

**Files:**
- Create: `app/Http/Requests/StoreCategoryRequest.php`
- Create: `app/Http/Requests/UpdateCategoryRequest.php`
- Create: `app/Http/Requests/StoreTaskRequest.php`
- Create: `app/Http/Requests/UpdateTaskRequest.php`

- [ ] **Step 1: Create StoreCategoryRequest**

```bash
docker compose run --rm app php artisan make:request StoreCategoryRequest
```

Edit `app/Http/Requests/StoreCategoryRequest.php`:
```php
<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'max:100',
                Rule::unique('categories')->where('user_id', $this->user()->id),
            ],
            'color' => ['required', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'icon'  => ['required', 'string', 'max:10'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.unique' => 'Você já tem uma categoria com esse nome.',
            'color.regex' => 'A cor deve ser um código hexadecimal válido (ex: #3b82f6).',
        ];
    }
}
```

- [ ] **Step 2: Create UpdateCategoryRequest**

```bash
docker compose run --rm app php artisan make:request UpdateCategoryRequest
```

Edit `app/Http/Requests/UpdateCategoryRequest.php`:
```php
<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'max:100',
                Rule::unique('categories')
                    ->where('user_id', $this->user()->id)
                    ->ignore($this->route('category')),
            ],
            'color' => ['required', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'icon'  => ['required', 'string', 'max:10'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.unique' => 'Você já tem uma categoria com esse nome.',
            'color.regex' => 'A cor deve ser um código hexadecimal válido (ex: #3b82f6).',
        ];
    }
}
```

- [ ] **Step 3: Create StoreTaskRequest**

```bash
docker compose run --rm app php artisan make:request StoreTaskRequest
```

Edit `app/Http/Requests/StoreTaskRequest.php`:
```php
<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreTaskRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title'       => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'category_id' => ['required', 'exists:categories,id'],
            'priority'    => ['required', 'in:low,medium,high'],
            'due_date'    => ['nullable', 'date', 'after_or_equal:today'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $categoryId = $this->input('category_id');
            if ($categoryId) {
                $belongs = $this->user()->categories()
                    ->where('id', $categoryId)
                    ->exists();

                if (! $belongs) {
                    $validator->errors()->add('category_id', 'Categoria inválida.');
                }
            }
        });
    }

    public function messages(): array
    {
        return [
            'title.required'       => 'O título é obrigatório.',
            'category_id.required' => 'Selecione uma categoria.',
            'category_id.exists'   => 'Categoria inválida.',
            'priority.in'          => 'Prioridade inválida.',
            'due_date.after_or_equal' => 'A data de vencimento não pode ser no passado.',
        ];
    }
}
```

- [ ] **Step 4: Create UpdateTaskRequest**

```bash
docker compose run --rm app php artisan make:request UpdateTaskRequest
```

Edit `app/Http/Requests/UpdateTaskRequest.php`:
```php
<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTaskRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title'       => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'category_id' => ['required', 'exists:categories,id'],
            'priority'    => ['required', 'in:low,medium,high'],
            'status'      => ['required', 'in:pending,completed'],
            'due_date'    => ['nullable', 'date'],  // no after_or_equal — allow editing past tasks
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $categoryId = $this->input('category_id');
            if ($categoryId) {
                $belongs = $this->user()->categories()
                    ->where('id', $categoryId)
                    ->exists();

                if (! $belongs) {
                    $validator->errors()->add('category_id', 'Categoria inválida.');
                }
            }
        });
    }

    public function messages(): array
    {
        return [
            'title.required'       => 'O título é obrigatório.',
            'category_id.required' => 'Selecione uma categoria.',
            'category_id.exists'   => 'Categoria inválida.',
            'priority.in'          => 'Prioridade inválida.',
            'status.in'            => 'Status inválido.',
        ];
    }
}
```

- [ ] **Step 5: Commit**

```bash
git add app/Http/Requests/
git commit -m "feat: add Form Requests for tasks and categories with ownership validation"
```

---

## Task 8: Routes

**Files:**
- Modify: `routes/web.php`

- [ ] **Step 1: Replace routes/web.php**

```php
<?php

use App\Http\Controllers\CategoryController;
use App\Http\Controllers\TaskController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('tasks.index');
});

Route::middleware('auth')->group(function () {
    Route::resource('tasks', TaskController::class);
    Route::patch('tasks/{task}/toggle', [TaskController::class, 'toggle'])
        ->name('tasks.toggle');

    Route::resource('categories', CategoryController::class);
});

require __DIR__.'/auth.php';
```

- [ ] **Step 2: Verify routes**

```bash
docker compose run --rm app php artisan route:list --path=tasks
docker compose run --rm app php artisan route:list --path=categories
```

Expected: routes for index, create, store, show (skipped), edit, update, destroy + toggle.

- [ ] **Step 3: Commit**

```bash
git add routes/web.php
git commit -m "feat: define task and category resource routes with auth middleware"
```

---

## Task 9: CategoryController

**Files:**
- Create: `app/Http/Controllers/CategoryController.php`

- [ ] **Step 1: Create controller**

```bash
docker compose run --rm app php artisan make:controller CategoryController --resource
```

- [ ] **Step 2: Implement full controller**

Replace `app/Http/Controllers/CategoryController.php`:
```php
<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCategoryRequest;
use App\Http\Requests\UpdateCategoryRequest;
use App\Models\Category;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class CategoryController extends Controller
{
    public function index(): View
    {
        $categories = auth()->user()
            ->categories()
            ->withCount('tasks')
            ->orderBy('name')
            ->get();

        return view('categories.index', compact('categories'));
    }

    public function create(): View
    {
        return view('categories.create');
    }

    public function store(StoreCategoryRequest $request): RedirectResponse
    {
        auth()->user()->categories()->create($request->validated());

        return redirect()
            ->route('categories.index')
            ->with('success', 'Categoria criada com sucesso!');
    }

    public function edit(Category $category): View
    {
        $this->authorizeCategory($category);

        return view('categories.edit', compact('category'));
    }

    public function update(UpdateCategoryRequest $request, Category $category): RedirectResponse
    {
        $this->authorizeCategory($category);

        $category->update($request->validated());

        return redirect()
            ->route('categories.index')
            ->with('success', 'Categoria atualizada com sucesso!');
    }

    public function destroy(Category $category): RedirectResponse
    {
        $this->authorizeCategory($category);

        $category->delete();

        return redirect()
            ->route('categories.index')
            ->with('success', 'Categoria removida. Tarefas associadas ficaram sem categoria.');
    }

    private function authorizeCategory(Category $category): void
    {
        abort_if($category->user_id !== auth()->id(), 403);
    }
}
```

- [ ] **Step 3: Commit**

```bash
git add app/Http/Controllers/CategoryController.php
git commit -m "feat: implement CategoryController with full CRUD and ownership check"
```

---

## Task 10: TaskController

**Files:**
- Create: `app/Http/Controllers/TaskController.php`

- [ ] **Step 1: Create controller**

```bash
docker compose run --rm app php artisan make:controller TaskController --resource
```

- [ ] **Step 2: Implement full controller**

Replace `app/Http/Controllers/TaskController.php`:
```php
<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTaskRequest;
use App\Http\Requests\UpdateTaskRequest;
use App\Models\Task;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class TaskController extends Controller
{
    public function index(): View
    {
        $user = auth()->user();

        $query = $user->tasks()->with('category');

        // Filters via query string
        if (request('status')) {
            $query->where('status', request('status'));
        }

        if (request('priority')) {
            $query->where('priority', request('priority'));
        }

        if (request('category')) {
            $query->where('category_id', request('category'));
        }

        $tasks = $query->orderByRaw("CASE status WHEN 'pending' THEN 0 ELSE 1 END")
            ->orderByRaw("CASE priority WHEN 'high' THEN 0 WHEN 'medium' THEN 1 ELSE 2 END")
            ->orderBy('due_date')
            ->get();

        $categories = $user->categories()->orderBy('name')->get();

        return view('tasks.index', compact('tasks', 'categories'));
    }

    public function create(): View
    {
        $categories = auth()->user()->categories()->orderBy('name')->get();

        return view('tasks.create', compact('categories'));
    }

    public function store(StoreTaskRequest $request): RedirectResponse
    {
        auth()->user()->tasks()->create($request->validated());

        return redirect()
            ->route('tasks.index')
            ->with('success', 'Tarefa criada com sucesso!');
    }

    public function edit(Task $task): View
    {
        $this->authorizeTask($task);

        $categories = auth()->user()->categories()->orderBy('name')->get();

        return view('tasks.edit', compact('task', 'categories'));
    }

    public function update(UpdateTaskRequest $request, Task $task): RedirectResponse
    {
        $this->authorizeTask($task);

        $task->update($request->validated());

        return redirect()
            ->route('tasks.index')
            ->with('success', 'Tarefa atualizada com sucesso!');
    }

    public function destroy(Task $task): RedirectResponse
    {
        $this->authorizeTask($task);

        $task->delete();

        return redirect()
            ->route('tasks.index')
            ->with('success', 'Tarefa removida com sucesso!');
    }

    public function toggle(Task $task): RedirectResponse
    {
        $this->authorizeTask($task);

        $task->update([
            'status' => $task->isCompleted() ? 'pending' : 'completed',
        ]);

        return redirect()->back()->with('success', 'Status atualizado!');
    }

    private function authorizeTask(Task $task): void
    {
        abort_if($task->user_id !== auth()->id(), 403);
    }
}
```

- [ ] **Step 3: Commit**

```bash
git add app/Http/Controllers/TaskController.php
git commit -m "feat: implement TaskController with CRUD, filters, and toggle action"
```

---

## Task 11: Blade Components

**Files:**
- Create: `resources/views/components/sidebar.blade.php`
- Create: `resources/views/components/badge.blade.php`
- Create: `resources/views/components/priority-badge.blade.php`
- Create: `resources/views/components/button.blade.php`
- Create: `resources/views/components/form-field.blade.php`
- Create: `resources/views/components/delete-form.blade.php`
- Create: `resources/views/components/task-card.blade.php`

- [ ] **Step 1: Create sidebar component**

Create `resources/views/components/sidebar.blade.php`:
```blade
@php
    $categories = auth()->user()->categories()->orderBy('name')->get();
    $navLinks = [
        ['route' => 'tasks.index', 'label' => 'Todas as Tarefas', 'icon' => '📋'],
        ['route' => 'tasks.index', 'label' => 'Concluídas', 'icon' => '✅', 'params' => ['status' => 'completed']],
        ['route' => 'categories.index', 'label' => 'Categorias', 'icon' => '📁'],
    ];
@endphp

<aside class="fixed inset-y-0 left-0 w-56 bg-slate-800 border-r border-slate-700 flex flex-col z-10">
    {{-- Brand --}}
    <div class="px-4 py-5 border-b border-slate-700">
        <span class="text-violet-400 font-bold text-lg tracking-wide">⚡ TaskManager</span>
    </div>

    {{-- Main nav --}}
    <nav class="flex-1 overflow-y-auto px-3 py-4 space-y-1">
        @foreach($navLinks as $link)
            @php
                $params = $link['params'] ?? [];
                $isActive = request()->routeIs($link['route']) && 
                            collect($params)->every(fn($v, $k) => request($k) == $v);
            @endphp
            <a href="{{ route($link['route'], $params) }}"
               class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm transition-colors
                      {{ $isActive ? 'bg-violet-900/50 text-violet-300' : 'text-slate-400 hover:bg-slate-700 hover:text-slate-100' }}">
                <span>{{ $link['icon'] }}</span>
                <span>{{ $link['label'] }}</span>
            </a>
        @endforeach

        {{-- Categories section --}}
        @if($categories->isNotEmpty())
            <div class="pt-4">
                <p class="px-3 text-xs font-semibold text-slate-500 uppercase tracking-widest mb-2">
                    Minhas Categorias
                </p>
                @foreach($categories as $category)
                    <a href="{{ route('tasks.index', ['category' => $category->id]) }}"
                       class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm text-slate-400 hover:bg-slate-700 hover:text-slate-100 transition-colors">
                        <span class="w-2 h-2 rounded-full flex-shrink-0"
                              style="background-color: {{ $category->color }}"></span>
                        <span>{{ $category->icon }} {{ $category->name }}</span>
                    </a>
                @endforeach
            </div>
        @endif
    </nav>

    {{-- User footer --}}
    <div class="px-4 py-4 border-t border-slate-700">
        <div class="flex items-center gap-2 text-sm text-slate-400">
            <span class="w-7 h-7 rounded-full bg-violet-700 flex items-center justify-center text-white text-xs font-bold">
                {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
            </span>
            <span class="truncate">{{ auth()->user()->name }}</span>
        </div>
        <form method="POST" action="{{ route('logout') }}" class="mt-2">
            @csrf
            <button type="submit" class="text-xs text-slate-500 hover:text-slate-300 transition-colors">
                Sair →
            </button>
        </form>
    </div>
</aside>
```

- [ ] **Step 2: Create badge component**

Create `resources/views/components/badge.blade.php`:
```blade
@props(['color' => '#6b7280'])

@php
    // Map hex color to approximate Tailwind-compatible inline style
    $bg = $color . '22'; // 13% opacity
@endphp

<span {{ $attributes->merge(['class' => 'inline-flex items-center px-2 py-0.5 rounded text-xs font-medium']) }}
      style="background-color: {{ $bg }}; color: {{ $color }}; border: 1px solid {{ $color }}33">
    {{ $slot }}
</span>
```

- [ ] **Step 3: Create priority-badge component**

Create `resources/views/components/priority-badge.blade.php`:
```blade
@props(['priority'])

@php
    $config = match($priority) {
        'high'   => ['label' => 'Alta',  'class' => 'bg-red-900/40 text-red-400 border border-red-800'],
        'medium' => ['label' => 'Média', 'class' => 'bg-yellow-900/40 text-yellow-400 border border-yellow-800'],
        'low'    => ['label' => 'Baixa', 'class' => 'bg-green-900/40 text-green-400 border border-green-800'],
        default  => ['label' => $priority, 'class' => 'bg-slate-700 text-slate-400'],
    };
@endphp

<span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium {{ $config['class'] }}">
    {{ $config['label'] }}
</span>
```

- [ ] **Step 4: Create button component**

Create `resources/views/components/button.blade.php`:
```blade
@props([
    'variant' => 'primary',
    'type' => 'button',
    'href' => null,
])

@php
    $classes = match($variant) {
        'primary'   => 'bg-violet-700 hover:bg-violet-600 text-white',
        'secondary' => 'bg-slate-700 hover:bg-slate-600 text-slate-100 border border-slate-600',
        'danger'    => 'bg-red-900/60 hover:bg-red-800 text-red-300 border border-red-800',
        default     => 'bg-violet-700 hover:bg-violet-600 text-white',
    };

    $base = 'inline-flex items-center gap-1.5 px-4 py-2 rounded-lg text-sm font-medium transition-colors focus:outline-none focus:ring-2 focus:ring-violet-500 focus:ring-offset-2 focus:ring-offset-slate-900';
@endphp

@if($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => "$base $classes"]) }}>{{ $slot }}</a>
@else
    <button type="{{ $type }}" {{ $attributes->merge(['class' => "$base $classes"]) }}>{{ $slot }}</button>
@endif
```

- [ ] **Step 5: Create form-field component**

Create `resources/views/components/form-field.blade.php`:
```blade
@props(['label', 'name', 'required' => false])

<div {{ $attributes->merge(['class' => 'space-y-1']) }}>
    <label for="{{ $name }}"
           class="block text-sm font-medium text-slate-300">
        {{ $label }}
        @if($required)
            <span class="text-red-400">*</span>
        @endif
    </label>

    {{ $slot }}

    @error($name)
        <p class="text-xs text-red-400">{{ $message }}</p>
    @enderror
</div>
```

- [ ] **Step 6: Create delete-form component**

Create `resources/views/components/delete-form.blade.php`:
```blade
@props(['action', 'confirmMessage' => 'Tem certeza que deseja excluir?'])

<form method="POST" action="{{ $action }}"
      onsubmit="return confirm('{{ $confirmMessage }}')">
    @csrf
    @method('DELETE')
    {{ $slot }}
</form>
```

- [ ] **Step 7: Create task-card component**

Create `resources/views/components/task-card.blade.php`:
```blade
@props(['task'])

<div class="bg-slate-800 border border-slate-700 rounded-xl px-4 py-3 flex items-start gap-3 transition-opacity
            {{ $task->isCompleted() ? 'opacity-60' : '' }}">

    {{-- Toggle checkbox --}}
    <form method="POST" action="{{ route('tasks.toggle', $task) }}" class="mt-0.5 flex-shrink-0">
        @csrf
        @method('PATCH')
        <button type="submit"
                class="w-5 h-5 rounded border-2 flex items-center justify-center transition-colors
                       {{ $task->isCompleted()
                          ? 'bg-violet-700 border-violet-700'
                          : 'border-violet-500 hover:border-violet-400' }}">
            @if($task->isCompleted())
                <svg class="w-3 h-3 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
                </svg>
            @endif
        </button>
    </form>

    {{-- Content --}}
    <div class="flex-1 min-w-0">
        <p class="text-sm text-slate-100 truncate
                  {{ $task->isCompleted() ? 'line-through text-slate-500' : '' }}">
            {{ $task->title }}
        </p>

        @if($task->description)
            <p class="text-xs text-slate-500 mt-0.5 truncate">{{ $task->description }}</p>
        @endif

        <div class="flex flex-wrap items-center gap-1.5 mt-1.5">
            {{-- Due date --}}
            @if($task->due_date)
                <span class="text-xs {{ $task->isOverdue() ? 'text-red-400' : 'text-slate-500' }}">
                    {{ $task->isOverdue() ? '⚠️ ' : '📅 ' }}{{ $task->due_date->format('d/m/Y') }}
                </span>
            @endif
        </div>
    </div>

    {{-- Badges --}}
    <div class="flex items-center gap-1.5 flex-shrink-0">
        <x-priority-badge :priority="$task->priority" />

        @if($task->category)
            <x-badge :color="$task->category->color">
                {{ $task->category->icon }} {{ $task->category->name }}
            </x-badge>
        @endif
    </div>

    {{-- Actions --}}
    <div class="flex items-center gap-1 flex-shrink-0 ml-1">
        <x-button href="{{ route('tasks.edit', $task) }}" variant="secondary"
                  class="!px-2 !py-1 text-xs">
            ✏️
        </x-button>

        <x-delete-form :action="route('tasks.destroy', $task)"
                       confirmMessage="Excluir a tarefa '{{ $task->title }}'?">
            <x-button type="submit" variant="danger" class="!px-2 !py-1 text-xs">
                🗑️
            </x-button>
        </x-delete-form>
    </div>
</div>
```

- [ ] **Step 8: Commit**

```bash
git add resources/views/components/
git commit -m "feat: add 7 reusable Blade x-components (sidebar, badge, button, form-field, task-card, etc)"
```

---

## Task 12: App Layout (Dark Sidebar)

**Files:**
- Modify: `resources/views/layouts/app.blade.php`

- [ ] **Step 1: Rewrite app layout with sidebar**

Replace `resources/views/layouts/app.blade.php`:
```blade
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Task Manager') }}</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased bg-slate-900 text-slate-100">

    <div class="min-h-screen flex">

        {{-- Sidebar --}}
        <x-sidebar />

        {{-- Main content --}}
        <div class="flex-1 ml-56">

            {{-- Flash messages --}}
            @if(session('success'))
                <div class="bg-green-900/40 border-b border-green-800 text-green-300 text-sm px-6 py-3">
                    ✅ {{ session('success') }}
                </div>
            @endif

            @if(session('error'))
                <div class="bg-red-900/40 border-b border-red-800 text-red-300 text-sm px-6 py-3">
                    ❌ {{ session('error') }}
                </div>
            @endif

            {{-- Page header slot --}}
            @isset($header)
                <header class="bg-slate-800/50 border-b border-slate-700 px-6 py-4">
                    {{ $header }}
                </header>
            @endisset

            {{-- Page content --}}
            <main class="p-6">
                {{ $slot }}
            </main>
        </div>
    </div>
</body>
</html>
```

- [ ] **Step 2: Commit**

```bash
git add resources/views/layouts/app.blade.php
git commit -m "feat: update app layout with dark sidebar, flash messages, and header slot"
```

---

## Task 13: Task Views

**Files:**
- Create: `resources/views/tasks/index.blade.php`
- Create: `resources/views/tasks/create.blade.php`
- Create: `resources/views/tasks/edit.blade.php`

- [ ] **Step 1: Create tasks/index view**

```bash
mkdir -p resources/views/tasks
```

Create `resources/views/tasks/index.blade.php`:
```blade
<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h1 class="text-xl font-semibold text-slate-100">
                @if(request('status') === 'completed')
                    ✅ Tarefas Concluídas
                @elseif(request('category'))
                    📋 Tarefas por Categoria
                @else
                    📋 Todas as Tarefas
                @endif
            </h1>
            <x-button href="{{ route('tasks.create') }}">
                + Nova Tarefa
            </x-button>
        </div>
    </x-slot>

    {{-- Filter bar --}}
    <form method="GET" action="{{ route('tasks.index') }}" class="flex flex-wrap gap-3 mb-6">
        <select name="priority"
                class="bg-slate-800 border border-slate-600 text-slate-300 text-sm rounded-lg px-3 py-2 focus:ring-violet-500 focus:border-violet-500"
                onchange="this.form.submit()">
            <option value="">Todas as prioridades</option>
            <option value="high"   {{ request('priority') === 'high'   ? 'selected' : '' }}>Alta</option>
            <option value="medium" {{ request('priority') === 'medium' ? 'selected' : '' }}>Média</option>
            <option value="low"    {{ request('priority') === 'low'    ? 'selected' : '' }}>Baixa</option>
        </select>

        <select name="status"
                class="bg-slate-800 border border-slate-600 text-slate-300 text-sm rounded-lg px-3 py-2 focus:ring-violet-500 focus:border-violet-500"
                onchange="this.form.submit()">
            <option value="">Todos os status</option>
            <option value="pending"   {{ request('status') === 'pending'   ? 'selected' : '' }}>Pendente</option>
            <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Concluída</option>
        </select>

        @if(request()->hasAny(['priority', 'status', 'category']))
            <a href="{{ route('tasks.index') }}"
               class="text-sm text-violet-400 hover:text-violet-300 self-center">
                × Limpar filtros
            </a>
        @endif
    </form>

    {{-- Task list --}}
    @if($tasks->isEmpty())
        <div class="text-center py-16">
            <p class="text-slate-500 text-lg">Nenhuma tarefa encontrada.</p>
            <p class="text-slate-600 text-sm mt-1">
                <a href="{{ route('tasks.create') }}" class="text-violet-400 hover:underline">Criar uma tarefa</a>
            </p>
        </div>
    @else
        <div class="space-y-2">
            @foreach($tasks as $task)
                <x-task-card :task="$task" />
            @endforeach
        </div>

        <p class="text-slate-600 text-xs mt-4">
            {{ $tasks->count() }} tarefa(s) exibida(s)
        </p>
    @endif
</x-app-layout>
```

- [ ] **Step 2: Create tasks/create view**

Create `resources/views/tasks/create.blade.php`:
```blade
<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <a href="{{ route('tasks.index') }}" class="text-slate-500 hover:text-slate-300">←</a>
            <h1 class="text-xl font-semibold text-slate-100">Nova Tarefa</h1>
        </div>
    </x-slot>

    <div class="max-w-2xl">
        <form method="POST" action="{{ route('tasks.store') }}" class="space-y-5">
            @csrf

            <x-form-field label="Título" name="title" :required="true">
                <input type="text"
                       id="title"
                       name="title"
                       value="{{ old('title') }}"
                       placeholder="Ex: Revisar proposta do cliente..."
                       class="w-full bg-slate-800 border border-slate-600 text-slate-100 rounded-lg px-3 py-2 text-sm
                              focus:ring-violet-500 focus:border-violet-500 placeholder-slate-500
                              @error('title') border-red-500 @enderror">
            </x-form-field>

            <x-form-field label="Descrição" name="description">
                <textarea id="description"
                          name="description"
                          rows="3"
                          placeholder="Detalhes opcionais..."
                          class="w-full bg-slate-800 border border-slate-600 text-slate-100 rounded-lg px-3 py-2 text-sm
                                 focus:ring-violet-500 focus:border-violet-500 placeholder-slate-500
                                 @error('description') border-red-500 @enderror">{{ old('description') }}</textarea>
            </x-form-field>

            <div class="grid grid-cols-2 gap-4">
                <x-form-field label="Categoria" name="category_id" :required="true">
                    <select id="category_id"
                            name="category_id"
                            class="w-full bg-slate-800 border border-slate-600 text-slate-300 rounded-lg px-3 py-2 text-sm
                                   focus:ring-violet-500 focus:border-violet-500
                                   @error('category_id') border-red-500 @enderror">
                        <option value="">Selecione...</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}"
                                    {{ old('category_id') == $category->id ? 'selected' : '' }}>
                                {{ $category->icon }} {{ $category->name }}
                            </option>
                        @endforeach
                    </select>
                </x-form-field>

                <x-form-field label="Prioridade" name="priority" :required="true">
                    <select id="priority"
                            name="priority"
                            class="w-full bg-slate-800 border border-slate-600 text-slate-300 rounded-lg px-3 py-2 text-sm
                                   focus:ring-violet-500 focus:border-violet-500
                                   @error('priority') border-red-500 @enderror">
                        <option value="low"    {{ old('priority', 'medium') === 'low'    ? 'selected' : '' }}>Baixa</option>
                        <option value="medium" {{ old('priority', 'medium') === 'medium' ? 'selected' : '' }}>Média</option>
                        <option value="high"   {{ old('priority', 'medium') === 'high'   ? 'selected' : '' }}>Alta</option>
                    </select>
                </x-form-field>
            </div>

            <x-form-field label="Data de Vencimento" name="due_date">
                <input type="date"
                       id="due_date"
                       name="due_date"
                       value="{{ old('due_date') }}"
                       min="{{ now()->toDateString() }}"
                       class="bg-slate-800 border border-slate-600 text-slate-300 rounded-lg px-3 py-2 text-sm
                              focus:ring-violet-500 focus:border-violet-500
                              @error('due_date') border-red-500 @enderror">
            </x-form-field>

            <div class="flex justify-end gap-3 pt-2">
                <x-button href="{{ route('tasks.index') }}" variant="secondary">
                    Cancelar
                </x-button>
                <x-button type="submit">
                    Salvar Tarefa
                </x-button>
            </div>
        </form>
    </div>
</x-app-layout>
```

- [ ] **Step 3: Create tasks/edit view**

Create `resources/views/tasks/edit.blade.php`:
```blade
<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <a href="{{ route('tasks.index') }}" class="text-slate-500 hover:text-slate-300">←</a>
            <h1 class="text-xl font-semibold text-slate-100">Editar Tarefa</h1>
        </div>
    </x-slot>

    <div class="max-w-2xl">
        <form method="POST" action="{{ route('tasks.update', $task) }}" class="space-y-5">
            @csrf
            @method('PUT')

            <x-form-field label="Título" name="title" :required="true">
                <input type="text"
                       id="title"
                       name="title"
                       value="{{ old('title', $task->title) }}"
                       class="w-full bg-slate-800 border border-slate-600 text-slate-100 rounded-lg px-3 py-2 text-sm
                              focus:ring-violet-500 focus:border-violet-500
                              @error('title') border-red-500 @enderror">
            </x-form-field>

            <x-form-field label="Descrição" name="description">
                <textarea id="description"
                          name="description"
                          rows="3"
                          class="w-full bg-slate-800 border border-slate-600 text-slate-100 rounded-lg px-3 py-2 text-sm
                                 focus:ring-violet-500 focus:border-violet-500
                                 @error('description') border-red-500 @enderror">{{ old('description', $task->description) }}</textarea>
            </x-form-field>

            <div class="grid grid-cols-2 gap-4">
                <x-form-field label="Categoria" name="category_id" :required="true">
                    <select id="category_id"
                            name="category_id"
                            class="w-full bg-slate-800 border border-slate-600 text-slate-300 rounded-lg px-3 py-2 text-sm
                                   focus:ring-violet-500 focus:border-violet-500
                                   @error('category_id') border-red-500 @enderror">
                        <option value="">Selecione...</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}"
                                    {{ old('category_id', $task->category_id) == $category->id ? 'selected' : '' }}>
                                {{ $category->icon }} {{ $category->name }}
                            </option>
                        @endforeach
                    </select>
                </x-form-field>

                <x-form-field label="Prioridade" name="priority" :required="true">
                    <select id="priority"
                            name="priority"
                            class="w-full bg-slate-800 border border-slate-600 text-slate-300 rounded-lg px-3 py-2 text-sm
                                   focus:ring-violet-500 focus:border-violet-500
                                   @error('priority') border-red-500 @enderror">
                        <option value="low"    {{ old('priority', $task->priority) === 'low'    ? 'selected' : '' }}>Baixa</option>
                        <option value="medium" {{ old('priority', $task->priority) === 'medium' ? 'selected' : '' }}>Média</option>
                        <option value="high"   {{ old('priority', $task->priority) === 'high'   ? 'selected' : '' }}>Alta</option>
                    </select>
                </x-form-field>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <x-form-field label="Status" name="status" :required="true">
                    <select id="status"
                            name="status"
                            class="w-full bg-slate-800 border border-slate-600 text-slate-300 rounded-lg px-3 py-2 text-sm
                                   focus:ring-violet-500 focus:border-violet-500
                                   @error('status') border-red-500 @enderror">
                        <option value="pending"   {{ old('status', $task->status) === 'pending'   ? 'selected' : '' }}>Pendente</option>
                        <option value="completed" {{ old('status', $task->status) === 'completed' ? 'selected' : '' }}>Concluída</option>
                    </select>
                </x-form-field>

                <x-form-field label="Data de Vencimento" name="due_date">
                    <input type="date"
                           id="due_date"
                           name="due_date"
                           value="{{ old('due_date', $task->due_date?->toDateString()) }}"
                           class="w-full bg-slate-800 border border-slate-600 text-slate-300 rounded-lg px-3 py-2 text-sm
                                  focus:ring-violet-500 focus:border-violet-500
                                  @error('due_date') border-red-500 @enderror">
                </x-form-field>
            </div>

            <div class="flex justify-between pt-2">
                <x-delete-form :action="route('tasks.destroy', $task)"
                               confirmMessage="Excluir a tarefa '{{ $task->title }}'?">
                    <x-button type="submit" variant="danger">
                        🗑️ Excluir Tarefa
                    </x-button>
                </x-delete-form>

                <div class="flex gap-3">
                    <x-button href="{{ route('tasks.index') }}" variant="secondary">
                        Cancelar
                    </x-button>
                    <x-button type="submit">
                        Salvar Alterações
                    </x-button>
                </div>
            </div>
        </form>
    </div>
</x-app-layout>
```

- [ ] **Step 4: Commit**

```bash
git add resources/views/tasks/
git commit -m "feat: add task views (index with filters, create, edit)"
```

---

## Task 14: Category Views

**Files:**
- Create: `resources/views/categories/index.blade.php`
- Create: `resources/views/categories/create.blade.php`
- Create: `resources/views/categories/edit.blade.php`

- [ ] **Step 1: Create categories/index view**

```bash
mkdir -p resources/views/categories
```

Create `resources/views/categories/index.blade.php`:
```blade
<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h1 class="text-xl font-semibold text-slate-100">📁 Categorias</h1>
            <x-button href="{{ route('categories.create') }}">
                + Nova Categoria
            </x-button>
        </div>
    </x-slot>

    @if($categories->isEmpty())
        <div class="text-center py-16">
            <p class="text-slate-500 text-lg">Nenhuma categoria criada ainda.</p>
            <p class="text-slate-600 text-sm mt-1">
                <a href="{{ route('categories.create') }}" class="text-violet-400 hover:underline">
                    Criar sua primeira categoria
                </a>
            </p>
        </div>
    @else
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
            @foreach($categories as $category)
                <div class="bg-slate-800 border border-slate-700 rounded-xl p-5 flex flex-col gap-3">
                    {{-- Icon & Name --}}
                    <div class="flex flex-col items-center text-center gap-2">
                        <span class="text-3xl">{{ $category->icon }}</span>
                        <h3 class="font-semibold text-slate-100">{{ $category->name }}</h3>
                        <div class="w-full h-1 rounded-full" style="background-color: {{ $category->color }}"></div>
                    </div>

                    {{-- Task count --}}
                    <p class="text-center text-xs text-slate-500">
                        {{ $category->tasks_count }} tarefa(s)
                    </p>

                    {{-- Actions --}}
                    <div class="flex justify-center gap-2">
                        <x-button href="{{ route('categories.edit', $category) }}" variant="secondary"
                                  class="!px-3 !py-1.5 text-xs">
                            ✏️ Editar
                        </x-button>

                        <x-delete-form :action="route('categories.destroy', $category)"
                                       confirmMessage="Excluir a categoria '{{ $category->name }}'? Tarefas associadas ficarão sem categoria.">
                            <x-button type="submit" variant="danger" class="!px-3 !py-1.5 text-xs">
                                🗑️
                            </x-button>
                        </x-delete-form>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</x-app-layout>
```

- [ ] **Step 2: Create categories/create view**

Create `resources/views/categories/create.blade.php`:
```blade
<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <a href="{{ route('categories.index') }}" class="text-slate-500 hover:text-slate-300">←</a>
            <h1 class="text-xl font-semibold text-slate-100">Nova Categoria</h1>
        </div>
    </x-slot>

    <div class="max-w-md">
        <form method="POST" action="{{ route('categories.store') }}" class="space-y-5">
            @csrf

            <x-form-field label="Nome" name="name" :required="true">
                <input type="text"
                       id="name"
                       name="name"
                       value="{{ old('name') }}"
                       placeholder="Ex: Trabalho, Pessoal, Estudo..."
                       class="w-full bg-slate-800 border border-slate-600 text-slate-100 rounded-lg px-3 py-2 text-sm
                              focus:ring-violet-500 focus:border-violet-500 placeholder-slate-500
                              @error('name') border-red-500 @enderror">
            </x-form-field>

            <div class="grid grid-cols-2 gap-4">
                <x-form-field label="Cor" name="color" :required="true">
                    <div class="flex items-center gap-2">
                        <input type="color"
                               id="color"
                               name="color"
                               value="{{ old('color', '#7c3aed') }}"
                               class="h-10 w-14 rounded border border-slate-600 bg-slate-800 cursor-pointer p-0.5
                                      @error('color') border-red-500 @enderror">
                        <span class="text-xs text-slate-500">Clique para escolher</span>
                    </div>
                </x-form-field>

                <x-form-field label="Ícone (emoji)" name="icon" :required="true">
                    <input type="text"
                           id="icon"
                           name="icon"
                           value="{{ old('icon', '📁') }}"
                           maxlength="10"
                           placeholder="📁"
                           class="w-full bg-slate-800 border border-slate-600 text-slate-100 rounded-lg px-3 py-2 text-sm
                                  focus:ring-violet-500 focus:border-violet-500
                                  @error('icon') border-red-500 @enderror">
                </x-form-field>
            </div>

            <div class="flex justify-end gap-3 pt-2">
                <x-button href="{{ route('categories.index') }}" variant="secondary">
                    Cancelar
                </x-button>
                <x-button type="submit">
                    Salvar Categoria
                </x-button>
            </div>
        </form>
    </div>
</x-app-layout>
```

- [ ] **Step 3: Create categories/edit view**

Create `resources/views/categories/edit.blade.php`:
```blade
<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <a href="{{ route('categories.index') }}" class="text-slate-500 hover:text-slate-300">←</a>
            <h1 class="text-xl font-semibold text-slate-100">Editar Categoria</h1>
        </div>
    </x-slot>

    <div class="max-w-md">
        <form method="POST" action="{{ route('categories.update', $category) }}" class="space-y-5">
            @csrf
            @method('PUT')

            <x-form-field label="Nome" name="name" :required="true">
                <input type="text"
                       id="name"
                       name="name"
                       value="{{ old('name', $category->name) }}"
                       class="w-full bg-slate-800 border border-slate-600 text-slate-100 rounded-lg px-3 py-2 text-sm
                              focus:ring-violet-500 focus:border-violet-500
                              @error('name') border-red-500 @enderror">
            </x-form-field>

            <div class="grid grid-cols-2 gap-4">
                <x-form-field label="Cor" name="color" :required="true">
                    <div class="flex items-center gap-2">
                        <input type="color"
                               id="color"
                               name="color"
                               value="{{ old('color', $category->color) }}"
                               class="h-10 w-14 rounded border border-slate-600 bg-slate-800 cursor-pointer p-0.5
                                      @error('color') border-red-500 @enderror">
                        <span class="text-xs text-slate-500">{{ old('color', $category->color) }}</span>
                    </div>
                </x-form-field>

                <x-form-field label="Ícone (emoji)" name="icon" :required="true">
                    <input type="text"
                           id="icon"
                           name="icon"
                           value="{{ old('icon', $category->icon) }}"
                           maxlength="10"
                           class="w-full bg-slate-800 border border-slate-600 text-slate-100 rounded-lg px-3 py-2 text-sm
                                  focus:ring-violet-500 focus:border-violet-500
                                  @error('icon') border-red-500 @enderror">
                </x-form-field>
            </div>

            <div class="flex justify-between pt-2">
                <x-delete-form :action="route('categories.destroy', $category)"
                               confirmMessage="Excluir '{{ $category->name }}'? Tarefas associadas ficarão sem categoria.">
                    <x-button type="submit" variant="danger">
                        🗑️ Excluir
                    </x-button>
                </x-delete-form>

                <div class="flex gap-3">
                    <x-button href="{{ route('categories.index') }}" variant="secondary">
                        Cancelar
                    </x-button>
                    <x-button type="submit">
                        Salvar Alterações
                    </x-button>
                </div>
            </div>
        </form>
    </div>
</x-app-layout>
```

- [ ] **Step 4: Commit**

```bash
git add resources/views/categories/
git commit -m "feat: add category views (index grid, create, edit)"
```

---

## Task 15: Auth Views Dark Theme

**Files:**
- Modify: `resources/views/auth/login.blade.php`
- Modify: `resources/views/auth/register.blade.php`
- Modify: `resources/views/layouts/guest.blade.php`

- [ ] **Step 1: Update guest layout for dark theme**

Edit `resources/views/layouts/guest.blade.php`. Find the `<body>` tag and update:
```html
<body class="font-sans text-slate-100 antialiased bg-slate-900">
```

Find the card container (usually `<div class="w-full sm:max-w-md ...">`) and ensure it uses dark classes:
```html
<div class="w-full sm:max-w-md mt-6 px-6 py-8 bg-slate-800 border border-slate-700 shadow-md overflow-hidden sm:rounded-xl">
```

Update the brand heading inside guest layout:
```html
<a href="/" class="text-violet-400 font-bold text-2xl">⚡ TaskManager</a>
```

- [ ] **Step 2: Update login view inputs**

Edit `resources/views/auth/login.blade.php`. Find all input fields and update their classes to dark variants:
```blade
{{-- Email field input class --}}
class="block mt-1 w-full bg-slate-700 border-slate-600 text-slate-100 rounded-lg
       focus:ring-violet-500 focus:border-violet-500"

{{-- Password field input class --}}
class="block mt-1 w-full bg-slate-700 border-slate-600 text-slate-100 rounded-lg
       focus:ring-violet-500 focus:border-violet-500"
```

Find the submit button and update:
```blade
class="inline-flex items-center px-4 py-2 bg-violet-700 border border-transparent
       rounded-lg font-semibold text-xs text-white uppercase tracking-widest
       hover:bg-violet-600 focus:ring-violet-500 transition ease-in-out duration-150"
```

Update labels:
```blade
class="block font-medium text-sm text-slate-300"
```

- [ ] **Step 3: Update register view inputs**

Edit `resources/views/auth/register.blade.php`. Apply the same input class updates as login:
- All `<input>` elements: `bg-slate-700 border-slate-600 text-slate-100`
- All `<label>` elements: `text-slate-300`
- Submit button: `bg-violet-700 hover:bg-violet-600`

- [ ] **Step 4: Rebuild assets and verify auth pages**

```bash
docker compose run --rm app npm run build
```

Open `http://localhost:8080/login` — should show dark login form with violet accents.
Open `http://localhost:8080/register` — same dark theme.

- [ ] **Step 5: Commit**

```bash
git add resources/views/auth/ resources/views/layouts/guest.blade.php
git commit -m "feat: apply dark theme to auth views (login, register)"
```

---

## Task 16: End-to-End Verification

- [ ] **Step 1: Fresh migrate and seed**

```bash
docker compose run --rm app php artisan migrate:fresh --seed
```

Expected: all migrations + seeders run successfully.

- [ ] **Step 2: Verify containers are running**

```bash
docker compose ps
```

Expected: `taskmanager_app` and `taskmanager_nginx` both `running`.

- [ ] **Step 3: Manual verification checklist**

Open `http://localhost:8080` and verify:

**Auth:**
- [ ] Redirect to `/login` when not authenticated
- [ ] Register new user at `/register`
- [ ] Login with `demo@taskmanager.com` / `password`
- [ ] Logout works

**Dashboard:**
- [ ] Sidebar shows categories with colored dots
- [ ] All 8 seeded tasks appear in correct order (pending first, high priority first)
- [ ] Completed task appears greyed out with strikethrough
- [ ] Overdue task shows ⚠️ warning

**Task CRUD:**
- [ ] Create task — validation errors shown for missing required fields
- [ ] Create task — success flash message appears
- [ ] Edit task — form pre-filled with existing values
- [ ] Toggle status via checkbox — page reloads with updated state
- [ ] Delete task — confirmation dialog appears, task removed

**Category CRUD:**
- [ ] Create category — color picker and emoji input work
- [ ] Edit category — form pre-filled
- [ ] Delete category — tasks remain but lose category association
- [ ] Sidebar updates automatically after category changes

**Filters:**
- [ ] Filter by priority works
- [ ] Filter by status works
- [ ] Click category in sidebar → filters tasks by that category
- [ ] "Limpar filtros" resets all filters

**Security:**
- [ ] Create second user via register
- [ ] Attempt to access `/tasks/1/edit` as second user → 403 Forbidden
- [ ] Attempt to access `/categories/1/edit` as second user → 403 Forbidden

- [ ] **Step 4: Final commit**

```bash
git add .
git commit -m "feat: complete Task Manager portfolio app with full CRUD, dark mode, sidebar, Docker"
```

---

## Setup Commands Reference

```bash
# First time setup
docker compose build
docker compose up -d
docker compose run --rm app composer install
docker compose run --rm app cp .env.example .env
docker compose run --rm app php artisan key:generate
docker compose run --rm app php artisan migrate --seed
npm install && npm run build   # or run inside container

# Access the app
open http://localhost:8080

# Demo credentials
Email: demo@taskmanager.com
Password: password

# Reset database
docker compose run --rm app php artisan migrate:fresh --seed

# Stop
docker compose down
```
