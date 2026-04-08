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