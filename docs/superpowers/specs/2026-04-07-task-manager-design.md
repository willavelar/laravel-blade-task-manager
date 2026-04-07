# Gerenciador de Tarefas Pessoais — Design Spec

**Data:** 2026-04-07
**Status:** Aprovado
**Objetivo:** Aplicação portfolio demonstrando boas práticas Laravel 13.4.0 com Blade

---

## 1. Visão Geral

Aplicação web de gerenciamento de tarefas pessoais com categorias. Foco em demonstrar:
- CRUD completo com Resource Controllers
- Relacionamentos Eloquent (Task → Category → User)
- Validação desacoplada com Form Requests
- Autenticação com Laravel Breeze
- Componentização com Blade x-components
- Containerização com Docker

**Usuário-alvo:** Avaliadores de portfolio técnico Laravel.

---

## 2. Stack

| Camada | Tecnologia |
|---|---|
| Framework | Laravel 13.4.0 |
| PHP | 8.3 |
| Frontend | Blade + Tailwind CSS (via Breeze) |
| Banco de dados | SQLite |
| Autenticação | Laravel Breeze (sessão + Blade) |
| Interatividade | Nenhuma (MVC puro, full page reloads) |
| Containerização | Docker (php-fpm + nginx) |

---

## 3. Modelos e Relacionamentos

### User (gerado pelo Breeze)
- `id`, `name`, `email`, `password`, `timestamps`
- `hasMany` → Category
- `hasMany` → Task

### Category
```
id           bigint PK
user_id      FK → users.id (cascade delete)
name         string(100)
color        string(7)   — hex: #3b82f6
icon         string(10)  — emoji: 💼
timestamps
```
- `belongsTo` → User
- `hasMany` → Task

### Task
```
id           bigint PK
user_id      FK → users.id (cascade delete)
category_id  FK → categories.id (nullable, set null on delete)
title        string(255)
description  text nullable
priority     enum: low | medium | high
status       enum: pending | completed
due_date     date nullable
timestamps
```
- `belongsTo` → User
- `belongsTo` → Category

---

## 4. Arquitetura de Controllers

### TaskController (Resource)
| Método | Rota | Ação |
|---|---|---|
| GET | /tasks | index — lista todas as tarefas do usuário |
| GET | /tasks/create | create — formulário de criação |
| POST | /tasks | store — salva nova tarefa |
| GET | /tasks/{task}/edit | edit — formulário de edição |
| PUT | /tasks/{task} | update — atualiza tarefa |
| DELETE | /tasks/{task} | destroy — exclui tarefa |
| PATCH | /tasks/{task}/toggle | toggle — alterna status pending/completed |

### CategoryController (Resource)
| Método | Rota | Ação |
|---|---|---|
| GET | /categories | index — lista categorias em grid de cards |
| GET | /categories/create | create — formulário de criação |
| POST | /categories | store — salva nova categoria |
| GET | /categories/{category}/edit | edit — formulário de edição |
| PUT | /categories/{category} | update — atualiza categoria |
| DELETE | /categories/{category} | destroy — exclui categoria (tarefas ficam sem categoria) |

Todas as rotas dentro de `Route::middleware('auth')`.

---

## 5. Form Requests e Validações

### StoreTaskRequest / UpdateTaskRequest
```php
'title'       => 'required|string|max:255',
'description' => 'nullable|string',
'category_id' => 'required|exists:categories,id',  // validado como do usuário
'priority'    => 'required|in:low,medium,high',
'due_date'    => 'nullable|date|after_or_equal:today',  // apenas no Store
'status'      => 'sometimes|in:pending,completed',       // apenas no Update
```
Autorização: `auth()->user()->categories()->where('id', $categoryId)->exists()`

### StoreCategoryRequest / UpdateCategoryRequest
```php
'name'  => 'required|string|max:100',
'color' => 'required|regex:/^#[0-9A-Fa-f]{6}$/',
'icon'  => 'required|string|max:10',
```
Unicidade de nome por usuário validada via regra `unique` com escopo.

---

## 6. Componentes Blade (x-components)

| Componente | Descrição |
|---|---|
| `<x-app-layout>` | Layout raiz com slot para sidebar + conteúdo (gerado pelo Breeze, adaptado) |
| `<x-sidebar>` | Navegação lateral com links e lista de categorias do usuário |
| `<x-task-card>` | Card de tarefa com checkbox, título, badges, ações editar/excluir |
| `<x-badge>` | Badge colorido genérico (prioridade, categoria, status) |
| `<x-priority-badge>` | Badge especializado com cor por prioridade (low=verde, medium=amarelo, high=vermelho) |
| `<x-form-field>` | Label + input/select/textarea com exibição de erro de validação |
| `<x-button>` | Botão com variantes (primary, secondary, danger) |
| `<x-delete-form>` | Form POST com method DELETE e confirmação via `confirm()` nativo do browser |

---

## 7. Visual

- **Tema:** Dark mode
- **Paleta:** Fundo `#0f172a` (slate-900), cards `#1e293b` (slate-800), bordas `#334155` (slate-700)
- **Acento:** Roxo `#7c3aed` (violet-700) para botões primários e destaques
- **Tipografia:** Tailwind CSS padrão (Inter / sistema)
- **Layout:** Sidebar fixa à esquerda (160px), conteúdo principal à direita

### Telas principais
1. **tasks.index** — lista de tarefas com filtros (status, prioridade via query string), botão Nova Tarefa
2. **tasks.create / tasks.edit** — formulário com todos os campos, select de categoria e prioridade
3. **categories.index** — grid de cards com ícone, nome, cor e contagem de tarefas
4. **categories.create / categories.edit** — formulário com color picker e campo de ícone (emoji)
5. **auth (login/register)** — páginas padrão do Breeze com tema dark customizado

---

## 8. Segurança

- Todas as queries filtradas por `auth()->id()` — sem vazamento entre usuários
- Form Requests verificam que `category_id` pertence ao usuário autenticado
- CSRF em todos os forms (padrão Laravel)
- Cascade delete: excluir usuário remove suas categorias e tarefas

---

## 9. Docker

### Estrutura de containers
```yaml
services:
  app:
    build: ./docker/php
    image: php:8.3-fpm
    volumes: [.:/var/www/html]

  nginx:
    image: nginx:alpine
    ports: ["8080:80"]
    depends_on: [app]
```

SQLite armazenado em `database/database.sqlite` (volume do container `app`).

### Comandos de setup
```bash
docker compose up -d
docker compose exec app composer install
docker compose exec app cp .env.example .env
docker compose exec app php artisan key:generate
docker compose exec app php artisan migrate --seed
```

Acesso: `http://localhost:8080`

---

## 10. Seeders (para demo do portfolio)

- `UserSeeder` — cria usuário demo: `demo@taskmanager.com` / `password`
- `CategorySeeder` — cria 3 categorias padrão (Trabalho 💼, Pessoal 🏠, Estudo 📚)
- `TaskSeeder` — cria 8–10 tarefas distribuídas entre categorias e prioridades

---

## 11. Fora do Escopo

- Notificações / e-mail
- API REST
- Testes automatizados
- Upload de arquivos
- Subtarefas
- Compartilhamento entre usuários
- JavaScript / Alpine.js / Livewire
