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
