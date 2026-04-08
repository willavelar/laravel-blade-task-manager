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