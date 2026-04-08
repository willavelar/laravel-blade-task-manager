<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCategoryRequest;
use App\Http\Requests\UpdateCategoryRequest;
use App\Models\Category;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class CategoryController extends Controller
{
    public function index(): View
    {
        $categories = auth()->user()
            ->categories()
            ->withCount('tasks')
            ->orderBy('name')
            ->get();

        return view('categories.index', compact('categories'));
    }

    public function create(): View
    {
        return view('categories.create');
    }

    public function store(StoreCategoryRequest $request): RedirectResponse
    {
        auth()->user()->categories()->create($request->validated());

        return redirect()
            ->route('categories.index')
            ->with('success', 'Categoria criada com sucesso!');
    }

    public function edit(Category $category): View
    {
        $this->authorizeCategory($category);

        return view('categories.edit', compact('category'));
    }

    public function update(UpdateCategoryRequest $request, Category $category): RedirectResponse
    {
        $this->authorizeCategory($category);

        $category->update($request->validated());

        return redirect()
            ->route('categories.index')
            ->with('success', 'Categoria atualizada com sucesso!');
    }

    public function destroy(Category $category): RedirectResponse
    {
        $this->authorizeCategory($category);

        $category->delete();

        return redirect()
            ->route('categories.index')
            ->with('success', 'Categoria removida. Tarefas associadas ficaram sem categoria.');
    }

    private function authorizeCategory(Category $category): void
    {
        abort_if($category->user_id !== auth()->id(), 403);
    }
}
