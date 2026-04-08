@php
    $categories = auth()->user()->categories()->orderBy('name')->get();
    $navLinks = [
        ['route' => 'tasks.index', 'label' => 'Todas as Tarefas', 'icon' => '📋', 'params' => []],
        ['route' => 'tasks.index', 'label' => 'Concluídas', 'icon' => '✅', 'params' => ['status' => 'completed']],
        ['route' => 'categories.index', 'label' => 'Categorias', 'icon' => '📁', 'params' => []],
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