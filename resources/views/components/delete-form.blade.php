@props(['action', 'confirmMessage' => 'Tem certeza que deseja excluir?'])

<form method="POST" action="{{ $action }}"
      onsubmit="return confirm('{{ $confirmMessage }}')">
    @csrf
    @method('DELETE')
    {{ $slot }}
</form>