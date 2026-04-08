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
                    <div class="flex flex-col items-center text-center gap-2">
                        <span class="text-3xl">{{ $category->icon }}</span>
                        <h3 class="font-semibold text-slate-100">{{ $category->name }}</h3>
                        <div class="w-full h-1 rounded-full" style="background-color: {{ $category->color }}"></div>
                    </div>

                    <p class="text-center text-xs text-slate-500">
                        {{ $category->tasks_count }} tarefa(s)
                    </p>

                    <div class="flex justify-center gap-2">
                        <x-button href="{{ route('categories.edit', $category) }}" variant="secondary"
                                  class="!px-3 !py-1.5 text-xs">
                            ✏️ Editar
                        </x-button>

                        <x-delete-form :action="route('categories.destroy', $category)"
                                       confirmMessage="Excluir a categoria '{{ addslashes($category->name) }}'? Tarefas associadas ficarão sem categoria.">
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