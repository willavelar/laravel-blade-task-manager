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
                               confirmMessage="Excluir a tarefa '{{ addslashes($task->title) }}'?">
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