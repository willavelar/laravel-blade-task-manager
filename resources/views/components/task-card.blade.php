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
                       confirmMessage="Excluir a tarefa '{{ addslashes($task->title) }}'?">
            <x-button type="submit" variant="danger" class="!px-2 !py-1 text-xs">
                🗑️
            </x-button>
        </x-delete-form>
    </div>
</div>