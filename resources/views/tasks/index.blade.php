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